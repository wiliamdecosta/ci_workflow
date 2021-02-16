<?php

/**
 * P_job_wf Model
 *
 */
class P_job_wf extends Abstract_model {

    public $table           = "p_job_wf";
    public $pkey            = "job_wf_id";
    public $alias           = "pj";

    public $fields          = array(
                                'job_wf_id'     => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Job Wf ID'),
                                'role_id'             => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Role'),
                                'job_wf_name'       => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Nama Pekerjaan'),
                                'file_pekerjaan'     => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'File Pekerjaan'),
                                
                                'f_after_submit'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Fungsi Setelah Submit'),
                                'f_after_reject'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Fungsi Setelah Reject'),
                                'f_after_send_back'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Fungsi Setelah Send Back'),
                                
                                'created_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'      => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'        => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By'),
                            );

    public $selectClause    = "pj.*, role.role_name";
    public $fromClause      = "p_job_wf pj
                                        inner join roles role on pj.role_id = role.role_id";

    public $refs            = array();

    function __construct() {

        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            
            $this->db->set('created_date',"now()",false);
            $this->record['created_by'] = $userdata['user_name'];
            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];

            unset($this->record[$this->pkey]);
        }else {
            
            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];
        }
        return true;
    }

}

/* End of file P_job_wf.php */