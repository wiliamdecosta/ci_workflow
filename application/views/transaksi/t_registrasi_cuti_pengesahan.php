<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url(); ?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="#">Transaksi</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Form Pengesahan Cuti</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<?php 
    $sql = "SELECT * FROM t_registrasi_cuti WHERE order_no = ?";
    $query = $this->db->query($sql, array($this->input->post('order_no')));
    $row = $query->row_array();

    $verifikator = ($row['verified_by'] == '') ? $this->session->userdata('user_name') : $row['verified_by'];

?>

<div class="space-4"></div>
<div class="row">
    <div class="col-xs-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Form Pengesahan Cuti </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse"> </a>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form id="form-pengesahan" method="post" name="form-pengesahan" class="form-horizontal">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    
                    <input type="hidden" id="submitter_curr_t_order_control_id" value="<?php echo $this->input->post('curr_t_order_control_id'); ?>">
                    <input type="hidden" id="submitter_ref_t_order_control_id" value="<?php echo $this->input->post('ref_t_order_control_id'); ?>">
                    <input type="hidden" id="submitter_p_w_doc_type_id" value="<?php echo $this->input->post('p_w_doc_type_id'); ?>">
                    <input type="hidden" id="submitter_profile_type" value="<?php echo $this->input->post('profile_type'); ?>">
                    <input type="hidden" id="submitter_action_status" value="<?php echo $this->input->post('action_status'); ?>">
                    <input type="hidden" id="submitter_curr_job_wf_id" value="<?php echo $this->input->post('curr_job_wf_id'); ?>">
                    <input type="hidden" id="submitter_prev_job_wf_id" value="<?php echo $this->input->post('prev_job_wf_id'); ?>">
                    <input type="hidden" id="submitter_next_job_wf_id" value="<?php echo $this->input->post('next_job_wf_id'); ?>">
                    <input type="hidden" id="submitter_user_takeover" value="<?php echo $this->input->post('user_takeover'); ?>">
                    <input type="hidden" id="submitter_order_no" value="<?php echo $this->input->post('order_no'); ?>">
                    <input type="hidden" id="submitter_order_id" value="<?php echo $this->input->post('order_id'); ?>">
                    <input type="hidden" id="submitter_status_order" value="<?php echo $this->input->post('status_order'); ?>">
                    

                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-3 control-label">No Order</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" readonly required="" name="order_no" id="order_no" value="<?php echo $row['order_no'];?>">
                                <input type="hidden" class="form-control" readonly required="" name="t_registrasi_cuti_id" id="t_registrasi_cuti_id" value="<?php echo $row['t_registrasi_cuti_id'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Nama Pemohon</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" required="" name="nama_pemohon" id="nama_pemohon" value="<?php echo $row['nama_pemohon'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">NIP</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" required="" name="nip" id="nip" value="<?php echo $row['nip'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Jumlah Hari Cuti</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" required="" name="jumlah_hari_cuti" id="jumlah_hari_cuti" value="<?php echo $row['jumlah_hari_cuti'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Alasan Cuti</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" required="" name="alasan_cuti" id="alasan_cuti" value="<?php echo $row['alasan_cuti'];?>">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Verifikator</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" readonly name="verified_by" id="verified_by" value="<?php echo $verifikator; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">NIP Verifikator</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" readonly name="verified_nip" id="verified_nip" value="<?php echo $row['verified_nip'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Catatan Verifikator</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" readonly name="verified_notes" id="verified_notes" value="<?php echo $row['verified_notes'];?>">
                            </div>
                        </div>
                        
                    </div>
                    <div class="form-actions bottom">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="button" class="btn default" onClick="backSummary();">Back</button>
                            </div>
                            <?php if($this->input->post('action_status') != 'VIEW'): ?>
                            <div class="col-md-offset-3 col-md-4">
                                <button type="button" class="btn btn-warning" onClick="onSubmitWF();">Submit Pengesahan</button>
                            </div>  
                            <?php endif; ?>                        
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('workflow/lov_submitter'); ?>

<script>
    function backSummary() {
        loadContentWithParams( 'workflow.wf_summary' , {p_w_doc_type_id : <?php echo $this->input->post('p_w_doc_type_id');?>} );
    }

    function onSubmitWF() {
        var params_submitter = {};
        
        params_submitter.curr_t_order_control_id = $('#submitter_curr_t_order_control_id').val();
        params_submitter.ref_t_order_control_id = $('#submitter_ref_t_order_control_id').val();
        params_submitter.p_w_doc_type_id = $('#submitter_p_w_doc_type_id').val();
        params_submitter.profile_type = $('#submitter_profile_type').val();
        params_submitter.action_status = $('#submitter_action_status').val();
        params_submitter.curr_job_wf_id = $('#submitter_curr_job_wf_id').val();
        params_submitter.prev_job_wf_id = $('#submitter_prev_job_wf_id').val();
        params_submitter.next_job_wf_id = $('#submitter_next_job_wf_id').val();
        params_submitter.user_takeover = $('#submitter_user_takeover').val();
        params_submitter.order_no = $('#submitter_order_no').val();
        params_submitter.order_id = $('#submitter_order_id').val();
        params_submitter.next_job_wf_name = '<?php echo getJobWFName($this->input->post('next_job_wf_id'),'next'); ?>';
        params_submitter.prev_job_wf_name = '<?php echo getJobWFName($this->input->post('prev_job_wf_id'), 'prev'); ?>';
        
        params_submitter.status_order = $('#submitter_status_order').val();
        
        modal_lov_submitter_show(params_submitter);
    }

</script>
