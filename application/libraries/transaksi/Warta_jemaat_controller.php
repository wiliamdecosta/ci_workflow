<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Warta_jemaat_controller
* @version 2018-09-27 20:25:30
*/
class Warta_jemaat_controller {

    function read() {

        $page = getVarClean('page','int',1);
        $limit = getVarClean('rows','int',5);
        $sidx = getVarClean('sidx','str','warta_jemaat_id');
        $sord = getVarClean('sord','str','desc');

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        try {

            $ci = & get_instance();
            $ci->load->model('ibadah/warta_jemaat');
            $table = $ci->warta_jemaat;

            $req_param = array(
                "sort_by" => $sidx,
                "sord" => $sord,
                "limit" => null,
                "field" => null,
                "where" => null,
                "where_in" => null,
                "where_not_in" => null,
                "search" => $_REQUEST['_search'],
                "search_field" => isset($_REQUEST['searchField']) ? $_REQUEST['searchField'] : null,
                "search_operator" => isset($_REQUEST['searchOper']) ? $_REQUEST['searchOper'] : null,
                "search_str" => isset($_REQUEST['searchString']) ? $_REQUEST['searchString'] : null
            );

            // Filter Table
            $req_param['where'] = array();

            $table->setJQGridParam($req_param);
            $count = $table->countAll();

            if ($count > 0) $total_pages = ceil($count / $limit);
            else $total_pages = 1;

            if ($page > $total_pages) $page = $total_pages;
            $start = $limit * $page - ($limit); // do not put $limit*($page - 1)

            $req_param['limit'] = array(
                'start' => $start,
                'end' => $limit
            );

            $table->setJQGridParam($req_param);

            if ($page == 0) $data['page'] = 1;
            else $data['page'] = $page;

            $data['total'] = $total_pages;
            $data['records'] = $count;

            $data['rows'] = $table->getAll();
            $data['success'] = true;

        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        return $data;
    }

    function crud() {

        $data = array();
        $oper = getVarClean('oper', 'str', '');
        switch ($oper) {
            case 'add' :
                $data = $this->create();
            break;

            case 'edit' :
                $data = $this->update();
            break;

            case 'del' :
                $data = $this->destroy();
            break;

            default :
                $data = $this->read();
            break;
        }

        return $data;
    }


    function create() {

        $ci = & get_instance();
        $ci->load->model('ibadah/warta_jemaat');
        $table = $ci->warta_jemaat;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'CREATE';
        $errors = array();

        if (isset($items[0])){
            $numItems = count($items);
            for($i=0; $i < $numItems; $i++){
                try{

                    $table->db->trans_begin(); //Begin Trans

                        $table->setRecord($items[$i]);
                        $table->create();

                    $table->db->trans_commit(); //Commit Trans

                }catch(Exception $e){

                    $table->db->trans_rollback(); //Rollback Trans
                    $errors[] = $e->getMessage();
                }
            }

            $numErrors = count($errors);
            if ($numErrors > 0){
                $data['message'] = $numErrors." from ".$numItems." record(s) failed to be saved.<br/><br/><b>System Response:</b><br/>- ".implode("<br/>- ", $errors)."";
            }else{
                $data['success'] = true;
                $data['message'] = 'Data added successfully';
            }
            $data['rows'] =$items;
        }else {

            try{
                $table->db->trans_begin(); //Begin Trans

                    $table->setRecord($items);
                    $table->create();

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data added successfully';

            }catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;

    }

    function update() {

        $ci = & get_instance();
        $ci->load->model('ibadah/warta_jemaat');
        $table = $ci->warta_jemaat;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }

        $table->actionType = 'UPDATE';

        if (isset($items[0])){
            $errors = array();
            $numItems = count($items);
            for($i=0; $i < $numItems; $i++){
                try{
                    $table->db->trans_begin(); //Begin Trans

                        $table->setRecord($items[$i]);
                        $table->update();

                    $table->db->trans_commit(); //Commit Trans

                    $items[$i] = $table->get($items[$i][$table->pkey]);
                }catch(Exception $e){
                    $table->db->trans_rollback(); //Rollback Trans

                    $errors[] = $e->getMessage();
                }
            }

            $numErrors = count($errors);
            if ($numErrors > 0){
                $data['message'] = $numErrors." from ".$numItems." record(s) failed to be saved.<br/><br/><b>System Response:</b><br/>- ".implode("<br/>- ", $errors)."";
            }else{
                $data['success'] = true;
                $data['message'] = 'Data update successfully';
            }
            $data['rows'] =$items;
        }else {

            try{
                $table->db->trans_begin(); //Begin Trans

                    $table->setRecord($items);
                    $table->update();

                $table->db->trans_commit(); //Commit Trans

                $data['success'] = true;
                $data['message'] = 'Data update successfully';

                $data['rows'] = $table->get($items[$table->pkey]);
            }catch (Exception $e) {
                $table->db->trans_rollback(); //Rollback Trans

                $data['message'] = $e->getMessage();
                $data['rows'] = $items;
            }

        }
        return $data;

    }

    function destroy() {
        $ci = & get_instance();
        $ci->load->model('ibadah/warta_jemaat');
        $table = $ci->warta_jemaat;

        $data = array('rows' => array(), 'page' => 1, 'records' => 0, 'total' => 1, 'success' => false, 'message' => '');

        $jsonItems = getVarClean('items', 'str', '');
        $items = jsonDecode($jsonItems);

        try{
            $table->db->trans_begin(); //Begin Trans

            $total = 0;
            if (is_array($items)){
                foreach ($items as $key => $value){
                    if (empty($value)) throw new Exception('Empty parameter');
                    $table->remove($value);
                    $data['rows'][] = array($table->pkey => $value);
                    $total++;
                }
            }else{
                $items = (int) $items;
                if (empty($items)){
                    throw new Exception('Empty parameter');
                }
                $table->remove($items);
                $data['rows'][] = array($table->pkey => $items);
                $data['total'] = $total = 1;
            }

            $data['success'] = true;
            $data['message'] = $total.' Data deleted successfully';

            $table->db->trans_commit(); //Commit Trans

        }catch (Exception $e) {
            $table->db->trans_rollback(); //Rollback Trans
            $data['message'] = $e->getMessage();
            $data['rows'] = array();
            $data['total'] = 0;
        }
        return $data;
    }

    function download_warta() {

            $warta_jemaat_id = getVarClean('warta_jemaat_id', 'int', 0);

            $ci = & get_instance();
            $ci->load->model('ibadah/warta_jemaat');
            $table = $ci->warta_jemaat;

            $item = $table->get($warta_jemaat_id);

            startDoc("warta_jemaat_".date('Y-m-d').".doc");

            $output = '<html>
                                <head>
                                <title>Warta Jemaat</title>
                                <style>
                                    table {
                                        border-collapse: collapse;
                                        border:1px solid #000000;
                                    }

                                    table tr th {
                                        border:1px solid #000000;
                                        padding:5px;
                                        background: #14A8F6;
                                        color:#ffffff;
                                    }

                                    table tr td {
                                        border:1px solid #000000;
                                        padding:5px;
                                    }
                                </style>
                                <body>';
            $output .= '<div align="center"><img src="'.base_url().'/assets/image/logo_hkbp.png" width="200" height="200"></div>';
            $output .= '<h1 align="center" style="font-size:48px;">Warta Jemaat</h1>';
            $output .= '<h1 align="center" style="font-size:48px;">Minggu , '.dateToString($item['tgl_terbit_warta']).'</h1>';
            $output .= '<h1 align="center" style="font-size:32px;">'.$item['tema_minggu'].'</h1>';

            $output .= htmlspecialchars_decode(strip_tags(stripslashes($item['kalimat_pembuka'])));
            $output .= '<h2>Pelayan Firman : '. $item['pf_minggu'].'('.$item['pf_gereja'].')</h2>';

            /* Persembahan */
            $output .= '<h2><u>Persembahan & Kehadiran :</u></h2>';
            $itemspersembahan = $table->getPersembahanKehadiran($item['tgl_terbit_warta']);
            $arr_total_persembahan = array(
                'total_persembahan' => 0,
                'total_pria' => 0,
                'total_wanita' => 0,
                'total_anak_anak' => 0
            );
            $output .= '<table>';
            $output .= '<tr>
                                <th>Tanggal</th>
                                <th>Ibadah</th>
                                <th>Bertempat Di Kel</th>
                                <th>Sektor</th>
                                <th>Pelayan Firman</th>
                                <th>Jumlah Persembahan</th>
                                <th>Jumlah Pria</th>
                                <th>Jumlah Wanita</th>
                                <th>Jumlah Anak-anak</th>
                            </tr>';

            foreach($itemspersembahan as $ipersembahan) {

                $arr_total_persembahan['total_persembahan'] += $ipersembahan['total_persembahan'];
                $arr_total_persembahan['total_pria'] += $ipersembahan['total_pria'];
                $arr_total_persembahan['total_wanita'] += $ipersembahan['total_wanita'];
                $arr_total_persembahan['total_anak_anak'] += $ipersembahan['total_anak_anak'];

                $output .= '<tr>';
                $output .= '<td>'.dateToString($ipersembahan['tgl_ibadah']).'</td>';
                $output .= '<td>'.$ipersembahan['jenis_ibadah'].'</td>';
                $output .= '<td>'.$ipersembahan['nama_lengkap'].'</td>';
                $output .= '<td>'.$ipersembahan['sektor_kode'].'</td>';
                $output .= '<td>'.$ipersembahan['pelayan_firman'].'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_persembahan'],2).'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_pria'],0).'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_wanita'],0).'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_anak_anak'],0).'</td>';
                $output .= '</tr>';
            }
            $output .= '<tr>';
            $output .= '<td colspan="5" align="center"><b>Total</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_persembahan'],2).'</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_pria'],0).'</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_wanita'],0).'</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_anak_anak'],0).'</b></td>';
            $output .= '</tr>';
            $output .= '</table>';

            /* Ulang Tahun */
            $output .= '<h2><u>Ulang Tahun :</u></h2>';
            $output .= '<p> Majelis Jemaat mengucapkan Selamat Berbahagia kepada Warga Jemaat yang dalam sepekan ini merayakan HUT pertambahan usia. Tuhan Yesus memberkati dengan kelimpahan berkat-Nya.</p>';
            $itemsultah = $table->getUlangTahun($item['tgl_terbit_warta']);

            $arr_temp = array();
            foreach($itemsultah as $iultah) {
                $arr_temp[$iultah['tgl'].' '.getMonthName($iultah['bln'])][] = $iultah['nama_lengkap'];
            }

            if(count($arr_temp) > 1) {
                $output .= '<ul>';
                foreach($arr_temp as $key => $val) {
                    $output .= '<li> '.$key.' : '.implode(', ',$arr_temp[$key]).'</li>';
                }
                $output .= '</ul>';
            }

            $output .= '<br>';
            $output .= htmlspecialchars_decode(strip_tags(stripslashes($item['other_info'])));

            $output .= '</body>';
            $output .= '</html>';

            echo $output;
            exit;

    }

    function view_warta() {

            $warta_jemaat_id = getVarClean('warta_jemaat_id', 'int', 0);

            $ci = & get_instance();
            $ci->load->model('ibadah/warta_jemaat');
            $table = $ci->warta_jemaat;

            $item = $table->get($warta_jemaat_id);

            $output = '<html>
                                <head>
                                <title>Warta Jemaat</title>
                                <style>
                                    table {
                                        border-collapse: collapse;
                                        border:1px solid #000000;
                                    }

                                    table tr th {
                                        border:1px solid #000000;
                                        padding:5px;
                                        background: #14A8F6;
                                        color:#ffffff;
                                    }

                                    table tr td {
                                        border:1px solid #000000;
                                        padding:5px;
                                    }
                                </style>
                                <body>';
            $output .= '<div align="center"><img src="'.base_url().'/assets/image/logo_hkbp.png" width="200" height="200"></div>';
            $output .= '<h1 align="center" style="font-size:48px;">Warta Jemaat</h1>';
            $output .= '<h1 align="center" style="font-size:48px;">Minggu , '.dateToString($item['tgl_terbit_warta']).'</h1>';
            $output .= '<h1 align="center" style="font-size:32px;">'.$item['tema_minggu'].'</h1>';

            $output .= htmlspecialchars_decode(strip_tags(stripslashes($item['kalimat_pembuka'])));
            $output .= '<h2>Pelayan Firman : '. $item['pf_minggu'].'('.$item['pf_gereja'].')</h2>';

            /* Persembahan */
            $output .= '<h2><u>Persembahan & Kehadiran :</u></h2>';
            $itemspersembahan = $table->getPersembahanKehadiran($item['tgl_terbit_warta']);
            $arr_total_persembahan = array(
                'total_persembahan' => 0,
                'total_pria' => 0,
                'total_wanita' => 0,
                'total_anak_anak' => 0
            );
            $output .= '<table>';
            $output .= '<tr>
                                <th>Tanggal</th>
                                <th>Ibadah</th>
                                <th>Bertempat Di Kel</th>
                                <th>Sektor</th>
                                <th>Pelayan Firman</th>
                                <th>Jumlah Persembahan</th>
                                <th>Jumlah Pria</th>
                                <th>Jumlah Wanita</th>
                                <th>Jumlah Anak-anak</th>
                            </tr>';

            foreach($itemspersembahan as $ipersembahan) {

                $arr_total_persembahan['total_persembahan'] += $ipersembahan['total_persembahan'];
                $arr_total_persembahan['total_pria'] += $ipersembahan['total_pria'];
                $arr_total_persembahan['total_wanita'] += $ipersembahan['total_wanita'];
                $arr_total_persembahan['total_anak_anak'] += $ipersembahan['total_anak_anak'];

                $output .= '<tr>';
                $output .= '<td>'.dateToString($ipersembahan['tgl_ibadah']).'</td>';
                $output .= '<td>'.$ipersembahan['jenis_ibadah'].'</td>';
                $output .= '<td>'.$ipersembahan['nama_lengkap'].'</td>';
                $output .= '<td>'.$ipersembahan['sektor_kode'].'</td>';
                $output .= '<td>'.$ipersembahan['pelayan_firman'].'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_persembahan'],2).'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_pria'],0).'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_wanita'],0).'</td>';
                $output .= '<td align="right">'.numberFormat($ipersembahan['total_anak_anak'],0).'</td>';
                $output .= '</tr>';
            }
            $output .= '<tr>';
            $output .= '<td colspan="5" align="center"><b>Total</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_persembahan'],2).'</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_pria'],0).'</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_wanita'],0).'</b></td>';
            $output .= '<td align="right"><b>'.numberFormat($arr_total_persembahan['total_anak_anak'],0).'</b></td>';
            $output .= '</tr>';
            $output .= '</table>';

            /* Ulang Tahun */
            $output .= '<h2><u>Ulang Tahun :</u></h2>';
            $output .= '<p> Majelis Jemaat mengucapkan Selamat Berbahagia kepada Warga Jemaat yang dalam sepekan ini merayakan HUT pertambahan usia. Tuhan Yesus memberkati dengan kelimpahan berkat-Nya.</p>';
            $itemsultah = $table->getUlangTahun($item['tgl_terbit_warta']);

            $arr_temp = array();
            foreach($itemsultah as $iultah) {
                $arr_temp[$iultah['tgl'].' '.getMonthName($iultah['bln'])][] = $iultah['nama_lengkap'];
            }

            if(count($arr_temp) > 1) {
                $output .= '<ul>';
                foreach($arr_temp as $key => $val) {
                    $output .= '<li> '.$key.' : '.implode(', ',$arr_temp[$key]).'</li>';
                }
                $output .= '</ul>';
            }

            $output .= '<br>';
            $output .= htmlspecialchars_decode(strip_tags(stripslashes($item['other_info'])));

            $output .= '</body>';
            $output .= '</html>';

            echo $output;
            exit;

    }
}

/* End of file Tingkat_pendidikan_controller.php */