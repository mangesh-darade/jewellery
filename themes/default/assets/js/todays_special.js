//////////////////////////////////////////////////////// REQUEST DATE FUNCTION ///////////////////////////////////////////////////
$(function () {
    $("#requestDeliveryDate").datepicker({
        dateFormat: "MM dd, yy", // Set the date format to "Month Day, Year"
        showOn: "focus", // Only show datepicker on input focus
        onSelect: function (dateText, inst) {
            var selectedDate = $(this).datepicker('getDate');
            var currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0); // Set hours to 0 for accurate comparison
            if (selectedDate < currentDate) {
                $(this).datepicker('setDate', currentDate);
                alert("Please select a date that is today or later.");
            }
        }
    });

    $("#requestDeliveryDate").datepicker("setDate", new Date());
    $(".input-group-addon").on('click', function () {
        $("#requestDeliveryDate").focus(); // Focus on the input to show datepicker
    });
});
$(document).ready(function () {
    $('.categories').show();
    $('#reset').click(function () {
        localStorage.removeItem('selectedHorizonatlTab');
        localStorage.removeItem('currentOrderItems');
        localStorage.removeItem('productId');
        localStorage.removeItem('add_note');


        if (confirm("Are you sure you want to remove the Items?")) {
            window.location.reload(true);
        } else {

        }
    });
    function showCategories() {
        $('.categoriesList').empty();
        $('.subcategoriesList').empty();
        $('.productList').empty();

        $.each(categories, function (index, category) {
            // Append categories with the necessary class and hover-fill span
            $('.categoriesList').append('<li class="btn btn-sty category last-category" data-category-id="' + category.category_id + '">' + category.category_name + '<span class="hover-fill"></span></li>');

            // Set initial background for the last category
            $('.categoriesList li:last-child').css({
                'background': 'linear-gradient(to right, #fff 96%, #008E80 4%)',
                'position': 'relative', // Required for absolute positioning of the hover-fill
                'padding': '10px 20px', // Padding for better click area
                'margin': '5px 0', // Margin for spacing
                'color': '#000', // Default text color
                'font-weight': 'bold', // Make text bold for visibility
                'overflow': 'hidden', // Prevent overflow of the pseudo-element
                'z-index': '1', // Behind the text
                'font-weight': '300',
            });

            // Style for the hover-fill span
            $('.categoriesList li:last-child .hover-fill').css({
                'content': "''",
                'position': 'absolute',
                'top': '0',
                'left': '100%', // Start off to the right
                'width': '100%',
                'height': '100%',
                'background': '#008E80', // Background color
                'transition': 'transform 0.4s ease-in-out', // Transition for the slide effect
                'z-index': '-1', // Behind the text
            });

            // Append subcategories
            $.each(category.subcategories, function (index, subcategory) {
                $('.subcategoriesList').append('<li class="btn btn-sty subcategory" style="display:none;" data-category-id="' + category.category_id + '">' + subcategory.subcategory_name + '</li>');
            });
        });

        // Apply hover effect using jQuery
        $('.categoriesList').on('mouseenter', '.last-category', function () {
            $(this).find('.hover-fill').css({
                'transform': 'translateX(-100%)', // Slide fill to left on hover
            });
            $(this).css('color', '#fff'); // Change text color on hover
        }).on('mouseleave', '.last-category', function () {
            $(this).find('.hover-fill').css({
                'transform': 'translateX(100%)', // Reset fill position to right on mouse leave
            });
            $(this).css('color', '#000'); // Reset text color
        });
    }
    showCategories();
    $('.hand-o-left').click(function () {
        showCategories();
        $('.subcategoriesList').hide();  // Hide subcategories list
        $('.categoriesList').show();
        $('.subCatName').empty();
        $('.recent').hide();
        // Show categories list
    });
    $('.recent').hide();
    $(document).on('click', '.category', function () {
        $('.categoriesList').hide();
        $('.subcategoriesList').show();
        $('.recent').show();
        categoryName = $(this).text();
        var CategoryID = $(this).data('category-id');
        var categoryName = $(this).text();
        $('.catName').text(categoryName);
        var $subcategories = $('.subcategory[data-category-id="' + CategoryID + '"]');
        if ($subcategories.length > 0) {
            $subcategories.show();
        } else {
            var categoryName = $(this).text();
            var CategoryID = $(this).data('category-id');
            getProducts(CategoryID);
        }
    });
    $(document).on('click', '.subcategory', function () {
        $('.recent').show();
        var SubCategoryID = $(this).data('category-id');
        var subcategoryName = $(this).text();
        $('.subCatName').text(subcategoryName);
        getProducts(SubCategoryID);
    });
    function getProducts(CategoryID, SubCategoryID) {
        $.ajax({
            url: site.base_url + "Todays_Special/GetProductsbyCategoriesID",
            method: 'GET',
            data: {
                subcategoryId: SubCategoryID,
                categoriesId: CategoryID,
            },
            dataType: 'json',
            success: function (response) {
                response.forEach(function (subCategorie) {
                    var productDetails = subCategorie.productsByCat;
                    getProductsbyCategories(productDetails);

                });
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
    function getProductsbyCategories(requests) {
        requests.forEach(function (product) {
            $('.subcategoriesList').hide();
            if (product) {
                var productItem = $('<li class="btn btn-sty ItemsDetails" data-product-id="' + product.id + '">' + product.name + '<span class="hover-fill"></span></li>');
                $('#procurement_order').on('mouseenter', '.ItemsDetails', function () {
                    $(this).find('.hover-fill').css({
                        'transform': 'translateX(-100%)' // Slide fill to the left
                    });
                }).on('mouseleave', '.ItemsDetails', function () {
                    $(this).find('.hover-fill').css({
                        'transform': 'translateX(100%)' // Reset fill position to the right
                    });
                });

                // $('.productList li:last-child').css('background-color', '#CCCCCC'); 
                $('.productList').append(productItem);
                productItem.click(function () {

                    localStorage.setItem('requestsData', JSON.stringify(requests));
                    var storedRequests = JSON.parse(localStorage.getItem('requestsData'));
                    console.log("Data stored in local storage:", storedRequests);
                    var productId = $(this).data('product-id').toString();
                    console.log("Product ID:", productId);
                    function findById(storedRequests, id) {
                        for (var i = 0; i < storedRequests.length; i++) {
                            console.log("Comparing:", storedRequests[i].id, id);
                            if (storedRequests[i].id === id) {
                                return storedRequests[i];
                            }
                        }
                        return null;
                    }
                    var productDetails = findById(storedRequests, productId);
                    
                    console.log('dsfsfsf');
                    console.log(productDetails);
                    $('#add_note').show();
                    var order = 'current_order';
                    loadOrderItems(null, productDetails, order);
                });
            }


        });
    }

    ///////////////////////////////////////////////////////////////////////////////////
    function getProcurementOrderList(selectedHorizonatlTabValue) {
        $.ajax({
            url: site.base_url + "Todays_Special/getProcurementOrderList",
            method: 'GET',
            data: {
                orderStatus: selectedHorizonatlTabValue
            },
            dataType: 'json',
            success: function (response) {
                if (response) {
                    if (selectedHorizonatlTabValue) {
                        switch (selectedHorizonatlTabValue) {
                            case 'current_order':
                                $("#update_order").hide();
                                $(".right_section").show();
                                $(".repeat_order").hide();
                                $('#order_status').text(('Current Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    var product_details = orderItem.productsByCat;
                                    loadOrderItems(Items, product_details);
                                });
                                break;
                            case 'partial_order_item':
                                $("#update_order").hide();
                                $(".right_section").hide();
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    loadOrderItems(Items);
                                });
                                break;
                            case 'previous_order':
                                $("#update_order").hide();
                                $('#order_status').text(('Previous Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.getOrderData;
                                    loadOrderItems(Items);
                                });
                                break;
                            default:
                                console.error('Unknown order type');
                                break;
                        }
                    }

                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response Text:", xhr.responseText);
            }
        });
    }
     function loadOrderItems(response, productDetails, order) {

        localStorage.setItem('productDetails', response);
        var selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
        if (selectedHorizonatlTab === null) {
            $(".right_section").show();
        }
        $("#received_order").hide();
        if (order == 'currentorder') {
            $(".right_section").show();
            $('#dynamicTable tfoot').empty();
            var unit_price = parseFloat(productDetails.unit_price).toFixed(2);
            const orderdetails = `
               <tr>
                    <th scope="col">Category</th>
                    <th scope="col">Product</th>
                    <th scope="col">Qty.</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">Net Price</th>
                    <th scope="col">Tax</th>
                    <th scope="col">Sub Total</th>
                    <th scope="col">Delete</th>
                </tr>`;
            $('#dynamicTable thead').html(orderdetails);
            console.log('currentorder');
            console.log(productDetails);
            var current_order =
                '<tr>' +
                '<td><input type="hidden" name="categoryName[]" class="text-center categoryName-input" value="' + productDetails.categoryName + '">' + productDetails.categoryName + '</td>' +
                '<td><input type="hidden" name="productName[]" class="text-center productName-input" data-product_id="' + productDetails.id + '" value="' + productDetails.name + '">' + productDetails.name + '</td>' +
                '<td><input type="number" name="order_quantity[]" style="width:50%" class="text-end quantity-input" value = "' + productDetails.quantity + '" required></td>' +
                '<td><input type="hidden" name="unitName[]" class="text-center unitName-input" value="' + productDetails.unitName + '">' + productDetails.unitName + '</td>' +
                '<td id="unit_price" class="text-end unit_price " data-unit_price="' + productDetails.unit_price + '"  data-unit_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                '<td id="net_price" class="text-end net_price " data-net_price="' + productDetails.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + productDetails.tax_rate + '"  data-tax_rate="' + productDetails.tax_rate + '">' + formatMoney(productDetails.tax_rate) + '</td>' +
                '<td id="subtotal" class="text-end subtotal " data-order-id="' + productDetails.unit_price + '" data-tax_method="' + productDetails.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                '<td><i class="fa fa-trash delete-btn"></i></td>' +
                '</tr>';
            var newRow = $(current_order);
            $('#dynamicTable tbody').append(newRow);
            var unit_price = formatMoney(unit_price);
            // var rowCount = localStorage.getItem('rowCount');
            var rowCount = $('#dynamicTable tbody tr').length;
            var tfootRow = '<tr>' + //Item footer
                '<td><strong>Total Items</strong></td>' +
                '<td class="text-center" id = "rowCount">' + rowCount + '</td>' +
                '<td></td>' +
                '<td></td>' +
                '<td id ="unit_prices" class="text-end">' + unit_price + '</td>' +
                '<td id ="net_prices" class="text-end">' + '</td>' +
                '<td id ="tax_rates" class="text-end">' + '</td>' +
                // '<td><strong>Order Total</strong></td>' +
                '<td id ="subTotals" class="text-end">' + '</td>' +
                '<td></td>' +
                '</tr>';

            $('#dynamicTable tfoot').append(tfootRow);


        } else {
            if (selectedHorizonatlTab == 'update_order') {
                var unit_price = 0;
                $('#dynamicTable tbody').empty();
                const current_order = `
                    <tr>
                        <th scope="col">Category</th>
                        <th scope="col">Product</th>
                        <th scope="col">Qty.</th>
                        <th scope="col">Unit</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col">Net Price</th>
                        <th scope="col">Tax</th>
                        <th scope="col">Sub Total</th>
                        <th scope="col">Delete</th>
                    </tr>`;
                $('#dynamicTable thead').html(current_order);
                response.forEach(function (Open_order) {
                    unit_price = parseFloat(Open_order.unit_price).toFixed(2);
                    console.log('Update_order');
                    console.log(Open_order);
                    $('#editorRow').hide();

                    var current_order =
                        '<tr>' +
                        '<td><input type="hidden" name="categoryName[]" class="text-center categoryName-input" value="' + Open_order.categoryName + '">' + Open_order.categoryName + '</td>' +
                        '<td><input type="hidden" name="productName[]" class="text-center productName-input" value="' + Open_order.product_name + '">' + Open_order.product_name + '</td>' +
                        '<td><input type="number" name="order_quantity[]" style="width:50%" class="text-end quantity-input" value = "' + Open_order.order_quantity + '" required></td>' +
                        '<td><input type="hidden" name="unitName[]" class="text-center unitName-input" value="' + Open_order.unitName + '">' + Open_order.unitName + '</td>' +
                        '<td id="unit_price" class="text-end unit_price " data-unit_price="' + Open_order.unit_price + '"  data-unit_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="net_price" class="text-end net_price " data-net_price="' + Open_order.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + Open_order.tax_rate + '"  data-tax_rate="' + Open_order.tax_rate + '">' + formatMoney(Open_order.tax_rate) + '</td>' +
                        // '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '">' + Open_order.subtotal + '</td>' +
                        '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '" data-tax_method="' + Open_order.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(Open_order.subtotal) + '</td>' +
                        '<td><i class="fa fa-trash delete-btn"></i></td>' +
                        '</tr>';
                    $('#update_order').attr('data-id', Open_order.itemsId);
                    var newRow = $(current_order);
                    $('#dynamicTable tbody').append(newRow);


                });
                var rowCount = $('#dynamicTable tbody tr').length;
                var unit_price = formatMoney(unit_price);
                var tfootRow = '<tr>' + //Item footer
                    '<td><strong>Total Items</strong></td>' +
                    '<td class="text-center" id = "rowCount">' + rowCount + '</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td id ="unit_prices" class="text-end">' + unit_price + '</td>' +
                    '<td id ="net_prices" class="text-end">' + '</td>' +
                    '<td id ="tax_rates" class="text-end">' + '</td>' +
                    '<td class = "text-end" id ="subTotals">' + '</td>' +
                    '<td></td>' +
                    '</tr>';

                $('#dynamicTable tfoot').append(tfootRow);

                $(".repeat_order").hide();
                $("#add_note").show();
                $("#update_order").show();
                $("#place_order").hide();

            }
            if (selectedHorizonatlTab == 'current_order') {

                var unit_price = 0;

                $('#dynamicTable tbody').empty();
                $('#dynamicTable tfoot').empty();
                const current_order = `
                    <tr>
                        <th scope="col">Category</th>
                        <th scope="col">Product</th>
                        <th scope="col">Qty.</th>
                        <th scope="col">Unit</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col">Net Price</th>
                        <th scope="col">Tax</th>
                        <th scope="col">Sub Total</th>
                        <th scope="col">Delete</th>
                    </tr>`;
                $('#dynamicTable thead').html(current_order);
                var Open_orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
                console.log("add current order ");
                console.log(Open_orders);
                Open_orders.forEach(function (Open_order) {
                    unit_price = parseFloat(Open_order.unit_price).toFixed(2);
                    const quantity = parseInt(Open_order.order_quantity);
                    const subtotal = (unit_price * quantity).toFixed(2);
                    console.log('current_order');
                    console.log(Open_orders);
                    const partial_current_order =   // Item Body
                        '<tr >' +
                        '<td>' + Open_order.categoryName + '</td>' +
                        '<td>' + Open_order.product_name + '</td>' +
                        '<td class=""><input type="number" name = "order_quantity[]" style="width:50%"  class="text-end quantity-input" value="' + Open_order.order_quantity + '"></td>' +
                        '<td>' + Open_order.unit + '</td>' +
                        '<td id="unit_price" class="text-end unit_price " data-unit_price="' + Open_order.Open_order + '"  data-unit_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="net_price" class="text-end net_price " data-net_price="' + Open_order.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                        '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + Open_order.tax_rate + '"  data-tax_rate="' + Open_order.tax_rate + '">' + formatMoney(Open_order.tax_rate) + '</td>' +
                        // '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.subtotal + '">' + Open_order.subtotal + '</td>' +
                        '<td id="subtotal" class="text-end subtotal " data-order-id="' + subtotal + '" data-tax_method="' + Open_order.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(subtotal) + '</td>' +
                        '<td><i class="fa fa-trash delete-btn" ></i></td>' +
                        // '<td><a id="orderAction" class="view-order-link"  href=""  data-order-id="' + Items.itemsId + '">delete</a></td>';
                        '</tr>';
                    // Append each row to the tbody of the table
                    var newRow = $(partial_current_order);
                    $('#dynamicTable tbody').append(newRow);
                });
                if (response) {
                    response.forEach(function (Open_order) {
                        unit_price = parseFloat(Open_order.unit_price).toFixed(2);
                        const quantity = parseInt(Open_order.order_quantity);
                        const subtotal = (unit_price * quantity).toFixed(2);
                        console.log('current_order');
                        console.log(Open_order);
                        const current_order =   // Item Body
                            '<tr >' +
                            '<td>' + Open_order.categoryName + '</td>' +
                            '<td>' + Open_order.product_name + '</td>' +
                            '<td class=""><input type="number" name = "order_quantity[]" style="width:50%" class="text-end quantity-input" value="' + Open_order.order_quantity + '"></td>' +
                            '<td>' + Open_order.unitName + '</td>' +
                            '<td id="unit_price" class="text-end unit_price " data-unit_price="' + Open_order.Open_order + '"  data-unit_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                            '<td id="net_price" class="text-end net_price " data-net_price="' + Open_order.unit_price + '"  data-net_price="' + unit_price + '">' + formatMoney(unit_price) + '</td>' +
                            '<td id="tax_rate" class="text-end tax_rate " data-tax_rate="' + Open_order.tax_rate + '"  data-tax_rate="' + Open_order.tax_rate + '">' + formatMoney(Open_order.tax_rate) + '</td>' +
                            // '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '">' + Open_order.subtotal + '</td>' +
                            '<td id="subtotal" class="text-end subtotal " data-order-id="' + Open_order.unit_price + '"  data-tax_method="' + Open_order.tax_method + '" data-item_price="' + unit_price + '">' + formatMoney(subtotal) + '</td>' +

                            '<td><i class="fa fa-trash delete-btn" ></i></td>' +
                            // '<td><a id="orderAction" class="view-order-link"  href=""  data-order-id="' + Items.itemsId + '">delete</a></td>';
                            '</tr>';
                        var newRow = $(current_order);
                        $('#dynamicTable tbody').append(newRow);
                    });
                }

                var rowCount = $('#dynamicTable tbody tr').length;
                var unit_price = formatMoney(unit_price);
                if (rowCount > 0) {
                    var tfootRows = '<tr>' + //Item footer
                        '<td><strong>Total Items</strong></td>' +
                        '<td class="text-center" id = "rowCount">' + rowCount + '</td>' +
                        '<td></td>' +
                        '<td></td>' +
                        '<td id ="unit_prices" class="text-end">' + unit_price + '</td>' +
                        '<td id ="net_prices" class="text-end">' + '</td>' +
                        '<td id ="tax_rates" class="text-end">' + '</td>' +
                        '<td id ="subTotals" class="text-end">' + '</td>' +
                        '<td></td>' +
                        '</tr>';
                    $('#dynamicTable tfoot').append(tfootRows);
                }
                $(".repeat_order").hide();
                $("#add_note").show();
                $("#place_order").show();

            }
            if (selectedHorizonatlTab == 'partial_order_item') {
                $('#dynamicTable tbody').empty();
                $("#received_order").hide();

                let partial_order_item =
                    '<tr>' +
                    '<th scope="col">Category</th>' +
                    '<th scope="col">Product</th>' +
                    '<th scope="col">Qty. Requested</th>' +
                    '<th scope="col">Qty. Received</th>' +
                    '<th scope="col">Difference</th>';
                partial_order_item += '<th scope="col">Add to Current Order</th>';
                partial_order_item += '</tr>';
                $('#dynamicTable thead').html(partial_order_item);
                response.forEach(function (Open_order) {
                    console.log("partial");
                    console.log(Open_order);
                    //    $('#plusIcon1').attr('data-id', Open_order.order_id);
                    var Difference = (Open_order.order_quantity - Open_order.received_quantity);
                    var partial_order_item =
                        '<tr>' +
                        '<td>' + Open_order.categoryName + '</td>' +
                        '<td>' + Open_order.product_name + '</td>' +
                        '<td class="text-center">' + formatQuantity(Open_order.order_quantity) + '</td>' +
                        '<td class="text-center">' + formatQuantity(Open_order.received_quantity) + '</td>' +
                        '<td class="text-center "> ' + Difference + '</td>' +
                        // '<td><input type="hidden" name="subtotal[]" class="text-center unitName-input" value="' + Open_order.subtotal + '">' + '</td>' +
                        // '<td><i class="fa fa-plus-circle plusIcon1 partial_order_link" data-order-id="' + Open_order.order_id + '"></i><span id="addedText1" style="display: none; color:#00C314">Added to Current Order</span></td>' +
                        // '</tr>';
                        '<td><i class="fa fa-plus-circle partial_order_item_addToCurrentOrder" data-order-id="' + Open_order.order_id + '" data-unit="' + Open_order.unitName + '" data-unit_price="' + Open_order.unit_price + '" data-tax_method="' + Open_order.tax_method + '" data-tax_rate="' + Open_order.tax_rate + '"data-unit-price="' + Open_order.subtotal + '"></i>' +
                        '<span class="addedText" style="display: none; color: #00C314;">Added to Current Order</span></td>' +
                        '</tr>';

                    // Append each row to the tbody of the table
                    $('#dynamicTable tbody').append(partial_order_item);
                    loadOrdersFromLocalStorage();
                });

            }
            if (selectedHorizonatlTab == 'previous_order') {
                $('#dynamicTable tfoot').empty();
                $('#dynamicTable tbody').empty();
                let previous_order = '<tr>' +
                    '<th scope="col">Order #</th>' +
                    '<th scope="col">Total Items</th>' +
                    '<th scope="col">Placed On</th>' +
                    '<th scope="col">Placed By</th>' +
                    '<th scope="col">Received On</th>' +
                    '<th scope="col">Order Status</th>';
                // '<th scope="col">View Order</th>';
                previous_order += '</tr>';
                $('#dynamicTable thead').html(previous_order);

                response.forEach(function (Open_order) {
                    var receiveddate = (Open_order.receivedOn === '0000-00-00 00:00:00') ? '-' : Open_order.receivedOn;
                    console.log('Open_order');
                    console.log(Open_order);
                    var previous_order =
                        '<tr>' +
                        '<td><a href="" id="orderAction" class="Previous-Item-link" data-order-id="' + Open_order.orderId + '" data-order-no="' + Open_order.orderNo + '">' + Open_order.orderNo + '</a></td>' +
                        // '<td>' + Open_order.orderNo + '</td>' +
                        '<td class="text-center">' + formatQuantity(Open_order.itemCount) + '</td>' +
                        '<td>' + Open_order.placedOn + '</td>' +
                        '<td>' + Open_order.createdByFirstName + '</td>' +
                        '<td>' + receiveddate + '</td>' +
                        '<td>' + Open_order.orderStatus + '</td>';
                    // '<td><a href="" id = "orderAction" class="Previous-Item-link" data-order-id="' + Open_order.orderId + '" data-order-no="' + Open_order.orderNo + '">View</a></td>';
                    '</tr>';
                    // Append each row to the tbody of the table
                    $('#dynamicTable tbody').append(previous_order);


                });

                $(".place_order").hide();
                $(".right_section").hide();

            }

        }
    }







































































    $('#place_order').click(function () {
        if ($('#dynamicTable tbody').children().length === 0) {
            return;
        }
        var outletNames = $('#outletName').val();
        var tableData = [];
        // var note = localStorage.getItem('add_note') || '';
        var requestedDeliveryDate = formatDate($("#requestDeliveryDate").datepicker("getDate"));
        // Iterate through each row in tbody
        $('#dynamicTable tbody tr').each(function (index, row) {
            var rowData = {};
            $(row).find('td').each(function (index, column) {
                var columnName = $('#dynamicTable thead th').eq(index).text().trim().toLowerCase().replace(/\s+/g, '_'); // Modify column header text
                var cellValue;
                // Check if the cell contains an input element
                var inputElement = $(column).find('input');
                if (inputElement.length > 0) {
                    cellValue = inputElement.val().trim(); // Get input value
                } else {
                    cellValue = $(column).text().trim(); // Get text content
                }
                rowData[columnName] = cellValue; // Store column data in the rowData object
            });
            rowData['note'] = note;
            rowData['outletNames'] = outletNames;

            rowData['requested_delivery_date'] = requestedDeliveryDate;
            tableData.push(rowData);
        });
        console.log('tableData', tableData);
        var queryParams = $.param({ orders: tableData });
        var url = site.base_url + "Todays_Special/PlaceProcurementOrder?" + queryParams;
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json', // Expect JSON data in response
            success: function (response) {
                Toastify({
                    text: 'Order placed successfully',
                    duration: 1000,  // Duration in milliseconds (1 seconds)
                    gravity: 'top-right', // Display position: 'bottom-left', 'bottom-right', 'top-left', 'top-right'
                    close: true,  // Whether to add a close button
                    callback: function () {
                        $('#dynamicTable thead').empty();
                        $('#dynamicTable tbody').empty();
                        $('#dynamicTable tfoot').empty();
                        localStorage.removeItem('currentOrderItems');
                        localStorage.removeItem('add_note');
                        resetDropdown();
                        $('.status').click();
                    }
                }).showToast();
            },
            error: function (xhr, status, error) {
                console.error('Error sending data: ' + error);
            }
        });
    });

   

    function formatDate(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);
        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }


    /////////////////////////////////////////////////// SEARCH CATEGORIES //////////////////////////////////////////////
    $('.search-box').on('keyup', function () {
        var searchText = $(this).val().toLowerCase(); // Get user input and convert to lowercase

        // Filter categories
        $('.categoriesList li').each(function () {
            var categoryText = $(this).text().toLowerCase();
            var isVisible = categoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });

        // Filter subcategories
        $('.subcategoriesList li').each(function () {
            var subcategoryText = $(this).text().toLowerCase();
            var isVisible = subcategoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });
        $('.productList li').each(function () {
            var subcategoryText = $(this).text().toLowerCase();
            var isVisible = subcategoryText.indexOf(searchText) > -1;
            $(this).toggle(isVisible);
        });
    });

    //////////////////////////////////////////////////////// NAVIGATON ////////////////////////////////////////////////
    $('#clickMe').click(function () {
        var selectedHorizonatlTab = '';
        selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
        if (selectedHorizonatlTab === "partial_order_item") {
            // selectedHorizonatlTabselectedHorizonatlTab = "partially";
            selectedHorizonatlTab = "partially";
        }
        if (selectedHorizonatlTab === "update_order") {
            $('#Open').click();
            return;
        }
        if (selectedHorizonatlTab === "previous_order_received") {
            selectedHorizonatlTab = "previous_order";
        }
        $('#' + selectedHorizonatlTab + ' .status').click();
    });

    /////////////////////////////////////////////////////// REMOVE RECORD ////////////////////////////////////////////////
    // jQuery code to remove table row when delete button is clicked
    $(document).on('click', '.delete-btn', function () {
        var $row = $(this).closest('tr');
        var productName = $row.find('td:nth-child(2)').text(); // Adjust selector to target the product name
        $row.remove();
        var Open_orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
        Open_orders = Open_orders.filter(function (order) {
            return order.product_name !== productName; // Remove item based on product_name
        });
        localStorage.setItem('currentOrderItems', JSON.stringify(Open_orders));
        updateRowCount();
    });

    $(document).on('click', '.plusIcon1', function () {
        $("#addedText1").show();
        $(".plusIcon1").hide();

    });
    ////////////////////////////////////////////////////// UPDATE ROW COUNT //////////////////////////////////////////////////
    function updateRowCount() {
        var rowCount = $('#dynamicTable tbody tr').length;
        $('#rowCount').text(rowCount);
        localStorage.setItem('rowCount', rowCount);
        console.log("Updated row count:", rowCount);
        // You can update a UI element showing the row count, or perform any other action here
    }

    $('.status').click(function (event, status) {
        $(".orderNo").hide();
        $('#editorRow').hide();
        var selectedHorizonatlTab = $(this).attr('value');
        localStorage.setItem('selectedHorizonatlTab', selectedHorizonatlTab);
        if (selectedHorizonatlTab === "partially") {
            $(".right_section").hide();
            $('#add_note').hide();
            $(".repeat_order").hide();
        }
        if (selectedHorizonatlTab === "Open") {
            $('#add_note').hide();
        }
        if (status === "open_order") {
            $('#add_note').hide();
            var selectedHorizonatlTab = "Open";

        }
        if (selectedHorizonatlTab === "previous_order") {
            $('#add_note').hide();
            $(".right_section").hide();
        }
        if (selectedHorizonatlTab === "current_order") {
            $('#dynamicTable tfoot').empty();
            $('#dynamicTable tbody').empty();
            $('#update_order').hide();
            $('#add_note').show();
            $('#place_order').show();
            $(".repeat_order").hide();
            $(".right_section").show();

        }
        getProcurementOrderList(selectedHorizonatlTab);
    });



    $(document).ready(function () {
        function toggleDivs() {
            if ($('#activeStatus').hasClass('active')) {
                $('.hide-div').show();
                $('.show-div').hide();
            } else {
                $('.hide-div').hide();
                $('.show-div').show();
            }

            // Additional check for #dynamicTable visibility
            if ($('#dynamicTable').is(':hidden')) {
                $('.show-div').hide();
            }
        }

        $('.nav-link').on('click', function () {
            $('.nav-item').removeClass('active');
            $(this).parent().addClass('active');
            toggleDivs();
        });

        // Click event for #repeat_order
        $('#repeat_order').on('click', function () {
            // Redirect to current order tab logic (not provided in the snippet)

            // Hide .show-div after redirect
            $('.hide-div').show();
            $('.show-div').hide();
        });

        // Ensure the correct div is shown on page load based on the active tab and dynamicTable visibility
        toggleDivs();
    });

    $(document).ready(function () {
        $('#activeStatus .status').click(); // This line triggers the click event on page load
    });



    $(document).on('click', '.Update_rec-order-link', function (event) {
        $(".orderNo").show();
        $(".place_order").hide();
        $(".right_section").show();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);


        if (orderId) {
            var orderStatus = 'update_order';
            getProcurementOrderItemsListByOrderStatus(orderStatus, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });
    $(document).on('click', '.partial-order-link', function (event) {
        $(".orderNo").show();
        $(".right_section").hide();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);
        if (orderId) {
            var selectedHorizonatlTabValue = 'partial_order_item';
            getProcurementOrderItemsListByOrderStatus(selectedHorizonatlTabValue, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });
    $(document).on('click', '.Previous-Item-link', function (event) {
        $(".right_section").show();
        $(".orderNo").show();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        $('#repeat_order').attr('data-id', orderId);
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);

        if (orderId) {
            var orderStatus = 'previous_order_received';
            getProcurementOrderItemsListByOrderStatus(orderStatus, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });
    $(document).on('click', '.partial_order_item_addToCurrentOrder', function (event) {
        // Prevent default action (if any)
        event.preventDefault();
        var $row = $(this).closest('tr');
        var rowData = {
            categoryName: $row.find('td:eq(0)').text().trim(),
            product_name: $row.find('td:eq(1)').text().trim(),
            order_quantity: $row.find('td:eq(2)').text().trim(),
            quantity: $row.find('td:eq(2)').text().trim(),
            received_quantity: $row.find('td:eq(3)').text().trim(),
            // Add more fields as needed
            order_id: $(this).data('order-id'), // Assuming data-order-id is set correctly
            unit: $(this).data('unit'),
            subtotal: $(this).data('unit-price'),
            unit_price: $(this).data('unit-price'),
            tax_method: $(this).data('tax_method'),
            tax_rate: $(this).data('tax_rate')
        };

        // Store row data in local storage
        var orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
        orders.push(rowData);
        console.log("partial_order_item_addToCurrentOrder");
        console.log(orders);
        localStorage.setItem('currentOrderItems', JSON.stringify(orders));

        // Update UI: Replace plus icon with "Added to Current Order" message in green
        $(this).hide(); // Hide the plus icon
        $row.find('.addedText').show(); // Show the added message
    });
    $(document).on('click', '.Received-Item-link', function (event) {
        $(".right_section").show();
        $(".orderNo").show();
        event.preventDefault();
        var orderId = $(this).data('order-id'); // Use jQuery to get data attribute
        $('#repeat_order').attr('data-id', orderId);
        var OrderNo = $(this).data('order-no');
        $('.orderNo').text("#" + OrderNo);

        if (orderId) {
            var orderStatus = 'Received_orders';
            getProcurementOrderItemsListByOrderStatus(orderStatus, orderId);
        } else {
            console.error("No 'data-order-id' attribute found on the clicked element.");
        }
    });


    function loadOrdersFromLocalStorage() {
        var orders = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
        $('#dynamicTable tbody tr').each(function () {
            var $row = $(this);
            var order_id = $row.find('.partial_order_item_addToCurrentOrder').data('order-id');
            // Check if current row's order_id exists in stored orders
            var foundOrder = orders.find(function (order) {
                return order.order_id === order_id;
            });
            if (foundOrder) {
                // Row data is already in local storage, update UI
                $row.find('.partial_order_item_addToCurrentOrder').hide(); // Hide plus icon
                $row.find('.addedText').show(); // Show added message
            }
        });
    }
    // Call function to load and check stored orders on page load
    loadOrdersFromLocalStorage();

    ////////////////////////////////////////////////// UPDATE ORDER DB SAVE //////////////////////////////////////////////////////////////
    $('.TabClick').click(function () {
        var tableData = [];
        // var dataId = $(this).data('id');
        var dataId = $(this).attr('data-id');
        var selectedHorizonatlTabValue = $(this).val();
        // Iterate through each row in tbody
        $('#dynamicTable tbody tr').each(function (index, row) {
            var rowData = {};
            //    var  allValid = '';
            //     $(row).find('.quantity-input').each(function () {
            //         var inputValue = $(this).val();  // Get the value of the input field

            //         if (inputValue.trim() === '') { // Check if the input value is empty or contains only whitespace
            //             allValid = false; // Set flag to false if any field is blank
            //             $(this).addClass('error'); // Optionally, add an 'error' class to highlight the invalid field
            //         } else {
            //             $(this).removeClass('error'); // Remove 'error' class if the field is valid
            //         }
            //         console.log('Row ' + index + ' Input Value:', inputValue); // Print the value and the row index
            //     });
            //     alert(allValid)
            //     if (!allValid) {
            //         alert('Received Quanity Required');
            //       } 
            $(row).find('td').each(function (index, column) {
                var columnName = $('#dynamicTable thead th').eq(index).text().trim().toLowerCase().replace(/\s+/g, '_'); // Modify column header text
                var cellValue;
                var inputElement = $(column).find('input');
                if (inputElement.length > 0) {
                    cellValue = inputElement.val().trim(); // Get input value
                } else {
                    cellValue = $(column).text().trim(); // Get text content
                }
                rowData[columnName] = cellValue; // Store column data in the rowData object
            });

            rowData['selectedHorizonatlTabValue'] = selectedHorizonatlTabValue;
            rowData['order_id'] = dataId;
            rowData['note'] = note;
            tableData.push(rowData);
        });


        console.log('tableData', tableData);
        var queryParams = $.param({ orders: tableData });

        itemData = '';
        switch (selectedHorizonatlTabValue) {
            case 'received_order':
                itemData = queryParams;
                // selectedHorizonatlTabValue = 'received_order';
                break;
            case 'update_order':
                itemData = queryParams;
                break;
            default:
                console.error('Unknown order type');
                break;
        }
        console.log('itemData');
        console.log(itemData);
        var url = site.base_url + "Todays_Special/updateProcurementOrder?" + itemData;
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json', // Expect JSON data in response
            success: function (response) {
                Toastify({
                    text: 'Order Updated successfully',
                    duration: 1000,  // Duration in milliseconds (1 seconds)
                    gravity: 'top-right', // Display position: 'bottom-left', 'bottom-right', 'top-left', 'top-right'
                    close: true,  // Whether to add a close button
                    callback: function () {
                        $('#dynamicTable thead').empty();
                        $('#dynamicTable tbody').empty();
                        $('#dynamicTable tfoot').empty();
                        localStorage.removeItem('currentOrderItems');
                        localStorage.removeItem('add_note');

                        $('.status').click();
                    }
                }).showToast();
            },
            error: function (xhr, status, error) {
                console.error('Error sending data: ' + error);
            }
        });
    });
    // $('#received_order').click(function () {
    //     if ($('#dynamicTable tbody').children().length === 0) {
    //         return;
    //     }
    //     var orderId = $('.order_id').data('order_id');
    //     // alert(orderId)
    //     var outletNames = $('#outletName').val();
    //     var tableData = [];
    //     // var note = localStorage.getItem('add_note') || '';
    //     // var note = $('#editor').redactor('get');
    //     // var requestedDeliveryDate = formatDate($("#requestDeliveryDate").datepicker("getDate"));
    //     // Iterate through each row in tbody
    //     $('#dynamicTable tbody tr').each(function (index, row) {
    //         var rowData = {};
    //         $(row).find('td').each(function (index, column) {
    //             var columnName = $('#dynamicTable thead th').eq(index).text().trim().toLowerCase().replace(/\s+/g, '_'); // Modify column header text
    //             var cellValue;
    //             // Check if the cell contains an input element
    //             var inputElement = $(column).find('input');
    //             if (inputElement.length > 0) {
    //                 cellValue = inputElement.val().trim(); // Get input value
    //             } else {
    //                 cellValue = $(column).text().trim(); // Get text content
    //             }
    //             rowData[columnName] = cellValue; // Store column data in the rowData object
    //         });
    //         // rowData['note'] = note;
    //         rowData['outletNames'] = outletNames;

    //         // rowData['requested_delivery_date'] = requestedDeliveryDate;
    //         tableData.push(rowData);
    //     });
    //     console.log('tableData', tableData);
    //     var queryParams = $.param({ orders: tableData });
    //     var url = site.base_url + "Todays_Special/received_order?" + queryParams;
    //     $.ajax({
    //         url: url,
    //         method: 'GET',
    //         dataType: 'json', // Expect JSON data in response
    //         success: function (response) {
    //             Toastify({
    //                 text: 'Data Received successfully',
    //                 duration: 1000,  // Duration in milliseconds (1 seconds)
    //                 gravity: 'top-right', // Display position: 'bottom-left', 'bottom-right', 'top-left', 'top-right'
    //                 close: true,  // Whether to add a close button
    //                 callback: function () {
    //                     $('#dynamicTable thead').empty();
    //                     $('#dynamicTable tbody').empty();
    //                     $('#dynamicTable tfoot').empty();
    //                     localStorage.removeItem('currentOrderItems');
    //                     localStorage.removeItem('add_note');
    //                     resetDropdown();
    //                     $('.status').click();
    //                 }
    //             }).showToast();
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Error sending data: ' + error);
    //         }
    //     });
    // });

    //////////////////////////////////////////////////////// REPEAT ORDER ///////////////////////////////////////
    $('.repeat_order').click(function () {
        var order_id = $(this).attr('data-id');
        var orderStatus = $(this).attr('value');
        $(".right_section").show();
        $("#activeStatus").toggleClass("active");
        $("#previous_order").removeClass("active");
        getProcurementOrderItemsListByOrderStatus(orderStatus, order_id)
    });
});





document.addEventListener('DOMContentLoaded', function () {

    const specificLinks = document.querySelectorAll('#clickMe, .nav-link[value="Open"], .nav-link[value="partially"], .nav-link[value="previous_order"]');
    const divToChange = document.querySelector('#procurement_order .sixty-eight-percent');

    function applyWidth() {
        if (divToChange) {
            divToChange.style.setProperty('width', '82%', 'important');
            console.log("Width changed to 82%");
        }
    }

    function resetWidth() {
        if (divToChange) {
            divToChange.style.removeProperty('width');
            console.log("Width has been reset");
        }
    }

    specificLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            applyWidth();
        });
    });

    document.addEventListener('click', function (e) {
        const target = e.target;

        if (target.matches('#repeat_order, .Update_rec-order-link, .nav-link[value="current_order"]')) {
            e.preventDefault();
            resetWidth();
        }
    });
});

