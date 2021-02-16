<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url(); ?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="#">Reports</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Final PL</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <div class="col-xs-12">

        <div class="tab-content no-border">
            <div class="space-4"></div>

            <div class="row">
                <div class="col-md-3">
                    <button class="btn btn-primary" type="button" id="btn-back" onclick="backToProcessControl()"><i class="fa fa-arrow-left"></i> Kembali Batch Control</button>
                </div>
            </div>

            <h3> P&L by Business Line (After Elimination)</h3>
            <h4> <?php echo $this->input->post('periodcode'); ?></h4>

            <div class="row">
                <div class="col-xs-12 table-scrollable">
                   <table class="table table-bordered table-hover table-condensed">
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;">P&L Line Item </th>
                                <th colspan="4" style="text-align: center;">Carrier</th>
                                <th rowspan="2" style="vertical-align: middle;">International Adjacent</th>
                                <th rowspan="2" style="vertical-align: middle; text-align: center;">Towers</th>
                                <th rowspan="2" style="vertical-align: middle; text-align: center;">Infrastructure</th>
                                <th rowspan="2"  style="vertical-align: middle; text-align: center;">Simple Total</th>
                            </tr>
                            <tr>
                                <th style="text-align: center;">Domestic Traffic</th>
                                <th style="text-align: center;">Domestic Network</th>
                                <th style="text-align: center;">International Traffic</th>
                                <th style="text-align: center;">International Network</th>
                            </tr>
                        </thead>
                        <tbody id="pl-final">

                        </tbody>
                    </table>
                </div>                
            </div>
            <div class="space-4"></div>
            <div class="row" id="btn-group-cetak">
                    <div class="col-xs-4"></div>
                    <div class="col-xs-6">
                        <button class="btn btn-warning" id="btn-cetak" onclick="cetak();">Cetak</button>
                        <button class="btn btn-primary" id="btn-download" onclick="download();">Download</button>
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
    function backToProcessControl() {
        loadContentWithParams("report.tblp_batchcontrol_report", { });
    }
</script>

<script>
    // function showData(){
        // var i_search = '';

        // loadDataTable(i_search);
    // }
</script>

<script>


    function openInNewTab(url) {
        window.open(url, 'Cetak', 'left=0,top=0,width=800,height=500,toolbar=no,scrollbars=yes,resizable=yes');
    }

    function cetak() {
            var i_batch_control_id = <?php echo $this->input->post('pbatchcontrolid_pk'); ?>;
            var periodcode = "<?php echo $this->input->post('periodcode'); ?>";
            var periodid_fk = "<?php echo $this->input->post('periodid_fk'); ?>";

            var url = "<?php echo base_url(); ?>"+"pl_final_pdf/pageCetak?";
            url += "<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>";
            url += "&pbatchcontrolid_pk="+i_batch_control_id;
            url += "&periodid_fk="+periodid_fk;
            url += "&periodcode="+periodcode;

            openInNewTab(url);

    }

    function download() {

            var i_batch_control_id = <?php echo $this->input->post('pbatchcontrolid_pk'); ?>;
            var periodcode = "<?php echo $this->input->post('periodcode'); ?>";
            var periodid_fk = "<?php echo $this->input->post('periodid_fk'); ?>";

            var url = "<?php echo WS_JQGRID . "report.pl_final_controller/download_excel/?"; ?>";
            url += "<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>";
            url += "&pbatchcontrolid_pk="+i_batch_control_id;
            url += "&periodid_fk="+periodid_fk;
            url += "&periodcode="+periodcode;

            swal({
                title: "Konfirmasi",
                text: 'Anda yakin ingin melakukan download data?',
                type: "info",
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonText: "Ya, Yakin",
                confirmButtonColor: "#538cf6",
                cancelButtonText: "Tidak",
                closeOnConfirm: true,
                closeOnCancel: true,
                html: true
            },
            function(isConfirm){
                if(isConfirm) {
                    window.location = url;
                    return true;
                }else {
                    return false;
                }
            });
    }

</script>

<script>
    // function loadDataTable(i_search) {
        // $( "#pl-final" ).html( 'Loading...');
        $.ajax({
            url: '<?php echo WS_JQGRID."report.pl_final_controller/readTable"; ?>',
            type: "POST",
            data: {
                i_search : '',
                pbatchcontrolid_pk : <?php echo $this->input->post('pbatchcontrolid_pk'); ?>
            },
            success: function (data) {
                $( "#pl-final" ).html( data );
            },
            error: function (xhr, status, error) {
                swal({title: "Error!", text: xhr.responseText, html: true, type: "error"});
            }
        });
    // }
</script>