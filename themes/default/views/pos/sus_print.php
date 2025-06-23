<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Print Page</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script>
        function printPage() {
            window.print();
            window.location.href = "pos";
        }
        </script>
        <script>
        function handleButtonClick() {
            window.location.href = "<?= site_url('pos/index') ?>/" + <?= $suspend_data->id ?>;
        }
        function handleButtonClick1() {
            window.location.href = "<?= site_url('pos/index') ?>/";
        }
        </script>
        <style>
        #wrapper {
            max-width: fit-content !important;
            margin: 0 auto;
            padding-top: 20px !important;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .well {
            padding: 0.5rem;
            margin-bottom: 3px;
            background-color: #ddd;
            border: 1px solid #e3e3e3;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
        }

        p {
            font-size: 10pt !important;
            margin: 0 0 5px;
        }

        @media print {
            .no-print {
                display: none;
            }

        }

        .d-flx {
            display: flex;
            justify-content: space-between;
            /* align-items:end; */
        }

        .table .thead-light th {
            background: #428BCA;
            color: #fff;
            font-weight: bold;
        }

        .table-bordered {
            border: 1.5px solid #aeaeae !important;
        }

        thead.thead-light.text-center{
            border: 1.5px solid #aeaeae!important;
        }

        .table td,
        .table th {
            padding: .3rem;
            border: 1.5px solid #aeaeae!important;
        }

        .btn-sky {
            background: #428BCA;
            color: #fff;
            font-weight: 700;
        }

        .qrimg {
            width: 10%;
        }

        .custom-font {
            font-size: 0.75rem;
        }
        .cusom-flexsetting{
            display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        }

        #deleteSuspend{
            background:#AA0000;
        }

        .backtopos{
        background: #fff;
        color: #333;
        border: 2px solid #009dff;
        }
        </style>
    </head>
    <?php
