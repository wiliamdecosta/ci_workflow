<?php
    function getJobWFName($p_job_wf_id, $type='') {
        if(empty($p_job_wf_id) and $type == 'next')
            return 'Selesai';
        if(empty($p_job_wf_id) and $type == 'prev')
            return 'Tahap Awal / Input Data';

        $ci =& get_instance();
        $ci->load->model('workflow/p_job_wf');
        $table = $ci->p_job_wf;

        $item = $table->get($p_job_wf_id);
        return $item['job_wf_name'];
    }

    function getTotalInbox() {
        $ci =& get_instance();
        $ci->load->model('workflow/wf');
        $table = $ci->wf;
        $userinfo = $ci->session->userdata;

        $result = $table->getTotalInbox($userinfo['user_name']);
        if(empty($result)) return 0;
        return $result;
    }
?>