<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = '';

if ($this->input->post('startDate_term1')) {
    $startDateterm1 = explode('/', substr($this->input->post('startDate_term1') , 0, 10));
    $startDate_term1 = $startDateterm1[2] . "-" . $startDateterm1[1] . "-" . $startDateterm1[0] . "  00:00";
    $v .= "&startDate_term1=" . $startDate_term1;
}
if ($this->input->post('endDate_term1')) {
    $endDateterm1 = explode('/', substr($this->input->post('endDate_term1') , 0, 10));
    $endDate_term1 = $endDateterm1[2] . "-" . $endDateterm1[1] . "-" . $endDateterm1[0]. "  23:59";
    $v .= "&endDate_term1=" . $endDate_term1;
}
if ($this->input->post('startDate_term2')) {
    $startDateterm2 = explode('/', substr($this->input->post('startDate_term2') , 0, 10));
    $startDate_term2 = $startDateterm2[2] . "-" . $startDateterm2[1] . "-" . $startDateterm2[0] . "  00:00";
    $v .= "&startDate_term2=" . $startDate_term2;
}
if ($this->input->post('endDate_term2')) {
    $endDateterm2 = explode('/', substr($this->input->post('endDate_term2') , 0, 10));
    $endDate_term2 = $endDateterm2[2] . "-" . $endDateterm2[1] . "-" . $endDateterm2[0]. "  23:59";
    $v .= "&endDate_term2=" . $endDate_term2;
}
if ($this->input->post('warehouse')) {
    $warehouseData = $this->input->post('warehouse'); 
    foreach ($warehouseData as $key => $value) {
        $v .= "&warehouse[]=" . urlencode($value); 
    }
}


?>

<style>
.table th,
.table td {
    text-align: center;
    width: 15%;
}

.custom-set {
    display: inline-flex !important;
    position: absolute;
    width: 25%;
    right: 1.3em;
    top: 4rem;
    z-index: 1111;
}

.d-flx {
    display: flex;
}

.font-weight-set {
    font-weight: 200;
}
.text-left {
    text-align: left !important;
}

.text-center {
    text-align: center !important;
}

</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<script>
// $(document).ready(function() {

//     var oTable = $('#PrData').dataTable({
//         "aaSorting": [],
//         "aLengthMenu": [
//             [10, 25, 50, 100, -1],
//             [10, 25, 50, 100, "<?= lang('all') ?>"]
//         ],
//         "iDisplayLength": <?= $Settings->rows_per_page ?>,
//         'bProcessing': true,
//         'bServerSide': true,
//         'sAjaxSource': '<?= site_url('reports/termWiseSaleReports/?v=1' . $v) ?>',
//         'fnServerData': function(sSource, aoData, fnCallback) {
//             // console.log('URL:', '<?= site_url('reports/termWiseSaleReports  /?v=1' . $v) ?>');
//             aoData.push({
//                 "name": "<?= $this->security->get_csrf_token_name() ?>",
//                 "value": "<?= $this->security->get_csrf_hash() ?>",
//             });
//             $.ajax({
//                 'dataType': 'json',
//                 'type': 'POST',
//                 'url': sSource,
//                 'data': aoData,
//                 'success': fnCallback,
//             });
//         },
//         "searching": true,
//         "aoColumns": [
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat }
//         ],
//         'fnRowCallback': function(nRow, aData, iDisplayIndex) {
//             nRow.id = aData[0];
//             var nCells = nRow.getElementsByTagName('td');
//             nCells['0'].innerHTML = aData[0];
//         },
//         "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
//             var Stock = 0,
//                 t1Sale = 0,
//                 t2Sale = 0,
//                 excessInv = 0,
//                 shortageInv = 0;

//             for (var i = 0; i < aaData.length; i++) {
//                 Stock += parseFloat(aaData[aiDisplay[i]][5]);
//                 t1Sale += parseFloat(aaData[aiDisplay[i]][6]);
//                 t2Sale += parseFloat(aaData[aiDisplay[i]][7]);
//                 excessInv += parseFloat(aaData[aiDisplay[i]][8]);
//                 shortageInv += parseFloat(aaData[aiDisplay[i]][9]);
//             }

