<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_gift_card'); ?></h4>
        </div>
        <div class="modal-body">
            <?php if ($gift_card->expiry && $gift_card->expiry < date('Y-m-d')) { ?>
                <div class="alert alert-danger">
                    <?= lang('card_expired') ?>
                </div>
            <?php } else if ($gift_card->balance > 0) { ?>
                <div class="alert alert-info">
                    <?= lang('balance').': '.$Settings->default_currency.' '.$gift_card->balance; ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">
                    <?= lang('card_is_used'); ?>
                </div>
            <?php } ?>
            <div class="card">
                <div class="front">
                    <img src="<?=$assets;?>/images/card.png" alt="" class="card_img">
                    <div class="card-content white-text">
                        <?php $br = $this->sma->save_barcode($gift_card->card_no, 'code128', 30, false); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="353px" height="206px" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <text x="5"  y="20" style="font-size:16;fill:#FFF;">
                                <?= lang('gift_card'); ?>
                            </text>
                            <text x="185"  y="20" style="font-size:16;fill:#FFF;">
                                <?= $gift_card->card_no; ?>
                            </text>
                            <text x="5"  y="75" style="font-size:36;fill:#FFF;">
                                <?= $Settings->default_currency.' '.$this->sma->formatMoney($gift_card->value); ?>
                            </text>
                            <text x="5"  y="105" style="font-size:16;fill:#FFF;">
                                <?= $customer ? ($customer->company != '-' ? $customer->company : $customer->name) : ''; ?>
                            </text>
                            <text x="5"  y="125" style="font-size:16;fill:#FFF;">
                                <?= $gift_card->expiry ? lang('expiry').': '.$this->sma->hrsd($gift_card->expiry) : ''; ?>
                            </text>
                            <image xlink:href="<?= base_url() ?>assets/mdata/<?= $Customer_assets ?>/uploads/barcode<?= $this->session->userdata('user_id') ?>.png" x="-10" y="145" height="30" width="353" />
                        </svg>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <div class="back">
                    <img src="<?=$assets;?>/images/card2.png" alt="" class="card_img">
                    <div class="card-content">
                        <div class="middle">
                            <?= '<img src="' . base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" />'; ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if ($gift_card->balance > 0 && (! $gift_card->expiry || $gift_card->expiry > date('Y-m-d'))) { ?>
            <button type="button" class="btn btn-primary btn-block no-print" onClick="window.print();"><?= lang('print'); ?></button>
            <?php } ?>
            <?php
            if ($topups) {
                echo '<div class="no-print">';
                echo '<h2>'.lang('last_topups').'</h2>';
                echo '<table class="table table-striped table-condensed">';
                echo '<thead>';
                echo '<tr><th>'.lang('date').'</th><th>'.lang('amount').'</th><th>'.lang('created_by').'</th></tr>';
                echo '</thead>';
                foreach ($topups as $topup) {
                    echo '<tr>';
                    echo '<td>'.$this->sma->hrld($topup->date).'</td>';
                    echo '<td>'.$this->sma->formatMoney($topup->amount).'</td>';
                    echo '<td>'.$topup->first_name.' '.$topup->last_name.' ('.$topup->email.')</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
