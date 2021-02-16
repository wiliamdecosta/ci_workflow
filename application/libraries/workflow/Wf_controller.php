<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Wf_controller
* @version 2019-08-09 14:54:19
*/
class Wf_controller {

    function list_inbox() {
        $ci =& get_instance();
        $userinfo = $ci->session->userdata;

        $ci->load->model('workflow/wf');
        $table = $ci->wf;

        $items = $table->getListInbox($userinfo['user_name']);

        $strOutput = '';
        $total = 0;

        $strOutput = '
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <table class="table table-hover table-striped">
                                <tr>
                                    <th class="red">Nama Pekerjaan</th>
                                    <th class="red">Jumlah</th>
                                    <th class="red">Lihat Detail</th>
                                </tr>';

        foreach($items as $item) {
            
            $onClickEvent = "loadContentWithParams('workflow.wf_summary', ".$item['params'].");";
            $total += $item['jumlah'];
            
            if($item['jumlah'] == 0)
                $btnOpenInbox = '&nbsp;';
            else
                $btnOpenInbox = '<button type="button" onClick="'.$onClickEvent.'" class="btn btn-xs btn-danger"> Lihat Detail </button>';

            $strOutput .= '<tr>';
            $strOutput .= '<td>'.$item['document_name'].'</td>';
            $strOutput .= '<td align="right"><strong>'.$item['jumlah'].'</strong></td>';
            $strOutput .= '<td>'.$btnOpenInbox.'</td>';
            $strOutput .= '</tr>';
        }

        $strOutput .= '<tr class="red">
                            <td colspan="2" align="right"><strong>Jumlah Pekerjaan Tersedia : '.$total.' </strong></td>
                            <td>&nbsp;</td>
                        </tr>';
        $strOutput .= '</table>
                        </div>
                        </div>';

        echo $strOutput;
        exit;
    }

    function summary_box() {
        $ci =& get_instance();
        $userinfo = $ci->session->userdata;

        $ci->load->model('workflow/wf');
        $table = $ci->wf;

        $p_w_doc_type_id = getVarClean('p_w_doc_type_id', 'int', 0);
        $items = $table->getSummaryBox($p_w_doc_type_id, $userinfo['user_name']);

        $strOutput = '';
        $strOutput = '<div class="portlet box blue-madison">
                            <div class="portlet-title">
                                <div class="caption">Summary</div>
                                <div class="tools">
                                    <a class="collapse" href="javascript:;" data-original-title="" title=""> </a>
                                </div>
                            </div>
                            <div class="portlet-body">';
        $strOutput .= '
                                <table class="table table-bordered table-hover" id="dynamic-table">
                                    <thead>
                                        <tr>
                                            <th class="center" colspan="2"> Pekerjaan</th>
                                            <th class="center" width="15"> Jumlah </th>
                                            <th class="center"> Pilih </th>
                                        </tr>
                                    </thead>
                                    ';

        $strOutput .= '<tbody>';
        $profile_type = '';
        $checked = true;
        $selected = 'checked=""';

        $background_color = array('INBOX' => '#1BBC9B',
                                        'OUTBOX' => '#67809F',
                                        'REJECT' => '#E87E04',
                                        'FINISH' => '#9B59B6');
        foreach($items as $item) {
            if($item['profile_type'] != $profile_type) {
                
                
                
                $strOutput .= '<tr style="color:'.$background_color[$item['profile_type']].'">
                                        <th colspan="4" class="blue-madison">'.$item['profile_type'].'</th>
                                    </tr>';
                
                $profile_type = $item['profile_type'];
            }


            $element_id = $p_w_doc_type_id.'_'.$item['job_wf_id'].'_'.$item['profile_type'];
            $strOutput .= '<tr>
                                    <td>&nbsp;</td>
                                    <td>'.$item['pekerjaan'].'</td>
                                    <td align="right">'.$item['jumlah'].'</td>
                                    <td align="center"><input class="bigger-radio" type="radio" '.$selected.' name="pilih_summary" onChange="loadUserTaskList(this,event);" value="'.$element_id.'"></td>
                                </tr>';
            
            if($checked) {
                $checked = false;
                $selected = '';
            }
        }

        $strOutput .= '</tbody>';
        $strOutput .= '</table>';
        $strOutput .= '</div>';
        $strOutput .= '</div>';


        echo $strOutput;
        exit;
    }

