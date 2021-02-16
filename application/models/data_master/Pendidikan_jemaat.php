<?php

/**
 * Pendidikan_jemaat Model
 *
 */
class Pendidikan_jemaat extends Abstract_model {

    public $table           = "tbl_pendidikan_jemaat";
    public $pkey            = "pendidikan_jemaat_id";
    public $alias           = "pj";

    public $fields          = array(
                                'pendidikan_jemaat_id'      => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Sektor'),

                                'jemaat_id'                    => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'ID Jemaat'),
                                'tingkat_pendidikan_id'    => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Tingkat Pendidikan'),

                                'nama_sekolah'               => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Nama Sekolah'),
                                'jurusan'                      => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Jurusan'),
                                'alamat_sekolah'             => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Alamat Sekolah'),
                                'tahun_lulus'                => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Tahun Lulus'),

                                'creation_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Creation Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "pj.pendidikan_jemaat_id,pj.jemaat_id,pj.tingkat_pendidikan_id,pj.nama_sekolah,pj.jurusan,pj.alamat_sekolah,pj.tahun_lulus,pj.creation_date,pj.created_by,pj.updated_date,pj.updated_by,
                                        tp.tingkat_pendidikan_nama";
    public $fromClause      = "tbl_pendidikan_jemaat pj
                                        left join tbl_tingkat_pendidikan tp on pj.tingkat_pendidikan_id = tp.tingkat_pendidikan_id";

    public $refs            = array();
    public $multiUnique  = array('jemaat_id','tingkat_pendidikan_id');

    function __construct() {
        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {

            if($this->isMultipleUnique()) {
                throw new Exception('Duplikat Tingkat Pendidikan');
            }
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

            if($this->isMultipleUnique()) {
                throw new Exception('Duplikat Tingkat Pendidikan');
            }

            $this->db->set('updated_date',"current_date",false);
            $this->record['updated_by'] = $userdata['user_name'];

        }
        return true;
    }

}

/* End of file Activity.php */