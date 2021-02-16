<?php

/**
 * Jemaat Model
 *
 */
class Jemaat extends Abstract_model {

    public $table           = "tbl_jemaat";
    public $pkey            = "jemaat_id";
    public $alias           = "jemaat";

    public $fields          = array(
                                'jemaat_id'                => array('pkey' => true, 'type' => 'int', 'nullable' => true, 'unique' => true, 'display' => 'ID Sektor'),

                                'sektor_id'                 => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Sektor'),
                                'kk_id'                     => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'ID KK'),

                                'nomor_induk_jemaat'           => array('nullable' => false, 'type' => 'str', 'unique' => true, 'display' => 'Nomor Induk Jemaat'),
                                'nama_lengkap'           => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Nama Lengkap'),
                                'tempat_lahir'            => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Tempat Lahir'),
                                'tanggal_lahir'           => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Tanggal Lahir'),
                                'jenis_kelamin'           => array('nullable' => false, 'type' => 'str', 'unique' => false, 'display' => 'Jenis Kelamin'),
                                'alamat_jemaat'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Alamat'),
                                'no_telp'                   => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'No Telp'),
                                'no_ktp'                    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'No KTP'),
                                'email'                     => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Email'),

                                'status_baptis'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status Baptis'),
                                'tempat_baptis'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Tempat Baptis'),

                                'status_sidi'             => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status Sidi'),
                                'tempat_sidi'            => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Tempat Sidi'),

                                'status_nikah'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status Nikah'),
                                'tempat_nikah'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Tempat Nikah'),

                                'pekerjaan_jemaat'       => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Pekerjaan Jemaat'),
                                'jumlah_penghasilan'    => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Penghasilan Jemaat'),

                                'kendaraan_jemaat'      => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Kendaraan Jemaat'),
                                'foto_jemaat'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Foto Jemaat'),

                                'status_anggota_kel'           => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Status Anggota Kel'),

                                'creation_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Creation Date'),
                                'created_by'               => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Created By'),
                                'updated_date'            => array('nullable' => true, 'type' => 'date', 'unique' => false, 'display' => 'Updated Date'),
                                'updated_by'              => array('nullable' => true, 'type' => 'str', 'unique' => false, 'display' => 'Updated By')
                            );

    public $selectClause    = "jemaat.jemaat_id, jemaat.nomor_induk_jemaat, jemaat.sektor_id,jemaat.kk_id,jemaat.nama_lengkap,jemaat.tempat_lahir,jemaat.tanggal_lahir,jemaat.jenis_kelamin,jemaat.alamat_jemaat,jemaat.no_telp,jemaat.no_ktp,jemaat.email,jemaat.status_baptis,jemaat.tempat_baptis,jemaat.status_sidi,jemaat.tempat_sidi,jemaat.status_nikah,jemaat.tempat_nikah,jemaat.pekerjaan_jemaat,jemaat.jumlah_penghasilan,jemaat.kendaraan_jemaat,jemaat.foto_jemaat,jemaat.creation_date,jemaat.created_by,jemaat.updated_date,jemaat.updated_by,
                                    sektor.sektor_kode, jemaat.status_anggota_kel, jemaat.status_anggota_kel as status_display";
    public $fromClause      = "tbl_jemaat jemaat
                                        left join tbl_sektor sektor on jemaat.sektor_id = sektor.sektor_id";

    public $refs            = array('tbl_jemaat' => 'kk_id');

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

    function getDataIndukItems() {
        $sql = "WITH RECURSIVE tree (jemaat_id, kk_id, cnt) AS (

                    SELECT jemaat_id, kk_id, 0::bigint AS cnt
                    FROM tbl_jemaat t
                    WHERE NOT EXISTS (
                        SELECT jemaat_id
                        FROM tbl_jemaat
                        WHERE kk_id = t.jemaat_id
                    )

                    UNION ALL

                    SELECT t.jemaat_id, t.kk_id, tree.cnt + (
                            SELECT count(jemaat_id)
                            FROM tbl_jemaat
                            WHERE kk_id = t.jemaat_id
                        )
                    FROM tbl_jemaat t JOIN tree ON t.jemaat_id = tree.kk_id
                )

                SELECT tree.jemaat_id, max(tree.cnt) AS jumlah_anggota,
                jmt.nama_lengkap, jmt.nomor_induk_jemaat, jmt.sektor_id, sktr.sektor_kode, jmt.tempat_lahir, jmt.tanggal_lahir, jmt.no_telp, jmt.alamat_jemaat, jmt.pekerjaan_jemaat
                FROM tree
                inner join tbl_jemaat jmt on tree.jemaat_id = jmt.jemaat_id
                inner join tbl_sektor sktr on jmt.sektor_id = sktr.sektor_id
                WHERE tree.kk_id IS NULL and
                jmt.status_sidi = 'Y'

                GROUP BY tree.jemaat_id, jmt.nomor_induk_jemaat, jmt.nama_lengkap, jmt.sektor_id, sktr.sektor_kode, jmt.tempat_lahir, jmt.tanggal_lahir, jmt.no_telp, jmt.alamat_jemaat, jmt.pekerjaan_jemaat
                ORDER BY tree.jemaat_id";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        return $result;
    }

}

/* End of file Activity.php */