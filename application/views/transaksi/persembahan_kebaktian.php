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
            <span>Entry Data Persembahan & Ibadah</span>
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

<?php $this->load->view('lov/lov_jemaat'); ?>


<script>
var disableDays = [];

function showLOVJemaat(id, code, sektor) {
    modal_lov_jemaat_show(id, code, sektor);
}

function clearInputJemaat() {
    $('#form_jemaat_id').val('');
    $('#form_jemaat_nama').val('');
    $('#form_sektor_kode').val('');
}
</script>

<script>

    jQuery(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID."ibadah.persembahan_kebaktian_controller/crud"; ?>',
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'pk_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Jenis Ibadah',name: 'jenis_ibadah',width: 120, align: "left",editable: true, edittype: 'select',
                    editrules: {required: true},
                    editoptions: {
                    value: "Ibadah Minggu:Ibadah Minggu;Ibadah Keluarga:Ibadah Keluarga",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    },
                    dataEvents:[{
                        "type":"change",
                        "fn":function(e){

                            $('#tgl_ibadah').val('');
                            $('#tgl_ibadah').datepicker("remove");

                            if($(e.target).val() == 'Ibadah Minggu') {

                                disableDays = [1,2,3,4,5,6];
                                $('#tgl_ibadah').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: disableDays});
                                $('#form_jemaat_id').val('');
                                $('#form_jemaat_nama').val('');
                                $('#form_sektor_kode').val('');
                                $('#btn-kepala-keluarga').hide();

                            }else if($(e.target).val() == 'Ibadah Keluarga') {
                                disableDays = [0];
                                $('#tgl_ibadah').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: disableDays});
                                $('#btn-kepala-keluarga').show();
                            }
                            $('#tgl_ibadah').datepicker("refresh");


                        }
                    }]
                }},
                {label: 'Tanggal Ibadah',name: 'tgl_ibadah',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 20,
                        maxlength:100
                    },
                    editrules: {required: true}
                },
                {label: 'Bertempat Di Keluarga', name: 'nama_lengkap', width: 200,  editable: false, search:false, sortable:false},
                {label: 'Sektor', name: 'sektor_kode', width: 120,  editable: false, search:false, sortable:false},

                {label: 'Bertempat Di Keluarga',
                    name: 'jemaat_id',
                    width: 200,
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
                                elm.append('<input id="form_jemaat_id" type="text"  style="display:none;">'+
                                        '<input id="form_jemaat_nama" size="30" readonly type="text" class="FormElement form-control" placeholder="Pilih Nama Kepala Keluarga">'+
                                        '<button class="btn btn-success" id="btn-kepala-keluarga" type="button" onclick="showLOVJemaat(\'form_jemaat_id\',\'form_jemaat_nama\',\'form_sektor_kode\')">'+
                                        '   <span class="fa fa-search bigger-110"></span>'+
                                        '</button>'+
                                        ' <input id="form_sektor_kode" size="30" readonly type="text" class="FormElement form-control" placeholder="Sektor">');
                                $("#form_jemaat_id").val(value);
                                elm.parent().removeClass('jqgrid-required');
                            }, 100);

                            return elm;
                        },
                        "custom_value":function( element, oper, gridval) {

                            if(oper === 'get') {
                                return $("#form_jemaat_id").val();
                            } else if( oper === 'set') {
                                $("#form_jemaat_id").val(gridval);
                                var gridId = this.id;
                                // give the editor time to set display
                                setTimeout(function(){
                                    var selectedRowId = $("#"+gridId).jqGrid ('getGridParam', 'selrow');
                                    if(selectedRowId != null) {
                                        var code_display = $("#"+gridId).jqGrid('getCell', selectedRowId, 'nama_lengkap');
                                        var sektor_kode = $("#"+gridId).jqGrid('getCell', selectedRowId, 'sektor_kode');
                                        $("#form_jemaat_nama").val( code_display );
                                        $("#form_sektor_kode").val( sektor_kode );
                                    }
                                },100);
                            }
                        }
                    }
                },

                {label: 'Pelayan Firman',name: 'pelayan_firman',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 30,
                        maxlength:100
                    },
                    editrules: {required: false}
                },

                {label: 'Minggu Ke',name: 'minggu_ke',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 20,
                        maxlength:10
                    },
                    editrules: {required: false},
                    formoptions: {
                        elmsuffix:"&nbsp;&nbsp;<i>(Disesuaikan dengan tanggal ibadah)</i>",
                    }
                },
                {label: 'Total Persembahan',name: 'total_persembahan',width: 150, align: "left", hidden:true, editable: true, number:true,
                    editoptions: {
                        size: 30,
                        maxlength:255,
                        dataInit: function(element) {
                            $(element).keypress(function(e){
                                 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                                    return false;
                                 }
                            });
                            element.style.textAlign = 'right';
                            $(element).number( true, 2);
                        }
                    },
                    editrules: {required: true, edithidden:true}
                },
                {label: 'Jumlah Pria',name: 'total_pria',width: 150, align: "left", hidden:true, editable: true, number:true,
                    editoptions: {
                        size: 20,
                        maxlength:255,
                        dataInit: function(element) {
                            $(element).keypress(function(e){
                                 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                                    return false;
                                 }
                            });
                            element.style.textAlign = 'right';
                            $(element).number( true, 0);
                        }
                    },
                    editrules: {required: true, edithidden:true}
                },
                {label: 'Jumlah Wanita',name: 'total_wanita',width: 150, align: "left", hidden:true, editable: true, number:true,
                    editoptions: {
                        size: 20,
                        maxlength:255,
                        dataInit: function(element) {
                            $(element).keypress(function(e){
                                 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                                    return false;
                                 }
                            });
                            element.style.textAlign = 'right';
                            $(element).number( true, 0);
                        }
                    },
                    editrules: {required: true, edithidden:true}
                },
                {label: 'Jumlah Anak-anak',name: 'total_anak_anak',width: 150, align: "left", hidden:true, editable: true, number:true,
                    editoptions: {
                        size: 20,
                        maxlength:255,
                        dataInit: function(element) {
                            $(element).keypress(function(e){
                                 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                                    return false;
                                 }
                            });
                            element.style.textAlign = 'right';
                            $(element).number( true, 0);
                        }
                    },
                    editrules: {required: true, edithidden:true}
                },
                {label: 'Keterangan',name: 'keterangan',width: 200, align: "left",editable: true,
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
            editurl: '<?php echo WS_JQGRID."ibadah.persembahan_kebaktian_controller/crud"; ?>',
            caption: "Entry Data Persembahan & Ibadah"

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

                    $('#minggu_ke').attr('readonly', true);

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

                    $('#minggu_ke').attr('readonly', true);
                    $('#minggu_ke').val('Generate By System');

                    setTimeout(function() {
                        clearInputJemaat();
                        disableDays = [1,2,3,4,5,6];
                        $('#tgl_ibadah').datepicker({autoclose:true, format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true, daysOfWeekDisabled: disableDays});
                        $('#btn-kepala-keluarga').hide();

                    },100);
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

                    clearInputJemaat();


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