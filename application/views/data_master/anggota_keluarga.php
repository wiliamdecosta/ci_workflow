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
            <span>Jemaat</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <div class="col-xs-12">
        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="">
                    <a href="javascript:;" data-toggle="tab" aria-expanded="true" id="tab-1">
                        <i class="blue"></i>
                        <strong> Kepala Keluarga </strong>
                    </a>
                </li>
                <li class="active">
                    <a href="javascript:;" data-toggle="tab" aria-expanded="true" id="tab-2">
                        <i class="blue"></i>
                        <strong> Anggota Keluarga </strong>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content no-border">
            <h4>Anggota Keluarga Bpk/Ibu : <?php echo $this->input->post('nama_kk'); ?>  ( <?php echo $this->input->post('sektor_kode'); ?> )</h4>
            <div class="row">
                <label class="control-label col-md-2">Pencarian :</label>
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group">
                        <input id="i_search" type="text" class="FormElement form-control" size="50">
                        <span class="input-group-btn">
                            <button class="btn btn-success" type="button" id="btn-search" onclick="showData()">Cari</button>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                   <table id="grid-table"></table>
                   <div id="grid-pager"></div>
                </div>
            </div>
            <div class="space-4"></div>
            <hr>
            <div class="row" id="detail_placeholder" style="display:none;">
                <div class="col-xs-12">
                    <table id="grid-table-detail"></table>
                    <div id="grid-pager-detail"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('lov/lov_tingkat_pendidikan'); ?>

<script>
    $("#tab-1").on("click", function(event) {

        event.stopPropagation();
        loadContentWithParams("data_master.jemaat",{});
    });
</script>

<script>
    function showData(){
        var i_search = $('#i_search').val();
        var kk_id = <?php echo $this->input->post('kk_id'); ?>;

        jQuery("#grid-table").jqGrid('setGridParam',{
            url: '<?php echo WS_JQGRID."data_master.jemaat_controller/readAnggotaKeluarga"; ?>',
            postData: {
                i_search : i_search,
                kk_id: kk_id
            }
        });
        $("#grid-table").trigger("reloadGrid");
        responsive_jqgrid('#grid-table', '#grid-pager');
    }
</script>


<script>
function showLOVTingkatPendidikan(id, code) {
    modal_lov_tingkat_pendidikan_show(id, code);
}

function clearInputTingkatPendidikan() {
    $('#form_tingkat_pendidikan_id').val('');
    $('#form_tingkat_pendidikan_nama').val('');
}
</script>

