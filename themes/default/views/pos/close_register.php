<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;"
                onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>

            <h4 class="modal-title" id="myModalLabel">
                <?= lang('close_register') . ' (' . $this->sma->hrld($register_open_time ? $register_open_time : $this->session->userdata('register_open_time')) . ' - ' . $this->sma->hrld(date('Y-m-d H:i:s')) . ')'; ?>
            </h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("pos/close_register/" . $user_id, $attrib);
        ?>
        <div class="modal-body">
            <div id="alerts"></div>

            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('cash_in_hand'); ?>:</h4>
                    </td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($this->session->userdata('cash_in_hand')); ?></span>
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('cash_sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($cashsales->paid ? $cashsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($cashsales->paid ? $cashsales->paid : '0.00') . ' (' . $this->sma->formatMoney($cashsales->total ? $cashsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('ch_sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($chsales->paid ? $chsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($chsales->paid ? $chsales->paid : '0.00') . ' (' . $this->sma->formatMoney($chsales->total ? $chsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('cc_sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($ccsales->paid ? $ccsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($ccsales->paid ? $ccsales->paid : '0.00') . ' (' . $this->sma->formatMoney($ccsales->total ? $ccsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>

                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('dc_sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <span><?= $this->sma->formatMoney($dcsales->paid ? $dcsales->paid : '0.00') ?>
                    </td>
                    <!--                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <span><?= $this->sma->formatMoney($dcsales->total ? $dcsales->total : '0.00'); ?></span>
                    </td>-->
                </tr>

                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('gc_sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($gcsales->paid ? $gcsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($gcsales->paid ? $gcsales->paid : '0.00') . ' (' . $this->sma->formatMoney($gcsales->total ? $gcsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>

                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('deposit_sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($depositsales->paid ? $depositsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($depositsales->paid ? $depositsales->paid : '0.00') . ' (' . $this->sma->formatMoney($depositsales->total ? $depositsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>

                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('Other Sale'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <?= $this->sma->formatMoney($othersales->paid ? $othersales->paid : '0.00') ?></td>
                    <!--                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <?= $this->sma->formatMoney($othersales->total ? $othersales->total : '0.00'); ?> 
                    </td>-->
                </tr>
                <?php if($pos_settings->neft){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('NEFT'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($neftsales->paid ? $neftsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($neftsales->paid ? $neftsales->paid : '0.00') . ' (' . $this->sma->formatMoney($neftsales->total ? $neftsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>
                <?php if($pos_settings->paytm_opt){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('PAYTM'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($paytmsales->paid ? $paytmsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($paytmsales->paid ? $paytmsales->paid : '0.00') . ' (' . $this->sma->formatMoney($paytmsales->total ? $paytmsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if($pos_settings->google_pay){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('Google Pay'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($googlepaysales->paid ? $googlepaysales->paid : '0.00')?></span>

                            <!--<span><?= $this->sma->formatMoney($googlepaysales->paid ? $googlepaysales->paid : '0.00') . ' (' . $this->sma->formatMoney($googlepaysales->total ? $googlepaysales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if($pos_settings->swiggy){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('Swiggy'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($swiggysales->paid ? $swiggysales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($swiggysales->paid ? $swiggysales->paid : '0.00') . ' (' . $this->sma->formatMoney($swiggysales->total ? $swiggysales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if($pos_settings->zomato){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('Zomato'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($zomatosales->paid ? $zomatosales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($zomatosales->paid ? $zomatosales->paid : '0.00') . ' (' . $this->sma->formatMoney($zomatosales->total ? $zomatosales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if($pos_settings->ubereats){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('Ubereats'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($ubereatssales->paid ? $ubereatssales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($ubereatssales->paid ? $ubereatssales->paid : '0.00') . ' (' . $this->sma->formatMoney($ubereatssales->total ? $ubereatssales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if($pos_settings->complimentary){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('Complimentary'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($complimentrysales->paid ? $complimentrysales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($complimentrysales->paid ? $complimentrysales->paid : '0.00') . ' (' . $this->sma->formatMoney($complimentrysales->total ? $complimentrysales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if($pos_settings->UPI_QRCODE){ ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('UPI & QR Code'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($upiqrcode->paid ? $upiqrcode->paid : '0.00') ?></span>
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($pos_settings->paypal_pro) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('paypal_pro'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($pppsales->paid ? $pppsales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($pppsales->paid ? $pppsales->paid : '0.00') . ' (' . $this->sma->formatMoney($pppsales->total ? $pppsales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($pos_settings->stripe) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;">
                        <h4><?= lang('stripe'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;">
                        <h4>
                            <span><?= $this->sma->formatMoney($stripesales->paid ? $stripesales->paid : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($stripesales->paid ? $stripesales->paid : '0.00') . ' (' . $this->sma->formatMoney($stripesales->total ? $stripesales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($pos_settings->authorize) { ?>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('authorize'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($authorizesales->paid ? $authorizesales->paid : '0.00')?></span>
                            <!--<span><?= $this->sma->formatMoney($authorizesales->paid ? $authorizesales->paid : '0.00') . ' (' . $this->sma->formatMoney($authorizesales->total ? $authorizesales->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <?php } ?>

                <tr>
                    <td width="300px;" style="font-weight:bold; border-bottom: 1px solid #DDD;""><h4><strong><?= lang('Total Paid'); ?>:</strong></h4></td>
                    <td width=" 200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                            <span><strong><?= $this->sma->formatMoney($totalsales->paid ? $totalsales->paid : '0.00') ?></strong> </span>
                          
                        </h4></td>
                </tr>    
                
                <tr>
                    <td width=" 300px;" style="font-weight:bold;border-bottom: 1px solid #DDD;""><h4><strong><?= lang('total_sales'); ?>:</strong></h4></td>
                    <td width=" 200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                          <!--<span><strong><?= $this->sma->formatMoney($totalsales->total ? $totalsales->total + $duesales->duetotal + str_replace("-", '', $refunds->returned) : '0.00') ?></strong> </span>-->
                          <span><strong><?= $this->sma->formatMoney($totalsales->paid ? $totalsales->paid + $duesales->duetotal  : '0.00') ?></strong> </span>
                            <!--<span><?= $this->sma->formatMoney($totalsales->paid ? $totalsales->paid : '0.00') . ' (' . $this->sma->formatMoney($totalsales->total ? $totalsales->total : '0.00') . ')'; ?></span>-->
                        </h4></td>
                </tr>
                
                <tr>
                    <td width=" 300px;" style="font-weight:bold; border-bottom: 1px solid #DDD;""><h4><strong><?= lang('Total Due'); ?>: </strong></h4></td>
                    <td width=" 200px;" style="font-weight:bold;text-align:right; border-bottom: 1px solid #DDD;""><h4>
                            <span><strong><?= $this->sma->formatMoney($duesales->duetotal + $duepartial->partial_due) ?> </strong></span>
                          
                        </h4></td>
                </tr>   
                
                <tr>
                    <td style=" border-top: 1px solid #DDD;">
                        <h4><?= lang('Refunds On Cash'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-top: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($refunds->returned ? $refunds->returned : '0.00')?></span>
                            <!--<span><?= $this->sma->formatMoney($refunds->returned ? $refunds->returned : '0.00') . ' (' . $this->sma->formatMoney($refunds->total ? $refunds->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;">
                        <h4><?= lang('Refunds On Other'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-top: 1px solid #DDD;">
                        <h4>
                            <span><?= $this->sma->formatMoney($returned_other->returned ? $returned_other->returned : '0.00') ?></span>
                            <!--<span><?= $this->sma->formatMoney($refunds->returned_other ? $refunds->returned_other : '0.00') . ' (' . $this->sma->formatMoney($refunds->total ? $refunds->total : '0.00') . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;">
                        <h4><?= lang('expenses'); ?>:</h4>
                    </td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;">
                        <h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->sma->formatMoney($expense) ?></span>
                            <!--<span><?php // $expense = $expenses ? $expenses->total : 0; echo $this->sma->formatMoney($expense) . ' (' . $this->sma->formatMoney($expense) . ')'; ?></span>-->
                        </h4>
                    </td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;">
                        <h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;">
                        <h4>
                            <?php 
                     //$total_cash_amount = $cashsales->paid ? (($cashsales->paid + ($this->session->userdata('cash_in_hand'))) + ($refunds->returned ? $refunds->returned : 0) - $expense) : $this->sma->formatMoney($this->session->userdata('cash_in_hand')-$expense); 
                     $total_cash_amount = $cashsales->paid ? (($cashsales->paid + ($this->session->userdata('cash_in_hand'))) + ($refunds->returned ? $refunds->returned : 0) - $expense) : $this->session->userdata('cash_in_hand')-$expense; 
                      ?>
                            <span><strong><?= $this->sma->formatMoney($total_cash_amount); ?></strong></span>
                        </h4>
                    </td>
                </tr>

                <tr>
                    <td width="300px;" style="font-weight:bold;">
                        <h4><strong><?= lang('Deposit Received'); ?></strong>:</h4>
                        <span style="font-size:12px; font-weight: normal;">Paid By :
                            <?= $deposit_received->paid_by ?></span>
                    </td>
                    <td style="text-align:right;">
                        <h4>
                            <span><strong><?= $this->sma->formatMoney($deposit_received->deposit_amount); ?></strong></span>
                        </h4>
                    </td>
                </tr>
            </table>

            <?php

            if ($suspended_bills) {
                echo '<hr><h3>' . lang('opened_bills') . '</h3><table class="table table-hovered table-bordered"><thead><tr><th>' . lang('customer') . '</th><th>' . lang('date') . '</th><th>' . lang('total_items') . '</th><th>' . lang('amount') . '</th><th><i class="fa fa-trash-o"></i></th></tr></thead><tbody>';
                foreach ($suspended_bills as $bill) {
                    echo '<tr><td>' . $bill->customer . '</td><td>' . $this->sma->hrld($bill->date) . '</td><td class="text-center">' . $bill->count . '</td><td class="text-right">' . $bill->total . '</td><td class="text-center"><a href="#" class="tip po" title="<b>' . $this->lang->line("delete_bill") . '</b>" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger po-delete\' href=\'' . site_url('pos/delete/' . $bill->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"  rel="popover"><i class="fa fa-trash-o"></i></a></td></tr>';
                }
                echo '</tbody></table>';
            }

            ?>
            <hr>
            <div class="row no-print">
                <div class="col-sm-6">
                    <!-- <div class="form-group">
                        <?= lang("total_cash", "total_cash_submitted"); ?>
                        <?= form_hidden('total_cash', $total_cash_amount); ?>
                        <?= form_input('total_cash_submitted', (isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash_amount), 'class="form-control input-tip" id="total_cash_submitted" required="required"'); ?>
                    </div> -->
                    <div class="form-group row">
                        <?php
                            $col_class = ($pos_settings->display_coinage == 1) ? 'col-xs-6' : 'col-xs-12';
                        ?>
                        <div class="<?= $col_class ?>">
                            <?= lang("total_cash", "total_cash_submitted"); ?>
                            <?= form_hidden('total_cash', $total_cash_amount); ?>
                            <?php
                                $readonly = (isset($pos_settings->display_coinage) && $pos_settings->display_coinage) ? 'readonly' : '';
                                $input_value = isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash_amount;
                            ?>
                            <?= form_input('total_cash_submitted', $input_value, 'class="form-control input-tip" id="total_cash_submitted" required="required" ' . $readonly); ?>
                        </div>

                        <?php if ($pos_settings->display_coinage == 1) { ?>
                        <div class="col-xs-6" style="margin-top: 25px;">
                            <a href="#" id="update_register_btn" class="btn btn-primary btn-block" data-toggle="modal"
                                data-target="#updateRegisterModal">
                                <i class="fa fa-refresh"></i> <?= lang('Update_Register'); ?>
                            </a>
                        </div>
                        <?php } ?>


                    </div>
                    <div class="form-group">
                        <?= lang("total_cheques", "total_cheques_submitted"); ?>
                        <?= form_hidden('total_cheques', $chsales->total_cheques); ?>
                        <?= form_input('total_cheques_submitted', (isset($_POST['total_cheques_submitted']) ? $_POST['total_cheques_submitted'] : $chsales->total_cheques), 'class="form-control input-tip" id="total_cheques_submitted" required="required"'); ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <?php if ($suspended_bills) { ?>
                    <div class="form-group">
                        <?= lang("transfer_opened_bills", "transfer_opened_bills"); ?>
                        <?php $u = $user_id ? $user_id : $this->session->userdata('user_id');
                            $usrs[-1] = lang('delete_all');
                            $usrs[0] = lang('leave_opened');
                            foreach ($users as $user) {
                                if ($user->id != $u) {
                                    $usrs[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                            }
                            ?>
                        <?= form_dropdown('transfer_opened_bills', $usrs, (isset($_POST['transfer_opened_bills']) ? $_POST['transfer_opened_bills'] : 0), 'class="form-control input-tip" id="transfer_opened_bills" required="required"'); ?>
                    </div>
                    <?php } ?>
                    <div class="form-group">
                        <?= lang("total_cc_slips", "total_cc_slips_submitted"); ?>
                        <?= form_hidden('total_cc_slips', $ccsales->total_cc_slips); ?>
                        <?= form_input('total_cc_slips_submitted', (isset($_POST['total_cc_slips_submitted']) ? $_POST['total_cc_slips_submitted'] : $ccsales->total_cc_slips), 'class="form-control input-tip" id="total_cc_slips_submitted" required="required"'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group no-print">
                <label for="note"><?= lang("note"); ?></label>

                <div class="controls">
                    <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                </div>
            </div>

        </div>
        <div class="modal-footer no-print">
            <?= form_submit('close_register', lang('close_register'), 'class="btn btn-primary" id="close-register-btn"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

</div>
<?= $modal_js ?>
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.po', function(e) {
        e.preventDefault();
        $('.po').popover({
            html: true,
            placement: 'left',
            trigger: 'manual'
        }).popover('show').not(this).popover('hide');
        return false;
    });
    $(document).on('click', '.po-close', function() {
        $('.po').popover('hide');
        return false;
    });
    $(document).on('click', '.po-delete', function(e) {
        var row = $(this).closest('tr');
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        $.ajax({
            type: "get",
            url: link,
            success: function(data) {
                row.remove();
                addAlert(data, 'success');
            },
            error: function(data) {
                addAlert('Failed', 'danger');
            }
        });
        return false;
    });
});

function addAlert(message, type) {
    $('#alerts').empty().append(
        '<div class="alert alert-' + type + '">' +
        '<button type="button" class="close" data-dismiss="alert">' +
        '&times;</button>' + message + '</div>');
}
$(document).ready(function() {
    $('#update_register_btn').on('click', function(e) {
        e.preventDefault();
        $('#myModal').html(
            '<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');

        $.get('<?= site_url('pos/update_register'); ?>', function(data) {
            $('#myModal').html(data);
        });
    });
});
document.getElementById('close-register-btn').addEventListener('click', function(e) {
    const confirmed = confirm("Are you sure you want to close the register?");
    if (!confirmed) {
        e.preventDefault();
    }
});
</script>
<style>
a#update_register_btn {
    margin-top: 5px;
}
input#total_cash_submitted{
    text-align: right;
}
</style>