    function user_task_list() {
        $ci =& get_instance();
        $userinfo = $ci->session->userdata;

        $ci->load->model('workflow/wf');
        $table = $ci->wf;

        $element_id      = $ci->input->post('element_id');
        $user_name       = $userinfo['user_name'];

        $page = intval($ci->input->post('page')) ;
        $limit = $ci->input->post('limit');
        $sort = 'donor_date';
        $dir = 'desc';

        /* search parameter */
        $searchPhrase      = $ci->input->post('searchPhrase');
        $tgl_terima        = $ci->input->post('tgl_terima');

        $data = array('total' => 0, 'contents' => self::emptyTaskList());
     
        if(empty($element_id)) {
            echo json_encode($data);
            exit;
        }

        $element_arr = explode('_', $element_id);
        $p_w_doc_type_id = $element_arr[0];
        $p_w_job_wf_id = $element_arr[1];
        $profile_type = $element_arr[2];

        $sql = "SELECT a.t_order_control_id, a.ref_order_control_id, a.p_w_doc_type_id, a.p_w_job_wf_id,
                    a.user_id_donor, a.user_id_takeover, a.user_id_submitter, a.donor_date, a.taken_date,
                    a.submit_date, a.message, a.order_id,  a.order_no, a.profile_type,
                    udonor.user_name user_donor,
                    utakeover.user_name user_takeover,
                    usubmitter.user_name user_submitter,
                    job_wf.file_pekerjaan, job_wf.job_wf_name,
                    prev_pwf.prev_job_wf_id , next_pwf.next_job_wf_id,
                    order_stat.code status_order
                FROM t_order_control_wf a
                LEFT JOIN users udonor on a.user_id_donor = udonor.user_id
                LEFT JOIN users utakeover on a.user_id_takeover = utakeover.user_id
                LEFT JOIN users usubmitter on a.user_id_submitter = usubmitter.user_id
                INNER JOIN p_job_wf job_wf on a.p_w_job_wf_id = job_wf.job_wf_id
                LEFT JOIN p_workflow prev_pwf on a.p_w_job_wf_id = prev_pwf.next_job_wf_id
                           and a.p_w_doc_type_id = prev_pwf.p_document_type_id
                LEFT JOIN p_workflow next_pwf on  a.p_w_job_wf_id = next_pwf.prev_job_wf_id
                          and a.p_w_doc_type_id = next_pwf.p_document_type_id
                INNER JOIN t_order ord on a.order_id = ord.t_order_id
                INNER JOIN p_order_status order_stat on ord.p_order_status_id = order_stat.p_order_status_id

                ";
        
        $req_param = array (
            "table" => $sql,
            "sort_by" => $sort,
            "sord" => $dir,
            "limit" => null,
            "search" => ''
        );
        $req_param['where'] = array();
        $req_param['where'][] = "a.p_w_doc_type_id = ".$p_w_doc_type_id;
        $req_param['where'][] = "a.p_w_job_wf_id = ".$p_w_job_wf_id;
        $req_param['where'][] = "a.profile_type = '".$profile_type."'";        

        if(!empty($searchPhrase)) {
             $req_param['where'][] = "(upper(a.message) LIKE upper('%".$searchPhrase."%')
                                                OR upper(a.order_no) LIKE upper('%".$searchPhrase."%'))";
        }

        if(!empty($tgl_terima)) {
            $req_param['where'][] = "date(a.donor_date) = date('".$tgl_terima."')";
        }

        $count = $table->bootgrid_countAll($req_param);
        if( $count > 0 && !empty($limit) ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 1;
        }

        if ($page > $total_pages)
            $page = $total_pages;

        $start = $limit*$page - ($limit); // do not put $limit*($page - 1)

        $req_param['limit'] = array(
            'start' => $start,
            'end' => $limit
        );

        $items = $table->bootgrid_getData($req_param);
        $data = array();

        $data['total'] = $count;
        $data['contents'] = self::getTaskListHTML($items);

