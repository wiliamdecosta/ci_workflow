<script src="<?php echo base_url(); ?>assets/tinymce/tinymce.min.js"></script>

<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url(); ?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="#">Data Master</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Warta Jemaat</span>
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
        </div>
    </div>
</div>

<script>

    function downloadWartaJemaat(warta_jemaat_id) {
        var url = "<?php echo WS_JQGRID . "ibadah.warta_jemaat_controller/download_warta/?"; ?>";
        url += "<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>";
        url += "&warta_jemaat_id="+warta_jemaat_id;
        window.location = url;
    }

    function viewWartaJemaat(warta_jemaat_id) {
        var url = "<?php echo WS_JQGRID . "ibadah.warta_jemaat_controller/view_warta/?"; ?>";
        url += "<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>";
        url += "&warta_jemaat_id="+warta_jemaat_id;

        window.open(url, "Warta Jemaat", "width="+screen.availWidth+",height="+screen.availHeight);
    }


</script>

<script>

    jQuery(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID."ibadah.warta_jemaat_controller/crud"; ?>',
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'warta_jemaat_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Download Warta',width: 250,align: "center",
                    formatter:function(cellvalue, options, rowObject) {
                        var key = rowObject['warta_jemaat_id'];
                        return '<button class="btn btn-primary btn-xs" onclick="downloadWartaJemaat('+key+')">Download Warta</button> <button class="btn btn-default btn-xs" onclick="viewWartaJemaat('+key+')">View Warta</button>';
                    }
                },
                {label: 'Tema Minggu',name: 'tema_minggu',width: 250, align: "left",editable: true,
                    editoptions: {
                        size: 80,
                        maxlength:100
                    },
                    editrules: {required: false}
                },
                {label: 'Tanggal Pembuatan',name: 'tgl_pembuatan_warta',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 20,
                        maxlength:100
                    },
                    editrules: {required: true},
                    formoptions: {
                        elmsuffix:"&nbsp;&nbsp;<i>(Hanya di hari kerja)</i>",
                    }
                },
                {label: 'Tanggal Terbit Warta',name: 'tgl_terbit_warta',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 20,
                        maxlength:100
                    },
                    editrules: {required: true},
                    formoptions: {
                        elmsuffix:"&nbsp;&nbsp;<i>(Hanya di hari minggu)</i>",
                    }
                },

                {label: 'PF Minggu',name: 'pf_minggu',width: 200, align: "left",editable: true,
                    editoptions: {
                        size: 30,
                        maxlength:50
                    },
                    editrules: {required: false}
                },
                {label: 'PF dari Gereja',name: 'pf_gereja',width: 200, align: "left",editable: true,
                    editoptions: {
                        size: 30,
                        maxlength:50
                    },
                    editrules: {required: false}
                },

                {label: 'Kalimat Pembuka', name: 'kalimat_pembuka', width: 150, editable: true,
                    editrules:{
                       required:false,
                       edithidden:true
                    },
                    hidden:true,
                    align: "left",
                    edittype: 'custom',
                    editoptions: {
                        "custom_element":function( value  , options) {
                            var elm = $('<textarea class="mceEditor"></textarea>');
                            elm.val( value );
                            // give the editor time to initialize
                            setTimeout( function() {
                                try {
                                    tinymce.remove("#" + options.id);
                                } catch(ex) {}
                                tinymce.init({ mode:"specific_textareas", width:700, height:"200", editor_selector : "mceEditor", statusbar:false, menubar:true,
                                    plugins: [
                                        'table'
                                    ]
                                });
                            }, 100);

                            return elm;
                        },
                        "custom_value":function( element, oper, gridval) {
                            if(oper === 'get') {
                                return tinymce.get('kalimat_pembuka').getContent({format: 'row'});
                            } else if( oper === 'set') {
                                if(tinymce.get('kalimat_pembuka')) {
                                    tinymce.get('kalimat_pembuka').setContent( gridval );
                                }
                            }
                        }
                    }
                },
                {label: 'Informasi Lain', name: 'other_info', width: 150, editable: true,
                    editrules:{
                       required:false,
                       edithidden:true
                    },
                    hidden:true,
                    align: "left",
                    edittype: 'custom',
                    editoptions: {
                        "custom_element":function( value  , options) {
                            var elm = $('<textarea class="mceEditor1"></textarea>');
                            elm.val( value );
                            // give the editor time to initialize
                            setTimeout( function() {
                                try {
                                    tinymce.remove("#" + options.id);
                                } catch(ex) {}
                                tinymce.init({ mode:"specific_textareas", width:700, height:"200", editor_selector : "mceEditor1", statusbar:false, menubar:true,
                                    plugins: [
                                        'table'
                                    ]
                                });
                            }, 100);

                            return elm;
                        },
                        "custom_value":function( element, oper, gridval) {
                            if(oper === 'get') {
                                return tinymce.get('other_info').getContent({format: 'row'});
                            } else if( oper === 'set') {
                                if(tinymce.get('other_info')) {
                                    tinymce.get('other_info').setContent( gridval );
                                }
                            }
                        }
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
            shrinkToFit: false,
            multiboxonly: true,
            onSelectRow: function (rowid) {
                /*do something when selected*/

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
            editurl: '<?php echo WS_JQGRID."ibadah.warta_jemaat_controller/crud"; ?>',
            caption: "Warta Jemaat"

        });

        jQuery('#grid-table').jqGrid('navGrid', '#grid-pager',
            {   //navbar options
                edit: true,
                editicon: 'fa fa-pencil blue bigger-120',
                add: true,
                addicon: 'fa fa-plus-circle purple bigger-120',
                del: true,
                delicon: 'fa fa-trash-o red bigger-120',
                search: true,
                searchicon: 'fa fa-search orange bigger-120',
                refresh: true,
                afterRefresh: function () {
                    // some code here
                    jQuery("#detailsPlaceholder").hide();
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

                    $('#tgl_pembuatan_warta').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: [0]});
                    $('#tgl_terbit_warta').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: [1,2,3,4,5,6]});

                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();

                    form.closest('.ui-jqdialog').css('max-height','500px');
                    form.closest('.ui-jqdialog').css('width','900px');
                    form.closest('.ui-jqdialog').css('overflow','scroll');
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
                closeAfterAdd: true,
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

                    $('#tgl_pembuatan_warta').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: [0]});
                    $('#tgl_terbit_warta').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: [1,2,3,4,5,6]});
                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();

                    form.closest('.ui-jqdialog').css('max-height','500px');
                    form.closest('.ui-jqdialog').css('width','900px');
                    form.closest('.ui-jqdialog').css('overflow','scroll');
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

    function responsive_jqgrid(grid_selector, pager_selector) {

        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() );
        $(pager_selector).jqGrid( 'setGridWidth', parent_column.width() );

    }

</script>