session_start();
$_SESSION['flag'] = "back_to_pos";
    ?>
    <body>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div id="" class="col-md-6 py-5">
                    <!-- <?= $this->sma->save_barcode($customer_detail->reference_no, 'code128', 66, false); ?> -->
                    <div class="order_barcodes" style="display: flex; align-items: center; justify-content:space-between;">
                    <?php
                     
                    ?>   
                    <?= $this->sma->qrcode('link', $refNo->reference_no, 4); ?>
                        <img src="<?= $assets ?>pos/images/UnpaidLogo.svg" alt="Logo" style="display: block; width: 10%;" />

                    </div>

                    <div class="container">
                        <div class="text-center mt-2">
                            <h3 style="text-transform:uppercase; margin-bottom: 0px;">
                                <?= $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                            <div class="row justify-content-center">
                                <div class="text-center col-md-8">
                                    <?php
                                    echo "<p style='margin: 0 0 5px;'>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' .
                                    lang("tel") . ":&nbsp;" . $biller->phone .', '. lang("email") . ";&nbsp;" . $biller->email; 
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flx">
                            <div>
                                <!-- <h5>Billed To</h5> -->
                                <!-- <p><strong>Customer: <?= $item_detail->name ?></strong></p> -->
                                <p><strong>Customer:</strong> <?= $customer_detail->name ?></p>

                                <?php if ($customer_detail->name == 'Walk in Customer') { ?>
                                <!-- <p><strong>Mobile:</strong> <?= $customer_detail->phone ?></p> -->
                                <?php }else { ?>
                                <p><strong>Mobile:</strong> <?= $customer_detail->phone ?></p>
                                <?php  }?>
                                <!-- <p><strong>Email:</strong> <?= $item_detail->email ?></p>
                                <p><strong>Address:</strong> <?= $item_detail->address ?></p> -->
                            </div>

                            <div class="">
                                <!-- <h5 class="text-right">Invoice</h5> -->
                                <p class="text-right"><strong>Date:</strong> <?= $customer_detail->date ?></p>
                                <!-- <p class="text-right"><strong>Invoice No.:</strong> <?= $item_details->date ?></p> -->
                                <p class="text-right"><strong>Reference No:</strong>
                                    <?= $customer_detail->reference_no ?>
                                </p>
                            </div>

                        </div>

                        <table class="table table-bordered custom-font">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Product Name</th>
                                    <th>Product Code</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $total_quantity = 0; // To accumulate total quantity
                                    $total_amount = 0; // To accumulate total amount
                                    $index = 0; 
                                    ?>

                                <?php foreach ($item_details as $items): ?>
                                <tr>
                                    <td><?= ++$index ?></td>
                                    <td><?= htmlspecialchars($items->product_name) ?></td>
                                    <td><?= htmlspecialchars($items->product_code) ?></td>
                                    <td class="text-center"><?= number_format($items->quantity,2) ?></td>
                                    <td class="text-right">Rs.<?= number_format($items->unit_price, 2) ?></td>
                                    <td class="text-right">
                                        <?php 
                                            $amount = $items->quantity * $items->unit_price; 
                                            $total_quantity += $items->quantity; // Accumulate total quantity
                                            $total_amount += $amount; // Accumulate total amount
                                        ?>
                                        Rs.<?= number_format($amount, 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-center"><?= number_format($total_quantity,2) ?></td>
                                    <td class="text-right"></td>
                                    <td class="text-right">Rs.<?= number_format(ceil($total_amount), 2) ?></td>
                                </tr>



                                <?php 
                                    $formatter = new NumberFormatter('en_US', NumberFormatter::SPELLOUT);
                                    $grandTotal = $formatter->format(ceil($total_amount));
                                ?>
                                <tr>
                                    <td colspan="5" class="text-start">
                                        <div class="d-flex justify-content-between">
                                            <strong>Grand Total:</strong>
                                            <span class="text-right"><?php echo '( ' . strtoupper(($grandTotal)) . ' RUPEES ONLY )'; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-right">Rs.<?= number_format(ceil($total_amount), 2) ?></td>
                                </tr>
                            </tbody>
                        </table>


                        
                            <span class="pull-right col-xs-12">
                                <button class="btn  btn-block no-print btn-sky"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);"
                                    onclick="printPage()">Print</button>
                            </span>
                            <div class="cusom-flexsetting">  
                            <span class="pull-right col-md-3 p-0">
                                <button class="btn no-print btn-sky col-md-12 backtopos"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);"
                                    onclick="handleButtonClick()">Add More Items</button>

                            </span>
                            <span class="pull-right col-md-3 p-0">
                                <button class="btn no-print btn-sky col-md-12 backtopos"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);"
                                    onclick="handleButtonClick1()">Next Customer</button>

                            </span>
                            <span class="pull-right col-md-3 p-0">
                                <button id="deleteSuspend" class="btn no-print btn-sky col-md-12"
                                    style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);">Cancel</button>
                            </span>
                        </div>

                        <!-- <p><a href="http://localhost/elintpos_in_15.01_AWS_India/reciept/pdf/0b71c90f0d8a3aaa5ebdbdc6eada7c7a"
                        class="btn btn-primary">Download Receipt</a></p> -->

                        <!-- <div class="col-xs-12 mt-4 no-print" style="background:#f7f2f2; padding:1rem">
                            <h5 style="font-weight:bold;"><strong>Printing Instructions:</strong></h5>
                            <ul>
                                <li style="text-transform: capitalize;">Please disable the header and footer in browser print
                                    settings.</li>
                                <li style="text-transform: capitalize;"><strong>Chrome:</strong> Menu > Print > Disable
                                    Header/Footer in Options & Set Margins to None.</li>
                            </ul>
                        </div> -->

                    </div>

                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>

    </html>
    <script>
    $(document).ready(function() {
            $('#deleteSuspend').on('click', function() {
                window.location.href = "<?= site_url('pos/deleteSuspendForUnpaidInvoice') ?>/" + <?= $suspend_data->id ?>;
            });
        });

    </script>