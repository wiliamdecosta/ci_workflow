<?php

/**
 * P_document_type Model
 *
 */
class P_document_type extends Abstract_model {

    public $table           = "p_document_type";
    public $pkey            = "p_document_type_id";
    public $alias           = "doc";

    public $fields          = array(
                                'p_document_type_id'                => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'Jenis Dokumen WF'),
                                'document_name'              => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Nama Dokumen'),
                                'description'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Keterangan'),

                                'created_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Created Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "doc.*";
    public $fromClause      = "p_document_type doc";

    public $refs            = array();

    function __construct() {
        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
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

            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

}

/* End of file P_document_type.php */