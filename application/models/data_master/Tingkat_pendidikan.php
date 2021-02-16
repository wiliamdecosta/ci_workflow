<?php

/**
 * Tingkat_pendidikan Model
 *
 */
class Tingkat_pendidikan extends Abstract_model {

    public $table           = "tbl_tingkat_pendidikan";
    public $pkey            = "tingkat_pendidikan_id";
    public $alias           = "tp";

    public $fields          = array(
                                'tingkat_pendidikan_id'                => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Sektor'),
                                'tingkat_pendidikan_nama'              => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Jenis Pendidikan'),

                                'creation_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Creation Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "tp.*";
    public $fromClause      = "tbl_tingkat_pendidikan tp";

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
            $this->db->set('creation_date',"current_date",false);
            $this->record['created_by'] = $userdata['user_name'];
            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

            $this->record[$this->pkey] = $this->generate_seq_id($this->table, $this->pkey);

        }else {
            //do something
            //example:

            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

}

/* End of file Activity.php */