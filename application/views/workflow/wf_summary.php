<!-- breadcrumb -->
<div class="page-bar">
<h3 class="page-title">Task List</h3>
</div>

<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <input type="hidden" id="temp_fsummary" value="workflow.wf_summary" />

    <div class="col-xs-12 col-sm-4" id="summary-panel">

    </div>

    <div class="col-xs-12 col-sm-8" id="user-task-list-panel">
        <div class="portlet box blue-madison">
            <div class="portlet-title">
                <div class="caption"> Daftar Pekerjaan </div>
                <div class="tools">
                    <a class="collapse" href="javascript:;" data-original-title="" title=""> </a>
                </div>
            </div>
            <div class="portlet-body" style="background:#f9f9f9;">
                <div class="row">
                    <div class="col-xs-12 well well-sm" style="margin-bottom:0px;">
                    <div class="form-group">
                        <label class="control-label">&nbsp;</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input class="form-control" type="text" placeholder="Tgl Masuk Inbox" id="filter_date_task_list"/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar icon-on-right bigger-110"></span>
                                </span>
                            </div>
                        </div>

                       <label class="control-label">&nbsp;</label>
                        <div class="col-sm-7">
                            <div class="input-group">
                                <input class="form-control" type="text" placeholder="Pencarian teks" id="filter_search_task_list"/>
                                <span class="input-group-btn">
                                    <button id="btn_filter_task_list" type="button" class="btn btn-danger">
                                        <i class="fa fa-search bigger-130">  </i>
                                        Filter
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12-offset">
                        <table class="table table-bordered summary-table" style="margin-bottom:0px;">
                            <thead>
                                <tr>
                                    <th width="80">Terima</th>
                                    <th>Pekerjaan</th>
                                    <th>Dokumen</th>
                                </tr>
                            </thead>

                            <tbody id="task-list-content">

                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 well well-sm">
                       <div class="col-sm-7">
                            <div id="task-list-pager"></div>
                        </div>
                        <div class="col-sm-5">
                            <span id="pageInfo">View x of n from y data</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var pager_selector = '#task-list-pager';
    var pager_items_on_page = 5;

    $(function() {

        $.ajax({
            type: 'POST',
            url: '<?php echo WS_JQGRID."workflow.wf_controller/summary_box"; ?>',
            data: { p_w_doc_type_id : <?php echo $this->input->post('p_w_doc_type_id'); ?> },
            timeout: 1000000,
            success: function(data) {
                 $("#summary-panel").html(data);
                 var element_id = $('input[name=pilih_summary]:checked').val();
                 openUserTaskList(element_id);
            }
        });

        $(pager_selector).pagination({
            items: 0, /* total data */
            itemsOnPage: pager_items_on_page, /* data pada suatu halaman default 10*/
            cssStyle: 'light-theme',
            onPageClick:function(pageNumber, ev) {
                var element_id = $('input[name=pilih_summary]:checked').val();
                openUserTaskList(element_id);
            }
        });


        $("#filter_date_task_list").datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            orientation : 'bottom',
            todayHighlight : true
        });

        $('#btn_filter_task_list').on('click', function(event) {
            var element_id = $('input[name=pilih_summary]:checked').val();
            openUserTaskList(element_id,1);
        });

        
    });


    function openUserTaskList(element_id, page_number) {
        
        var params = {};
                
        if( typeof page_number == 'undefined' ) {
            params.page = $(pager_selector).pagination('getCurrentPage');
        }else {
            params.page = page_number;
            $(pager_selector).pagination('selectPage', page_number);
            window.location.replace("#");
        }
        
        params.element_id = element_id;
        params.limit = pager_items_on_page;
        params.searchPhrase = $('#filter_search_task_list').val();
        params.tgl_terima = $('#filter_date_task_list').val();

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo WS_JQGRID."workflow.wf_controller/user_task_list"; ?>',
            data: params,
            timeout: 10000,
            success: function(data) {
                 /* update right content */
                 $("#task-list-content").html(data.contents);
                 /* update pager */
                 updatePager(data.total);
            },
            error: function(xhr, textStatus, errorThrown){
                swal("Perhatian", "Summary Error", "warning");
            }
        });
    }

    function loadUserTaskList(choosen_radio, event) {
        event.stopPropagation();
        $('#filter_date_task_list').datepicker('setDate', null);
        $('#filter_search_task_list').val("");
        openUserTaskList(choosen_radio.value, 1);
        
    }

    function updatePager(total_data) {
        $(pager_selector).pagination('updateItems', total_data);
        var currentPage = $(pager_selector).pagination('getCurrentPage');
        var totalPages = $(pager_selector).pagination('getPagesCount');

        if(currentPage > totalPages) {
            currentPage = 1;
            $(pager_selector).pagination('selectPage', 1);
        }

        $('#pageInfo').html('<strong>View Page ' + currentPage + ' of ' + totalPages + ' ( Total : ' + total_data + ' data ) </strong>');
    }

    function loadWFForm(file_name, wfobj) {

        if( wfobj.user_id_login == '' || wfobj.user_id_login == null ) {
            swal("Perhatian", "Session Anda habis. Silahkan login kembali", "warning");
            return;
        }

        if( file_name == '' ) {
            swal("Perhatian", "File Name Kosong", "warning");
            return;
        }

        if(wfobj.action_status == 'TERIMA') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo WS_JQGRID."workflow.wf_controller/taken_task"; ?>',
                data: {curr_t_order_control_id : wfobj.curr_t_order_control_id},
                timeout: 10000,
                success: function(data) {
                    if( data.success )
                        loadContentWithParams( file_name , wfobj );
                    else
                        swal("Perhatian", "Taken Task Error", "warning");
                },
                error: function(xhr, textStatus, errorThrown){
                    swal("Perhatian", "Summary Error", "warning");
                }
            });
        }else {
            loadContentWithParams( file_name , wfobj );
        }
    }



</script>