        echo json_encode($data);
        exit;

    }

    public function emptyTaskList() {
        return '<tr>
                    <td colspan="3" align="center"> Tidak ada data untuk ditampilkan </td>
                </tr>';
    }

    public function getTaskListHTML($items) {
        $ci =& get_instance();
        $userinfo = $ci->session->userdata;

        $result = '';
        foreach($items as $item) {
            
            $params = array();
            $params['profile_type'] = $item['profile_type'];
            $params['p_w_doc_type_id'] = $item['p_w_doc_type_id'];
            $params['user_id_donor'] = $item['user_id_donor'];
            $params['user_id_submitter'] = $item['user_id_submitter'];
            $params['donor_date']  = $item['donor_date'];  
            $params['taken_date']  = $item['taken_date']; 
            $params['submit_date']  = $item['submit_date']; 
            $params['message']  = $item['message']; 
            $params['order_id']  = $item['order_id'];
            $params['order_no']  = $item['order_no'];
            $params['user_donor']  = $item['user_donor'];
            $params['user_takeover']  = $item['user_takeover'];
            $params['user_submitter']  = $item['user_submitter'];
            $params['curr_job_wf_id']  = $item['p_w_job_wf_id'];
            $params['prev_job_wf_id']  = $item['prev_job_wf_id'];
            $params['next_job_wf_id']  = $item['next_job_wf_id'];
            $params['user_id_login'] = $userinfo['user_id'];
            $params['curr_t_order_control_id'] = $item['t_order_control_id'];
            $params['ref_t_order_control_id'] = $item['ref_order_control_id'];
            $params['status_order'] = $item['status_order'];
            

            $result .= '<tr>';
            if($item['profile_type'] != 'INBOX') {
                $params['action_status'] = "VIEW";
                $json_param = str_replace('"', "'", json_encode($params));
                $result .= '<td><button type="button" class="btn btn-sm btn-danger" onClick="loadWFForm(\''.$item['file_pekerjaan'].'\','.$json_param.')">View</button></td>';
            }else {
                if(empty($item['taken_date'])) {
                    $params['action_status'] = "TERIMA";
                    $json_param = str_replace('"', "'", json_encode($params));
                    $result .= '<td><button type="button" class="btn btn-sm btn-danger" onClick="loadWFForm(\''.$item['file_pekerjaan'].'\','.$json_param.')">Terima</button></td>';
                }else {
                    $params['action_status'] = "BUKA";
                    $json_param = str_replace('"', "'", json_encode($params));
                    $result .= '<td><button type="button" class="btn btn-sm btn-danger" onClick="loadWFForm(\''.$item['file_pekerjaan'].'\','.$json_param.')">Buka</button></td>';
                }
            }

            $result .= '<td>
                            <table class="table">
                                <tr>
                                    <td>Nama Pekerjaan</td>
                                    <td>:</td>
                                    <td colspan="2"><span class="red"><strong>'.$item['job_wf_name'].'</strong></span></td>
                                </tr>
                                <tr>
                                    <td>Pengirim</td>
                                    <td>:</td>
                                    <td>'.$item['user_donor'].'</td>
                                    <td>'.$item['donor_date'].'</td>
                                </tr>
                                <tr>
                                    <td>Penerima</td>
                                    <td>:</td>
                                    <td>'.$userinfo['user_name'].'</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Pengambil</td>
                                    <td>:</td>
                                    <td>'.$item['user_takeover'].'</td>
                                    <td>'.$item['taken_date'].'</td>
                                </tr>
                                <tr>
                                    <td>Submitter</td>
                                    <td>:</td>
                                    <td>'.$item['user_submitter'].'</td>
                                    <td>'.$item['submit_date'].'</td>
                                </tr>
                            </table>
                        </td>'; /* pekerjaan */
            $result .= '<td>
                            <table class="table">
                                <tr>
                                    <td>Nomor Order</td>
                                    <td>:</td>
                                    <td>'.$item['order_no'].'</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Dibaca</td>
                                    <td>:</td>
                                    <td>'.$item['taken_date'].'</td>
                                </tr>
                                <tr>
                                    <td>Status Order</td>
                                    <td>:</td>
                                    <td>'.$item['status_order'].'</td>
                                </tr>
                                <tr>
                                    <td>Status Dokumen</td>
                                    <td>:</td>
                                    <td>'.$item['profile_type'].'</td>
                                </tr>
                                <tr>
                                    <td><b>Pesan</b></td>
                                    <td>:</td>
                                    <td><b>'.$item['message'].'</b></td>
                                </tr>
                            </table>
                        </td>'; /* dokumen */
            
            $result .= '</tr>';


        }

        return $result;
    }

    function taken_task() {
        $ci =& get_instance();
        $userinfo = $ci->session->userdata;

        $ci->load->model('workflow/wf');
        $table = $ci->wf;

        $curr_t_order_control_id = $ci->input->post('curr_t_order_control_id');
        $data = array('success' => true, 'message' => '');
        
        try {
            if(empty($curr_t_order_control_id)) 
                throw new Exception('ID control order tidak valid');
            
            $sql = "UPDATE t_order_control_wf
                        SET user_id_takeover = ?,
                        taken_date = now()
                    WHERE t_order_control_id = ?";
            
            $table->db->query($sql, array($userinfo['user_id'],  $curr_t_order_control_id));

        }catch(Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        }

        echo json_encode($data);
        exit;
    }

    function first_submit() {
        $ci =& get_instance();
        $userinfo = $ci->session->userdata;

        $ci->load->model('workflow/wf');
        $table = $ci->wf;

        $p_w_doc_type_id = $ci->input->post('p_w_doc_type_id');
        $order_no = $ci->input->post('order_no');
        $user_name = $userinfo['user_name'];

        $data = array('success' => true, 'message' => '');
        
        try {
            $sql = "SELECT f_first_submit(".$p_w_doc_type_id.",'".$order_no."','".$user_name."') as message";
            $query = $table->db->query($sql);
            $row = $query->row_array();

            if($row['message'] != 'sukses') {
                throw new Exception($row['message']);
            }

        }catch(Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        }

        echo json_encode($data);
        exit;
            
    }

    function submitter_submit() {
        $ci =& get_instance();
        $ci->load->model('workflow/wf');
        $table = $ci->wf;
        $userinfo = $ci->session->userdata;

        $params = json_decode($ci->input->post('params') , true);
        $interactive_message = $ci->input->post('interactive_message');
        
        $data = array('success' => true, 'message' => 'sukses');
        try {

            $sql = "CALL p_submit_engine_next_job(?,?,?,?,?,?,?,?,?, @out_ret_id, @out_ret_message)";
            $table->db->query($sql, array($params['curr_t_order_control_id'],
                                                              $params['ref_t_order_control_id'],
                                                              $params['p_w_doc_type_id'],
                                                              $params['curr_job_wf_id'],
                                                              $params['next_job_wf_id'],
                                                              $userinfo['user_name'],
                                                              $params['order_id'],
                                                              $params['order_no'],
                                                              $interactive_message                                                         
                                                            ));
            $query = $table->db->query('select @out_ret_id as ret_id, @out_ret_message as ret_message');
            $row = $query->row_array();
            
            if($row['ret_id'] == -999) {
                throw new Exception($row['ret_message']);
            }

        }catch(Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        }

        echo json_encode($data);
        exit;
    }

    function submitter_reject() {
        $ci =& get_instance();
        $ci->load->model('workflow/wf');
        $table = $ci->wf;
        $userinfo = $ci->session->userdata;

        $params = json_decode($ci->input->post('params') , true);
        $interactive_message = $ci->input->post('interactive_message');
        
        $data = array('success' => true, 'message' => 'sukses');
        try {

            $sql = "CALL p_submit_engine_reject_job(?,?,?,?,?,?,?,?,?, @out_ret_id, @out_ret_message)";
            $table->db->query($sql, array($params['curr_t_order_control_id'],
                                                              $params['ref_t_order_control_id'],
                                                              $params['p_w_doc_type_id'],
                                                              $params['curr_job_wf_id'],
                                                              $params['next_job_wf_id'],
                                                              $userinfo['user_name'],
                                                              $params['order_id'],
                                                              $params['order_no'],
                                                              $interactive_message                                                         
                                                            ));
            $query = $table->db->query('select @out_ret_id as ret_id, @out_ret_message as ret_message');
            $row = $query->row_array();
            
            if($row['ret_id'] == -999) {
                throw new Exception($row['ret_message']);
            }

        }catch(Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        }

        echo json_encode($data);
        exit;
    }

    function submitter_send_back() {
        $ci =& get_instance();
        $ci->load->model('workflow/wf');
        $table = $ci->wf;
        $userinfo = $ci->session->userdata;

        $params = json_decode($ci->input->post('params') , true);
        $interactive_message = $ci->input->post('interactive_message');
        
        $data = array('success' => true, 'message' => 'sukses');
        try {

            $sql = "CALL p_submit_engine_sendback_job(?,?,?,?,?,?,?,?,?, @out_ret_id, @out_ret_message)";
            $table->db->query($sql, array($params['curr_t_order_control_id'],
                                                              $params['ref_t_order_control_id'],
                                                              $params['p_w_doc_type_id'],
                                                              $params['curr_job_wf_id'],
                                                              $params['prev_job_wf_id'],
                                                              $userinfo['user_name'],
                                                              $params['order_id'],
                                                              $params['order_no'],
                                                              $interactive_message                                                         
                                                            ));
            $query = $table->db->query('select @out_ret_id as ret_id, @out_ret_message as ret_message');
            $row = $query->row_array();
            
            if($row['ret_id'] == -999) {
                throw new Exception($row['ret_message']);
            }

        }catch(Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        }

        echo json_encode($data);
        exit;
    }


    
}

/* End of file Wf_controller.php */