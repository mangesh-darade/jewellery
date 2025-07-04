<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');

$v = "";

if($this->input->post('customer'))
{
    $v .= "&customer=" . $this->input->post('customer');
}

if($this->input->post('start_date'))
{
    $v .= "&start_date=" . $this->input->post('start_date');
}
if($this->input->post('end_date'))
{
    $v .= "&end_date=" . $this->input->post('end_date');
}

 
?>

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
<style>
    .text-bold {
        font-weight: bold !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Customer Deposit Ledgers'); ?> <?php
            if($this->input->post('start_date'))
            {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
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
<!--                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>-->
                 <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li> 
<!--                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>-->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo form_open("reports/customerDepositLedger"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                
                                <?= lang("Customers (Used Deposit Wallet Only)", "Customer"); ?>
                                <select class="form-control" name="customer">
                                      <option value="0"> Select Customer </option>
                                <?php
                               
                                $customername = '';
                                    $cust[''] = lang('select') . ' ' . lang('Customer');
                                    if(!empty($customers)) {
                                        foreach($customers as $customer)
                                        {
                                            if(isset($_POST['customer'])){
                                                if($customer->id == $_POST['customer']){
                                                   $customername = $customer->name;
                                                }
                                            }

                                            $cust[$customer->id] = $customer->name.(($customer->company != '-' && $customer->company != '') ?' ('.$customer->company.')' :'');
                                            echo '<option value="'.$customer->id.'"'.($customer->id == $_POST['customer']?'selected' : '').' >'.$customer->name.(($customer->company != '-' && $customer->company != '') ?' ('.$customer->company.')' :'').'</option>';

                                        } 
                                    }
//                                    echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control " id="customer" placeholder="' . lang("select") . " " . lang("customer") . '" style="width:100%"')
                                ?>  
                                </select>
                            </div>
                        </div>
                        
                        
                        
                        
                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?=  lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>"
                                               id="daterange_new" class="form-control">
                                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
                                        <input type="hidden" name="start_date" id="start_date"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ""; ?>">
                                        <input type="hidden" name="end_date" id="end_date"
                                               value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ""; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>                             
                                <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>
                
                <div class="table-responsive" id="table_body">
                    <table class="table table-striped table-bordered table-condensed table-hover dfTable reports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Phone</th>
                                <th>Card No.</th>
                                <th>Room No.</th>
                                <th>Total Deposit Amount</th>
                                <th>Balance Amount</th>
                                <th>Spent Amount (In Record) </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            if(!empty($customers)) {
                                        foreach($customers as $customer)
                                        {                                             
                                            ?>
                                               <tr>
                                                   <td class="text-left"><?=$customer->id?></td>
                                                   <td><?= ucwords($customer->name)?></td>
                                                   <td><?=$customer->phone?></td>
                                                   <td><?=$customer->card_no?></td>
                                                   <td><?=$customer->room_no?></td>
                                                   <td class="text-right"> <?= $this->sma->formatMoney($customer->total_deposit)?></td>
                                                   <td class="text-right"> <?= $this->sma->formatMoney($customer->balance_amount)?></td>
                                                   <td class="text-right"> <?= $this->sma->formatMoney($customer->spent_amount)?></td>
                                               </tr>

                                          <?php
                                          
                                                $deposits_total += $customer->total_deposit;
                                                $balance_total += $customer->balance_amount;
                                                $spent_total += $customer->spent_amount;
                                            } 
                                    }
                        ?>            
                            
                        </tbody>
                        <tfoot>
                            <tr>
                                
                                <th colspan="5">Total</th>
                                <th class="text-right"> <?= $this->sma->formatMoney($deposits_total)?></th>
                                <th class="text-right"> <?= $this->sma->formatMoney($balance_total)?></th>
                                <th class="text-right"><?= $this->sma->formatMoney($spent_total)?></th>
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
//        $('#pdf').click(function (event) {
//            event.preventDefault();
//            window.location.href = "<?=site_url('reports/getCustomerDepositLedger?v=1&export=pdf' . $v)?>";
//            return false;
//        });
        $('#xls').click(function (event) {
            event.preventDefault();
           <?php if($v) { ?>
                window.location.href = "<?=site_url('reports/getCustomerDepositLedger?v=1&export=xls' . $v)?>";
           <?php } else { ?>
                window.location.href = "<?=site_url('reports/customerDepositLedger?v=1&export=xls' . $v)?>";
           <?php } ?>
            return false;
        });
//        $('#image').click(function (event) {
//            event.preventDefault();
//			window.location.href = "<?=site_url('reports/getCustomerDepositLedger?v=1&export=img' . $v)?>";
//            /*html2canvas($('.box'), {
//                onrendered: function (canvas) {
//                    var img = canvas.toDataURL()
//                    window.open(img);
//                }
//            });*/
//            return false;
//        });
     
    <?php if($v) { ?>
       loadReport(1);
    <?php } ?>
        
    });
    
    function loadReport(page){    
        $.ajax({
            type: "POST",
            url: "<?= site_url('reports/load_ajax_reports')?>",
            data:'action=CustomerDepositLadger&page='+page+'<?= $v?>',
            beforeSend: function(){
                $("#table_body").html('<tr><td colspan="6"><div class="overlay"><i class="fa fa-refresh fa-spin"></i></div></td></tr>');
            },
            success: function(data){
               // console.log(data);
                $("#table_body").html(data);			 
            }
	});    
    }
</script>
