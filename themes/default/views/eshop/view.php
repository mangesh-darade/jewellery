<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
 
	$resOutput = $this->sma->posBillTable($default_printer,$inv,$return_sale,$rows,$return_rows);
	$font_size = isset($default_printer->font_size)?$default_printer->font_size:'14';
	 
     	$sms_code = md5('Reciept'.$inv->reference_no.$inv->id);
	$sms_url = base_url('reciept/send_sms').'?code='.md5('Reciept'.$inv->reference_no.$inv->id).'&phone='.$customer->phone;
	function product_name($name)
	{
	    return character_limiter($name, (isset($pos_settings->char_per_line) ? ($pos_settings->char_per_line - 8) : 35));
	}

	if ($modal) {
	    echo '<div class="modal-dialog  modal-lg  no-modal-header"><div class="modal-content"><div class="modal-body"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">XX<i class="fa fa-2x">&times;</i></button>';
} else {?><!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?=$page_title . " " . lang("no") . " " . $inv->id;?></title>
        <base href="<?=base_url()?>"/>
        <meta http-equiv="cache-control" content="max-age=0"/>
        <meta http-equiv="cache-control" content="no-cache"/>
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
        <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
        <style type="text/css" media="all">
            body { color: #000; }
            #wrapper { max-width: <?php echo $default_printer->width?>px; margin: 0 auto; padding-top: 20px; }
            .btn { border-radius: 0; margin-bottom: 5px; }
            h3 { margin: 5px 0; }
            @media print {
                .no-print { display: none; }
                #wrapper { max-width: <?php echo $default_printer->width?>px; width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #ddd !important; }
            }
        </style>
    </head>

    <body>
<?php }
$inv->customer = $customer->name;
?>
<div id="wrapper">
    <div id="receiptData">
    <div class="no-print">
        <?php if ($message) {?>
            <div class="alert alert-success">
                <button data-dismiss="alert" class="close" type="button">Ã—</button>
                <?=is_array($message) ? print_r($message, true) : $message;?>
            </div>
        <?php }
        ?>
    </div>
    <div id="receipt-data" style="font-size: <?php echo $font_size;?>">
     
        <div class="text-center">
           <?php if(isset($default_printer->show_invoice_logo) && $default_printer->show_invoice_logo==1):?>
           	<img src="<?= base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo); ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
            <?php endif;?>
            <h3 style="text-transform:uppercase;"><?=$biller->company != '-' ? $biller->company : $biller->name;?></h3>
            <?php
            	echo "<p>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .
            	"<br>" . lang("tel") . ": " . $biller->phone . "<br>";

            	if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
            	    echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
            	}
            	if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
            	    echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
            	}
            	echo '</p></div>';
            	if ($Settings->invoice_view == 1) {
                ?>
                <div class="col-sm-12 text-center">
                    <h4 style="font-weight:bold;"><?=lang('tax_invoice');?></h4>
                </div>
            <?php }
            	echo "<p>" . lang("reference_no") . ": " . $inv->reference_no . "<br>";
                if (!empty($inv->return_sale_ref)) {
                    echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
                    if ($inv->return_id) {
                        echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                    } else {
                        echo '</p>';
                    }
                }
            	echo lang("customer") . ": " . $inv->customer . "<br>";
            	echo lang("address") . ": " . $customer->address. "<br>";
            	echo lang("phone") . ": " . $customer->phone . "<br>";
            ?>	
            <?php if(isset($default_printer->show_tin) && $default_printer->show_tin==1 && !empty($customer->vat_no)):
            	echo lang('vat_no') . ": " . $customer ->vat_no. "<br>";
            
            endif;?>
            
            <?php	echo lang("date") . ": " . $this->sma->hrld($inv->date) . "</p>";
            ?>
            <div style="clear:both;"></div>
 
                <?php
                	$r = 1; $category = 0;
                	$tax_summary = array();
                	foreach ($rows as $row) {
                	    if (isset($tax_summary[$row->tax_code])) {
                	        $tax_summary[$row->tax_code]['items'] += $row->quantity;
                	        $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                	        $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                	    } else {
                	        $tax_summary[$row->tax_code]['items'] = $row->quantity;
                	        $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                	        $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                	        $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                	        $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                	        $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                	    }
                	 }
                    if ($return_rows) {
                         foreach ($return_rows as $row) {
                            if (isset($tax_summary[$row->tax_code])) {
                                $tax_summary[$row->tax_code]['items'] += $row->quantity;
                                $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                            } else {
                                $tax_summary[$row->tax_code]['items'] = $row->quantity;
                                $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                                $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                                $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                            }
                         $r++;
                        }
                    }

                ?>
            <?php echo    $resOutput;?>
             <?php
              
            	if ($payments) {
            	    echo '<table class="table table-striped table-condensed"><tbody>';
            	    foreach ($payments as $payment) {
            	        echo '<tr>';
            	        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
            	            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
            	            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
            	            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
            	        }
            	         elseif ($payment->paid_by == 'instomojo' && $payment->transaction_id) {
            	            echo '<td>' . lang("paid_by") . ': Instomojo</td>';
            	            echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        }
            	        elseif ($payment->paid_by == 'ccavenue' && $payment->transaction_id) {
            	            echo '<td>' . lang("paid_by") . ': CCavenue</td>';
            	            echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        }
            	        elseif ($payment->paid_by == 'paytm' && $payment->transaction_id) {
            	            echo '<td>' . lang("paid_by") . ': Paytm</td>';
            	            echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        }
            	         elseif ($payment->paid_by == 'paynear' && $payment->transaction_id) {
            	            echo '<td>' . lang("paid_by") . ': Paynear</td>';
            	            echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        }
            	         elseif ($payment->paid_by == 'payumoney' && $payment->transaction_id) {
            	            echo '<td>' . lang("paid_by") . ': Payumoney</td>';
            	            echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        }
            	         elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
            	            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
            	        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
            	            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
            	            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
            	        } elseif ($payment->paid_by == 'other' && $payment->amount) {
            	            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
            	            echo '<td>Transaction No : ' . $payment->transaction_id . '</td>';
            	            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
            	            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
            	        }
            	        echo '</tr>';
            	    }
            	    echo '</tbody></table>';
            	}

                if ($return_payments) {
                    echo '<strong>'.lang('return_payments').'</strong><table class="table table-striped table-condensed"><tbody>';
                    foreach ($return_payments as $payment) {
                        $payment->amount = (0-$payment->amount);
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

            	if ($Settings->invoice_view == 1) {
            	    if (!empty($tax_summary)) {
            	        echo '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
            	        echo '<table class="table table-condensed"><thead><tr><th  class="text-center">' . lang('name') . '</th><th  class="text-center">' . lang('code') . '</th><th  class="text-right">' . lang('qty') . '</th><th  class="text-right">' . lang('tax_excl') . '</th><th  class="text-right">' . lang('tax_amt') . '</th></tr></td><tbody>';
            	        foreach ($tax_summary as $summary) {
            	            echo '<tr><td>' . $summary['name'] . '</td><td class="text-center">' . $summary['code'] . '</td><td class="text-right">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
            	        }
            	        echo '</tbody></tfoot>';
            	        echo '<tr><th colspan="4" class="text-right">' . lang('total_tax_amount') . '</th><th class="text-right">' . $this->sma->formatMoney($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax) . '</th></tr>';
            	        echo '</tfoot></table>';
            	    }
            	}
            ?>

            <?=$customer->award_points != 0 && $Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
            .'<br>'.
            lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>' : '';?>
            <?=$inv->note ? '<p class="text-center">' . $this->sma->decode_html($inv->note) . '</p>' : '';?>
            <?=$inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->sma->decode_html($inv->staff_note) . '</p>' : '';?>
            <div class="well well-sm">
                <?=$this->sma->decode_html($biller->invoice_footer);?>
            </div>
        </div>

        <div class="order_barcodes">
            <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
            <?= $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>
        </div>
        <div style="clear:both;"></div>
    </div>
<?php if ($modal) {
	    echo '</div></div></div></div>';
	} else {
    ?>
<div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
    <hr>
    <?php if ($message) {?>
    <div class="alert alert-success">
        <button data-dismiss="alert" class="close" type="button">Ã—</button>
        <?=is_array($message) ? print_r($message, true) : $message;?>
    </div>
<?php }
    ?>

    <?php if ($pos_settings->java_applet) {?>
        <span class="col-xs-12"><a class="btn btn-block btn-primary" onClick="printReceipt()"><?=lang("print");?></a></span>
        <span class="col-xs-12"><a class="btn btn-block btn-info" type="button" onClick="openCashDrawer()">Open Cash
                Drawer</a></span>
        <div style="clear:both;"></div>
    <?php } else {?>
        <span class="pull-right col-xs-12">
        <a href="javascript:window.print()" id="web_print" class="btn btn-block btn-primary"
           onClick="window.print();return false;"><?=lang("web_print");?></a>
    </span>
    <?php }
        ?>
    <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?=lang("email");?></a></span>
   	 <?php if(!empty($sms_url)) :?>
		<span class="pull-left col-xs-12">
		 
            <?php  $DisSMSLink = (int)$sms_limit < 1  ? '<br><a href="http://simplypos.in/login.php" style="color:#000" target="_blank">Please Login on merchant panel & Rechagre Now</a>' :''; ?>
                       
		<?php if($sms_limit > 0): ?>
			<a class="btn btn-block btn-info" href="javascript:void(0);" id="sms_bill">SMS</a>
		<?php else:?>
			<a class="btn btn-block btn-info"  style="cursor:no-drop" href="javascript:void(0);" id="sms_bill_not"> SMS </a>
		<?php endif;?>
		</span>
	<?php endif;?>
    <span class="col-xs-12">
        <a class="btn btn-block btn-warning"  href="<?=site_url('pos');?>"><?=lang("back_to_pos");?></a>
    </span>
    <?php if (!$pos_settings->java_applet) {?>
        <div style="clear:both;"></div>
        <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
            <p style="font-weight:bold;">Please don't forget to disble the header and footer in browser print
                settings.</p>

            <p style="text-transform: capitalize;"><strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp;
                Header/Footer Make all --blank--</p>

            <p style="text-transform: capitalize;"><strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer
                in Option &amp; Set Margins to None</p></div>
    <?php }
        ?>
    <div style="clear:both;"></div>
    <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
  	  <div class="sms_note blue">(Note : Available SMS limit <?php print((int)$sms_limit)?> <?php echo $DisSMSLink;?>)</div>
    </div>
    <div style="clear:both;"></div>
</div>

</div>
<canvas id="hidden_screenshot" style="display:none;">

</canvas>
<div class="canvas_con" style="display:none;"></div>
<script type="text/javascript" src="<?=$assets?>pos/js/jquery-1.7.2.min.js"></script>
<?php if(!empty($sms_url)) :?>
<script>

$('#sms_bill').click(function () {
        var phone = prompt("<?=lang("phone");?>", "<?=$customer->phone;?>");
        var code = '<?php echo $sms_code?>';
        if (phone != null) {
            $.ajax({
                type: "GET",
                url: "<?=site_url('reciept/send_sms')?>",
                data: { phone: phone, code:code },
                dataType: "json",
                success: function (data) {
                     alert(data['msg']);
                },
                error: function () {
                    alert('<?=lang('ajax_request_failed');?>');
                    return false;
                }
            });
        }
        return false;
    });

</script>
<?php endif;?>
<?php if ($pos_settings->java_applet) {
	        function drawLine($char_per_line)
	        {
                $size = $char_per_line;
	            $new = '';
	            for ($i = 1; $i < $size; $i++) {
	                $new .= '-';
	            }
	            $new .= ' ';
	            return $new;
	        }

	        function printLine($str, $sep = ":", $space = null, $char_per_line)
	        {
                $size = $space ? $space : $char_per_line;
	            $lenght = strlen($str);
	            list($first, $second) = explode(":", $str, 2);
	            $new = $first . ($sep == ":" ? $sep : '');
	            for ($i = 1; $i < ($size - $lenght); $i++) {
	                $new .= ' ';
	            }
	            $new .= ($sep != ":" ? $sep : '') . $second;
	            return $new;
	        }

	        function printText($text, $char_per_line)
	        {
                $size = $char_per_line;
	            $new = wordwrap($text, $size, "\\n");
	            return $new;
	        }

	        function taxLine($name, $code, $qty, $amt, $tax, $char_per_line)
	        {
	            return printLine(printLine(printLine(printLine($name . ':' . $code, '', 18, $char_per_line) . ':' . $qty, '', 25, $char_per_line) . ':' . $amt, '', 35, $char_per_line) . ':' . $tax, ' ', $char_per_line);
	        }

        ?>

        <script type="text/javascript" src="<?=$assets?>pos/qz/js/deployJava.js"></script>
        <script type="text/javascript" src="<?=$assets?>pos/qz/qz-functions.js"></script>
        <script type="text/javascript">
            deployQZ('themes/<?=$Settings->theme?>/assets/pos/qz/qz-print.jar', '<?=$assets?>pos/qz/qz-print_jnlp.jnlp');
            usePrinter("<?=$pos_settings->receipt_printer;?>");
            <?php /*$image = $this->sma->save_barcode($inv->reference_no);*/?>
            function printReceipt() {
                //var barcode = 'data:image/png;base64,<?php /*echo $image;*/?>';
                receipt = "";
                receipt += chr(27) + chr(69) + "\r" + chr(27) + "\x61" + "\x31\r";
                receipt += "<?=$biller->company != '-' ? $biller->company : $biller->name;?>" + "\n";
                receipt += " \x1B\x45\x0A\r ";
                receipt += "<?=$biller->address . " " . $biller->city . " " . $biller->country;?>" + "\n";
                receipt += "<?=$biller->phone;?>" + "\n";
                receipt += "<?php if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {echo printLine($pos_settings->cf_title1 . ": " . $pos_settings->cf_value1, null, null, $pos_settings->char_per_line);}
                                    ?>" + "\n";
                receipt += "<?php if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {echo printLine($pos_settings->cf_title2 . ": " . $pos_settings->cf_value2, null, null, $pos_settings->char_per_line);}
                                    ?>" + "\n";
                receipt += "<?=drawLine($pos_settings->char_per_line);?>\r\n";
                receipt += "<?php if ($Settings->invoice_view == 1) {echo lang('tax_invoice');}
                                    ?>\r\n";
                receipt += "<?php if ($Settings->invoice_view == 1) {echo drawLine($pos_settings->char_per_line);}
                                    ?>\r\n";
                receipt += "\x1B\x61\x30";
                receipt += "<?=printLine(lang("reference_no") . ": " . $inv->reference_no, null, null, $pos_settings->char_per_line)?>" + "\n";
                receipt += "<?=printLine(lang("sales_person") . ": " . $biller->name, null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("customer") . ": " . $inv->customer, null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("date") . ": " . date($dateFormats['php_ldate'], strtotime($inv->date)), null, null, $pos_settings->char_per_line)?>" + "\n\n";
                receipt += "<?php $r = 1;
                foreach ($rows as $row): ?>";
                receipt += "<?="#" . $r . " ";?>";
                receipt += "<?=printLine(product_name(addslashes($row->product_name)) . ($row->variant ? ' (' . $row->variant . ')' : '') . ":" . $row->tax_code, '*', null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine($this->sma->formatQuantity($row->quantity) . "x" . $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . ":  " . $this->sma->formatMoney($row->subtotal), ' ', null, $pos_settings->char_per_line) . "";?>" + "\n";
                receipt += "<?php $r++;
                endforeach;?>";
                <?php if ($return_rows) { ?>
                    receipt += "\n" + "<?=lang('returned_items');?>" + "\n";
                    <?php foreach ($return_rows as $row): ?>
                    receipt += "<?="#" . $r . " ";?>";
                    receipt += "<?=printLine(product_name(addslashes($row->product_name)) . ($row->variant ? ' (' . $row->variant . ')' : '') . ":" . $row->tax_code, '*', null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine($this->sma->formatQuantity($row->quantity) . "x" . $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . ":  " . $this->sma->formatMoney($row->subtotal), ' ', null, $pos_settings->char_per_line) . "";?>" + "\n";
                <?php $r++; endforeach; } ?>
                receipt += "\x1B\x61\x31";
                receipt += "<?=drawLine($pos_settings->char_per_line);?>\r\n";
                receipt += "\x1B\x61\x30";
                receipt += "<?=printLine(lang("total") . ": " . $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php if ($inv->order_tax != 0) {?>
                receipt += "<?=printLine(lang("tax") . ": " . $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php }
                        ?>
<?php if ($inv->total_discount != 0) {?>
                receipt += "<?=printLine(lang("discount") . ": (" . $this->sma->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . ") " . $this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php }
                        ?>
<?php if ($pos_settings->rounding) {?>
                receipt += "<?=printLine(lang("rounding") . ": " . $inv->rounding, null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("grand_total") . ": " . $this->sma->formatMoney($return_sale ? ($this->sma->roundMoney($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : $this->sma->roundMoney($inv->grand_total + $inv->rounding)), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php } else {?>
                receipt += "<?=printLine(lang("grand_total") . ": " . $this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php }
                        ?>
<?php if ($inv->paid < $inv->grand_total) {?>
                receipt += "<?=printLine(lang("paid_amount") . ": " . $this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("due_amount") . ": " . $this->sma->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)), null, null, $pos_settings->char_per_line);?>" + "\n\n";
                <?php }
                        ?>
<?php
	if ($payments) { ?>
        receipt += "\n" + "<?=printText(lang("payments"), $pos_settings->char_per_line);?>" + "\n";
	           <?php foreach ($payments as $payment) {
                if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {?>
                receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("change") . ": " . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php }elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {?>
                receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php }elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                    ?>
                receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("cheque_no") . ": " . $payment->cheque_no, null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php }elseif ($payment->paid_by == 'other' && $payment->amount) {?>
                receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->amount), null, null, $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?=printText(lang("payment_note") . ": " . $payment->note, $pos_settings->char_per_line);?>" + "\n";
                <?php }
            }
        }
            if ($return_payments) { ?>
                receipt += "\n" + "<?=printText(lang("return_payments"), $pos_settings->char_per_line);?>" + "\n";
                <?php foreach ($return_payments as $payment) {
                    if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && ($payment->pos_paid || $return_sale)) {?>
                    receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->amount), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("change") . ": " . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0), null, null, $pos_settings->char_per_line);?>" + "\n";
                    <?php } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {?>
                    receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), null, null, $pos_settings->char_per_line);?>" + "\n";
                    <?php } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                        ?>
                    receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("cheque_no") . ": " . $payment->cheque_no, null, null, $pos_settings->char_per_line);?>" + "\n";
                    <?php } elseif ($payment->paid_by == 'other' && $payment->amount) {?>
                    receipt += "<?=printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->amount), null, null, $pos_settings->char_per_line);?>" + "\n";
                    receipt += "<?=printText(lang("payment_note") . ": " . $payment->note, $pos_settings->char_per_line);?>" + "\n";
                    <?php }
                }
            }
        if ($Settings->invoice_view == 1) {
         if (!empty($tax_summary)) {
            ?>
                receipt += "\n" + "<?=lang('tax_summary');?>" + "\n";
                receipt += "<?=taxLine(lang('name'), lang('code'), lang('qty'), lang('tax_excl'), lang('tax_amt'), $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?php foreach ($tax_summary as $summary): ?>";
                receipt += "<?=taxLine($summary['name'], $summary['code'], $this->sma->formatQuantity($summary['items']), $this->sma->formatMoney($summary['amt']), $this->sma->formatMoney($summary['tax']), $pos_settings->char_per_line);?>" + "\n";
                receipt += "<?php endforeach;?>";
                receipt += "<?=printLine(lang("total_tax_amount") . ":" . $this->sma->formatMoney($inv->product_tax), null, null, $pos_settings->char_per_line);?>" + "\n";
                <?php
                	}
                	        }
                        ?>
                receipt += "\x1B\x61\x31";
                receipt += "\n" + "<?=$biller->invoice_footer ? printText(str_replace(array('\n', '\r'), ' ', $this->sma->decode_html($biller->invoice_footer)), $pos_settings->char_per_line) : ''?>" + "\n";
                receipt += "\x1B\x61\x30";
                <?php if (isset($pos_settings->cash_drawer_cose)) {?>
                print(receipt, '', '<?=$pos_settings->cash_drawer_cose;?>');
                <?php } else {?>
                print(receipt, '', '');
                <?php }
                        ?>

            }

        </script>
    <?php }
        ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#email').click(function () {
                        var email = prompt("<?=lang("email_address");?>", "<?=$customer->email;?>");
                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?=site_url('pos/email_receipt')?>",
                                data: {<?=$this->security->get_csrf_token_name();?>: "<?=$this->security->get_csrf_hash();?>", email: email, id: <?=$inv->id;?>},
                                dataType: "json",
                                success: function (data) {
                                   alert("Your receipt has been successfully sent by mail.");
                                },
                                error: function () {
                                    alert('<?=lang('ajax_request_failed');?>');
                                    return false;
                                }
                            });
                        }
                        return false;
                    });
                });
                    <?php
                    /* ------ For checking Print/notPrint Button updated by SW 21/01/2017 --------------- */
                    $_print  = $_SESSION['print_type'];
                    if (!$pos_settings->java_applet && isset($_SESSION['print_type']) && $_SESSION['print_type'] == 'print'):
                        unset($_SESSION['print_type']);
                        ?>
                    $(window).load(function () {
                        window.print();
                    });
                    <?php endif ?>
            </script>
			
	<?php if($_print!='notprint_notredirect' && !empty($_print)):?>		
	<script>
		jQuery("document").ready(function() {
		setTimeout(function() {
		jQuery("[id*='back_to_pos']").trigger('click');
		//jQuery("[id*='email']").trigger('click');
		window.location.href='pos';
		return false;
		},10);
		});
	</script>
        <?php endif;?>
</body>
</html>
<?php }
?>