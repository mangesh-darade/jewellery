<style>
.editsty {
    background-color: transparent !important;
    color: #007bff !important;
    border: 1px solid #007bff !important;
    padding: 4px 29px !important;
    font-size: 13px !important;
    border-radius: 3px !important;
    margin-top: -3rem;
    margin-left: -2em;
}

.editsty:hover {
    background-color: #007bff !important;
    color: white !important;
}

.panel {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
}

.panel-heading {
    font-weight: bold;
    font-size: 16px;
    border-left: 4px solid #007bff;
    padding: 10px;
    background-color: #fff;
    border-radius: 8px 8px 0 0;
}

.panel-default>.panel-heading {
    color: #333;
    background-color: #f5f5f5;
    border-color: #009DFF;
}

/* .table {
        width: 100%;
        margin-bottom: 0;
    }

    .table td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .table td:first-child {
        font-weight: bold;
        width: 35%;
    } */

.table input,
.table select {
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 6px;
}
span#select2-chosen-6,
span#select2-chosen-7,
span#select2-chosen-8,
span#select2-chosen-9 {
    text-align: left;
}

</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<div class="container">
    <!-- Edit Button -->
    <div class="modal-header">
        <button type="button" id="add_button" class="btn btn-primary editsty">Edit</button>
    </div>

    <div class="row">
        <!-- left Column: Personal Information -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Basic Information</strong></div>
                <div class="panel-body">
                    <table class="table">
                        <input type="hidden" id="customer_id" name="customer_id">
                        <tr>
                            <td><strong>Customer Name*</strong></td>
                            <td>
                                <?php echo form_input('name', '', 'class="form-control tip" id="name" onkeypress="return onlyAlphabets1(event,this);" readonly="readonly"'); ?>
                                <span id="error2" style="color:#a94442;font-size:10px; display: none">please enter
                                    alphabets only</span>
                            </td>

                        </tr>
                        <tr>
                            <td><strong>Phone *</strong></td>
                            <td>
                                <input type="tel" name="phone" class="form-control disebledForm" required="required"
                                    id="phone" onkeypress="return IsNumeric(event, this)" maxlength="10" readonly>
                                <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter
                                    numbers only</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>
                                <input type="email" name="email" class="form-control disebledForm" id="email"
                                    pattern="^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$">
                                <span id="email-errorss" style="color:#a94442; font-size:12px; text-align:left; display:none; margin-top: 4px; ">Please enter
                                    a valid email address</span>
                            </td>
                            <!-- <td>
                                <input type="email" name="email" class="form-control disebledForm" id="email"
                                    pattern="^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$">
                                <span id="email-errorss" style="color:#a94442; font-size:12px; text-align:left; display:none; margin-top: 4px; display: block;">
                                    Please enter a valid email address
                                </span>
                            </td> -->


                        </tr>
                        <tr>
                            <td><strong>DOB</strong></td>
                            <td>
                            <?php echo form_input('dob', '', 'type="text" class="form-control disebledForm input-tip date" id="dob" placeholder="YYYY-MM-DD" pattern="(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])" maxlength="10" title="Enter a valid date in YYYY-MM-DD format (Year must be between 1900-2099)" required'); ?>                            </td>
                        </tr>
                        <tr>
                            <td><strong>Anniversary</strong></td>
                            <td>
                            <?php echo form_input('anniversary', '', 'type="text" class="form-control disebledForm input-tip date" id="anniversary" placeholder="YYYY-MM-DD" pattern="(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])" maxlength="10" title="Enter a valid date in YYYY-MM-DD format (Year must be between 1900-2099)" required'); ?>                            </td>
                        </tr>
                            <td><strong>Address</strong></td>
                            <td>
                                <?php echo form_input('address', '', 'class="form-control disebledForm" id="address"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Country</strong></td>
                            <td>
                                <select name="country" id="country_name" class="form-control disebledForm">
                                    <option value="">Select Country</option>
                                    <?php foreach ($country as $country_val) { ?>
                                    <option value="<?= $country_val->name ?>"
                                        <?= (TRUE) ? 'selected' : '' ?>>
                                        <?= $country_val->name ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            
                            <td><strong>State</strong></td>
                            <td>
                                <!-- <select name="state" id="state" class="form-control disebledForm">
                                    <option value="">Select State</option>
                                    <?php foreach ($states as $state) { ?>
                                    <option value="<?= $state->id ?>"
                                        <?= (TRUE) ? 'selected' : '' ?>>
                                        <?= $state->name . '~' . $state->code ?>
                                    </option>
                                    <?php } ?>
                                </select> -->
                                <script>
                                    var states = <?= json_encode($states) ?>;
                                    console.log(states);
                                </script>
                                
                                <select name="state" class="form-control disebledForm" id="state_id">
                                    <option value="">Select state</option>
                                    <?php foreach ($states as $state) { ?>
                                        <option value="<?= $state->name ?>" 
                                            <?= (TRUE) ? 'selected' : '' ?>>
                                            <?= $state->name . '~' . $state->code ?>
                                        </option>
                                    <?php } ?>
                                </select>

                            </td>
                        </tr>
                        <tr>
                            <td><strong>City</strong></td>
                            <td>
                                <input type="text" name="city" id="city_name" class="form-control disebledForm">
                            </td>
                        </tr>
                        <tr>
                        <td><strong>Zip Code</strong></td>
                            <td>
                                <input type="text" name="postal_code" id="postal_code"
                                    class="form-control disebledForm" pattern="\d{6}">
                                <span id="postal_code-errorss" 
                                    style="color:#a94442; font-size:12px; text-align:left; display:none; margin-top:4px; ">
                                    Please enter a valid Zip number
                                </span>
                            </td>

                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- Right Column: Details -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Others Details</strong></div>
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <td><strong>Price Group</strong></td>
                            <td>
                                <select name="price_group_id" class="form-control disebledForm" id="price_group">
                                    <option value="">Select Price Group</option>
                                    <?php foreach ($price_groups as $group) { ?>
                                    <option value="<?= $group->id ?>"
                                        <?= ($group->id == $customer->price_group_id) ? 'selected' : '' ?>>
                                        <?= $group->name ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <!-- <?php echo form_input('price_group', '', 'class="form-control disebledForm tip" id="price_group"'); ?> -->
                            </td>
                        <tr>
                            <td><strong>Company Name</strong></td>
                            <td><?php echo form_input('company', '', 'class="form-control disebledForm tip" id="company"'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Members Card No</strong></td>
                            <td>
                                <?php echo form_input('ccf1', '', 'class="form-control disebledForm tip" id="ccf1"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Customer Group </strong></td>
                            <td>

                            <select name="customer_group_id" class="form-control disebledForm" id="customer_groupId">
                                <option value="">Select Price Group</option>
                                <?php foreach ($customer_groups as $group) { ?>
                                    <option value="<?= $group->id ?>">
                                        <?= $group->name ?>
                                    </option>
                                <?php } ?>
                            </select>
                           

                                <!-- <?php echo form_input('customer_group', '', 'class="form-control disebledForm tip" id="customer_group"'); ?> -->
                            </td>
                        </tr>
                        <tr>
                        <td><strong>VAT/TIN #</strong></td>
                        <td>
                            <?php echo form_input('vat_no', '', 'class="form-control disebledForm tip" id="vat_no" pattern="[0-9A-Za-z]{8,15}"'); ?>
                            <div id="vat_no-errorss" style="color:#a94442; font-size:12px; text-align:left; display: none; margin-top: 5px;">
                                Please enter a valid VAT/TIN number (8-15 alphanumeric characters)
                            </div>
                        </td>

                        <tr>
    <td><strong>GSTIN (GST #)</strong></td>
    <td>
        <?php echo form_input('gstn_no', '', 'class="form-control disebledForm tip" id="gstn_no" pattern="[0-9A-Z]{15}"'); ?>
        <div id="gstn_no-errorss" style="color:#a94442; font-size:12px; text-align:left; display: none; margin-top: 5px;">
            Please enter a valid GSTIN number (15 alphanumeric characters)
        </div>
    </td>
</tr>



                        <tr>
                        <td><strong>PAN #</strong></td>
                        <td>
                            <?php echo form_input('pan_no', '', 'class="form-control disebledForm tip" id="pan_no" pattern="[A-Z]{5}\d{4}[A-Z]{1}"'); ?>
                            <div id="pan_no-errorss" style="color:#a94442; font-size:12px; text-align:left; display: none; margin-top: 5px;">
                                Please enter a valid PAN number
                            </span>
                        </td>



                        </tr>
                        <td><strong>State Code</strong></td>
                        <td>
                            <input type="text" name="state_code" value="<?= $biller->state_code ?>" id="state_code"
                                class="form-control disebledForm" readonly>
                        </td>
                        </tr>
                        <!-- <tr>
                            <td><strong>Older Child's Birthday</strong></td>
                            <td><?php echo form_input('dob_child1', '', 'class="form-control disebledForm input-tip date" id="dob_child1"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Younger Child's Birthday</strong></td>
                            <td><?php echo form_input('dob_child2', '', 'class="form-control disebledForm input-tip date" id="dob_child2"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Father's Birthday</strong></td>
                            <td><?php echo form_input('dob_father', '', 'class="form-control disebledForm input-tip date" id="dob_father"'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Mother's Birthday</strong></td>
                            <td><?php echo form_input('dob_mother', '', 'class="form-control disebledForm input-tip date" id="dob_mother"'); ?>
                            </td>
                        </tr> -->
                        <tr class="hidden-rows">
                            <td><strong>Private Key</strong></td>
                            <td>---</td>
                        </tr>
                        <tr class="hidden-rows">
                            <td><strong>Reset E-shop Password</strong></td>
                            <td>
                                <input type="password" name="eshop_password" class="form-control disebledForm"
                                    placeholder="Leave blank if you don't want to reset">
                            </td>
                        </tr>
                        <tr class="hidden-rows">
                            <td><strong>Sync Data</strong></td>
                            <td>
                                <select name="sync_data" class="form-control disebledForm">
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.hidden-rows').hide();
});
</script>
<script>
$(document).on('input', function () {
    validateFields(); // Call validation function on input
});

$('#paymentModal').on('shown.bs.modal', function () {
    validateFields(); // Validate when the modal opens
});

function validateFields() {
    let isValid = true;

    // Specify the IDs of the fields you want to validate  
    let fieldIds = ['postal_code', 'email', 'pan_no', 'vat_no', 'gstn_no']; // Remove duplicate postal_code

    fieldIds.forEach(function (id) {
        let $field = $('#' + id);
        let $errorSpan = $('#' + id + '-errorss');

        if ($field.length && !$field[0].checkValidity()) {
            $errorSpan.show();
            isValid = false;
        } else {
            $errorSpan.hide();
        }
    });

    // Enable/disable the save button based on form validity
    $('#add_button').prop('disabled', !isValid);
}


// function getStates(countryId) {
//         $.ajax({
//             type: 'GET',
//             dataType: 'json',
//             url: '<?= base_url('customers/getstates') ?>', 
//             data: { 'country': countryId },
//             success: function(response) {
//                 // console.log('response');
//                 // console.log(response);
//                 // console.log('response');

//                 if (response.status == 'success') {
//                     var stateOptions = '<option value="">Select State</option>';
//                     $.each(response.data, function(index, state) {
//                         stateOptions += `<option value="${state.id}">${state.name}</option>`;
//                     });
//                     $("#state").html(stateOptions);
//                 } else {
//                     $("#state").html('<option value="">Select State</option>');
//                 }
//             }
//         });
//     }
    function getStates(countryId) {
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '<?= base_url('customers/getstatesCrm') ?>',
            data: {'country': countryId},
            success: function (response) {
            console.log("States: ", response);
            if (response.status === 'success') {
                // console.log('response.data');
                // console.log(response.data);
                // $('#state_id').html(response.data);

                let selectedState = $('#selected_state').val();
                if (selectedState) {
                    $('#state_id').val(selectedState).trigger('change');;
                }
            } else {
                // $('#state_id').html(response.data);
            }
        }
    });
    }

        $('#country_name').on('change', function() {
            var countryId = $(this).val();
            getStates(countryId);
        });


    $('#state_id').on('click', function() {
        var selectedValue = $(this).find('option:selected').text(); 
    var selectedState = selectedValue; 
        set_state(selectedState);
        
    });
    // });
    function set_state(state) {
        if (state == 'other' || state == '') {

            $('#state_code').attr('readonly', false);
            $('#state_id').attr('readonly', false);

            $('#state_code').val('');
            // $('#state_id').val('');
        } else {
            let str = state;
            const myArr = str.split('~');

            $('#state_code').val(myArr[1]);
            $('#state_code').attr('readonly', true);
            // $('#state').attr('readonly', true);
        }
    }
    $('#pan_no').on('input', function () {
        $(this).val($(this).val().toUpperCase());
    });
    $(document).on('input', '#gstn_no', function() {
    $(this).val($(this).val().toUpperCase()); // Converts input to uppercase
});

</script>