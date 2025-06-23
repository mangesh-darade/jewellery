<style>
    thead th:nth-child(7) .yadcf-filter-wrapper input,
    thead th:nth-child(8) .yadcf-filter-wrapper input,
    thead th:nth-child(9) .yadcf-filter-wrapper input,
    thead th:nth-child(10) .yadcf-filter-wrapper input,
    thead th:nth-child(11) .yadcf-filter-wrapper input {
    text-align: right;
}

    tbody td:nth-child(7),
    tbody td:nth-child(8),
    tbody td:nth-child(9),
    tbody td:nth-child(10),
    tbody td:nth-child(11) {
    text-align:right;
}
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";

if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
         var recharge_amt = 0, used_amt =0;
        var oTable = $('#CusData').dataTable({
            "aaSorting": [[0, "asc"], [1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
                        // 'sAjaxSource': '<?= site_url('reports/getCustomers/?v=1'.$v) ?>',

            'sAjaxSource': '<?= site_url('reports/getCustomersData/?v=1'.$v) ?>',

            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, null, null, {
                "mRender": formatQuantity,
                "bSearchable": false,
                "sClass": "text-right"
            }, {
                "mRender": currencyFormat,
                "bSearchable": false,
                "sClass": "text-right"
            }, {
                "mRender": currencyFormat,
                "bSearchable": false,
                "sClass": "text-right"
            }, {
                "mRender": currencyFormat,
                "bSearchable": false,
                "sClass": "text-right"
            }, {"mRender": currencyFormat, "bSearchable": false}, {
                "mRender": currencyFormat,
                "bSearchable": false
            }, {"mRender": currencyFormat, "bSearchable": false}, {
                "mRender": function (data) {
                    return data ? new Date(data).toLocaleDateString() : '';
                }
                // Format date based on its actual format

            }, {"bSortable": false}],
             'fnRowCallback': function (nRow, aaData, iDisplayIndex) {
              
              var nCells = nRow.getElementsByTagName('td');
             //   Get Total Topup and Total Expenses on Deposit
             $.ajax({
                type: 'ajax',
                dataType: 'json',
                method: 'get',
                url: '<?= base_url('reports/getdeposit') ?>?customer_id=' + aaData[10] + '<?= $v?>',
                async: false,
                success: function (result) {
                    console.log(result);
                    let recharge = parseFloat(result.recharge_amount) || 0;
                    let used = parseFloat(result.used_amount) || 0;

                    // Instead of modifying aaData, store the values in a temporary variable
                    let displayedRecharge = currencyFormat(recharge);
                    let displayedUsed = currencyFormat(used);

                    nCells[8].innerHTML = displayedRecharge;
                    nCells[9].innerHTML = displayedUsed;
                    aaData['recharge_amt'] = recharge;
                    aaData['used_amt'] = used;
                }
            });
                // Ensure correct cell assignment for other columns
                nCells[10].innerHTML = currencyFormat(aaData[9]);

                var lastInvoiceDate = aaData[11];
                var formattedDate = lastInvoiceDate ? new Date(lastInvoiceDate).toLocaleDateString() : '';
                nCells[11].innerHTML = formattedDate;
                nCells[12].innerHTML = "<div class='text-center'><a class=\"tip\" data-toggle=\"modal\" data-target=\"#myModal2\" title='add_payment' href='<?= site_url('reports/add_payment/')?>"+aaData[10]+"'><span class='label label-primary'>add_payment</span></a>&nbsp<a class=\"tip\" title='view_report' href='<?= site_url('reports/customer_report/')?>"+aaData[10]+"/<?= $this->input->post('start_date') ?>/<?= $this->input->post('end_date') ?>'><span class='label label-primary'>view_report</span></a></div>";
            },

        "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
        var purchases = 0, total = 0, paid = 0, balance = 0, discount_balance = 0, recharge_amt = 0, used_amt = 0;

                console.log(aaData);

                for (var i = 0; i < aiDisplay.length; i++) {
                    let index = aiDisplay[i];

                    purchases += parseFloat(aaData[index][4]) || 0;
                    total += parseFloat(aaData[index][5]) || 0;
                    paid += parseFloat(aaData[index][6]) || 0;
                    balance += parseFloat(aaData[index][7]) || 0;
                    recharge_amt += parseFloat(aaData[index]['recharge_amt']) || 0;
                    used_amt += parseFloat(aaData[index]['used_amt']) || 0;
                    discount_balance += parseFloat(aaData[index][9]) || 0;
                }

                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = formatQuantity(parseFloat(purchases));
                nCells[5].innerHTML = currencyFormat(parseFloat(total));
                nCells[6].innerHTML = currencyFormat(parseFloat(paid));
                nCells[7].innerHTML = currencyFormat(parseFloat(balance));
                nCells[8].innerHTML = currencyFormat(parseFloat(recharge_amt));
                nCells[9].innerHTML = currencyFormat(parseFloat(used_amt));
                nCells[10].innerHTML = currencyFormat(parseFloat(discount_balance));

            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('email_address');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<style>
    .text-right {
        text-align: right;
    }
</style>


<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?><?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?></h2>
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
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('view_report_customer'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                  <div id="form">

                    <?php echo form_open("reports/customers"); ?>
                    <div class="row">
                        <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
		                <div class="controls">
		                    <?= lang("date_range", "date_range"); ?>
		                    <div class="input-group">
		                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                        <input type="text"
		                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
		                               id="daterange_new" class="form-control">
		                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
		                         <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
		                         <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>" >
                                    </div>
		                </div>
		            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div  class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <!--<input type="button" id="report_reset" data-value="<?=base_url('reports/categories');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                             <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0" 
                           class="table table-bordered table-condensed table-hover table-striped reports-table">
                        <thead>
                        <tr class="primary">
                            
                            <th><?= lang("company"); ?></th>
                            <th><?= lang("name"); ?></th>
                            <th><?= lang("phone"); ?></th>
                            <th><?= lang("email_address"); ?></th>
                            <th><?= lang("total_sales"); ?></th>
                            <th><?= lang("total_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            
                            <th><?= lang("Recharge Amount"); ?></th>
                            <th><?= lang("Used Amount "); ?></th>
                            
                            <th><?= lang("Deposit Balance"); ?></th>
                            <th><?= lang("Last_Invoice_Date"); ?></th>
                             <!--<th><?= lang("ID"); ?></th>-->
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                           
                             <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-center"><?= lang("total_sales"); ?></th>
                            <th class="text-center"><?= lang("total_amount"); ?></th>
                            <th class="text-center"><?= lang("paid"); ?></th>
                            <th class="text-center"><?= lang("balance"); ?></th>
                           
                            <th class="text-center"><?= lang("Total Deposit"); ?></th>
                            <th class="text-center"><?= lang("Sales Deposit"); ?></th>
                             <th class="text-center"><?= lang("Balance Deposit"); ?></th>
                             <th class="text-center"><?= lang("Last Invoice Date"); ?></th>
                             <!--<th></th>-->
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCustomers/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCustomers/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCustomers/0/0/img/?v=1'.$v)?>";
            return false;
           /* event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;*/
        });
    });
</script>

