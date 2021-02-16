<?php

/**
 * Wf Model
 *
 */
class Wf extends Abstract_model {

    public $table           = "";
    public $pkey            = "";
    public $alias           = "";

    public $fields          = array();

    public $selectClause    = "";
    public $fromClause      = "";

    public $refs            = array();

    function __construct() {
        parent::__construct();
    }

    function validate() {

        $ci =& get_instance();
        $userdata = $ci->session->userdata;

        if($this->actionType == 'CREATE') {
            //do something

        }else {
            //do something
            //example:
        }
        return true;
    }

    function getListInbox($user_name) {
        /**
         * Catatan : Status REJECT & FINISH hanya bisa diupdate pada profile_type INBOX
         *              tidak berlaku buat OUTBOX 
         */
        $sql = "SELECT a.document_name, COALESCE(sum(b.jumlah),0) as jumlah, 
                    concat('{p_w_doc_type_id:', b.p_w_doc_type_id,'}') as params
                    FROM p_document_type a 
                    LEFT JOIN
                    (
                        select oc.p_w_doc_type_id, count(*) as jumlah 
                        from t_order_control_wf oc
                        inner join p_job_wf job_wf on oc.p_w_job_wf_id = job_wf.job_wf_id
                        inner join role_user ru on job_wf.role_id = ru.role_id
                        inner join users u on ru.user_id = u.user_id
                        where u.user_name = '".$user_name."'
                        and oc.profile_type = 'INBOX'
                        and (date(oc.donor_date) between date_add(current_date, interval -30 day) and current_date)
                        group by oc.p_w_doc_type_id
                        
                    ) b
                    ON a.p_document_type_id = b.p_w_doc_type_id
                    GROUP BY a.document_name";

        $query = $this->db->query($sql);
        $row = $query->result_array();

        return $row;
    }

    function getTotalInbox($user_name) {
        
        $sql = "select count(*) as jumlah_inbox 
                from t_order_control_wf oc
                inner join p_job_wf job_wf on oc.p_w_job_wf_id = job_wf.job_wf_id
                inner join role_user ru on job_wf.role_id = ru.role_id
                inner join users u on ru.user_id = u.user_id
                where u.user_name = '".$user_name."'
                and oc.profile_type = 'INBOX'
                and (date(oc.donor_date) between date_add(current_date, interval -30 day) and current_date)";
        
        $query = $this->db->query($sql);
        $row = $query->row_array();

        return $row['jumlah_inbox'];
    }

    function getSummaryBox($p_w_doc_type_id, $user_name) {
        
        $sql = "select  a.profile_type, b.job_wf_name as pekerjaan, b.job_wf_id, count(*) as jumlah
                    from t_order_control_wf a 
                    inner join p_job_wf b on a.p_w_job_wf_id = b.job_wf_id
                    inner join role_user ru on b.role_id = ru.role_id
                    inner join users u on ru.user_id = u.user_id
                    inner join p_profile_type pt on a.profile_type = pt.profile_type
                    inner join p_workflow wf on a.p_w_job_wf_id = wf.prev_job_wf_id
                    where a.p_w_doc_type_id = ?
                    and u.user_name = ?
                    group by a.profile_type, b.job_wf_name, b.job_wf_id
                    order by pt.list_no asc, wf.order_list_no asc";
        
        $query = $this->db->query($sql, array($p_w_doc_type_id, $user_name));
        $row = $query->result_array();

        return $row;
    }

