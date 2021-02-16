<?php

/**
 * T_registrasi_cuti Model
 *
 */
class T_registrasi_cuti extends Abstract_model {

    public $table           = "t_registrasi_cuti";
    public $pkey            = "t_registrasi_cuti_id";
    public $alias           = "a";

    public $fields          = array(
                                't_registrasi_cuti_id'                => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Registrasi Cuti'),
                                'order_no'              => array('nullable' => true, 'type' => 'str', 'unique' => true, 'display' => 'No Order'),
                                'nama_pemohon'           => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Nama Pemohon'),
                                'nip'           => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'NIP'),
                                'jumlah_hari_cuti'           => array('nullable' => false, 'type' => 'int', 'unique' => false, 'display' => 'Jumlah Hari Cuti'),
                                'alasan_cuti'           => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Alasan Cuti'),
                                'verified_by'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Verifikator'),
                                'verified_nip'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'NIP Verifikator'),
                                'verified_notes'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Notes'),
                                
                                'created_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Creation Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "a.*, b.p_order_status_id";
    public $fromClause      = "t_registrasi_cuti a
                                        left join t_order b on a.order_no = b.order_no
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
            // example :
            unset($this->record['order_no']);
            unset($this->record[$this->pkey]);

            $this->db->set('created_date',"now()",false);
            $this->record['created_by'] = $userdata['user_name'];
            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];

            $this->db->set('order_no',"f_order_no()",false);
            
            
        }else {
            //do something
            //example:
            unset($this->record['order_no']);
            $this->db->set('updated_date',"now()",false);
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

}

/* End of file Activity.php */