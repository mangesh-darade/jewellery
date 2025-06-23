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
var buttonText = '';
var buttonText_changed = '';
$(document).ready(function () {

    ///////////////////////////////////////////////////// ADD NOTES //////////////////////////////////////////
    // $('#editor').redactor('destroy');
    $('#editor').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slnote', v);
        }
    });
    if ('selectedHorizonatlTab' === 'current_order') {
        $('#editor').redactor('set', '');
    }
    else {
        if (slnote = localStorage.getItem('slnote')) {
            $('#editor').redactor('set', slnote);
        }
    }
    // // prevent default action usln enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    var $addNoteButton = $('#add_note');
    $('#add_note').click(function () {
        if ($('#dynamicTable tbody tr').length > 0 && $('#activeStatus a').attr('value') === 'current_order') {
            $('#editorRow').toggle();
            buttonText = $('#editorRow').is(':visible') ? 'Hide Note' : buttonText_changed ? buttonText_changed : 'Add Note';
            $addNoteButton.html('<i class="fa fa-file-text-o set-fnt"></i> ' + buttonText);
        }
    });
    //////////////////////////////////////////////////////// RESET //////////////////////////////////////////
  
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
            console.log('product');
            console.log(product);
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
                    $('#add_note').show();
                    var order = 'current_order';
                    loadOrderItems(null, productDetails, order);
                });
            }


        });
    }
    function loadOrderItems(response, selectedHorizontalTabValue) {
        localStorage.setItem('currentOrderItems', JSON.stringify(response));
        localStorage.setItem('selectedHorizontalTabValue', JSON.stringify(selectedHorizontalTabValue));

        let currentOrderItems = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
        let storedTabValue = JSON.parse(localStorage.getItem('selectedHorizontalTabValue'));

        console.log('currentOrderItems:', currentOrderItems);
        alert('loadOrderItems called with value: ' + storedTabValue);

        if (storedTabValue === 'current_order') {
            $('#editor').redactor('set', '');
            $('#dynamicTable tbody').empty();
            $('#dynamicTable tfoot').empty();
            $(".right_section").show();

            const tableHeader = `
            <tr>
                <th scope="col">Category</th>
                <th scope="col">Product</th>
                <th scope="col">Price</th>
                <th scope="col">Delete</th>
            </tr>`;
            $('#dynamicTable thead').html(tableHeader);

            // Flatten currentOrderItems to work with getOrderData arrays
            let flattenedCurrentOrderItems = currentOrderItems.flatMap(item => item.getOrderData || []);

            // Transform new response (assuming response structure matches getOrderData items)
            const transformedResponse = response.map(item => ({
                ...item,
                price: parseFloat(item.unit_price) || 0,
            })).map(({ unit_price, ...rest }) => rest);

            // Filter duplicates based on category_name, product_id, date
            const filteredResponse = transformedResponse.filter(newItem => {
                return !flattenedCurrentOrderItems.some(existingItem =>
                    existingItem.category_name === newItem.category_name &&
                    existingItem.product_id === newItem.product_id &&
                    existingItem.date === newItem.date
                );
            });

            // Merge flattened current items and new filtered response
            const mergedOrderItems = [...flattenedCurrentOrderItems, ...filteredResponse];

            console.log('mergedOrderItems:', mergedOrderItems);

            localStorage.setItem('currentOrderItems', JSON.stringify([{ getOrderData: mergedOrderItems }]));

            // Render each row from merged items
            mergedOrderItems.forEach(function (item) {
                let categoryOptions = categories.map(cat => {
                    const selected = cat.category_name === item.category_name ? 'selected' : '';
                    return `<option value="${cat.category_name}" ${selected}>${cat.category_name}</option>`;
                }).join('');

                const row = `
                <tr>
                    <td>
                        <select name="categoryName[]" class="categoryName-input">
                            ${categoryOptions}
                        </select>
                    </td>
                    <td>${item.product_name}</td>
                    <td><input type="number" name="price[]" style="width:50%" class="text-end only-number" value="${item.price || 0}"></td>
                    <td><i class="fa fa-trash delete-btn"></i></td>
                </tr>`;
                $('#dynamicTable tbody').append($(row));
            });

            const rowCount = $('#dynamicTable tbody tr').length;
            if (rowCount > 0) {
                const tfootRow = `
                <tr>
                    <td><strong>Total Items</strong></td>
                    <td class="text-center" id="rowCount">${rowCount}</td>
                    <td></td>
                    <td></td>
                </tr>`;
                $('#dynamicTable tfoot').append(tfootRow);
            }

            $(".repeat_order").hide();
            $("#add_note").show();
            $("#place_order").show();
        }
    }


    // function loadOrderItems(response, productDetails, order = null) {
    //     localStorage.setItem('productDetails', response);
    //     var selectedHorizonatlTab = localStorage.getItem('selectedHorizonatlTab');
    //     if (selectedHorizonatlTab === null) {
    //         $(".right_section").show();
    //     }
    //     $("#received_order").hide();
    //     if (order === 'currentorder') {
    //         alert('Product added to current order');
    //         $(".right_section").show();
    //         $('#dynamicTable tfoot').empty();
    //         let currentOrderItems = JSON.parse(localStorage.getItem('currentOrderItems')) || [];
    //         const newItem = {
    //             categoryName: productDetails.categoryName,
    //             product_name: productDetails.name,
    //             unit_price: productDetails.unit_price,
    //             order_quantity: 1 // default value
    //         };

    //         currentOrderItems.push(newItem);
    //         localStorage.setItem('currentOrderItems', JSON.stringify(currentOrderItems));
    //         $('#dynamicTable tbody').empty();
    //         const tableHeader = `
    //        <tr>
    //             <th scope="col">Category</th>
    //             <th scope="col">Product</th>
    //             <th scope="col">Price</th>
    //             <th scope="col">Delete</th>
    //         </tr>`;
    //         $('#dynamicTable thead').html(tableHeader);

    //         currentOrderItems.forEach(function (item) {
    //             let categoryOptions = categories.map(cat => {
    //                 const selected = cat.category_name === item.categoryName ? 'selected' : '';
    //                 return `<option value="${cat.category_name}" ${selected}>${cat.category_name}</option>`;
    //             }).join('');
    //             item.unit_price == null ? item.unit_price = 0 : item.unit_price;
    //             const row = `
    //             <tr>
    //             <td>
    //                 <select name="categoryName[]" class="categoryName-input">
    //                     ${categoryOptions}
    //                 </select>
    //             </td>
    //                 <td><input type="hidden" name="productName[]" class="text-center productName-input" value="${item.product_name}">${item.product_name}</td>
    //                 <td><input type="number" name="price[]" style="width:50%" class="text-end only-number" value="${item.unit_price}" required></td>
    //                 <td><i class="fa fa-trash delete-btn"></i></td>
    //             </tr>`;
    //             $('#dynamicTable tbody').append($(row));
    //         });

    //         const rowCount = $('#dynamicTable tbody tr').length;
    //         const tfootRow = `
    //         <tr>
    //             <td><strong>Total Items</strong></td>
    //             <td class="text-center" id="rowCount">${rowCount}</td>
    //             <td></td>
    //             <td><i class="fa fa-trash delete-btn"></i></td>
    //         </tr>`;
    //         $('#dynamicTable tfoot').append(tfootRow);
    //     } else {
    //         if (selectedHorizonatlTab === 'current_order') {
    //             $addNoteButton.html('<i class="fa fa-file-text-o set-fnt"></i> Add Note');
    //             buttonText_changed = 'Add Note';
    //             $('#editor').redactor('set', '');
    //             $('#dynamicTable tbody').empty();
    //             $('#dynamicTable tfoot').empty();

    //             const tableHeader = `
    //            <tr>
    //             <th scope="col">Category</th>
    //             <th scope="col">Product</th>
    //             <th scope="col">Price</th>
    //             <th scope="col">Delete</th>
    //            </tr>`;
    //             $('#dynamicTable thead').html(tableHeader);

    //             let currentOrderItems = JSON.parse(localStorage.getItem('currentOrderItems')) || [];

    //             const transformedResponse = response.map(item => ({
    //                 ...item,
    //                 price: parseFloat(item.unit_price) || 0,
    //             }));
    //             transformedResponse.forEach(item => delete item.unit_price);
    //             // Filter out duplicates based on category_id, product_id, and date
    //             const filteredResponse = transformedResponse.filter(newItem => {
    //                 return !currentOrderItems.some(existingItem =>
    //                     existingItem.category_name === newItem.category_id &&
    //                     existingItem.product_id === newItem.product_id &&
    //                     existingItem.date === newItem.date
    //                 );
    //             });
    //             const mergedOrderItems = [...currentOrderItems, ...filteredResponse];
    //             console.log('mergedOrderItems:', mergedOrderItems);
    //             localStorage.setItem('currentOrderItems', JSON.stringify(mergedOrderItems));

    //             // Render rows for the merged items
    //             mergedOrderItems.forEach(function (item) {
    //                 let categoryOptions = categories.map(cat => {
    //                     const selected = cat.category_name === item.categoryName ? 'selected' : '';
    //                     return `<option value="${cat.category_name}" ${selected}>${cat.category_name}</option>`;
    //                 }).join('');
    //                 const row = `
    //                 <tr>
    //                     <td>
    //                         <select name="categoryName[]" class="categoryName-input">
    //                             ${categoryOptions}
    //                         </select>
    //                     </td>
    //                     <td>${item.product_name}</td>
    //                     <td><input type="number" name="price[]" style="width:50%" class="text-end only-number" value="${item.price}"></td>
    //                     <td><i class="fa fa-trash delete-btn"></i></td>
    //                 </tr>`;
    //                 $('#dynamicTable tbody').append($(row));
    //             });

    //             const rowCount = $('#dynamicTable tbody tr').length;
    //             if (rowCount > 0) {
    //                 const tfootRow = `
    //             <tr>
    //                 <td><strong>Total Items</strong></td>
    //                 <td class="text-center" id="rowCount">${rowCount}</td>
    //                 <td></td>
    //                 <td></td>
    //             </tr>`;
    //                 $('#dynamicTable tfoot').append(tfootRow);
    //             }

    //             $(".repeat_order").hide();
    //             $("#add_note").show();
    //             $("#place_order").show();
    //         }

    //         if (selectedHorizonatlTab == 'partially') {
    //             $('#dynamicTable tfoot').empty();
    //             $('#dynamicTable tbody').empty();
    //             let partially_order = '<tr>' +

    //                 '<th scope="col">Date</th>' +
    //                 '<th scope="col">Category</th>' +
    //                 '<th scope="col">Product</th>' +
    //                 '<th scope="col">Price</th>';
    //             partially_order += '</tr>';
    //             $('#dynamicTable thead').html(partially_order);

    //             response.forEach(function (Open_order) {
    //                 var item_price = (Open_order.price == null || Open_order.price == '') ? Open_order.price = 0 : Open_order.price;
    //                 var partially_order =
    //                     '<tr>' +
    //                     '<td class="">' + Open_order.date + '</td>' +
    //                     '<td class="">' + Open_order.category_name + '</td>' +
    //                     '<td>' + Open_order.product_name + '</td>' +
    //                     '<td  class="text-end">' + formatMoney(item_price) + '</td>' +
    //                     // '<td><a href="" id = "orderAction" class="partial-order-link" data-order-id="' + Open_order.orderId + '" data-order-no="' + Open_order.orderNo + '">View</a></td>';
    //                     '</tr>';
    //                 // Append each row to the tbody of the table
    //                 $('#dynamicTable tbody').append(partially_order);
    //             });
    //             $("#repeat_order").hide();
    //             $("#update_order").hide();
    //             $("#place_order").hide();
    //             $(".repeat_order").hide();

    //         }
    //         if (selectedHorizonatlTab === 'previous_order') {
    //             $('#dynamicTable tfoot').empty();
    //             $('#dynamicTable tbody').empty();

    //             const tableHeader = `
    //         <tr>
    //             <th scope="col">Product</th>
    //             <th scope="col">Add to Today Order</th>
    //         </tr>`;
    //             $('#dynamicTable thead').html(tableHeader);

    //             response.forEach(function (Open_order) {
    //                 const receivedDate = (Open_order.receivedOn === '0000-00-00 00:00:00') ? '-' : Open_order.receivedOn;

    //                 const row = `
    //             <tr>
    //                 <td>${Open_order.product_name}</td>
    //                 <td>
    //                     <i class="fa fa-plus-circle partial_order_item_addToCurrentOrder"
    //                        data-order-id="${Open_order.order_id}"
    //                        data-unit="${Open_order.unitName}"
    //                        data-unit_price="${Open_order.unit_price}"
    //                        data-tax_method="${Open_order.tax_method}"
    //                        data-tax_rate="${Open_order.tax_rate}"
    //                        data-unit-price="${Open_order.subtotal}"></i>
    //                     <span class="addedText" style="display: none; color: #00C314;">Added to Current Order</span>
    //                 </td>
    //             </tr>`;
    //                 $('#dynamicTable tbody').append($(row));
    //             });

    //             $(".place_order").hide();
    //             $(".right_section").hide();
    //         }
    //     }
    // }

    $(document).ready(function () {

        function formatDate(date) {
            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            var hours = ('0' + date.getHours()).slice(-2);
            var minutes = ('0' + date.getMinutes()).slice(-2);
            var seconds = ('0' + date.getSeconds()).slice(-2);
            return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
        }
        function resetDropdown() {
            $('#outletName')[0].selectedIndex = 0; // Set to index 0 (first option)
            return true;
        }
        /////////////////////////////////////////////////////////// PLACE ORDER ////////////////////////////////////////
        // Initialize an empty array to store tbody data
        $('#place_order').click(function () {
            if ($('#dynamicTable tbody').children().length === 0) {
                return;
            }
            var outletNames = $('#outletName').val();
            var tableData = [];
            // var note = localStorage.getItem('add_note') || '';
            var note = $('#editor').redactor('get');
            var requestedDeliveryDate = formatDate($("#requestDeliveryDate").datepicker("getDate"));
            // Iterate through each row in tbody
            $('#dynamicTable tbody tr').each(function (index, row) {
                var rowData = {};
                $(row).find('td').each(function (index, column) {
                    var columnName = $('#dynamicTable thead th').eq(index).text().trim().toLowerCase().replace(/\s+/g, '_');
                    var cellValue;

                    var inputElement = $(column).find('input');
                    var selectElement = $(column).find('select');

                    if (inputElement.length > 0) {
                        cellValue = inputElement.val().trim(); // Get input value
                    } else if (selectElement.length > 0) {
                        cellValue = selectElement.val(); // Get selected value from dropdown
                    } else {
                        cellValue = $(column).text().trim(); // Fallback to plain text
                    }

                    rowData[columnName] = cellValue;
                });

                rowData['requested_delivery_date'] = requestedDeliveryDate;
                tableData.push(rowData);
            });
            var queryParams = $.param({ orders: tableData });
            var url = site.base_url + "Todays_Special/PlaceProcurementOrder?" + queryParams;
            // CSRF values from server (embedded via PHP)
            var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
            var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

            // Add CSRF token to your POST data
            var dataToSend = {
                orders: tableData // your table data
            };
            dataToSend[csrfName] = csrfHash;

            $.ajax({
                url: site.base_url + "Todays_Special/PlaceProcurementOrder",
                method: 'POST',
                data: dataToSend,
                dataType: 'json',
                success: function (response) {
                    Toastify({
                        text: 'Order placed successfully',
                        duration: 1000,
                        gravity: 'top-right',
                        close: true,
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
        localStorage.removeItem('slnote');
    });

    ////////////////////////////////////////////////////// CHANGE QUANTITY /////////////////////////////////////////////////////////

    $(document).on('input', '.quantity-input', function () {
        var inputValue = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(inputValue);
        if (isNaN(inputValue) || inputValue <= 0) {
            $(this).val(parseInt(inputValue, 10) + 1);
        }
        var total = 0;
        var totalTax = 0;
        var totalnetPrice = 0;
        var row = $(this).closest('tr');
        var unitPrice = parseFloat(row.find('.subtotal').data('order-id'));
        var taxRate = parseFloat(row.find('.tax_rate').data('tax_rate'));
        var taxMethod = parseFloat(row.find('.subtotal').data('tax_method'));
        var quantity = parseFloat($(this).val());
        if (!isNaN(quantity) && !isNaN(unitPrice)) {
            var subtotal = quantity * unitPrice;
            row.find('.subtotal').text(formatMoney(subtotal)); // Update the subtotal cell in this row

            // Calculate tax based on tax method
            var productDetails = {
                unit_price: unitPrice,
                tax_rate: taxRate,
                quantity: quantity,
                tax_method: taxMethod
            };
            calculateProductTax(productDetails, row);

            // Calculate total of all subtotals and tax
            $('#dynamicTable tbody tr').each(function () {
                var subtotalValue = parseFloat($(this).find('.subtotal').data('item_price'));
                var subtotaltax_rate = parseFloat($(this).find('.tax_rate').data('tax_rate'));
                var subtotalnet_price = parseFloat($(this).find('.net_price').data('net_price'));
                var rowTax = parseFloat($(this).find('.tax_rate').text().replace(/[^\d.-]/g, ''));
                if (!isNaN(subtotalValue)) {
                    total += subtotalValue;
                    totalTax += subtotaltax_rate;
                    totalnetPrice += subtotalnet_price;
                }
            });
            // Update total and tax displays
            $('#subTotals').text(formatMoney(total));
            $('#net_prices').text(formatMoney(totalnetPrice));
            $('#tax_rates').text(formatMoney(totalTax));
            calculateOrderTotal();
        }

    });
    ///////////////////////////////////////////////////// CALCULATE TAX ///////////////////////////////////////////////////////
    // Function to calculate tax based on product details
    function calculateProductTax(productDetails, row = null) {

        var unitPrice = parseFloat(productDetails.unit_price);
        var taxRate = parseFloat(productDetails.tax_rate);
        var quantity = parseFloat(productDetails.quantity);
        var taxMethod = parseFloat(productDetails.tax_method);

        var netPrice = 0;
        var productTax = 0;
        if (taxMethod == 0) {
            pr_tax_val = formatDecimal((((unitPrice) * parseFloat(taxRate)) / (100 + parseFloat(taxRate))), 4);
            pr_tax_rate = formatDecimal(taxRate) + '%';
        } else {
            pr_tax_val = formatDecimal((((unitPrice) * parseFloat(taxRate)) / 100), 4);
            pr_tax_rate = formatDecimal(taxRate) + '%';
        }
        // var net = netPrice * quantity;
        productTax += pr_tax_val * quantity;
        netPrice = (taxMethod == 0) ? formatDecimal(unitPrice - pr_tax_val, 4) : formatDecimal(unitPrice);
        netPrice = netPrice * quantity;
        // $('.tax_rate').text(formatMoney(productTax));
        // $('.net_price').text(formatMoney(netPrice));
        // Update the tax rate and net price in the row
        row.find('.tax_rate').text(formatMoney(productTax));
        row.find('.net_price').text(formatMoney(netPrice));
        return true;
    }
    //////////////////////////////////////////////////// CALCULATE TOTAL ////////////////////////////////////////////////////////////
    function calculateOrderTotal() {
        var totalSubtotal = 0;
        var totalUintPrice = 0;
        var totalTax = 0;
        var totalNetPrice = 0;

        $('#dynamicTable tbody').find('.subtotal').each(function () {
            var subtotalText = $(this).text().trim();
            var subtotalValue = parseFloat(subtotalText.replace(/^Rs.\s*/, '').replace(/,/g, ''));
            if (!isNaN(subtotalValue)) {
                totalSubtotal += subtotalValue;
            }
        });
        $('#dynamicTable tbody').find('.unit_price').each(function () {
            var unit_price = $(this).text().trim();
            var subtotalValue = parseFloat(unit_price.replace(/^Rs.\s*/, '').replace(/,/g, ''));
            if (!isNaN(subtotalValue)) {
                totalUintPrice += subtotalValue;
            }
        });
        $('#dynamicTable tbody').find('.tax_rate').each(function () {
            var taxText = $(this).text().trim();
            taxValue = parseFloat(taxText.replace(/^Rs.\s*/, '').replace(/,/g, ''));
            if (!isNaN(taxValue)) {
                totalTax += taxValue;
            }
        });

        $('#dynamicTable tbody').find('.net_price').each(function () {
            var netPriceText = $(this).text().trim();
            var netPriceValue = parseFloat(netPriceText.replace(/^Rs.\s*/, '').replace(/,/g, ''));

            if (!isNaN(netPriceValue)) {
                totalNetPrice += netPriceValue;
            }
        });

        $('#subTotals').text(formatMoney(totalSubtotal));
        $('#tax_rates').text(formatMoney(totalTax));
        $('#net_prices').text(formatMoney(totalNetPrice));
        $('#unit_prices').text(formatMoney(totalUintPrice));
        return;
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
        calculateOrderTotal();
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

    // --------------------------------------- css style change to full with of open order tab -----------------------------------//
    // $(document).ready(function () {
    //     $('.nav-item').click(function () {
    //         $('.sixty-eight-percent.sty-bg-set').css('width', '80%');
    //         $('.wd-set.p-0.bg-setting').css('width', '20%');
    //     });
    // });

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

    function getProcurementOrderList(selectedHorizonatlTabValue) {
        alert('getProcurementOrderList called with value: ' + selectedHorizonatlTabValue);
        $.ajax({
            url: site.base_url + "Todays_Special/getProcurementOrderList",
            method: 'GET',
            data: {
                orderStatus: selectedHorizonatlTabValue
            },
            dataType: 'json',
            success: function (response) {
                console.log('response');
                console.log(response);
                if (response) {
                    //    loadOrderItems(response);

                    if (selectedHorizonatlTabValue) {
                        switch (selectedHorizonatlTabValue) {
                            case 'current_order':
                                $("#update_order").hide();
                                $(".right_section").show();
                                $(".repeat_order").hide();
                                $('#order_status').text(('Current Order'));
                                loadOrderItems(response, selectedHorizonatlTabValue);

                                break;
                            case 'Received':
                            case 'partially':
                                $('#order_status').text(('Partial Order'));
                                $(".right_section").hide();
                                $("#update_order").hide();

                                loadOrderItems(Items, product_details);

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
    function getProcurementOrderItemsListByOrderStatus(selectedHorizonatlTabValue, orderId) {
        localStorage.setItem('selectedHorizonatlTab', selectedHorizonatlTabValue);
        $.ajax({
            url: site.base_url + "Todays_Special/getProcurementOrderItemsListByOrderStatus",
            method: 'GET',
            data: {
                orderStatus: selectedHorizonatlTabValue,
                order_id: orderId
            },
            dataType: 'json',
            success: function (response) {
                console.log('response');
                console.log(response);

                if (response) {
                    if (selectedHorizonatlTabValue) {
                        switch (selectedHorizonatlTabValue) {
                            case 'update_order':
                                $("#place_order").hide();
                                $('#order_status').text(('Update Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    loadOrderItems(Items);
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
                            case 'previous_order_received':
                                $("#update_order").hide();
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    loadOrderItems(Items);
                                });
                                break;
                            case 'current_order':
                                $("#update_order").hide();
                                $(".right_section").show();
                                $(".repeat_order").hide();
                                $('#order_status').text(('Current Order'));
                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
                                    // var product_details = orderItem.productsByCat;
                                    loadOrderItems(Items);
                                });
                                break;
                            case 'Received_orders':
                                $("#update_order").hide();
                                $(".right_section").hide();
                                $("#add_note").hide();
                                $("#received_order").show();
                                $(".repeat_order").hide();

                                response.forEach(function (orderItem) {
                                    var Items = orderItem.item_data;
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
    /////////////////////////////////////////////////////// LEFT SATUS //////////////////////////////////////////////
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
        var note = $('#editor').redactor('get');
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

