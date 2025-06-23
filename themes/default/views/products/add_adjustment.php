<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
<?php
$lasturl = explode("/", $_SERVER['HTTP_REFERER']);
$last_segment = count($lasturl) - 2;
if ($lasturl[$last_segment] == 'edit_adjustment') {
    ?>
        localStorage.clear();
<?php } ?>
    var count = 1, an = 1, product_variant = 0, shipping = 0, product_tax = 0, total = 0;
    var type_opt = {'addition': '<?= lang('addition'); ?>', 'subtraction': '<?= lang('subtraction'); ?>'};
    var wp_id = 0;

    localStorage.setItem('baseUrl', '<?= site_url() ?>');

    function getVariantDetails(VarientId, ProductId) {
        //alert(VarientId+' '+ProductId);
        var WarehouseId = $('#qawarehouse').val();
        $.ajax({
            type: 'get',
            url: '<?= site_url('products/get_variant_details') ?>',
            data: {
                VarientId: VarientId,
                ProductId: ProductId,
                WarehouseId: WarehouseId,
            },
            async: false,
            success: function (result) {
                var Res = $.parseJSON(result);

                $.each(Res, function (key, value) {
                    $('#ShowQty_' + ProductId).text(formatDecimal(value.quantity));
                    //console.log(key+' '+value.name);
                });
            }, error: function (result) {

                //console.log(result);
            }

        });
    }
    $(document).ready(function () {
        // Display List 
        var warehouse_name;
        var display_list = $('#display_product').val();
        if (display_list == 'search_product') {
            block_view('show', '#search_product');
            block_view('hide', '#product_list');
            var get_warehouse = $('#qawarehouse').val();
            //product_get_list(get_warehouse);

            warehouse_name = $("#qawarehouse option:selected").text();
            document.getElementById('wp_id').value = get_warehouse;
            $('#title_warehouse').html('<br/>(' + warehouse_name + ')');

        } else {
            block_view('show', '#product_list');
            block_view('hide', '#search_product');
            $('#title_warehouse').html('');
        }

        // warehouse Change
        document.getElementById('wp_id').value = $('#qawarehouse').val();
        $('#qawarehouse').change(function () {
            var get_warehouse = $(this).val();
            product_get_list(get_warehouse);
            warehouse_name = $("#qawarehouse option:selected").text();
            $('#title_warehouse').html('<br/>(' + warehouse_name + ')');
            document.getElementById('wp_id').value = get_warehouse;
            if (localStorage.getItem('qaitems')) {
                localStorage.removeItem('qaitems');
            }
            window.location.href = '<?=base_url('products/add_adjustment/');?>?warehouse='+get_warehouse;
        });
        // End warehouse Change

        $('#display_product').change(function () {
            if ($(this).val() == 'warehouse_product') {
                block_view('hide', '#search_product');
                block_view('show', '#product_list');
                var get_warehouse = $('#qawarehouse').val();
                product_get_list(get_warehouse);
                customPagination(1);
            } else if ($(this).val() == 'search_product') {
                $('.EachProduct').parent('div').removeClass('checked');
                $('.EachProduct').prop('checked', false);
                block_view('show', '#search_product');
                block_view('hide', '#product_list');
                var node = document.getElementById("qaTable2");
                while (node.hasChildNodes()) {
                    node.removeChild(node.lastChild);
                }
                $('.Product_Tag_Box').html('');
            }
        });


        // End Display List  


        if (localStorage.getItem('remove_qals')) {
            if (localStorage.getItem('qaitems')) {
                localStorage.removeItem('qaitems');
            }
            if (localStorage.getItem('qaref')) {
                localStorage.removeItem('qaref');
            }
            if (localStorage.getItem('qawarehouse')) {
                localStorage.removeItem('qawarehouse');
            }
            if (localStorage.getItem('qanote')) {
                localStorage.removeItem('qanote');
            }
            if (localStorage.getItem('qadate')) {
                localStorage.removeItem('qadate');
            }
            localStorage.removeItem('remove_qals');
        }

<?php if ($adjustment_items) { ?>
            localStorage.setItem('qaitems', JSON.stringify(<?= $adjustment_items; ?>));
<?php } ?>
<?php if ($warehouse_id) { ?>
            localStorage.setItem('qawarehouse', '<?= $warehouse_id; ?>');
            $('#qawarehouse').select2('readonly', true);
<?php } ?>

<?php if ($Owner || $Admin) { ?>
            if (!localStorage.getItem('qadate')) {
                $("#qadate").datetimepicker({
                    format: site.dateFormats.js_ldate,
                    fontAwesome: true,
                    language: 'sma',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    forceParse: 0
                }).datetimepicker('update', new Date());
            }
            $(document).on('change', '#qadate', function (e) {
                localStorage.setItem('qadate', $(this).val());
            });
            if (qadate = localStorage.getItem('qadate')) {
                $('#qadate').val(qadate);
            }
<?php } ?>
        
$("#add_item").autocomplete({
            // source: '<?= site_url('products/qa_suggestions'); ?>',
            source: function (request, response) {
                if (request.term.length >= 3) {
                    $.ajax({
                        type: 'get',
                        url: '<?= site_url('products/qa_suggestions'); ?>',
                        dataType: "json",
                        data: {
                        term: request.term,
                        warehouse_id: $("#wp_id").val(),
                       
                    },
                    success: function (data) {
                        response(data);
                    }
                });
                }
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                var inputValue = $(this).val();
                   inputval = inputValue.includes('-');
                     var formatPattern = /^\d{3}-\d+(\.\d+)?$/; // Regular expression for productcode-quantity format
                     var productEntries = inputValue.split(','); // Split by comma to get individual product entries
                  
                     var validEntries = [];

                     if (inputValue.includes('-')) {
                            // Validate each product entry
                            productEntries.forEach(function(entry) {
                                entry = entry.trim(); // Remove any extra spaces
                                if (formatPattern.test(entry)) {
                                    validEntries.push(entry);
                                }
                            });
                            if (validEntries.length > 1) {
                                var items = ui.content;
                                if (items.length > 0) {
                                    items.forEach(function(item) {
                                        ui.item = item;
                                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                                        $(this).autocomplete('close');
                                    }.bind(this)); // Use .bind(this) to maintain the correct 'this' context inside the loop
                                } else {
                                    // If no valid item found, show no match alert
                                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                                        $('#add_item').focus();
                                    });
                                    $(this).val('');
                                }
                            }
     
                        }
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {

                event.preventDefault();
                if (ui.item.options) {
                    product_option_model_call(ui.item);
                    $(this).val('');
                    return true;
                }
                if (ui.item.id !== 0) {
                    var row = add_adjustment_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        //});
        
        // Block List Function
        function block_view() {

            switch (arguments[0]) {
                case 'show':
                    $(arguments[1]).show();
                    break;

                case 'hide':
                    $(arguments[1]).hide();
                    break;
            }
        }
        function addcomboProducts(option_id, option_name = null, unitcost = null) {
    if (!Array.isArray(option_id.combo_items) || option_id.combo_items.length === 0) {
        return;  
    }
    option_id.combo_items.forEach(function(item) {
        var productid = item.product_id;
            option_id = item.code;
        // var  product_Id = option_name;
        var Product_color = $('#Product_color').val();
        $('.color-form-group').removeClass('has-error')
        if (Product_color == '0') {
            $('.color-form-group').addClass('has-error')
            return false;
        }
        // var itemId = $(".modalvarient").find('.product_item_id').attr("value")
        //var option_id = $(".modalvarient").find('.option_id').val();
        var term = $("#add_item").val() + "<?php echo $this->Settings->barcode_separator; ?>" +
            option_id + "<?php echo $this->Settings->barcode_separator; ?>" + Product_color;
        wh = $('#c').val(),
        $.ajax({
            type: "get",
            url: "<?= site_url('products/qa_suggestions') ?>",
            data: {
                term: term,
                bundel_id: option_id,
                Product_color: Product_color,
                warehouse_id: wh,
                product_Id: productid,
            },
            dataType: "json",
            success: function(data) {
                if (data !== null) {
                    add_invoice_item(data[0]);
                    $('.modalvarient').hide();
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                    $('.modalvarient').hide();
                }
            }
        });
    });
}


/** Modal Variant **/
function product_option_model_call(product) {
    var product_options =
        '<table class="table table-striped table-border"><thead><tr><th>Variant Name</th><th>Quantity</th><th>Net Price</th><th>Subtotal (INR)</th></tr></thead><tbody>';
        // if (Array.isArray(product.options)) {
        //     product.options.sort(function(a, b) {

        //         function parseNumericParts(value) {
        //             return value.split('/').map(part => parseFloat(part) || 0);
        //         }

        //         function compareNumericArrays(arr1, arr2) {
        //             const length = Math.min(arr1.length, arr2.length);
        //             for (let i = 0; i < length; i++) {
        //                 if (arr1[i] < arr2[i]) return -1;
        //                 if (arr1[i] > arr2[i]) return 1;
        //             }
        //             return arr1.length - arr2.length;
        //         }
        //         if (a.name && b.name) {
        //             const aParts = parseNumericParts(a.name);
        //             const bParts = parseNumericParts(b.name);

        //             return compareNumericArrays(aParts, bParts);
        //         } else if (a.name || b.name) {
        //             return a.name ? -1 : 1;
        //         } else {
        //             return (a.value || 0) - (b.value || 0);
        //         }
        //     });
        // }

    $.each(product.options, function(index, element) {
        var cost = element.price;
        var formattedCost = parseFloat(cost).toFixed(2);
        var unitcost = getUnitCost();
        var netCost = CalculateCost(product, unitcost, product);
        var variantRow;
        if (element.name.toLowerCase() == 'note') {
            variantRow = '<tr><td colspan="2" class="text-center">' +
                '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' + element.name +
                '\')">' +
                '<i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i> Note</button>' +
                '</td></tr>';
        } else {
            variantRow = '<tr>' +
                '<td class="w-25">' + element.name + '</td>' +
                '<td class="w25-center"><input type="number" min="0" value="0" ' +
                'class="form-control input-sm quantity-input quantity_input width-setting" data-variant-id="' +
                element.id + '" data-netcost="' + cost + '" data-initial-value="0" ></td>' +
                // '<td class="w25-right net-cost">' + formatMoney() + '</td>' + //existing
                '<td class="w25-right net-cost">' + '<input type="number" value="' + (formattedCost) + '" ' +
                'class="form-control input-sm net-cost-input net_cost_input width-setting" ' +
                'data-variant-id="' + element.id + '" ' + 'data-netcost="' + cost + '" ' +
                'data-initial-value="0">' + '</td>' +
                '<td class="w25-right"><input type="text" min="0" value="' + formatMoney() +
                '" class="form-control input-sm subtotal subtotal width-setting" data-variant-id="' + element
                .id + '" id="subtotals"></td>' +
                // '<td class="w25-right"><input type="text" min="0" value="' + formatMoney() + ' " ' +'class="form-control subtotal  width-setting" id= "subtotals"> </td>' + // existing
                // '<td class="w25-right net-cost">' + formatMoney() + '</td>' +
                '</tr>';
        }
        product_options += variantRow;
        //console.log(product_options)
    });
    //console.log(variantRow);
    product_options += '</tbody></table>';
    product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.row
        .id + "' >";
    product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.row.code +
        "' >";

    // Update modal content
    $('.modalvarient').find('.modal-title').html(product.row.name);
    $('.modalvarient').find('.modal-body').empty();
    $('.modalvarient').find('.modal-body').append(product_options);
    $('.modalvarient').show();

    return true;
}
function getUnitCost(rowNo) {
    return $('#unit_price_' + rowNo).val();
}
function CalculateCost(product, optionCost,product) {
    var productCost = product.row.cost;
    productCost = productCost == null ? 0 : productCost;
    optionCost = optionCost == null ? 0 : optionCost;
    var netCost = (parseFloat(optionCost) + parseFloat(productCost));
    return netCost;
}
$(document).on('change', '.quantity_input', function() {
    var quantity = parseFloat($(this).val());
    var variantId = $(this).data('variant-id');
    var netCosts = parseFloat($(this).closest('tr').find('.net-cost-input').val()); 
    var netCost = quantity * netCosts;  
    $(this).closest('tr').find('.subtotal').val(formatMoney(netCost));
    $(this).closest('tr').find('.net-cost').val(formatMoney(netCosts));
});

$(document).on('change', '.net-cost-input', function() {
    var quantity = parseFloat($(this).closest('tr').find('.quantity-input').val()); 
    var netCost = parseFloat($(this).val());
    var variantId = $(this).data('variant-id'); 
    var subtotal = quantity * netCost;
    $(this).closest('tr').find('.subtotal').val(formatMoney(subtotal));
  
});

$(document).on('change', '.subtotal', function() {
    var row = $(this).closest('tr');  
    var quantity = parseFloat(row.find('.quantity-input').val());  
    var subtotalValue = $(this).val(); 
    var subtotal = parseFloat(subtotalValue.replace(/[^0-9]/g, ''));  
    var netCost = parseFloat(subtotal) / (quantity);
    $(this).closest('tr').find('.net-cost-input').val(netCost.toFixed(2));
    $(this).closest('tr').find('.subtotal').val(formatMoney(subtotal.toFixed(2)));
});
$('#submitBtn').on('click', function() {

$('.quantity-input').each(function(index) {
    setTimeout(function() {
    var $this = $(this);
    var $row = $this.closest('tr');
    var variantId = $this.data('variant-id');
    var initialValue = $this.data('initial-value');
    var currentValue = $this.val();
    var netCost = $this.closest('tr').find('.net-cost').text();
    var updatedCost = netCost.replace("Rs.", "").replace(/,/g, "");
    var inputValue = $this.closest('tr').find('.subtotal').val(); 
    var subtotal = inputValue.replace("Rs.", "").replace(/,/g, "");
    var  unitcost = parseFloat(subtotal)/parseFloat(currentValue);
        unitcost = formatDecimal(unitcost);
    if (currentValue !== '0') {
        // var variantId = $this.data('variant-id');
        var quantity = currentValue;
        addProductToVarientProduct(variantId, quantity,unitcost);
        $this.data('initial-value', currentValue);
    }
}.bind(this), index * 100);  
});
$('.modalvarient').hide();
});

function addProductToVarientProduct(option_id, option_name,unitcost) {
    var Product_color = $('#Product_color').val();
    $('.color-form-group').removeClass('has-error')
    if (Product_color == '0') {
        $('.color-form-group').addClass('has-error')
        return false;
    }
    var note = '';
    if (option_name.toLowerCase() == 'note') {

        note = prompt("Please enter your note");
        if (note == null) {
            return false;
        }
    }
    var quantityInput = $('.modalvarient').find(`input[data-variant-id="${option_id}"]`);
    var quantity = quantityInput.length > 0 ? quantityInput.val() : 1; // Default to 1 if not found
    var itemId = $(".modalvarient").find('.product_item_id').attr("value")
    var term = $(".modalvarient").find('.product_term').val() + "<?php echo $this->Settings->barcode_separator; ?>" +
        option_id + "<?php echo $this->Settings->barcode_separator; ?>" + Product_color;

    wh = $('#wp_id').val(),
        cu = $('#posupplier').val();
    $.ajax({
        type: "get",
        url: "<?= site_url('products/qa_suggestions') ?>",
        data: {
            term: term,
            option_id: option_id,
            Product_color: Product_color,
            warehouse_id: wh,
            customer_id: cu,
            option_note: note,  
            quantity: quantity,
            subtotal: unitcost,
        },
        dataType: "json",
        success: function(data) {
            if (data !== null) {
                add_adjustment_item(data[0]);
                $('.modalvarient').hide();
            } else {
                bootbox.alert('<?= lang('no_match_found') ?>');
                $('.modalvarient').hide();
            }
        }
    });
}
        function resetProductWarehouse() {
            $('#search_product_warehouse').val('');
            $('.custom_pagination').show();
            customPagination(1);
        }
        function searchProductWarehouse() {
            var search_product_warehouse = $('#search_product_warehouse').val();
            if (search_product_warehouse.length != '') {
                $('.sr_tr_row').hide();
                $('.custom_pagination').hide();
                $('.sr_tr_row').find("input[value*='" + search_product_warehouse + "']").parent().parent().show();
            } else {
                $('.custom_pagination').show();
                customPagination(1);
            }
        }
        function product_get_list() {
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                url: '<?= site_url('products/product_list/') ?>' + arguments[0],
                async: false,
                success: function (result) {

                    var htmlset = '<table id="qaTable2" class="table items table-striped table-bordered table-condensed table-hover dataTable">';
                    htmlset += '<thead>';
                    htmlset += '<tr>';
                    htmlset += '<th style="min-width:30px; width: 30px; text-align: center;">';
                    //  htmlset+='<span class="select_all"><input class="checkbox checkth input-xs" type="checkbox" name="check" id="select_all"/></span>';/*checkth*/
                    htmlset += '</th>';
                    htmlset += '<th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>';
//                        htmlset+='<th><?= lang("Batch Number") ?></th>';
                    htmlset += '<th><?= lang('Stock'); ?> <span id="title_warehouse" style="text-transform: uppercase;"></span></th>';
                    htmlset += '<th><?= lang("variant"); ?></th>';
                    htmlset += '<th><?= lang("type"); ?></th>';
                    htmlset += '<th><?= lang("quantity"); ?></th>';
<?php if ($Settings->product_serial) { ?>
                        htmlset += '<th><?= lang("serial_no"); ?></th>';
<?php } ?>
                    htmlset += '</tr>';
                    htmlset += '</thead>';
                    htmlset += '<tbody>';
                    if (result != '') {
                        //console.log(result);
                        var i = 0, k = 0;
                        var SR = 1;
                        for (i = 0; i < result.length; i++) {
                            var pass_variant, quantity;
                            var product_tax = '0';
                            var item_cost = 0;
                            var unit_cost = result[i].item.cost;
                            var pr_tax = result[i].item.tax_rate;
                            var pr_tax_val = 0, pr_tax_rate = 0;
                            //console.log(result[i].variant);
                            if (Object.keys(result[i].variant).length != 0) {
                                pass_variant = '<select name="variant[]"  id="poption_' + result[i].item.product_id + '" disabled="true" class="form-control select  input-xs" onchange="return getVariantDetails(this.value, ' + result[i].item.product_id + ');">';

                                for (k = 0; k < Object.keys(result[i].variant).length; k++) {
                                    pass_variant += '<option value="' + result[i].variant[k].id + '" >' + result[i].variant[k].name + '</option>';
                                    quantity = result[i].variant[0].quantity;
                                }
                                pass_variant += '</select>'
                            } else {
                                quantity = result[i].item.quantity
                                pass_variant = 'N/A';
                            }
                            // console.log(+result[i].item.product_id+);
                            htmlset += '<tr id="row_' + result[i].item.product_id + '"   class="row_' + result[i].item.product_id + ' sr_tr_row sr_tr_' + SR + '">';
                            htmlset += '<td ><input class="checkbox multi-select input-xs EachProduct" type="checkbox" onclick="myfunction(' + result[i].item.product_id + ')" value="' + result[i].item.product_id + '" name="check" id="check_box_' + result[i].item.product_id + '" /></td>';

                            htmlset += '<td ><label  for="check_box_' + result[i].item.product_id + '" style="font-weight: normal !important;">' + result[i].item.name + '(' + result[i].item.code + ') <input name="product_id[]" disabled="true" id="product_id_' + result[i].item.product_id + '"  type="hidden" class="rid allcheck" value="' + result[i].item.product_id + '"></label></td>';

                            htmlset += '<input name="product_name[]" type="hidden" disabled="ture" id="product_name_' + result[i].item.product_id + '" class="rname allcheck" value="' + result[i].item.name + '">';

                            htmlset += '<td class="text-center" id="ShowQty_' + result[i].item.product_id + '">' + formatDecimal(quantity) + '</td>';

                            htmlset += '<td>' + pass_variant + '</td>';

                            htmlset += '<td><select name="type[]"  disabled="true" id="type_' + result[i].item.product_id + '" class="form-control select allcheck "><option value=""> Select </option><option value="subtraction" selected>Subtraction</option><option value="addition" >Addition</option></select> </td>';

                            htmlset += '<td><input  style="width:80px;" class="form-control text-center rquantity  allcheck " disabled="true"  tabindex="2" name="quantity[]" type="number" value="0" data-id="' + result[i].item.product_id + '" data-item="31" id="quantity_' + result[i].item.product_id + '" onclick="this.select();"></td>';

<?php if ($Settings->product_serial) { ?>
                                htmlset += '<td><input style="width:120px;" class="form-control input-sm rserial allcheck" id="serial_' + result[i].item.product_id + '"" name="serial[]"  disabled="false" type="text"  value=""></td>';
<?php } ?>
                            htmlset += '</tr>';
                            SR++;
                        }
                        customPagination(1);
                    } else {
                        htmlset += '<tr>';
                        htmlset += '<td colspan="7" class="text-center"> Product Not Found</td>';
                        htmlset += '</tr>';
                    }
                    htmlset += '</tbody>';
                    htmlset += '</table>';
                    $('#show_data ').html(htmlset);
                    // $('.qaTable2').DataTable({
                    //     "destroy": true,
                    // });
                    // $('#qaTable2 tbody').html(htmlset);
//                    $('#qaTable2').DataTable({ 
//                      "destroy": true, //use for reinitialize datatable
//                   });
                }, error: function () {
                    console.log('error');
                }

            });
        }
    });
    
    function showRecords(ActivePage) {
        if (ActivePage == 'Next') {
            var LiNext = $('#LiNext').prev().find('a').text();
            var PageCounter = parseInt(LiNext) + parseInt(1);

            customPagination(PageCounter);
        } else if (ActivePage == 'Previous') {
            var LiPrevious = $('#LiPrevious').next().find('a').text();
            var PageCounter = LiPrevious;
            if (LiPrevious > 1)
                PageCounter = parseInt(LiPrevious) - parseInt(10);
            customPagination(PageCounter);

        } else {
            $('.EachLi').removeClass('active');
            $('.sr_tr_row').hide();
            var EndLimit = ActivePage * 10;
            var StartLimit = EndLimit - 9;
            for (StartLimit; StartLimit <= EndLimit; StartLimit++) {
                //console.log('StartLimit '+StartLimit);
                $('.sr_tr_' + StartLimit).show();
            }
            $('.Li_' + ActivePage).addClass('active');
            $('#PageActive').val(ActivePage);
        }

    }

    function customPagination(PageCounter) {
        //alert(PageCounter);
        var TrCount = $('#qaTable2').find('tbody').find('.sr_tr_row').length;
        console.log(TrCount);
        var CalTrPage = TrCount / 10;
        var EndPageCounter = parseInt(PageCounter) + parseInt(10);
        if (Number.isInteger(CalTrPage)) {
        } else {
            CalTrPage = parseInt(parseInt(CalTrPage)) + parseInt(1);
        }
        //alert(PageCounter+' bb '+CalTrPage);
        if (parseInt(PageCounter) < parseInt(CalTrPage)) {
            $('.EachLi').removeClass('active');
            $('.sr_tr_row').hide();
            var LiHtml = '';
            if (CalTrPage > 10) {
                var NextLi = "'Next'";
                var PreviousLi = "'Previous'";
                LiHtml += '<li id="LiPrevious"><a href="javascript:void(0);" class="LiPrevious" onclick="return showRecords(' + PreviousLi + ')">Previous</a></li>';
            }

            for (var iLi = PageCounter; iLi <= CalTrPage; iLi++) {
                if (iLi < EndPageCounter)
                    LiHtml += '<li class="CustomLi"><a href="javascript:void(0);" class="EachLi Li_' + iLi + '" onclick="return showRecords(' + iLi + ')">' + iLi + '</a></li>';
                else
                    break;
            }
            if (CalTrPage > 10) {
                LiHtml += '<li id="LiNext"><a href="javascript:void(0);" class="LiNext" onclick="return showRecords(' + NextLi + ')">Next</a></li>';
            }
            //console.log(LiHtml);
            $('.custom_pagination').html(LiHtml);
            var ActivePage = $('#PageActive').val();
            showRecords(PageCounter);
        }
    }
    function myfunction(get) {
        if ($('#check_box_' + get).prop("checked") == true) {

            // $('#product_id_'+get).attr('disabled', false);
            // $('#poption_'+get).attr('disabled', false);
            // $('#quantity_'+get).attr('disabled', false);
            // $('#serial_'+get).attr('disabled', false);
            // $('#type_'+get).attr('disabled', false);
            boxdisabled('FALSE', '#product_id_' + get);
            boxdisabled('FALSE', '#poption_' + get);
            boxdisabled('FALSE', '#product_id_' + get);
            boxdisabled('FALSE', '#quantity_' + get);
            boxdisabled('FALSE', '#serial_' + get);
            boxdisabled('FALSE', '#type_' + get);
            boxdisabled('FALSE', '#product_name_' + get);

        } else {
            // $('#poption_'+get).attr('disabled', true);
            // $('#product_id_'+get).attr('disabled', true);
            // $('#quantity_'+get).attr('disabled', true);
            // $('#serial_'+get).attr('disabled', true);
            //  $('#type_'+get).attr('disabled', true);
            boxdisabled('TRUE', '#poption_' + get);
            boxdisabled('TRUE', '#product_id_' + get);
            boxdisabled('TRUE', '#quantity_' + get);
            boxdisabled('TRUE', '#serial_' + get);
            boxdisabled('TRUE', '#type_' + get);
            boxdisabled('TRUE', '#product_name_' + get);
        }
        selectProductWarehouse();
    }

    function removeProduct(Id) {
        $('.ProductTag_' + Id).remove();
        $('#check_box_' + Id).parent('div').removeClass('checked');
        $('#check_box_' + Id).prop('checked', false);
        myfunction(Id);
    }
    function boxdisabled(section, sectionid) {
        switch (section) {
            case 'TRUE':
                $(sectionid).attr('disabled', true);
                break;

            case 'FALSE':
                $(sectionid).attr('disabled', false);
                break;
        }

    }

    function selectProductWarehouse() {
        // Totals calculations after item addition
        var count = 1;
        var an = 1;
        var total = 0;
        var product_tax = 0;
        $('.Product_Tag_Box').html('');
        $('.EachProduct').each(function () {
            var Id = $(this).attr('id');
            //console.log(Id);
            if ($('#' + Id).is(':checked')) {
                //console.log(Id);
                var SplitId = Id.split('_');
                var MainId = SplitId[2];
                var product_name = $('#product_name_' + MainId).val();
                an++;
                //product_tax += pr_tax_val * item_qty;
                $('.Product_Tag_Box').append('<span class="Product_Tag ProductTag_' + MainId + '">' + product_name + ' <a href="javascript:void(0);" onclick="return removeProduct(' + MainId + ');">X</a></span>');
            }

        });

        // var gtotal = total + shipping;
        // $('#total').text(formatMoney(total));
        // $('#titems').text((an-1)+' ('+(parseFloat(count)-1)+')');
        // if (site.settings.tax1) {
        //     $('#ttax1').text(formatMoney(product_tax));
        // }
        // $('#gtotal').text(formatMoney(gtotal));
        // if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
        //     $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
        //     $(window).scrollTop($(window).scrollTop() + 1);
        // }
    }


</script>
<style>
    .select {
        width: 100% !important;
    }
    .modal-body {
    max-height: 70vh;
    overflow-y: auto;
    }

    .modal-body table td {
    text-align: center;
    }
    .width-setting {
    text-align: right;
    /* width: 60%; */
    border-radius: 0.3rem !important;
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_adjustment'); ?></h2>
    </div>
    <p class="introtext"><?php echo lang('enter_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/add_adjustment", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("date", "qadate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="qadate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("reference_no", "qaref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="qaref"'); ?>
                            </div>
                        </div>
                        <?= form_hidden('count_id', $count_id); ?>
                        <input type="hidden" id="wp_id" value="<?=(isset($_REQUEST['warehouse']) ? $_REQUEST['warehouse'] : ($warehouse_id ? $warehouse_id : $Settings->default_warehouse))?>" />
                        <?php //if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {  ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("warehouse", "qawarehouse"); ?>
                                <?php
                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    if ($Owner || $Admin) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    } else if (in_array($warehouse->id, $permisions_werehouse)) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_REQUEST['warehouse']) ? $_REQUEST['warehouse'] : ($warehouse_id ? $warehouse_id : $Settings->default_warehouse)), 'id="qawarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" ' . ($warehouse_id ? 'readonly' : '') . ' style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <?php /* } else {
                          $warehouse_input = array(
                          'type' => 'hidden',
                          'name' => 'warehouse',
                          'id' => 'qawarehouse',
                          'value' => $this->session->userdata('warehouse_id'),
                          );

                          echo form_input($warehouse_input);
                          } */ ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("Display Product", "display_product") ?>
                                <?php
                                $list_product = array('search_product' => 'Search Product', 'warehouse_product' => 'Warehouse Product');

                                echo form_dropdown('product_list', $list_product, $list_product['warehouse_product'], 'id="display_product" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" ' . ($warehouse_id ? 'readonly' : '') . ' style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div id="product_list">
                            <!--<h5 class='text-center' id="title_warehouse" style="text-transform: uppercase;font-weight: bold;"></h5>-->
                            <div class="col-md-12">
                                <label class="table-label"><?= lang("products"); ?> *</label>
                                <div class="controls table-controls" id="show_data">

                                </div>
                                <div class="pagination-box">
                                    <ul class="pagination custom_pagination"></ul>
                                </div>
                                <div class="Product_Tag_Box">
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div id="search_product">
                            <div class="col-md-12" id="sticker">
                                <div class="well well-sm">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <div class="input-group wide-tip">
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="control-group table-group">
                                    <label class="table-label"><?= lang("products"); ?> *</label>

                                    <div class="controls table-controls">
                                        <table id="qaTable" class="table items table-striped table-bordered table-condensed table-hover">
                                            <thead>
                                                <tr>
                                                   
                                                    <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>                                                        
                                                    <th class="col-md-2"><?= lang("variant"); ?></th>
                                                    <?php if ($Settings->product_serial) { ?>
                                                        <th class="col-md-1"><?= lang("serial_no") ?></th>
                                                    <?php } ?>
                                                    <?php if ($this->Settings->product_batch_setting) { ?>
                                                        <th class="col-md-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= lang("Batches"); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                        <th class="col-md-1"><?= lang("Batch Stock"); ?></th>
                                                    <?php } ?>
                                                    <th class="col-md-1"><?= lang("Item Stock"); ?></th>
                                                    <th class="col-md-1"><?= lang("Unit Cost"); ?></th>
                                                    <?php if($Admin || $Owner || $GP['Allow_Stock_Addition'] == 1 || $GP['Allow_Stock_Subtraction']==1){ ?>
                                                        <th class="col-md-1"><?= lang("type"); ?></th>
                                                    <?php } ?>
                                                    <th class="col-md-1"><?= lang("Unit Quantity"); ?></th>

                                                    <th style="max-width: 30px !important; text-align: center;">
                                                        <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                    <!--<div class="Product_Tag_Box">
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang("note", "qanote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qanote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_adjustment', lang("submit"), 'id="add_adjustment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <!--  <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                     <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                         <tr class="warning">
                             <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                             <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                             <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                             <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span>
                             </td>
                         </tr>
                     </table>
                 </div> -->
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
<div class="modal  modalvarient" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="modalClose('modalvarient')" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button id="submitBtn" class="btn btn-primary">Submit</button>
                <button type="button" onclick="modalClose('modalvarient')" class="btn btn-default"
                    data-toggle="modal">Close</button>
                <!--button type="button" class="btn btn-primary" onclick="addProductToVarientProduct('modalvarient')">Save changes</button -->
                </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<script>
    var Allow_Stock_Addition = <?= json_encode($GP['Allow_Stock_Addition']); ?>;
    var Allow_Stock_Subtraction = <?= json_encode($GP['Allow_Stock_Subtraction']); ?>;
    var owner = <?= json_encode($Owner); ?>;
    var admin = <?= json_encode($Admin); ?>;
</script>
<script>
    $(document).ready(function () {
        /* $('#qaTable2').DataTable({
         "destroy": true,
         });*/

        });
    $(document).on('ifChecked', '.checkth, .checkft', function (event) {
        $('.checkth, .checkft').iCheck('check');
        $('.multi-select').each(function () {
            //alert('hdjh');
            boxdisabled('FALSE', '.allcheck');
        });
    });
    $(document).on('ifUnchecked', '.checkth, .checkft', function (event) {
        $('.checkth, .checkft').iCheck('uncheck');
        $('.multi-select').each(function () {
            boxdisabled('TRUE', '.allcheck');

        });
    });

    $(document).on('ifChecked', '.multi-select', function (event) {
        myfunction($(this).attr('value'));
    });

    $(document).on('ifUnchecked', '.multi-select', function (event) {
        myfunction($(this).attr('value'));
    });

    function boxdisabled(section, sectionid) {
        switch (section) {
            case 'TRUE':
                $(sectionid).attr('disabled', true);
                break;

            case 'FALSE':
                $(sectionid).attr('disabled', false);
                break;
        }

    }
    function modalClose(modalClass) {
    $('.' + modalClass).hide();
}

</script>