<script>

    jQuery(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID."data_master.jemaat_controller/readAnggotaKeluarga"; ?>',
            postData: { kk_id : <?php echo $this->input->post('kk_id'); ?>},
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'jemaat_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Sektor', name: 'sektor_id', width: 250, align: "left", editable: true, hidden:true,
                    editrules: {edithidden: true, required:true},
                    edittype: 'select',
                    editoptions: {
                        dataUrl: "<?php echo WS_JQGRID.'data_master.sektor_controller/html_select_options'; ?>",
                        dataInit: function(elem) {
                            $(elem).width(250);  // set the width which you need
                        },
                        buildSelect: function (data) {
                            try {
                                var response = $.parseJSON(data);
                                if(response.success == false) {
                                    swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                                    return "";
                                }
                            }catch(err) {
                                return data;
                            }
                        }
                    }
                },
                {label: 'Status Anggota Kel', name: 'status_display', width: 120,  editable: false, search:false, sortable:false},
                {label: 'Status Anggota Kel',name: 'status_anggota_kel',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: "ISTRI:Istri;SUAMI:Suami;ANAK:Anak",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    }
                }},
                {label: 'Nomor Induk Jemaat',name: 'nomor_induk_jemaat',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 25,
                        maxlength:6
                    },
                    editrules: {required: true}
                },
                {label: 'Nama Lengkap',name: 'nama_lengkap',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 50,
                        maxlength:100
                    },
                    editrules: {required: true}
                },
                {label: 'Tempat Lahir',name: 'tempat_lahir',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 30,
                        maxlength:100
                    },
                    editrules: {required: true}
                },
                {label: 'Tanggal Lahir',name: 'tanggal_lahir',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 20,
                        maxlength:100
                    },
                    editrules: {required: true}
                },
                {label: 'Jenis Kelamin',name: 'jenis_kelamin',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: "L:Laki-laki;P:Perempuan",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    }
                }},
                {label: 'Alamat',name: 'alamat_jemaat',width: 200, align: "left",editable: true,
                    edittype:'textarea',
                    editoptions: {
                        rows: 2,
                        cols:50
                    }
                },
                {label: 'No.Telp',name: 'no_telp',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 30,
                        maxlength:50
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'No.KTP',name: 'no_ktp',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 30,
                        maxlength:50
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'Email',name: 'email',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 30,
                        maxlength:50
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'Status Baptis',name: 'status_baptis',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: ": Pilih Status Baptis;Y:Sudah Dibaptis;N:Belum Dibaptis",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    }
                }},
                {label: 'Tempat Baptis',name: 'tempat_baptis',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 50,
                        maxlength:50
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'Status Sidi',name: 'status_sidi',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: ": Pilih Status Sidi;Y:Sudah Disidi;N:Belum Disidi",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    }
                }},
                {label: 'Tempat Sidi',name: 'tempat_sidi',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 50,
                        maxlength:50
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'Status Nikah',name: 'status_nikah',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: ": Pilih Status Nikah;Y:Sudah Menikah;N:Belum Menikah",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    }
                }},
                {label: 'Tempat Menikah',name: 'tempat_nikah',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 50,
                        maxlength:50
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'Pekerjaan',name: 'pekerjaan_jemaat',width: 80, align: "left",editable: true, hidden:true,
                    editoptions: {
                        size: 50,
                        maxlength:100
                    },
                    editrules: {required: false, edithidden:true}
                },
                {label: 'Jumlah Penghasilan',name: 'jumlah_penghasilan',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: ":Belum Berpenghasilan;A: < Rp.1Jt;B: >= Rp.1Jt <= Rp.5jt;C: > Rp.5jt <= Rp.10Jt;D: > Rp.10Jt",
                    dataInit: function(elem) {
                        $(elem).width(180);  // set the width which you need
                    }
                }},


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
                var celValue = $('#grid-table').jqGrid('getCell', rowid, 'jemaat_id');
                var celCode = $('#grid-table').jqGrid('getCell', rowid, 'nama_lengkap');

                var grid_detail = jQuery("#grid-table-detail");
                if (rowid != null) {
                    grid_detail.jqGrid('setGridParam', {
                        url: "<?php echo WS_JQGRID."data_master.pendidikan_jemaat_controller/crud"; ?>",
                        postData: {jemaat_id: celValue}
                    });
                    var strCaption = 'Pendidikan Jemaat :: ' + celCode;
                    grid_detail.jqGrid('setCaption', strCaption);

                    $("#grid-table-detail").trigger("reloadGrid");
                    $("#detail_placeholder").show();
                }
                responsive_jqgrid("#grid-table-detail", "#grid-pager-detail");

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
            editurl: '<?php echo WS_JQGRID."data_master.jemaat_controller/crud"; ?>',
            caption: "Anggota Keluarga ::" + ' <?php echo $this->input->post('nama_kk'); ?>'

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

                    $('#tanggal_lahir').datepicker({format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true});

                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').css('max-height','500px');
                    form.closest('.ui-jqdialog').css('width','900px');
                    form.closest('.ui-jqdialog').css('overflow','scroll');

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
                editData: {
                    kk_id: function() {
                        return <?php echo $this->input->post('kk_id'); ?>;
                    }
                },
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

                    $('#tanggal_lahir').datepicker({format: "yyyy-mm-dd", todayHighlight: true, clearBtn: true});

                    setTimeout(function() {
                        clearInputTingkatPendidikan();
                        $('#sektor_id').val(<?php echo $this->input->post('sektor_id'); ?>);
                        $('#alamat_jemaat').val('<?php echo $this->input->post('alamat_jemaat'); ?>');
                    },100);
                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').css('max-height','500px');
                    form.closest('.ui-jqdialog').css('width','900px');
                    form.closest('.ui-jqdialog').css('overflow','scroll');

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

                    clearInputTingkatPendidikan();
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


        /* ------------------------------  detail grid --------------------------------*/
        jQuery("#grid-table-detail").jqGrid({
            url: "<?php echo WS_JQGRID.'data_master.pendidikan_jemaat_controller/crud'; ?>",
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'pendidikan_jemaat_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'ID Jemaat', name: 'jemaat_id', width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Tingkat Pendidikan', name: 'tingkat_pendidikan_nama', width: 120,  editable: false, search:false, sortable:false},
                {label: 'Tingkat Pendidikan',
                    name: 'tingkat_pendidikan_id',
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
                                elm.append('<input id="form_tingkat_pendidikan_id" type="text"  style="display:none;">'+
                                        '<input id="form_tingkat_pendidikan_nama" size="30" readonly type="text" class="FormElement form-control" placeholder="Pilih Tingkat Pendidikan">'+
                                        '<button class="btn btn-success" type="button" onclick="showLOVTingkatPendidikan(\'form_tingkat_pendidikan_id\',\'form_tingkat_pendidikan_nama\')">'+
                                        '   <span class="fa fa-search bigger-110"></span>'+
                                        '</button>');
                                $("#form_tingkat_pendidikan_id").val(value);
                                elm.parent().removeClass('jqgrid-required');
                            }, 100);

                            return elm;
                        },
                        "custom_value":function( element, oper, gridval) {

                            if(oper === 'get') {
                                return $("#form_tingkat_pendidikan_id").val();
                            } else if( oper === 'set') {
                                $("#form_tingkat_pendidikan_id").val(gridval);
                                var gridId = this.id;
                                // give the editor time to set display
                                setTimeout(function(){
                                    var selectedRowId = $("#"+gridId).jqGrid ('getGridParam', 'selrow');
                                    if(selectedRowId != null) {
                                        var code_display = $("#"+gridId).jqGrid('getCell', selectedRowId, 'tingkat_pendidikan_nama');
                                        $("#form_tingkat_pendidikan_nama").val( code_display );
                                    }
                                },100);
                            }
                        }
                    }
                },
                {label: 'Nama Sekolah',name: 'nama_sekolah',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 50,
                        maxlength:100
                    },
                    editrules: {required: true}
                },
                {label: 'Tahun Lulus',name: 'tahun_lulus',width: 120, align: "left",editable: true,
                    editoptions: {
                        size: 20,
                        maxlength:4
                    },
                    editrules: {required: true}
                },
                {label: 'Jurusan',name: 'jurusan',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 50,
                        maxlength:100
                    },
                    editrules: {required: false}
                },
                {label: 'Alamat Sekolah',name: 'alamat_jemaat',width: 200, align: "left",editable: true,hidden:true,
                    edittype:'textarea',
                    editoptions: {
                        rows: 2,
                        cols:50
                    },
                    editrules: {required: false, edithidden:true}
                }
            ],
            height: '100%',
            //autowidth: false,
            width:500,
            viewrecords: true,
            rowNum: 5,
            rowList: [5, 10, 20],
            rownumbers: true, // show row numbers
            rownumWidth: 35, // the width of the row numbers columns
            altRows: true,
            shrinkToFit: true,
            multiboxonly: true,
            onSelectRow: function (rowid) {
                /*do something when selected*/
            },
            sortorder:'',
            pager: '#grid-pager-detail',
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
            editurl: "<?php echo WS_JQGRID.'data_master.pendidikan_jemaat_controller/crud'; ?>",
            caption: "Pendidikan Jemaat"

        });

        jQuery('#grid-table-detail').jqGrid('navGrid', '#grid-pager-detail',
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
                    jemaat_id: function() {
                        var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
                        var jemaat_id = $("#grid-table").jqGrid('getCell', selRowId, 'jemaat_id');

                        return jemaat_id;
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
                },
                beforeInitData: function () {
                    $('#grid-table-detail').jqGrid('resetSelection');
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

    function responsive_jqgrid(grid_selector, pager_selector) {

        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() );
        $(pager_selector).jqGrid( 'setGridWidth', parent_column.width() );
    }

</script>