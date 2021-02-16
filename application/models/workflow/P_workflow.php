<?php

/**
 * P_workflow Model
 *
 */
class P_workflow extends Abstract_model {

    public $table           = "p_workflow";
    public $pkey            = "p_workflow_id";
    public $alias           = "wf";

    public $fields          = array(
                                'p_workflow_id'                => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'Jenis Dokumen WF'),
                                'p_document_type_id'    => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Document Type ID'),
                                
                                'order_list_no'           => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'No Urut'),
                                'prev_job_wf_id'           => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Prev Job'),
                                'next_job_wf_id'           => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Next Job'),
                                
                                'created_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "wf.*, pj.job_wf_name prev_job_wf_name, 
                                    role_prev.role_name as prev_role_name,
                                    coalesce(nj.job_wf_name,'Berhenti') next_job_wf_name,
                                    role_next.role_name as next_role_name";
    public $fromClause      = "p_workflow wf
                                        left join p_job_wf pj on wf.prev_job_wf_id = pj.job_wf_id
                                        left join p_job_wf nj on wf.next_job_wf_id = nj.job_wf_id
                                        left join roles role_prev on pj.role_id = role_prev.role_id
                                        left join roles role_next on nj.role_id = role_next.role_id
                                        
                                        ";

    public $refs            = array();

    function __construct() {
        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something

            if($this->record['prev_job_wf_id'] == $this->record['next_job_wf_id']) {
                throw new Exception('Pekerjaan sebelumnya dan pekerjaan berikutnya tidak boleh sama');
            }

            // example :
            $this->db->set('created_date',"now()",false);
            $this->record['created_by'] = $userdata['user_name'];
            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];

            //$this->record[$this->pkey] = $this->generate_seq_id($this->table, $this->pkey);
            unset($this->record[$this->pkey]);
        }else {
            //do something
            //example:
            if($this->record['prev_job_wf_id'] == $this->record['next_job_wf_id']) {
                throw new Exception('Pekerjaan sebelumnya dan pekerjaan berikutnya tidak boleh sama');
            }


            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];
        }
        return true;
    }

}

/* End of file P_workflow.php */