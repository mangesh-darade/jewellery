<!-- for hiding the coinage in theme five -->
<?php if ($Settings->theme != 'theme_five') { ?>
<?php
$displayStyle = ($pos_settingss->display_seller == 2) ? 'none' : 'block';
?>
<script defer type="text/javascript" src="<?= $assets ?>pos/js/coinage.js"></script>
<style>
.gray-out {
    background-color: #d3d3d3;
    pointer-events: none;
    /* Disable interactions */
    opacity: 0.6;
    /* Make it appear faded */
}
</style>
<style>
.highlight {
    background-color: green;
    color: white;
}

.remove.hidden {
    display: none;
}

.scroll-visible {
    overflow-x: auto;
    /* or scroll */
    -ms-overflow-style: auto;
    scrollbar-width: auto;
}

.scroll-visible::-webkit-scrollbar {
    height: 8px;
}

.scroll-visible::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.scroll-visible::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
<div class="modal fade in payment-setting" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close reset-denominations" data-dismiss="modal">
                    <span aria-hidden="true"><i class="fa fa-times-circle" aria-hidden="true"></i></span>
                    <span class="sr-only"><?= lang('close'); ?></span>
                </button>
                <div style="display:flex;align-items:center;">
                    <h2 class="modal-title" id="payModalLabel"><?= lang('finalize_sale'); ?>
                    </h2>
                    <button type="button" class="UpdtRegister update-register-btn" id="updateRegisterBtn">
                        <span aria-hidden="true"><i class="fa fa-refresh" aria-hidden="true"></i></span>
                        <span class="Update"><?= lang('Update Register'); ?></span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm custom-back-btn" id="backToPOS">
                        <a href=<?= base_url('pos') ?>>
                            <i class="fa fa-arrow-left"></i> Back to POS
                        </a>
                    </button>
                    <!-- Screen Blocking Overlay -->
                    <div id="returnOverlay" style="display: none;position: fixed;top: 0;left: 0;width: 100%;height: 100%;background: rgba(0, 0, 0, 0.5);
                              z-index: 9998;"></div>
                    <!-- Return Toast Popup -->
                    <div id="returnToast" style="position: relative; left:23%; transform: translateX(-50%); background-color:white; color: red; padding: 9px 22px;
    border-radius: 6px; box-shadow: rgba(0, 0, 0, 0.2) 0px 2px 10px; font-weight: bold; z-index: 9999; display: none;">
                        <span id="returnToastMessage"></span>
                        <button id="returnToastOkBtn" style="margin-left: 15px; background: red; color: white; border: none;
        padding: 4px 10px; border-radius: 4px; cursor: pointer; font-weight: bold;">OK</button>
                    </div>


                </div>
            </div>

            <div class="modal-body" id="payment_content">
                <div class="row">
                    <input type="hidden" value="" name="CollectedFlag" id="CollectedFlag" />

                    <div class="col-sm-6">
                        <?php if ($pos_settings->active_repeat_customer_discount && $pos_settings->auto_apply_repeat_customer_discount == '0') { ?>
                        <input type="checkbox" name="repeate_sales_discount" id="repeate_sales_discount">
                        <label for="repeate_sales_discount"> Apply Repeat Sales Discount </label>
                        <?php } ?>
                        <!-- //////////////////////////////////////////////// -->
                        <div class="container text-danger" id="showamtbalance" style="display:none">
                            <strong id="showawardpoint"></strong> <br />
                            <strong id="showdeposit"></strong> <br />
                            <strong id="showgiftcard"></strong>

                        </div>
                        <div class="container text-danger" id="showduebalance" style="display:none">
                            <strong id="showdue"></strong> <br />
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <!-- <?php
                        if ($sms_limit == 0) {
                            echo '<strong class="text-danger">  If SMS bal is 0 then (Your SMS package is expired. Please recharge with a valid SMS Package) </strong>';
                        } elseif ($sms_limit < 100) {
                            echo '<strong class="text-danger">  If SMS bal is less that 100 (Your SMS balance is low, SMS balance:- 98)</strong>';
                        }
                        ?> -->

                        <div class="row">
                            <div class="back-to-pos-btn">
                                <a href=<?= base_url('pos') ?> class=" btn-sm">
                                    <i class=""></i>
                                </a>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-4 sidemargin">
                        <div id="leftdiv">
                            <div id="printhead">
                            </div>
                            <!--removed-->
                            <input type="hidden" value="" name="customer1" id="custname" />
                            <input type="hidden" value="" name="cust_search" id="custsearch" />
                            <div id="print">
                                <div id="left-middle">
                                    <div id="product-list" style="padding-bottom: 3em !important;">
                                        <input type="hidden" value="<?= $GP['cart-unit_view']; ?>"
                                            name="per_cartunitview" id="per_cartunitview" />
                                        <input type="hidden" value="<?= $GP['cart-price_edit']; ?>"
                                            name="per_cartpriceedit" id="per_cartpriceedit" />
                                        <input type="hidden" value="<?= $Owner; ?>" name="per_owner"
                                            id="permission_owner" />
                                        <input type="hidden" value="<?= $Admin; ?>" name="per_admin"
                                            id="permission_admin" />
                                        <input type="hidden" value="<?= $Settings->add_tax_in_cart_unit_price; ?>"
                                            name="add_tax_in_cart_unit_price" id="add_tax_in_cart_unit_price" />
                                        <input type="hidden" value="<?= $Settings->add_discount_in_cart_unit_price; ?>"
                                            name="add_discount_in_cart_unit_price"
                                            id="add_discount_in_cart_unit_price" />
                                        <input type="hidden" value="<?= $pos_settings->change_qty_as_per_user_price; ?>"
                                            name="change_qty_as_per_user_price" id="change_qty_as_per_user_price" />

                                        <input type="hidden" name="Current_Date" id="Current_Date"
                                            value="<?php echo date('Y-m-d'); ?>">
                                        <div class="scroll-visible" style="max-width: 100%; overflow-x: auto;">
                                            <table
                                                class="table items table-striped table-bordered table-condensed table-hover table-responsive"
                                                id="posTable" style="margin-bottom: 50;">
                                                <thead>
                                                    <tr>
                                                        <!-- <th width="35%"><i class="fa fa-pencil"></i> <?= lang("Edit"); ?></th> -->
                                                        <th width="35%"><?= lang("product"); ?></th>
                                                        <th width="15%"><?= lang("price"); ?></th>
                                                        <th width="25%"><?= lang("qty"); ?></th>
                                                        <?php if ($Owner || $Admin || $GP['cart-unit_view']) { ?>
                                                        <th width="10%"><?= lang("unit"); ?></th>
                                                        <?php } ?>
                                                        <th width="15%"><?= lang("Sub total"); ?></th>
                                                        <!-- <th class="width5">
                                                            <i class="fa fa-trash-o"></i>
                                                        </th> -->
                                                    </tr>
                                                </thead>
                                                <tbody id="getsdta">
                                                </tbody>
                                            </table>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div id="left-bottom">
                                                <?php
                                                if ($Settings->theme == 'theme_three' && $Owner || $Admin) { ?>
                                                <table id="totalTable" style="margin-bottom: 4px !important;">
                                                    <?php } elseif ($Owner || $Admin) { ?>
                                                    <table id="totalTable" class="margincustome custmar">
                                                        <?php } else { ?>
                                                        <table id="totalTable">
                                                            <?php } ?>
                                                            <tr>
                                                                <td class="tdpaddingborder"><?= lang('items'); ?></td>
                                                                <td class="text-right tdpaddingborder font-weight-bold">
                                                                    <span id="titems1">0</span>
                                                                </td>
                                                                <td class="tdpaddingborder"><?= lang('total'); ?></td>
                                                                <td class="text-right tdpaddingborder font-weight-bold">
                                                                    <span id="total1">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <?php if ($Settings->default_tax_rate2 != '0') {  ?>
                                                                <td class="tdpadding"><?= lang('order_tax'); ?>
                                                                    <a href="#" id="pptax21">
                                                                        <i class="fa fa-edit"></i>
                                                                    </a>
                                                                </td>
                                                                <?php } else { ?>
                                                                <td class="tdpadding"><?= lang('product_tax'); ?>
                                                                    <a href="#" id="pptax21">
                                                                    </a>
                                                                </td>
                                                                <?php } ?>

                                                                <td class="text-right tdpadding font-weight-bold">
                                                                    <span id="ttax21">0.00</span>
                                                                </td>
                                                                <td class="tdpadding"><?= lang('discount'); ?>
                                                                    <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                                                                    <!-- <a href="#" id="ppdiscount">
                                                                                <i class="fa fa-edit"></i>
                                                                            </a> -->
                                                                    <?php } ?>
                                                                </td>
                                                                <td class="text-right tdpadding font-weight-bold">
                                                                    <span id="tds1">0.00</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 5px 10px; border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;"
                                                                    colspan="2">
                                                                    <?= lang('total_payable'); ?>
                                                                </td>
                                                                <td class="text-right"
                                                                    style="padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;"
                                                                    colspan="2">
                                                                    <span id="gtotal1">0.00</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="font16" style="margin-top: -18px;">
                                        <table class="table table-bordered table-condensed table-striped" id="totaltab"
                                            style="margin-bottom: 0; background: white;border: 9px solid #E6F5FF;">
                                            <tbody>
                                                <tr>
                                                    <td>Total Items</td>
                                                    <td class="text-right"><span id="item_count">0.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Payable</td>
                                                    <td class="text-right"><span id="twt">0.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Paying</td>
                                                    <td class="text-right"><span id="total_paying">0.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang("balance"); ?></td>
                                                    <td class="text-right"><span id="balance" class="bal">0.00</span>
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6 text-center mobcard card-div">
                        <div class="row card-box ps-scrollbar-y" id="toToggle"
                            style="overflow: scroll;z-index: 1000 !important;position: relative; background:rgba(255, 255, 255, 1);">
                            <!-- <span><button id="toggle" style="position: sticky;z-index: 1000;top: 87%; right: 49%;"> <i class="fa fa-chevron-down" id="dropBtn"></i></button></span> -->
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input type="radio" id="checkbox1" class="card custom_payment_icon"
                                        name="colorRadio" checked value="cash">

                                    <label for="checkbox1" class="payment-method">
                                        <span class="paddingTop whitebgredB">
                                            <img class="icon_image_option" src="<?= $assets ?>pos/images/NCash.svg"
                                                alt="">
                                            Cash
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <?php if ($pos_settings->google_pay == '1'): ?>
                            <div class=" col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox10" title="Google pay" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="Googlepay">
                                    <label for="checkbox10" class="payment-method">
                                        <span class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NGPay.svg" class="icon_image_option"
                                                alt="googlepay">
                                            Google pay</span>
                                    </label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/googlepay.jpg"
                                                    class="icon_image_option" alt="googlepay">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->UPI_QRCODE == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox16" title="UPI & QR CODE" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="UPI_QRCODE">
                                    <label for="checkbox16" class="payment-method"><span
                                            class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NQR.svg" class="icon_image_option2"
                                                alt="credit-card">UPI_QRCODE</span></label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/credit-card.jpg"
                                                    class="icon_image_option2" alt="credit-card">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->ccavenue == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox19" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="ccavenue">
                                    <label for="checkbox19" class="payment-method"><span class="paddingTop whitebgredB">
                                            <img src="<?= $assets ?>pos/images/NCCAvenue.svg" class="icon_image_option"
                                                alt="">CCavenue</span></label>
                                    <!-- <span class="payment-icon whitebgredB"><img
                                                    src="<?= $assets ?>pos/images/ccavenue.png"
                                                    class="icon_image_option" alt=""></span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->debit_card == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox7" title="Debit Card" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="DC">
                                    <label for="checkbox7" class="payment-method">
                                        <span class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NDebit.svg" alt="debit"
                                                class="icon_image_option">
                                            Debit Card
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->credit_card == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox8" title="Credit Card" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="CC">
                                    <label for="checkbox8" class="payment-method">
                                        <span class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NCredit.svg" alt="creaditcard"
                                                class="icon_image_option">
                                            Credit Card
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input title="Payswiff" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" id="payswiff" value="payswiff">
                                    <label for="payswiff" class="payment-method">
                                        <span class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NPaySwiff.svg" class="icon_image_option"
                                                alt="credit-card">
                                            Pay Swiff
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>


                            <?php if ($pos_settings->award_point == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input type="radio" id="checkbox18" class="card custom_payment_icon"
                                        name="colorRadio" value="award_point">

                                    <label for="checkbox18" class="payment-method">
                                        <span class="paddingTop whitebgredB">
                                            <img class="icon_image_option"
                                                src="<?= $assets ?>pos/images/NAwardPoints.svg" alt="">
                                            Award Point
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <?php endif; ?>


                            <?php if ($pos_settings->deposit == '1'): ?>

                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input type="radio" id="checkbox3" class="card custom_payment_icon"
                                        name="colorRadio" value="deposit">
                                    <label for="checkbox3" class="payment-method">
                                        <span class="paddingTop whitebgredB">
                                            <img class="icon_image_option" src="<?= $assets ?>pos/images/NDeposit.svg"
                                                alt="">
                                            Deposit
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($pos_settings->razorpay == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input type="radio" id="checkbox21" class="card custom_payment_icon"
                                        name="colorRadio" value="razorpay">
                                    <label for="checkbox21" class="payment-method"><span class="paddingTop whitebgredB">
                                            <img src="<?= $assets ?>pos/images/NRazorpay.svg" alt=""
                                                class="icon_image_option">Razorpay</span></label>
                                    <!-- <span class="payment-icon whitebgredB"> <img
                                                    src="<?= $assets ?>pos/images/razorpay.svg" alt=""
                                                    class="icon_image_option"></span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->Cheque == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input type="radio" id="checkbox2" class="card custom_payment_icon"
                                        name="colorRadio" value="Cheque">

                                    <label for="checkbox2" class="payment-method">
                                        <span class="paddingTop whitebgredB">
                                            <img class="icon_image_option" src="<?= $assets ?>pos/images/NCheque.svg"
                                                alt="">
                                            Cheque
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <?php endif; ?>

                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input type="radio" id="checkbox4" class="card custom_payment_icon"
                                        name="colorRadio" value="other">
                                    <label for="checkbox4" class="payment-method">
                                        <span class="paddingTop whitebgredB">
                                            <img class="icon_image_option" src="<?= $assets ?>pos/images/NOther.svg"
                                                alt="">
                                            Other
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <?php if ($pos_settings->gift_card == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox5" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="gift_card">
                                    <label for="checkbox5" class="payment-method">
                                        <span class="paddingTop orangebg">
                                            <img class="icon_image_option" src="<?= $assets ?>pos/images/NGift.svg"
                                                alt="">
                                            Gift Card
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->neft == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox6" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="NEFT">
                                    <label for="checkbox6" class="payment-method">
                                        <span class="paddingTop orangebg">
                                            <img src="<?= $assets ?>pos/images/NNeft.svg" alt=""
                                                class="icon_image_option2">
                                            NEFT
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- <div class="col-md-4 col-sm-6 col-xs-6">
                                        <div class="radio-div">
                                            <input id="checkbox9" type="radio" class="card custom_payment_icon"
                                                name="colorRadio" value="paytm">
                                            <label for="checkbox9" class="payment-method">
                                                <span class="paddingTop whitebgblueB"> Paytm Gateway</span>
                                            </label>
                                            <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/patm.png" class="icon_image_option"
                                                    alt="credit-card"></span>
                                        </div>
                                    </div> -->
                            <?php if ($pos_settings->paytm == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox9" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="paytm">
                                    <label for="checkbox9" class="payment-method">
                                        <span class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NPaytm.svg" class="icon_image_option"
                                                alt="paytm">
                                            Paytm Gateway
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>



                            <?php if ($pos_settings->swiggy == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox11" title="Swiggy" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="swiggy">
                                    <label for="checkbox11" class="payment-method"><span
                                            class="paddingTop whitebgblueB"> <img
                                                src="<?= $assets ?>pos/images/NSwiggy.svg"
                                                class="icon_image_option1">Swiggy</span></label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/swiggy.png"
                                                    class="icon_image_option1">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->zomato == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox12" title="Zomato" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="zomato">
                                    <label for="checkbox12" class="payment-method"><span
                                            class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NZomato.svg"
                                                class="icon_image_option">zomato
                                        </span></label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/zomato.png"
                                                    class="icon_image_option">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($pos_settings->ubereats == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox13" title="Ubereats" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="ubereats">
                                    <label for="checkbox13" class="payment-method"><span
                                            class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NUberEats.svg"
                                                class="icon_image_option">ubereats</span></label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/ubereats.jpg"
                                                    class="icon_image_option">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->magicpin == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox14" title="Magicpin" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="magicpin">
                                    <label for="checkbox14" class="payment-method"><span
                                            class="paddingTop whitebgblueB"><img
                                                src="<?= $assets ?>pos/images/NMagicPin.svg" class="icon_image_option">
                                            magicpin</span></label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/magicpin.png"
                                                    class="icon_image_option">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->complimentary == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox14" title="Debit Card" type="radio"
                                        class="card custom_payment_icon" name="colorRadio" value="complimentry">
                                    <label for="checkbox14" class="payment-method"><span
                                            class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NComplimentary.svg"
                                                class="icon_image_option2" alt="credit-card">
                                            Complimentry</span></label>
                                    <!-- <span class="payment-icon whitebgblueB">
                                                <img src="<?= $assets ?>pos/images/credit-card.jpg"
                                                    class="icon_image_option2" alt="credit-card">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->paypal_pro == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox15" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="ppp">
                                    <label for="checkbox15" class="payment-method"><span class="paddingTop whitebgredB">
                                            <img src="<?= $assets ?>pos/images/NPaypal.svg" class="icon_image_option"
                                                alt="">PayPal</span></label>
                                    <!-- <span class="payment-icon whitebgredB"><img
                                                    src="<?= $assets ?>pos/images/paypal.jpg"
                                                    class="icon_image_option" alt=""></span> -->
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($pos_settings->stripe == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox16" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="stripe">
                                    <label for="checkbox16" class="payment-method"><span class="paddingTop orangebg">
                                            <img src="<?= $assets ?>pos/images/NStripe.svg" class="icon_image_option1"
                                                alt="">Stripe</span></label>
                                    <!-- <span class="payment-icon orangebg ">
                                                <img src="<?= $assets ?>pos/images/Stripe.png"
                                                    class="icon_image_option1" alt=""></span> -->
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($pos_settings->authorize == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox17" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="authorize">
                                    <label for="checkbox17" class="payment-method"><span class="paddingTop orangebg">
                                            <img src="<?= $assets ?>pos/images/NAuthNet.svg" class="icon_image_option1"
                                                alt="">Authorize</span></label>
                                    <!-- <span class="payment-icon orangebg"><img
                                                    src="<?= $assets ?>pos/images/authorize.png"
                                                    class="icon_image_option1" alt=""></span> -->
                                </diV>
                            </div>
                            <?php endif; ?>
                            <?php if ($pos_settings->instamojo == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox18" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="instamojo">
                                    <label for="checkbox18" class="payment-method"><span class="paddingTop whitebg">
                                            <img src="<?= $assets ?>pos/images/NInstamojo.svg" class="icon_image_option"
                                                alt="">Instamojo</span></label>
                                    <!-- <span class="payment-icon whitebg"><img
                                                    src="<?= $assets ?>pos/images/instamojo.jpg"
                                                    class="icon_image_option" alt="">
                                            </span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->paytm_opt == '1'): ?>
                            <div class=" col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div">
                                    <input id="checkbox20" type="radio" class="card custom_payment_icon"
                                        name="colorRadio" value="PAYTM">
                                    <label for="checkbox20" class="payment-method">
                                        <span class="paddingTop whitebgblueB">
                                            <img src="<?= $assets ?>pos/images/NPaytm.svg" class="icon_image_option"
                                                alt="credit-card">
                                            PAYTM
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->paynear == '1' && !empty($this->pos_settings->paynear_web)): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6" id="paynear_btn_holder">
                                <div class="radio-div">
                                    <input type="radio" class="card custom_payment_icon" name="colorRadio"
                                        id="paynear_btn" value="paynear">
                                    <label for="paynear_btn" class="payment-method"><span
                                            class="paddingTop whitebgredB">
                                            <img src="<?= $assets ?>pos/images/NPayNearMe.svg"
                                                class="icon_image_option1" alt="">Paynear</span></label>
                                    <!-- <span class="payment-icon whitebgredB"><img
                                                    src="<?= $assets ?>pos/images/paynear.png"
                                                    class="icon_image_option1" alt=""></span> -->
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($pos_settings->payumoney == '1'): ?>
                            <div class="col-md-4 col-sm-6 col-xs-6" id="payumoney_btn_holder">
                                <div class="radio-div">
                                    <input type="radio" class="card custom_payment_icon" name="colorRadio"
                                        id="payumoney_btn" value="payumoney">
                                    <label for="payumoney_btn" class="payment-method"><span
                                            class="paddingTop whitebgredB">
                                            <img src="<?= $assets ?>pos/images/NPayUMoney.svg"
                                                class="icon_image_option1" alt="">Payumoney</span></label>
                                    <!-- <span class="payment-icon whitebgredB"><img
                                                    src="<?= $assets ?>pos/images/payu.png"
                                                    class="icon_image_option1" alt=""></span> -->
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>



                        <?php if ($pos_settings->paynear == '1' && !empty($this->pos_settings->paynear_app)): ?>
                        <div class="row card-box" id="paynear_btn_app_holder" style="display:none;">

                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                    <input type="radio" class="card custom_payment_icon" name="colorRadio"
                                        id="paynear_btn1" value="paynear" data-value="1"><label
                                        for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico14.png"
                                                alt=""></span></label>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                    <input type="radio" class="card custom_payment_icon" name="colorRadio"
                                        id="paynear_btn2" value="paynear" data-value="2"><label
                                        for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico15.png"
                                                alt=""></span></label>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-6">
                                <div class="radio-div" data-toggle="tooltip" title="Paynear">
                                    <input type="radio" class="card custom_payment_icon" name="colorRadio"
                                        id="paynear_btn3" value="paynear" data-value="3"><label
                                        for="checkbox1"><span><img src="<?= $assets ?>pos/images/ico16.png"
                                                alt=""></span></label>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="container">
                            <div class="col-lg-12 text-center margilset" id="toggle"
                                style="z-index:1000; margin-left: -3rem; background:white; width: 528%;">

                                <img id="arrow-toggle" src="<?= $assets ?>pos/images/PaymentMethodsArrow.svg"
                                    alt="Payment Methods">

                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <!-- <div class="col-md-1 col-sm-3" id="verticalbtns">
                                <div class="class-title" style="font-weight: bold;"><?= lang('quick_cash'); ?></div>
                                <div class="btn-group btn-group-vertical" >
                                    <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable"><i class="fa fa-inr" aria-hidden="true"></i> 0.00 </button>
                                    <?php
                                    foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                        if ($cash_note_amount != 1000 && $cash_note_amount != 5000) {
                                            echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . '<i class="fa fa-inr" aria-hidden="true"></i>' . ' ' . $cash_note_amount . '</button>';
                                        }
                                    }
                                    ?>
                                    <button type="button" class="btn btn-lg btn-danger" id="clear-cash-notes"><?= lang('clear'); ?></button>
                                </div>
                            </div> -->
                    <div class="col-md-5 col-sm-6">
                        <!-- Denomination Container -->
                        <div id="denomination-container"></div>
                        <div id="amount-section-container" style="display: none;">
                            <div id="selectedAmount" style="display: none;">
                                <strong>Selected Amount:</strong> <span id="selectedValue">0</span>
                            </div>
                            <div>
                                <input type="hidden" id="selectedValues">
                            </div>
                            <div id="pendingAmount" style="display: none;">
                                <strong>Pending Amount:</strong> <span id="pendingValue">0</span>
                            </div>
                            <input type="hidden" id="pendingValues">
                        </div>

                        <!-- Clear Button -->
                        <button id="clearAll" class="clear-btn">
                            Clear All
                        </button>
                    </div>


                    <div class="col-md-4 ">
                        <div class="amount-outer" id="yourDivId" style="display: none;">
                            <div id="amnt" class="ps-container">
                                <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                <div class="form-group" style="margin:15px 0;">
                                    <!--?=lang("biller", "biller");?-->
                                    <?php
                                            foreach ($billers as $biller) {
                                                $bl[$biller->id] = $biller->company != '-' ? $biller->name . '(' . $biller->company . ')' : $biller->name;
                                            }
                                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                            ?>
                                </div>
                                <?php
                                    } else {
                                        $biller_input = array(
                                            'type' => 'hidden',
                                            'name' => 'biller',
                                            'id' => 'posbiller',
                                            'value' => $this->session->userdata('biller_id'),
                                        );
                                        echo form_input($biller_input);
                                    }
                                    ?>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-xs-6">
                                            <?= form_textarea('sale_note', '', 'id="sale_note" class="form-control kb-text skip" style="height: 35px;" placeholder="' . lang('sale_note') . '" maxlength="250"'); ?>
                                        </div>
                                        <div class="col-sm-6 col-xs-6">
                                            <?= form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 35px;" placeholder="' . lang('staff_note') . '" maxlength="250"'); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="clearfir"></div>
                                <div class="card-div" id="payments" style="cursor:pointer">
                                    <div class="well well-sm well_1">
                                        <div class="payment">
                                            <div class="row">
                                                <div class="col-sm-6 col-xs-6">
                                                    <div class="form-group">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount[]" type="text" id="amount_1"
                                                            class="pa form-control kb-pad1 amount paidby_amount"
                                                            onKeyPress="return isNumberKey(event)" autocomplete="off" />
                                                        <button id="edt" class="btn-edt" onClick="enDis('amount_1')"><i
                                                                class="fa fa-pencil" id="addIcon"
                                                                style="font-size: 1.2em;"></i></button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-xs-6">
                                                    <div class="form-group">
                                                        <?= lang("paying_by", "paid_by_1"); ?>
                                                        <select name="paid_by[]" id="paid_by_1"
                                                            class="form-control paid_by">
                                                            <?= $this->sma->paid_opts(); ?>
                                                            <?= '<option value="payswiff">' . lang("Payswiff") . '</option>'; ?>
                                                            <?= $pos_settings->paypal_pro ? '<option value="ppp">' . lang("paypal_pro") . '</option>' : ''; ?>
                                                            <?= $pos_settings->stripe ? '<option value="stripe">' . lang("stripe") . '</option>' : ''; ?>
                                                            <?= $pos_settings->authorize ? '<option value="authorize">' . lang("authorize") . '</option>' : ''; ?>
                                                            <?php echo (isset($pos_settings->instamojo) && $pos_settings->instamojo == '1') ? ' <option value="instamojo">Instamojo</option>' : ''; ?>
                                                            <?php echo (isset($pos_settings->ccavenue) && $pos_settings->ccavenue == '1') ? ' <option value="ccavenue">CCavenue</option>' : ''; ?>
                                                            <?php echo (isset($pos_settings->paytm) && $pos_settings->paytm == '1') ? ' <option value="paytm">Paytm PG</option>' : ''; ?>

                                                            <!--<?php echo (isset($pos_settings->paytm_opt) && $pos_settings->paytm_opt == '1') ? ' <option value="paytm">Paytm</option>' : ''; ?>-->
                                                            <?php echo (isset($pos_settings->paynear) && $pos_settings->paynear == '1') ? ' <option value="paynear">Paynear</option>' : ''; ?>
                                                            <?php echo (isset($pos_settings->payumoney) && $pos_settings->payumoney == '1') ? ' <option value="payumoney">Payumoney</option>' : ''; ?>

                                                            <?php echo (isset($pos_settings->UPI_QRCODE) && $pos_settings->UPI_QRCODE == '1') ? ' <option value="UPI_QRCODE">UPI & QR CODE</option>' : ''; ?>
                                                            <?php echo (isset($pos_settings->award_point) && $pos_settings->award_point == '1') ? ' <option value="award_point">Award Point</option>' : ''; ?>
                                                            <?= '<option value="razorpay">' . lang("Razorpay") . '</option>'; ?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-group gc_1" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no_1"); ?>
                                                        <input name="paying_gift_card_no[]" type="text"
                                                            id="gift_card_no_1"
                                                            class="pa form-control kb-pad gift_card_no" />
                                                        <div id="gc_details_1"></div>
                                                        <div id="errorgift_1"></div>
                                                    </div>
                                                    <!--Show Deposite Balance-->
                                                    <div class="form-group db_1" style="display:none;">
                                                        <?= lang("Deposit Balance"); ?>
                                                        <div id="depositdetails_1"></div>
                                                        <div id="errordeposit_1"></div>
                                                    </div>
                                                    <div class="form-group ap_1" style="display:none;">
                                                        <div id="apdetails_1"></div>
                                                        <div id="errorap_1"></div>
                                                        <input type="hidden" name="ap[]" id="ap_1">
                                                    </div>
                                                    <!----->
                                                    <div class="display pcc_1" style="display:none;">
                                                        <!-- Card Number: <div id="cardNo"></div>-->
                                                        <div id="cardty" style="display: none;"></div>
                                                        <div class="row">
                                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                                <div class="form-group">
                                                                    <input name="cc_transac_no[]" type="text"
                                                                        id="cc_transac_no_1"
                                                                        class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                        placeholder="Transaction No." />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                                <div class="form-group">
                                                                    <input name="cc_payment_other[]" type="text"
                                                                        id="cc_payment_other"
                                                                        class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                        placeholder="Other" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- <div class="form-group">
                                                                    <input type="text" id="swipe_1" class="form-control swipe kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                            placeholder="<?= lang('swipe') ?>"/>
                                                            </div>
                                                            <div class="row">
                                                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                    <input name="cc_no[]" type="text" id="pcc_no_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('cc_no') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                    <input name="cc_holer[]" type="text" id="pcc_holder_1"
                                                                                            class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('cc_holder') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <select name="cc_type[]" id="pcc_type_1"  placeholder="<?= lang('card_type') ?>">
                                                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                                                            <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                                                            <option  value="Discover"><?= lang("Discover"); ?></option>
                                                                                    </select>
                                                                                     <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <input name="cc_month[]" type="text" id="pcc_month_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('month') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <input name="cc_year" type="text" id="pcc_year_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="<?= lang('year') ?>"/>
                                                                            </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-3">
                                                                            <div class="form-group">
                                                                                    <input name="cc_cvv2" type="text" id="pcc_cvv2_1"
                                                                                            class="form-control kb-pad  ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                                            placeholder="cvv"/>
                                                                            </div>
                                                                    </div>
                                                            </div>-->
                                                    </div>
                                                    <div class="display pcheque_1" style="display:none;">
                                                        <div class="form-group">
                                                            <?= lang("cheque_no", "cheque_no_1"); ?>
                                                            <input name="cheque_no[]" type="text" id="cheque_no_1"
                                                                class="form-control cheque_no kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" />
                                                        </div>
                                                    </div>
                                                    <div class="display pother_1" style="display:none;">
                                                        <div class="form-group">
                                                            <input name="other_tran_no" placeholder="Transaction No"
                                                                type="text" id="other_tran_no_1"
                                                                class="form-control cheque_no kb-pad ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" />
                                                        </div>
                                                        <div class="form-group" id="note">
                                                            <input name="other_tran_mode" placeholder="Transaction Mode"
                                                                type="text" id="other_tran_mode_1"
                                                                class="form-control kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                maxlength="55" />
                                                        </div>
                                                    </div>



                                                    <div class="display form-group payment_note">
                                                        <?= lang('payment_note', 'payment_note'); ?>
                                                        <textarea name="payment_note[]" id="payment_note_1"
                                                            class="pa form-control kb-text payment_note"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="multi-payment"></div>
                                <button type="button" class="btn btn-primary col-md-12 addButton"><i
                                        class="fa fa-plus"></i> <?= lang('add_more_payments') ?></button>
                            </div>
                        </div>
                        <!-- Deposited box  -->
                        <div class="payment-box" id="payment-box" style="display: none!important;;">
                            <div class="row">
                                <span style="
                                        margin-left: 0px!important;">Total Payable:</span>

                                <span style="margin-left: 93px!important;" class="amount"> <span
                                        id="totalPayableAmount"></span></span>
                            </div>
                            <div class="row">
                                <button class="btn btn-deposit" id="Collected">Collected:</button>
                                <span style="
    margin-left: 93px!important;" class="amount"> <span id="depositedAmount"></span></span>
                            </div>
                            <div class="row">
                                <button class="btn btn-return" id="returnAmt" disabled>Return Amount:</button>
                                <span style="
    margin-left: 64px!important;" class="amount"> <span id="returnAmount"></span></span>
                            </div>
                            <!-- Return & Complete Button -->
                            <button id="" class="btn btn-primary" style="display: none; margin-top: 10px; margin-left: -13px;background: #009DFF;z-index: 399;width:112%
">
                                Return & Complete
                            </button>
                        </div>
                        <div class="row new-footer" style="
    position: fixed;
    bottom: -165px;
">
                            <div class="btn-group col-sm-12 text-center checkoutmodalbtn">
                                <button class="btn btn-info final-submit-btn disable-on-cash" name="cmd"
                                    id="submit-sale">
                                    <strong>Quick <?= lang('submit'); ?></strong>
                                    <img src="<?= $assets ?>pos/images/submit.png" alt="submit">
                                </button>
                                <button class="btn btn-info final-submit-btn disable-on-cash" name="cmdprint"
                                    id="submit-sale">
                                    <strong><?= lang('submit'); ?> & Print</strong>
                                    <img src="<?= $assets ?>pos/images/print.png" alt="submit">
                                </button>
                                <button class="btn btn-info final-submit-btn disable-on-cash" name="splitpay"
                                    id="splitpay" onclick="split_order_pay()" disabled>
                                    <strong>Split Pay</strong>
                                    <img src="<?= $assets ?>pos/images/split-pay.png" alt="submit">
                                </button>
                                <button class="btn btn-info final-submit-btn disable-on-cash" type="button"
                                    id="split-check" onclick="split_order();" disabled>
                                    <strong>Split Check</strong>
                                    <img src="<?= $assets ?>pos/images/split-check.png" alt="submit">
                                </button>
                                <button class="btn btn-info final-submit-btn disable-on-cash" name="cmdprint1"
                                    id="submit-sale">
                                    <strong>Other</strong>
                                    <img src="<?= $assets ?>pos/images/check.png" alt="submit">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?php } ?>