//             var nCells = nRow.getElementsByTagName('th');
//             nCells[5].innerHTML = (parseFloat(Stock));
//             nCells[6].innerHTML = (parseFloat(t1Sale));
//             nCells[7].innerHTML = (parseFloat(t2Sale));
//             nCells[8].innerHTML = (parseFloat(excessInv));
//             nCells[9].innerHTML = (parseFloat(shortageInv));
//         }
//     }).fnSetFilteringDelay().dtFilter([{
//             column_number: 0,
//             filter_default_label: "<?= lang('product_code'); ?>",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 1,
//             filter_default_label: "[<?= lang('category_name'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 2,
//             filter_default_label: "[<?= lang('product_name'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 3,
//             filter_default_label: "[<?= lang('variant_name'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 4,
//             filter_default_label: "[<?= lang('Warehouse'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 5,
//             filter_default_label: "[<?= lang('current_stock'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 6,
//             filter_default_label: "[<?= lang('term_1_sale'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 7,
//             filter_default_label: "[<?= lang('term_2_sale'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 8,
//             filter_default_label: "[<?= lang('excess_inventory'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 9,
//             filter_default_label: "[<?= lang('shortage_inventory'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//     ], "footer");
// });
$(document).ready(function () {
    $.ajax({
        type: "get",
        url: 'reports/termWiseSaleReports',
        data: "<?= 'v=1' . $v ?>",
        beforeSend: function () {
            $("#PrData").html(
                "<div class='overlay'><i class='fa fa-refresh fa-spin'></i>Loading data from server</div>"
            );
        },
        success: function (data) {
            console.log(data);
            $("#PrData").html(data);
            
            setTimeout(function () {
                $('#PrData1').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": 10,
                    "columnDefs": [
                        { "targets": [0, 1, 2, 3, 4], "className": "text-left" },  // Left align columns 1-5
                        { "targets": [5, 6, 7, 8, 9], "className": "text-center" }  // Center align columns 6-10
                    ],
                    "footerCallback": function (row, data, start, end, display) {
                        var api = this.api();

                        function sumColumn(index) {
                            return api
                                .column(index, { page: 'current' })  // Sum only visible rows
                                .data()
                                .reduce(function (a, b) {
                                    var cleanValue = b.replace(/<[^>]*>/g, '').trim(); // Remove HTML tags
                                    var numericValue = parseFloat(cleanValue.replace(/[^\d.-]/g, '')) || 0;
                                    return (parseFloat(a) || 0) + numericValue;
                                }, 0);
                        }

                        // Center align and apply formatQuantity for each footer column
                        $(api.column(5).footer()).html('<div class="text-center">' + formatQuantity(sumColumn(5).toFixed(2)) + '</div>');
                        $(api.column(6).footer()).html('<div class="text-center">' + formatQuantity(sumColumn(6).toFixed(2)) + '</div>');
                        $(api.column(7).footer()).html('<div class="text-center">' + formatQuantity(sumColumn(7).toFixed(2)) + '</div>');
                        $(api.column(8).footer()).html('<div class="text-center">' + formatQuantity(sumColumn(8).toFixed(2)) + '</div>');
                        $(api.column(9).footer()).html('<div class="text-center">' + formatQuantity(sumColumn(9).toFixed(2)) + '</div>');
                    }
                });
            }, 0);
        },
        error: function () {
            console.log('error');
            alert("An error occurred while loading data.");
        }
    });
});

</script>
<script type="text/javascript">
$(document).ready(function() {
    if ($("#start_date").val() || $("#start_date1").val())
        {
            $("#form").slideUp();
        $('#form').hide();

        }
        else{
            $("#form").slideDown();
        }
    $('.toggle_down').click(function() {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function() {
        $("#form").slideUp();
        return false;
    });
});
</script>
<style>
#form {
    display: none;
}
</style>
<!-- <div class="form-group">
    <label for="searchInput">Search:</label>
    <input type="text" id="searchInput" class="form-control" placeholder="Search for products...">