    public function bootgrid_countAll($param){

        $whereCondition = join(" AND ", $param['where']);
        if(!empty($whereCondition)) {
            $whereCondition = " WHERE ".$whereCondition;
        }

        if($param['search'] != null && $param['search'] === 'true'){
            $wh = "UPPER(".$param['search_field'].")";
            switch ($param['search_operator']) {
                case "bw": // begin with
                    $wh .= " LIKE UPPER('".$param['search_str']."%')";
                    break;
                case "ew": // end with
                    $wh .= " LIKE UPPER('%".$param['search_str']."')";
                    break;
                case "cn": // contain %param%
                    $wh .= " LIKE UPPER('%".$param['search_str']."%')";
                    break;
                case "eq": // equal =
                    if(is_numeric($param['search_str'])) {
                        $wh .= " = ".$param['search_str'];
                    } else {
                        $wh .= " = UPPER('".$param['search_str']."')";
                    }
                    break;
                case "ne": // not equal
                    if(is_numeric($param['search_str'])) {
                        $wh .= " <> ".$param['search_str'];
                    } else {
                        $wh .= " <> UPPER('".$param['search_str']."')";
                    }
                    break;
                case "lt":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " < ".$param['search_str'];
                    } else {
                        $wh .= " < '".$param['search_str']."'";
                    }
                    break;
                case "le":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " <= ".$param['search_str'];
                    } else {
                        $wh .= " <= '".$param['search_str']."'";
                    }
                    break;
                case "gt":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " > ".$param['search_str'];
                    } else {
                        $wh .= " > '".$param['search_str']."'";
                    }
                    break;
                case "ge":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " >= ".$param['search_str'];
                    } else {
                        $wh .= " >= '".$param['search_str']."'";
                    }
                    break;
                default :
                    $wh = "";
            }
        }

        if(!empty($wh)) {
            if($whereCondition != "" )
                $whereCondition .= " AND ".$wh;
            else
                $whereCondition = " WHERE ".$wh;
        }

        $sql = "select count(1) totalcount from (".$param['table']." ".$whereCondition.") as x";
        $query = $this->db->query($sql);
        $row = $query->row_array();

        $query->free_result();
        return $row['totalcount'];
    }


    public function bootgrid_getData($param){

        $param['table'] = str_replace("SELECT","",$param['table']);
        $this->db->select($param['table']);

        $whereCondition = '';
        $whereCondition = join(" AND ", $param['where']);
        if($param['search'] != null && $param['search'] === 'true'){
            $wh = "UPPER(".$param['search_field'].")";
            switch ($param['search_operator']) {
                case "bw": // begin with
                    $wh .= " LIKE UPPER('".$param['search_str']."%')";
                    break;
                case "ew": // end with
                    $wh .= " LIKE UPPER('%".$param['search_str']."')";
                    break;
                case "cn": // contain %param%
                    $wh .= " LIKE UPPER('%".$param['search_str']."%')";
                    break;
                case "eq": // equal =
                    if(is_numeric($param['search_str'])) {
                        $wh .= " = ".$param['search_str'];
                    } else {
                        $wh .= " = UPPER('".$param['search_str']."')";
                    }
                    break;
                case "ne": // not equal
                    if(is_numeric($param['search_str'])) {
                        $wh .= " <> ".$param['search_str'];
                    } else {
                        $wh .= " <> UPPER('".$param['search_str']."')";
                    }
                    break;
                case "lt":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " < ".$param['search_str'];
                    } else {
                        $wh .= " < '".$param['search_str']."'";
                    }
                    break;
                case "le":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " <= ".$param['search_str'];
                    } else {
                        $wh .= " <= '".$param['search_str']."'";
                    }
                    break;
                case "gt":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " > ".$param['search_str'];
                    } else {
                        $wh .= " > '".$param['search_str']."'";
                    }
                    break;
                case "ge":
                    if(is_numeric($param['search_str'])) {
                        $wh .= " >= ".$param['search_str'];
                    } else {
                        $wh .= " >= '".$param['search_str']."'";
                    }
                    break;
                default :
                    $wh = "";
            }
        }

        if(!empty($wh)) {
            if($whereCondition != "" )
                $whereCondition .= " AND ".$wh;
            else
                $whereCondition = $wh;
        }

        if($whereCondition != "")
            $this->db->where($whereCondition, null, false);

        if(!empty($param['sort_by']))
            $this->db->order_by($param['sort_by'], $param['sord']);

        if($param['limit'] != null)
            $this->db->limit($param['limit']['end'], $param['limit']['start']);

//print_r($this->db->get_compiled_select());exit;
        $queryResult = $this->db->get();
        $items = $queryResult->result_array();

        return $items;
    }

}

/* End of file Wf.php */