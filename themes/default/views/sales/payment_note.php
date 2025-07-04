<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
    }
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <?php if ($logo) { ?>
                <!--<div class="text-center" style="margin-bottom:20px;">-->
                <img src="<?= base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo; ?>"
                     alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                <!--</div>-->
            <?php } ?>
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-5">
                    <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>
                    <?php
                    echo $biller->address . "<br />" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br />" . $biller->country;
                    echo "<p>";
                    if ($biller->cf1 != "-" && $biller->cf1 != "") {
                        echo "<br>". $biller->cf1;
                    }
                    if ($biller->cf2 != "-" && $biller->cf2 != "") {
                        echo "<br>" . $biller->cf2;
                    }
                    if ($biller->cf3 != "-" && $biller->cf3 != "") {
                        echo "<br>"  . $biller->cf3;
                    }
                    if ($biller->cf4 != "-" && $biller->cf4 != "") {
                        echo "<br>" . $biller->cf4;
                    }
                    if ($biller->cf5 != "-" && $biller->cf5 != "") {
                        echo "<br>". $biller->cf5;
                    }
                    if ($biller->cf6 != "-" && $biller->cf6 != "") {
                        echo "<br>" . $biller->cf6;
                    }
                    echo "</p>";
                    echo lang("tel") . ": " . $biller->phone . "<br />" . lang("email") . ": " . $biller->email;
                    ?>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-5">
                    <h2 class=""><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                    <?= $customer->company ? "" : "Attn: " . $customer->name ?>
                    <?php
                    echo $customer->address . "<br />" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br />" . $customer->country;
                    echo "<p>";
                    if ($customer->cf1 != "-" && $customer->cf1 != "") {
                        echo "<br>"  . $customer->cf1;
                    }
                    if ($customer->cf2 != "-" && $customer->cf2 != "") {
                        echo "<br>"  . $customer->cf2;
                    }
                    if ($customer->cf3 != "-" && $customer->cf3 != "") {
                        echo "<br>"  . $customer->cf3;
                    }
                    if ($customer->cf4 != "-" && $customer->cf4 != "") {
                        echo "<br>" . $customer->cf4;
                    }
                    if ($customer->cf5 != "-" && $customer->cf5 != "") {
                        echo "<br>"  . $customer->cf5;
                    }
                    if ($customer->cf6 != "-" && $customer->cf6 != "") {
                        echo "<br>" . $customer->cf6;
                    }
                    echo "</p>";
                    echo lang("tel") . ": " . $customer->phone . "<br />" . lang("email") . ": " . $customer->email;
                    ?>

                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <p style="font-weight:bold;"><?= lang("date"); ?>: <?= $this->sma->hrsd($payment->date); ?></p>

                    <p style="font-weight:bold;"><?= lang("payment_reference"); ?>: <?= $payment->reference_no; ?></p>
                </div>
            </div>
            <div class="well">               
                <table class="table table-borderless" style="margin-bottom:0;">
                 <tbody>
                    <tr>
                        <td>
                            <strong><?= $payment->type == 'returned' ? lang("payment_returned") : lang("payment_received"); ?></strong>
                        </td>
                        <td class="text-right">
                            <strong class="text-right"><?php echo $this->sma->formatMoney($payment->amount); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("paid_by"); ?></strong></td>
                        <td class="text-right"><strong class="text-right"><?php echo lang($payment->paid_by); ?></strong></td>
                    </tr>                    
                    <?php 
                       if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC' || $payment->paid_by == 'DC'){
                           $tansactlable =  lang('transaction_no');
                           $tansactvalue =  $payment->transaction_id;
                       }
                       elseif ($payment->paid_by == 'Cheque') {
                           $tansactlable =  lang('cheque_no');
                           $tansactvalue =  $payment->cheque_no ;
                       } else {
                           $tansactvalue = $tansactlable = '';
                       }

                       if($tansactvalue) {
                    ?>
                    <tr>
                        <td><strong><?= $tansactlable?> </strong></td>
                        <td class="text-right"><strong class="text-right"><?= $tansactvalue?> </strong></td>
                    </tr>
                    <?php } ?>
                    <?php if ( $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'authorize') { ?>
                    <tr>
                        <td>
                            <strong><?= lang("name"); ?></strong>
                        </td>
                        <td class="text-right">
                            <strong class="text-right"><?= $payment->cc_holder; ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?= lang("transaction_no"); ?></strong>
                        </td>
                        <td class="text-right">
                            <strong class="text-right"><?= $payment->transaction_id; ?></strong>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="2"><?= html_entity_decode($payment->note); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="clear: both;"></div>
            <div class="row">
                <div class="col-sm-4 pull-left">
                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p style="border-bottom: 1px solid #666;">&nbsp;</p>

                    <p><?= lang("stamp_sign"); ?></p>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>