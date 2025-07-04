<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("transfer") . " " . $transfer->transfer_no; ?></title>
    <link href="<?php echo $assets ?>styles/style.css" rel="stylesheet">
    <style type="text/css">
        html, body { height: 100%; background: #FFF; }
        body:before, body:after { display: none !important; }
        .table th { text-align: center; padding: 5px; }
        .table td { padding: 4px; }
    </style>
</head>

<body>
<div id="wrap">
    <?php if ($logo) { ?>
        <div class="text-center" style="margin-bottom:20px;">
            <img src="<?= base_url() . 'assets/mdata/'. $Customer_assets .'/uploads/logos/' . $Settings->logo; ?>"
                 alt="<?= $Settings->site_name; ?>">
        </div>
    <?php } ?>
    <div class="well well-sm">
        <div class="row bold">
            <div class="col-xs-4"><?= lang("date"); ?>: <?= $this->sma->hrld($transfer->date); ?>
                <br><?= lang("ref"); ?>: <?= $transfer->transfer_no; ?></div>
            <div class="col-xs-6 pull-right text-right order_barcodes">
                <?= $this->sma->save_barcode($transfer->transfer_no, 'code128', 66, false); ?>
                <?= $this->sma->qrcode('link', urlencode(site_url('transfers/view/' . $transfer->id)), 2); ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <div class="col-xs-5">
            <strong><?php echo $this->lang->line("Warehouse From"); ?></strong>
            <h2 style="margin-top:10px;"><?= ucfirst($from_warehouse->name) . " ( " . $from_warehouse->code . " )"; ?></h2>
            <address>
                 <?= ($from_warehouse->address!='')?'Address: '.$from_warehouse->address:'' ?>
                 <?= ($from_warehouse->phone!='')?'<br/>'.lang("tel").': '.$from_warehouse->phone:''?>
                 <?= ($from_warehouse->email!='')?'<br/>'.lang("email").': '.$from_warehouse->email:''?>
            </address>
        </div>
        <div class="col-xs-5">
 		<strong><?= lang(" Warehouse To"); ?></strong>
                <h2 style="margin-top:10px;"><?= ucfirst($to_warehouse->name) . " ( " . $to_warehouse->code . " )"; ?></h2>
                <address >
                       <?= ($to_warehouse->address!='')?'Address: '.$to_warehouse->address:'' ?>
                       <?= ($to_warehouse->phone!='')?'<br/>'.lang("tel").': '.$to_warehouse->phone:''?>
                       <?= ($to_warehouse->email!='')?'<br/>'.lang("email").': '.$to_warehouse->email:''?>
                </address>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead>
            <tr>
                <th style="text-align:center; vertical-align:middle;"><?php echo $this->lang->line("no"); ?></th>
                <th style="vertical-align:middle;"><?php echo $this->lang->line("description"); ?></th>
                <th style="text-align:center; vertical-align:middle;"><?php echo $this->lang->line("quantity"); ?></th>
                <th style="text-align:center; vertical-align:middle;"><?php echo $this->lang->line("Unit_cost"); ?></th>
                <?php
                if ($Settings->tax1) {
                    echo '<th style="text-align:center; vertical-align:middle;">' . $this->lang->line("tax") . '</th>';
                }
                ?>
                <th style="text-align:center; vertical-align:middle;"><?php echo $this->lang->line("subtotal"); ?></th>
            </tr>
            </thead>

            <tbody>
            <?php $r = 1; $total_qty = 0;
            foreach ($rows as $row):
            $total_qty = $total_qty + $row->unit_quantity;
                ?>
                <tr>
                    <td style="text-align:center; width:25px;"><?php echo $r; ?></td>
                    <td style="text-align:left;">
                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                    </td>
                    <td style="text-align:center; width:80px; "><?php echo $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                    <td style="width: 100px; text-align:right; padding-right:10px; vertical-align:middle;"><?php echo $this->sma->formatMoney($row->net_unit_cost); ?></td>
                    <?php
                    if ($Settings->tax1) {
                        echo '<td style="width: 80px; text-align:right; vertical-align:middle;"><!--<small>(' . $row->tax . ')</small>--> ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                    }
                    ?>
                    <td style="width: 100px; text-align:right; padding-right:10px; vertical-align:middle;"><?php echo $this->sma->formatMoney($row->subtotal); ?></td>
                </tr>
                <?php $r++;
            endforeach;
            ?>
            </tbody>
            <tfoot>
            <?php
            $col = 4;
            if ($Settings->tax1) {
                $col += 1;
            }
            ?>

            <?php if ($Settings->tax1) { ?>
                <tr>
                    <td class="text-center" colspan="2">Total</td>
                    <td class="text-center"> <?= $total_qty  ?> Qty. </td>
                    <td colspan="2"
                        style="text-align:right; padding-right:10px;"><?php echo $this->lang->line("total"); ?>
                        (<?php echo $default_currency->code; ?>)
                    </td>
                    <td style="text-align:right; padding-right:10px;"><?php echo $this->sma->formatMoney($transfer->total); ?></td>
                </tr>
                <?php echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . $this->lang->line("product_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($transfer->total_tax) . '</td></tr>';
            }
            ?>
            
                     <?php if($transfer->shipping > 0){ ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; padding-right:10px; "><?= lang("Shipping Charges "); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; "><?= $this->sma->formatMoney($transfer->shipping); ?></td>
                        </tr>
                        
                    <?php } ?>  
       

            <tr>
                <td colspan="<?php echo $col; ?>"
                    style="text-align:right; padding-right:10px; font-weight:bold;"><?php echo $this->lang->line("total_amount"); ?>
                    (<?php echo $default_currency->code; ?>)
                </td>
                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?php echo $this->sma->formatMoney($transfer->grand_total); ?></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <?php if ($transfer->note || $transfer->note != "") { ?>
                <div class="well well-sm">
                    <p class="bold"><?= lang("note"); ?>:</p>

                    <div><?= $this->sma->decode_html($transfer->note); ?></div>
                </div>
            <?php } ?>
        </div>
        <div class="col-xs-4 pull-left">
            <p><?= lang("created_by"); ?>: <?= $created_by->first_name.' '.$created_by->last_name; ?> </p>

            <p>&nbsp;</p>

            <p>&nbsp;</p>
            <hr>
            <p><?= lang("stamp_sign"); ?></p>
        </div>
        <div class="col-xs-4 pull-right">
            <p><?= lang("received_by"); ?>: </p>

            <p>&nbsp;</p>

            <p>&nbsp;</p>
            <hr>
            <p><?= lang("stamp_sign"); ?></p>
        </div>
    </div>
</div>
</body>
</html>