</div> -->
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Term Wise Sales Report'); ?> <?php
            
            if($this->input->post('startDate_term1'))
            {
                echo "From " . $this->input->post('startDate_term1') . " to " . $this->input->post('endDate_term1');
            }
            if($this->input->post('startDate_term2'))
            {
                echo "And From " . $this->input->post('startDate_term2') . " to " . $this->input->post('endDate_term2');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/term_wise_sale_report","id='searchproduct'"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?= lang("Start - End Date Term 1", "Start - End Date Term 1"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                            value="<?php echo isset($_POST['startDate_term1']) ? $_POST['startDate_term1'].'-'.$_POST['endDate_term1'] : "-";?>"
                                            id="daterange_new" class="form-control" autocomplete="off">
                                        <input type="hidden" name="startDate_term1" id="start_date"
                                            value="<?php echo isset($_POST['startDate_term1']) ? $_POST['startDate_term1'] : "";?>">
                                        <input type="hidden" name="endDate_term1" id="end_date"
                                            value="<?php echo isset($_POST['endDate_term1']) ? $_POST['endDate_term1'] : "";?>">
                                    </div>
                                    <div class="form-group">
                                        <?= lang("warehouse", "warehouse"); ?>
                                        <?php
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse[]', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" class="form-control select" style="width:100%;" ');
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?= lang("Start - End Date Term 2", "Start - End Date Term 2"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                            value="<?php echo isset($_POST['startDate_term2']) ? $_POST['startDate_term2'].'-'.$_POST['endDate_term2'] : "-";?>"
                                            id="daterange_new1" class="form-control" autocomplete="off">
                                        <input type="hidden" name="startDate_term2" id="start_date1"
                                            value="<?php echo isset($_POST['startDate_term2']) ? $_POST['startDate_term2'] : "";?>">
                                        <input type="hidden" name="endDate_term2" id="end_date1"
                                            value="<?php echo isset($_POST['endDate_term2']) ? $_POST['endDate_term2'] : "";?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>

                            <a href="<?= site_url('reports/term_wise_sale_report') ?>" type="reset" id="report_reset"
                                class="btn btn-warning input-xs">Reset </a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive" id="PrData">
                        <?=lang('loading_data_from_server')?> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// document.getElementById('searchInput').addEventListener('keyup', function() {
//     var input = this.value.toLowerCase();
//     var rows = document.querySelectorAll('#PrData tbody tr');
//     rows.forEach(function(row) {
//         var cells = row.getElementsByTagName('td');
//         var found = Array.from(cells).some(function(cell) {
//             return cell.textContent.toLowerCase().includes(input);
//         });
//         row.style.display = found ? '' : 'none';
//     });
// });
</script>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#daterange_new1').daterangepicker({
            timePicker: false,
            format: (site.dateFormats.js_sdate).toUpperCase(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                    .endOf('month')
                ]
            }
        },
        function(start, end) {
            $('#start_date1').val(start.format('DD/MM/YYYY ')); //HH:mm
            $('#end_date1').val(end.format('DD/MM/YYYY ')); //HH:mm
        });
        $('#daterange_new').daterangepicker({
            timePicker: false,
            format: (site.dateFormats.js_sdate).toUpperCase(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                    .endOf('month')
                ]
            }
        },
        function(start, end) {
            $('#start_date').val(start.format('DD/MM/YYYY ')); //HH:mm
            $('#end_date').val(end.format('DD/MM/YYYY ')); //HH:mm
        });
        
    $('#pdf').click(function(event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/termWiseSaleReports/pdf/?v=1' . $v)?>";
        return false;
    });
    $('#xls').click(function(event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/termWiseSaleReports/0/xls/?v=1' . $v)?>";
        return false;
    });
    $('#image').click(function(event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/termWiseSaleReports/0/0/img/?v=1' . $v)?>";
        /*html2canvas($('.box'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });*/
        return false;
    });
});
</script>