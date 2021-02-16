<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url(); ?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="#">Parameter</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Pengaturan Aliran Pekerjaan Workflow</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <div class="col-xs-12">
        <div class="tab-content no-border">
            <div class="row">
                <div class="col-xs-12">
                   <table id="grid-table"></table>
                   <div id="grid-pager"></div>
                </div>
            </div>
            <div class="space-4"></div>
            <hr>
            <div class="row">
                <div class="col-md-6" id="prev_detail_placeholder" style="display:none;">
                    <table id="prev-grid-table-detail"></table>
                    <div id="prev-grid-pager-detail"></div>
                </div>
                <div class="col-md-3" id="next_detail_placeholder" style="display:none;">
                    <table id="next-grid-table-detail"></table>
                    <div id="next-grid-pager-detail"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('lov/lov_job_wf'); ?>

<script>
function showLOVJobWF(id, code) {
    modal_lov_job_wf_show(id, code);
}

function clearInputJobWF() {
    $('#form_job_wf_id').val('');
    $('#form_job_wf_name').val('');
}

function clearInputNextJobWF() {
    $('#form_next_job_wf_id').val('');
    $('#form_next_job_wf_name').val('');
}

</script>

<script>

    jQuery(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID."workflow.p_document_type_controller/crud"; ?>',
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'p_document_type_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Nama Dokumen',name: 'document_name',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 30,
                        maxlength:50
                    },
                    editrules: {required: true}
                },
                {label: 'Description',name: 'description',width: 200, align: "left",editable: true,
                    edittype:'textarea',
                    editoptions: {
                        rows: 2,
                        cols:50
                    }
                }
            ],
            height: '100%',
            autowidth: true,
            viewrecords: true,
            rowNum: 10,
            rowList: [10,20,50],
            rownumbers: true, // show row numbers
            rownumWidth: 35, // the width of the row numbers columns
            altRows: true,
            shrinkToFit: true,
            multiboxonly: true,
            onSelectRow: function (rowid) {
                
                /*do something when selected*/
                var celValue = $('#grid-table').jqGrid('getCell', rowid, 'p_document_type_id');
                var celCode = $('#grid-table').jqGrid('getCell', rowid, 'document_name');

                var grid_detail = jQuery("#prev-grid-table-detail");
                if (rowid != null) {
                    grid_detail.jqGrid('setGridParam', {
                        url: "<?php echo WS_JQGRID."workflow.p_workflow_controller/crud"; ?>",
                        postData: {p_document_type_id: celValue}
                    });
                    var strCaption = 'Aliran Pekerjaan Workflow :: ' + celCode;
                    grid_detail.jqGrid('setCaption', strCaption);
                    
                    $("#prev-grid-table-detail").trigger("reloadGrid");
                    $("#prev_detail_placeholder").show();
                }
                responsive_jqgrid("#prev-grid-table-detail", "#prev-grid-pager-detail");

            },
            sortorder:'',
            pager: '#grid-pager',
            jsonReader: {
                root: 'rows',
                id: 'id',
                repeatitems: false
            },
            loadComplete: function (response) {
                if(response.success == false) {
                    swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                }

            },
            //memanggil controller jqgrid yang ada di controller crud
            editurl: '<?php echo WS_JQGRID."workflow.p_document_type_controller/crud"; ?>',
            caption: "Pengaturan Aliran Pekerjaan Workflow"

        });

        jQuery('#grid-table').jqGrid('navGrid', '#grid-pager',
            {   //navbar options
                edit: false,
                editicon: 'fa fa-pencil blue bigger-120',
                add: false,
                addicon: 'fa fa-plus-circle purple bigger-120',
                del: false,
                delicon: 'fa fa-trash-o red bigger-120',
                search: true,
                searchicon: 'fa fa-search orange bigger-120',
                refresh: true,
                afterRefresh: function () {
                    // some code here
                    $("#prev_detail_placeholder").hide();
                    $("#next_detail_placeholder").hide();
                },

                refreshicon: 'fa fa-refresh green bigger-120',
                view: false,
                viewicon: 'fa fa-search-plus grey bigger-120'
            },

            {
                // options for the Edit Dialog
                closeAfterEdit: true,
                closeOnEscape:true,
                recreateForm: true,
                serializeEditData: serializeJSON,
                width: 'auto',
                errorTextFormat: function (data) {
                    return 'Error: ' + data.responseText
                },
                beforeShowForm: function (e, form) {
                    var form = $(e[0]);
                    style_edit_form(form);

                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();
                },
                afterSubmit:function(response,postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if(response.success == false) {
                        return [false,response.message,response.responseText];
                    }
                    return [true,"",response.responseText];
                }
            },
            {
                //new record form
                closeAfterAdd: false,
                clearAfterAdd : true,
                closeOnEscape:true,
                recreateForm: true,
                width: 'auto',
                errorTextFormat: function (data) {
                    return 'Error: ' + data.responseText
                },
                serializeEditData: serializeJSON,
                viewPagerButtons: false,
                beforeShowForm: function (e, form) {
                    var form = $(e[0]);
                    style_edit_form(form);
                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();
                },
                afterSubmit:function(response,postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if(response.success == false) {
                        return [false,response.message,response.responseText];
                    }

                    $(".tinfo").html('<div class="ui-state-success">' + response.message + '</div>');
                    var tinfoel = $(".tinfo").show();
                    tinfoel.delay(3000).fadeOut();


                    return [true,"",response.responseText];
                }
            },
            {
                //delete record form
                serializeDelData: serializeJSON,
                recreateForm: true,
                beforeShowForm: function (e) {
                    var form = $(e[0]);
                    style_delete_form(form);

                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();
                },
                onClick: function (e) {
                    //alert(1);
                },
                afterSubmit:function(response,postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if(response.success == false) {
                        return [false,response.message,response.responseText];
                    }
                    return [true,"",response.responseText];
                }
            },
            {
                //search form
                closeAfterSearch: false,
                recreateForm: true,
                afterShowSearch: function (e) {
                    var form = $(e[0]);
                    style_search_form(form);
                    form.closest('.ui-jqdialog').center();
                },
                afterRedraw: function () {
                    style_search_filters($(this));
                }
            },
            {
                //view record form
                recreateForm: true,
                beforeShowForm: function (e) {
                    var form = $(e[0]);
                }
            }
        );

    });

    /* ------------------------------  detail grid --------------------------------*/
    jQuery("#prev-grid-table-detail").jqGrid({
        url: "<?php echo WS_JQGRID.'workflow.p_workflow_controller/crud'; ?>",
        datatype: "json",
        mtype: "POST",
        colModel: [
            {label: 'ID', name: 'p_workflow_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
            //{label: 'User ID', name: 'user_id', width: 5, sorttype: 'number', editable: true, hidden: true},
            {label: 'No. Urut Proses',name: 'order_list_no',width: 80, align: "right",editable: true, hidden:false,
                editoptions: {
                    size: 10,
                    maxlength:4,

                },
                editrules: {edithidden: true, required:true}
            },
            {label: 'Role', name: 'prev_role_name', width: 100, align: "left", editable: false},
            {label: 'Pekerjaan Sebelumnya', name: 'prev_job_wf_name', width: 120, align: "left", editable: false},
            {label: 'Pekerjaan Sebelumnya',
                name: 'prev_job_wf_id',
                width: 300,
                sortable: true,
                editable: true,
                hidden: true,
                editrules: {edithidden: true, required:true},
                edittype: 'custom',
                editoptions: {
                    "custom_element":function( value  , options) {
                        var elm = $('<span></span>');

                        // give the editor time to initialize
                        setTimeout( function() {
                            elm.append('<input id="form_prev_job_wf_id" type="text"  style="display:none;">'+
                                    '<input id="form_prev_job_wf_name" size="25" readonly type="text" class="FormElement form-control" placeholder="Pekerjaan Sebelumnya" required="">'+
                                    '<button class="btn btn-success" type="button" onclick="showLOVJobWF(\'form_prev_job_wf_id\',\'form_prev_job_wf_name\')">'+
                                    '   <span class="fa fa-search bigger-110"></span>'+
                                    '</button>');
                            $("#form_prev_job_wf_id").val(value);
                            elm.parent().removeClass('jqgrid-required');
                        }, 100);

                        return elm;
                    },
                    "custom_value":function( element, oper, gridval) {

                        if(oper === 'get') {
                            return $("#form_prev_job_wf_id").val();
                        } else if( oper === 'set') {
                            $("#form_prev_job_wf_id").val(gridval);
                            var gridId = this.id;
                            // give the editor time to set display
                            setTimeout(function(){
                                var selectedRowId = $("#"+gridId).jqGrid ('getGridParam', 'selrow');
                                if(selectedRowId != null) {
                                    var code_display = $("#"+gridId).jqGrid('getCell', selectedRowId, 'prev_job_wf_name');
                                    $("#form_prev_job_wf_name").val( code_display );
                                }
                            },100);
                        }
                    }
                }
            },
            {label: 'Pekerjaan Berikutnya', name: 'next_job_wf_name', width: 120, align: "left", editable: false, hidden:true},
            {label: 'Pekerjaan Berikutnya',
                name: 'next_job_wf_id',
                width: 300,
                sortable: true,
                editable: true,
                hidden: true,
                editrules: {edithidden: true, required:false},
                edittype: 'custom',
                editoptions: {
                    "custom_element":function( value  , options) {
                        var elm = $('<span></span>');

                        // give the editor time to initialize
                        setTimeout( function() {
                            elm.append('<input id="form_next_job_wf_id" type="text"  style="display:none;">'+
                                    '<input id="form_next_job_wf_name" readonly type="text" class="FormElement form-control" size="25" placeholder="Pekerjaan Berikutnya">'+
                                    '<button class="btn btn-success" type="button" onclick="showLOVJobWF(\'form_next_job_wf_id\',\'form_next_job_wf_name\')">'+
                                    '   <span class="fa fa-search bigger-110"></span>'+
                                    '</button>');
                            $("#form_next_job_wf_id").val(value);
                            elm.parent().removeClass('jqgrid-required');
                        }, 100);

                        return elm;
                    },
                    "custom_value":function( element, oper, gridval) {

                        if(oper === 'get') {
                            return $("#form_next_job_wf_id").val();
                        } else if( oper === 'set') {
                            $("#form_next_job_wf_id").val(gridval);
                            var gridId = this.id;
                            // give the editor time to set display
                            setTimeout(function(){
                                var selectedRowId = $("#"+gridId).jqGrid ('getGridParam', 'selrow');
                                if(selectedRowId != null) {
                                    var code_display = $("#"+gridId).jqGrid('getCell', selectedRowId, 'next_job_wf_name');
                                    $("#form_next_job_wf_name").val( code_display );
                                }
                            },100);
                        }
                    }
                }
            },
        ],
        height: '100%',
        //autowidth: false,
        width:500,
        viewrecords: false,
        rowNum: 5,
        rowList: [5, 10, 20],
        rownumbers: false, // show row numbers
        rownumWidth: 35, // the width of the row numbers columns
        altRows: true,
        shrinkToFit: true,
        multiboxonly: true,
        onSelectRow: function (rowid) {
            /*do something when selected*/
            var celValue = $('#prev-grid-table-detail').jqGrid('getCell', rowid, 'p_workflow_id');
            
            var grid_detail = jQuery("#next-grid-table-detail");
            if (rowid != null) {
                grid_detail.jqGrid('setGridParam', {
                    url: "<?php echo WS_JQGRID."workflow.p_workflow_controller/read_next"; ?>",
                    postData: {p_workflow_id: celValue}
                });
                
                $("#next-grid-table-detail").trigger("reloadGrid");
                $("#next_detail_placeholder").show();
            }
            responsive_jqgrid("#next-grid-table-detail", "#next-grid-pager-detail");

        },
        sortorder:'',
        pager: '#prev-grid-pager-detail',
        jsonReader: {
            root: 'rows',
            id: 'id',
            repeatitems: false
        },
        loadComplete: function (response) {
            if(response.success == false) {
                swal({title: 'Attention', text: response.message, html: true, type: "warning"});
            }

        },
        //memanggil controller jqgrid yang ada di controller crud
        editurl: "<?php echo WS_JQGRID.'workflow.p_workflow_controller/crud'; ?>",
        caption: "Aliran Pekerjaan Workflow"

    });

    jQuery('#prev-grid-table-detail').jqGrid('navGrid', '#prev-grid-pager-detail',
        {   //navbar options
            edit: false,
            editicon: 'fa fa-pencil blue bigger-120',
            add: true,
            addicon: 'fa fa-plus-circle purple bigger-120',
            del: false,
            delicon: 'fa fa-trash-o red bigger-120',
            search: true,
            searchicon: 'fa fa-search orange bigger-120',
            refresh: true,
            afterRefresh: function () {
                // some code here
                $("#next_detail_placeholder").hide();
            },

            refreshicon: 'fa fa-refresh green bigger-120',
            view: false,
            viewicon: 'fa fa-search-plus grey bigger-120'
        },

        {
            // options for the Edit Dialog
            closeAfterEdit: true,
            closeOnEscape:true,
            recreateForm: true,
            serializeEditData: serializeJSON,
            viewPagerButtons: false,
            width: 'auto',
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            },
            beforeShowForm: function (e, form) {
                var form = $(e[0]);
                style_edit_form(form);
            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            afterSubmit:function(response,postdata) {
                var response = jQuery.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }
                return [true,"",response.responseText];
            }
        },
        {
            //new record form
            editData: {
                p_document_type_id: function() {
                    var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
                    var p_document_type_id = $("#grid-table").jqGrid('getCell', selRowId, 'p_document_type_id');

                    return p_document_type_id;
                }
            },
            serializeEditData: serializeJSON,
            //new record form
            closeAfterAdd: true,
            clearAfterAdd : true,
            closeOnEscape:true,
            recreateForm: true,
            width: 'auto',
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            },
            viewPagerButtons: false,
            beforeShowForm: function (e, form) {
                var form = $(e[0]);
                style_edit_form(form);
                setTimeout(function() {
                    clearInputJobWF();
                    clearInputNextJobWF();
                },100);
            },
            beforeInitData: function () {
                $('#prev-grid-table-detail').jqGrid('resetSelection');
            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            afterSubmit:function(response,postdata) {
                var response = jQuery.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }

                $(".tinfo").html('<div class="ui-state-success">' + response.message + '</div>');
                var tinfoel = $(".tinfo").show();
                tinfoel.delay(3000).fadeOut();

                clearInputJobWF();
                clearInputNextJobWF();

                return [true,"",response.responseText];
            }
        },
        {
            //delete record form
            serializeDelData: serializeJSON,
            recreateForm: true,
            beforeShowForm: function (e) {
                var form = $(e[0]);
                style_delete_form(form);

            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            onClick: function (e) {
                //alert(1);
            },
            afterSubmit:function(response,postdata) {
                var response = jQuery.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }
                return [true,"",response.responseText];
            }
        },
        {
            //search form
            closeAfterSearch: false,
            recreateForm: true,
            afterShowSearch: function (e) {
                var form = $(e[0]);
                style_search_form(form);

                form.closest('.ui-jqdialog').center();
            },
            afterRedraw: function () {
                style_search_filters($(this));
            }
        },
        {
            //view record form
            recreateForm: true,
            beforeShowForm: function (e) {
                var form = $(e[0]);

            }
        }
    );


    /*------------------------------ next job --------------------- */
    /* ------------------------------  detail grid --------------------------------*/
    jQuery("#next-grid-table-detail").jqGrid({
        url: "<?php echo WS_JQGRID.'workflow.p_workflow_controller/read_next'; ?>",
        datatype: "json",
        mtype: "POST",
        colModel: [
            {label: 'ID', name: 'p_workflow_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
            {label: 'No. Urut',name: 'order_list_no',width: 50, align: "right",editable: true, hidden:true,
                editoptions: {
                    size: 10,
                    maxlength:4,

                },
                editrules: {edithidden: true, required:true}
            },
            {label: 'Pekerjaan Sebelumnya',name: 'prev_job_wf_id',width: 120, align: "right",editable: true, hidden:true,
                editoptions: {
                    size: 50
                }
            },
            
            {label: 'Pekerjaan Sebelumnya',name: 'prev_job_wf_name',width: 120, align: "right",editable: true, hidden:true,
                editoptions: {
                    size: 50
                },
                editrules: {edithidden: true, required:true}
            },
            {label: 'Pekerjaan Berikutnya', name: 'next_job_wf_name', width: 120, align: "left", editable: false},
            {label: 'Pekerjaan Berikutnya',
                name: 'next_job_wf_id',
                width: 300,
                sortable: true,
                editable: true,
                hidden: true,
                editrules: {edithidden: true, required:false},
                edittype: 'custom',
                editoptions: {
                    "custom_element":function( value  , options) {
                        var elm = $('<span></span>');

                        // give the editor time to initialize
                        setTimeout( function() {
                            elm.append('<input id="form_next_job_wf_id" type="text"  style="display:none;">'+
                                    '<input id="form_next_job_wf_name" readonly type="text" class="FormElement form-control" size="25" placeholder="Pekerjaan Berikutnya">'+
                                    '<button class="btn btn-success" type="button" onclick="showLOVJobWF(\'form_next_job_wf_id\',\'form_next_job_wf_name\')">'+
                                    '   <span class="fa fa-search bigger-110"></span>'+
                                    '</button>');
                            $("#form_next_job_wf_id").val(value);
                            elm.parent().removeClass('jqgrid-required');
                        }, 100);

                        return elm;
                    },
                    "custom_value":function( element, oper, gridval) {

                        if(oper === 'get') {
                            return $("#form_next_job_wf_id").val();
                        } else if( oper === 'set') {
                            $("#form_next_job_wf_id").val(gridval);
                            var gridId = this.id;
                            // give the editor time to set display
                            setTimeout(function(){
                                var selectedRowId = $("#"+gridId).jqGrid ('getGridParam', 'selrow');
                                if(selectedRowId != null) {
                                    var code_display = $("#"+gridId).jqGrid('getCell', selectedRowId, 'next_job_wf_name');
                                    $("#form_next_job_wf_name").val( code_display );
                                }
                            },100);
                        }
                    }
                }
            }
        ],
        height: '100%',
        //autowidth: false,
        width:500,
        viewrecords: false,
        rowNum: 5,
        rowList: [5, 10, 20],
        rownumbers: false, // show row numbers
        rownumWidth: 35, // the width of the row numbers columns
        altRows: true,
        shrinkToFit: true,
        multiboxonly: true,
        onSelectRow: function (rowid) {
            /*do something when selected*/
        },
        sortorder:'',
        pager: '#next-grid-pager-detail',
        jsonReader: {
            root: 'rows',
            id: 'id',
            repeatitems: false
        },
        loadComplete: function (response) {
            if(response.success == false) {
                swal({title: 'Attention', text: response.message, html: true, type: "warning"});
            }

        },
        //memanggil controller jqgrid yang ada di controller crud
        editurl: "<?php echo WS_JQGRID.'workflow.p_workflow_controller/crud'; ?>",
        caption: "Aliran Pekerjaan Workflow"

    });

    jQuery('#next-grid-table-detail').jqGrid('navGrid', '#next-grid-pager-detail',
        {   //navbar options
            edit: true,
            editicon: 'fa fa-pencil blue bigger-120',
            add: false,
            addicon: 'fa fa-plus-circle purple bigger-120',
            del: true,
            delicon: 'fa fa-trash-o red bigger-120',
            search: false,
            searchicon: 'fa fa-search orange bigger-120',
            refresh: true,
            afterRefresh: function () {
                // some code here
            },

            refreshicon: 'fa fa-refresh green bigger-120',
            view: false,
            viewicon: 'fa fa-search-plus grey bigger-120'
        },

        {
            // options for the Edit Dialog
            closeAfterEdit: true,
            closeOnEscape:true,
            recreateForm: true,
            serializeEditData: serializeJSON,
            viewPagerButtons: false,
            width: 'auto',
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            },
            beforeShowForm: function (e, form) {
                var form = $(e[0]);
                style_edit_form(form);
            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            afterSubmit:function(response,postdata) {
                var response = jQuery.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }
                return [true,"",response.responseText];
            }
        },
        {
            //new record form
            editData: {
                p_document_type_id: function() {
                    var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
                    var p_document_type_id = $("#grid-table").jqGrid('getCell', selRowId, 'p_document_type_id');

                    return p_document_type_id;
                }
            },
            serializeEditData: serializeJSON,
            //new record form
            closeAfterAdd: true,
            clearAfterAdd : true,
            closeOnEscape:true,
            recreateForm: true,
            width: 'auto',
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            },
            viewPagerButtons: false,
            beforeShowForm: function (e, form) {
                var form = $(e[0]);
                style_edit_form(form);
                setTimeout(function() {
                    clearInputJobWF();
                    clearInputNextJobWF();
                },100);
            },
            beforeInitData: function () {
                $('#next-grid-table-detail').jqGrid('resetSelection');
            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            afterSubmit:function(response,postdata) {
                var response = jQuery.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }

                $(".tinfo").html('<div class="ui-state-success">' + response.message + '</div>');
                var tinfoel = $(".tinfo").show();
                tinfoel.delay(3000).fadeOut();

                clearInputJobWF();
                clearInputNextJobWF();

                return [true,"",response.responseText];
            }
        },
        {
            //delete record form
            serializeDelData: serializeJSON,
            recreateForm: true,
            beforeShowForm: function (e) {
                var form = $(e[0]);
                style_delete_form(form);

            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            onClick: function (e) {
                //alert(1);
            },
            afterSubmit:function(response,postdata) {
                var response = jQuery.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }
                return [true,"",response.responseText];
            }
        },
        {
            //search form
            closeAfterSearch: false,
            recreateForm: true,
            afterShowSearch: function (e) {
                var form = $(e[0]);
                style_search_form(form);

                form.closest('.ui-jqdialog').center();
            },
            afterRedraw: function () {
                style_search_filters($(this));
            }
        },
        {
            //view record form
            recreateForm: true,
            beforeShowForm: function (e) {
                var form = $(e[0]);

            }
        }
    );

    function responsive_jqgrid(grid_selector, pager_selector) {

        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(grid_selector).jqGrid( 'setGridWidth', $(grid_selector).width() );
        $(pager_selector).jqGrid( 'setGridWidth', parent_column.width() );

    }

</script>