<?php

/**
 * Persembahan_kebaktian Model
 *
 */
class Persembahan_kebaktian extends Abstract_model {

    public $table           = "tbl_persembahan_kebaktian";
    public $pkey            = "pk_id";
    public $alias           = "kebaktian";

    public $fields          = array(
                                'pk_id'                => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Sektor'),

                                'jemaat_id'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Bertempat Di Keluarga'),

                                'jenis_ibadah'          => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Jenis Ibadah'),
                                'tgl_ibadah'            => array('nullable' => false, 'type' => 'date', 'unique' => false, 'display' => 'Tanggal Ibadah'),

                                'pelayan_firman'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Pelayan Firman'),
                                'minggu_ke'             => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Minggu Ke'),

                                'total_pria'              => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Total Pria'),
                                'total_wanita'           => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Total Wanita'),
                                'total_anak_anak'       => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Total Anak'),

                                'total_persembahan'    => array('nullable' => true, 'type' => 'int', 'unique' => false, 'display' => 'Total Persembahan'),

                                'keterangan'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Keterangan'),

                                'creation_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Creation Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "kebaktian.pk_id,kebaktian.jemaat_id, kebaktian.jenis_ibadah,kebaktian.tgl_ibadah,kebaktian.pelayan_firman,kebaktian.minggu_ke,kebaktian.total_pria,kebaktian.total_wanita,kebaktian.total_persembahan,kebaktian.keterangan,kebaktian.creation_date,kebaktian.created_by,kebaktian.updated_date,kebaktian.updated_by,kebaktian.total_anak_anak,
                                        jemaat.nama_lengkap, sektor.sektor_kode";
    public $fromClause      = "tbl_persembahan_kebaktian kebaktian
                                        left join tbl_jemaat jemaat on kebaktian.jemaat_id = jemaat.jemaat_id
                                        left join tbl_sektor sektor on jemaat.sektor_id = sektor.sektor_id";

    public $refs            = array();
    public $multiUnique  = array('jenis_ibadah','tgl_ibadah','jemaat_id');

    function __construct() {
        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something
            // example :
            if($this->isMultipleUnique()) {
                throw new Exception('Duplikat Jenis Ibadah dan Tanggal Ibadah/Jemaat');
            }

            $date = $this->record['tgl_ibadah'];
            $this->record['minggu_ke'] = $this->weekOfMonth($date);
            if(empty($this->record['jemaat_id'])) {
                unset($this->record['jemaat_id']);
            }

            $this->db->set('creation_date',"current_date",false);
            $this->record['created_by'] = $userdata['user_name'];
            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

            $this->record[$this->pkey] = $this->generate_seq_id($this->table, $this->pkey);

        }else {
            //do something
            //example:
            if($this->isMultipleUnique()) {
                throw new Exception('Duplikat Jenis Ibadah dan Tanggal Ibadah/Jemaat');
            }


            $date = $this->record['tgl_ibadah'];
            $this->record['minggu_ke'] = $this->weekOfMonth($date);
            if(empty($this->record['jemaat_id'])) {
                unset($this->record['jemaat_id']);
            }

            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

    public function weekOfMonth($date) {
        //Get the first day of the month.
        return ceil( date( 'j', strtotime( $date ) ) / 7 );
    }
}

/* End of file Activity.php */