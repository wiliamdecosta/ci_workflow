<?php

/**
 * Warta_jemaat Model
 *
 */
class Warta_jemaat extends Abstract_model {

    public $table           = "tbl_warta_jemaat";
    public $pkey            = "warta_jemaat_id";
    public $alias           = "warta";

    public $fields          = array(
                                'warta_jemaat_id'    => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Sektor'),

                                'tgl_pembuatan_warta'            => array('nullable' => false, 'type' => 'date', 'unique' => false, 'display' => 'Tanggal Pembuatan Warta'),
                                'tgl_terbit_warta'            => array('nullable' => false, 'type' => 'date', 'unique' => true, 'display' => 'Tanggal Terbit Warta'),

                                'kalimat_pembuka'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Kalimat Pembuka'),
                                'pf_minggu'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Pelayan Firman'),
                                'pf_gereja'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Dari Gereja'),

                                'tema_minggu'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Tema Minggu'),
                                'other_info'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Other Info'),

                                'creation_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Creation Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "warta.*";
    public $fromClause      = "tbl_warta_jemaat warta";

    public $refs            = array();
    public $multiUnique  = array();

    function __construct() {
        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
            if(date('Y-m-d', strtotime($this->record['tgl_pembuatan_warta'])) >= date('Y-m-d',strtotime($this->record['tgl_terbit_warta']))) {
                throw new Exception('Tgl Pembuatan Warta melebihi Tgl Terbit Warta');
            }

            $this->db->set('creation_date',"current_date",false);
            $this->record['created_by'] = $userdata['user_name'];
            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

            $this->record[$this->pkey] = $this->generate_seq_id($this->table, $this->pkey);

        }else {
            //do something
            //example:
            if(date('Y-m-d', strtotime($this->record['tgl_pembuatan_warta'])) >= date('Y-m-d',strtotime($this->record['tgl_terbit_warta']))) {
                throw new Exception('Tgl Pembuatan Warta melebihi Tgl Terbit Warta');
            }

            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

    function getPersembahanKehadiran($date) {

        $sql = "select kebaktian.*, jemaat.nama_lengkap, sektor.sektor_kode from
                tbl_persembahan_kebaktian kebaktian
                left join tbl_jemaat jemaat on kebaktian.jemaat_id = jemaat.jemaat_id
                left join tbl_sektor sektor on jemaat.sektor_id = sektor.sektor_id
                where kebaktian.tgl_ibadah between (to_date('".$date."','YYYY-MM-DD') + interval '-7' day) and to_date('".$date."','YYYY-MM-DD')";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        return $result;
    }

    function getUlangTahun($date) {

        $sql = "select nama_lengkap, tanggal_lahir, extract(day from tanggal_lahir) as tgl, extract(month from tanggal_lahir) as bln from tbl_jemaat
                    where
                    to_date( to_char( to_date('".$date."','YYYY-MM-DD'),'yyyy')||to_char(tanggal_lahir,'mmdd'), 'yyyymmdd' )
                    between to_date('".$date."','YYYY-MM-DD') and to_date('".$date."','YYYY-MM-DD') + 6
                    order by extract(month from tanggal_lahir), extract(day from tanggal_lahir)
                ";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        return $result;
    }

}

/* End of file Activity.php */