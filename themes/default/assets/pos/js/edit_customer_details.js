$(document).ready(function () {

    // $('#add_button').on('click', function () {
    //     var formData = {
    //         name: $('#customers_name').val(),
    //         priceGroup: $('#price_group').val(),
    //         companyName: $('#company').val(),
    //         membersCardNo: $('#ccf1').val(),
    //         customerGroup: $('#customer_group').val(),
    //         vatNo: $('#vat_no').val(),
    //         gstnNo: $('#gstn_no').val(),
    //         panNo: $('#pan_no').val(),
    //         stateCode: $('#state_code').val(),
    //         dobChild1: $('#dob_child1').val(),
    //         dobChild2: $('#dob_child2').val(),
    //         dobFather: $('#dob_father').val(),
    //         dobMother: $('#dob_mother').val(),
    //         eshopPassword: $("input[name='eshop_password']").val(),
    //         syncData: $("select[name='sync_data']").val(),
    //         phone: $('#phone').val(),
    //         email: $('#email').val(),
    //         address: $('#address').val(),
    //         country: $('#add_country').val(),
    //         stateName: $('#statename').val(),
    //         city: $('#city').val(),
    //         postalCode: $('#postal_code').val()
    //     };
    //     localStorage.setItem('formData', JSON.stringify(formData));
    //     toastr.success('Customer Details Save Successfully');
    //     setTimeout(function() {
    //         toastr.clear();  
    //     }, 3000);
    // });
    $('.disebledForm').prop('disabled', true);
    $('#add_button').click(function () {
        var buttonText = $(this).text();
        if (buttonText === 'Edit') {
            $(this).text('Save');

            $('.disebledForm').prop('disabled', false);
        } else if (buttonText === 'Save') {

            $(this).text('Edit');

            $('.disebledForm').prop('disabled', true);
            // Collect all form data
            function formatDate(inputDate) {
                if (!inputDate) return ''; // Return empty if no value

                var parts = inputDate.split('/'); // Split by "/"
                if (parts.length === 3) {
                    return parts[2] + '-' + parts[1] + '-' + parts[0]; // Rearrange to YYYY-MM-DD
                }
                return inputDate; // Return original if format is incorrect
            }
            
            var formData = {
                id: $('#customer_id').val(),
                name: $('#name').val(),
                price_group_id: $('#price_group').val(),
                // price_group_name: $('[name="area"]').text(),
                price_group_name: $('#price_group option:selected').text(),
                company: $('#company').val(),
                customer_group_id: $('#customer_groupId').val(),
                // customer_group_name: $('[name="area"]').text(),
                customer_group_name: $('#customer_groupId option:selected').text(),
                cf1: $('#ccf1').val(),
                vat_no: $('#vat_no').val(),
                gstn_no: $('#gstn_no').val(),
                pan_card: $('#pan_no').val(),
                state_code: $('#state_code').val(),
                dob_child1: $('#dob_child1').val(),
                dob_child2: $('#dob_child2').val(),
                dob_father: $('#dob_father').val(),
                dob_mother: $('#dob_mother').val(),
                password: $("input[name='eshop_password']").val(),
                is_synced: $("select[name='sync_data']").val(),
                phone: $('#phone').val(),
                email: $('#email').val(),
                dob: formatDate($('#dob').val() || ''),
                anniversary: formatDate($('#anniversary').val()),
                address: $('#address').val(),
                country: $('#country_name').val(),
                state: $('#state_id').val(),
                city: $('#city_name').val(),
                postal_code: $('#postal_code').val(),
            };
            localStorage.setItem('formData', JSON.stringify(formData));
            updateData(formData);
            setTimeout(function () {
                toastr.clear();
            }, 3000);
        }
    });

    $('.final-submit-btn').click(function (e) {
        e.preventDefault();
        var formData = JSON.parse(localStorage.getItem('formData')) || [];
        var formData = JSON.stringify(formData);
        $('#profile_details').val(formData);
        $('#submitForm').submit();
    });

});

$('#payment').click(function () {
    $('#showDivButton1').trigger('click');
    var getname = $('#customer_name').val();
    var phoneNumber = (getname).match(/\((\d+)\)/)[1];
    $.ajax({
        url: site.base_url + "customers/getCustomerDetails",
        type: "POST",
        data: { 'phone': phoneNumber },
        dataType: "json", 
        success: function(response) {
            retrieveFormData(response);
        },
        error: function () {
            alert("Error occurred while fetching data!");
        }
    });
});

function retrieveFormData(formData) {
    var cust_grp_id = 0;
    $.ajax({
        url: site.base_url + "pos/getCompanyDetails",
        type: "POST",
        data: { customer_id: formData.id },
        dataType: "json",
        success: function (response) {
           
            cust_grp_id = response.customer_group_id;
            $("#customer_groupId").val(response.customer_group_id).change();
            if (formData) {
                customer_id = formData.id || ''; // Assign to global variable
                $('#customer_id').val(customer_id);
                $('#country_name').val(response.country || '').trigger('change'); // Default to empty string if not defined
                $('#state_id').val(response.state).trigger('change');
                $('#state_code').val(formData.state_code || ''); // 'state_code' in the response
                $('#city_name').val(formData.city || ''); // 'city' in the response
                $('#postal_code').val(formData.postal_code || ''); // 'postal_code' in the response               

                function formatDate(dateStr) {
                    if (!dateStr) return ''; // Return empty string if no date
                    let parts = dateStr.trim().split('-'); // Trim and split by '-'

                    if (parts.length === 3) {
                        let formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
                        // console.log("Formatted Date:", formattedDate); // Debugging
                        return formattedDate;
                    }

                    return dateStr; // Return original if not in expected format
                }

                var dob_date = (formatDate(formData.dob));
                $('#dob').val(dob_date);
                $('#anniversary').val(formatDate(formData.anniversary || ''));

                $('#dob_child1').val(formData.dob_child1 || ''); // 'dob_child1' in the response
                $('#dob_child2').val(formData.dob_child2 || ''); // 'dob_child2' in the response
                $('#dob_father').val(formData.dob_father || ''); // 'dob_father' in the response
                $('#dob_mother').val(formData.dob_mother || ''); // 'dob_mother' in the response
                $('#ccf1').val(formData.cf1 || '');
                // $('#country').val(formData.country || '');        
                $('#address').val(formData.address || '');
                $('#price_group').val(formData.price_group_id || '').trigger("change");
                $('#award_points').val(formData.award_points || '');
                $('#group_name').val(formData.group_name || '');
                $('#vat_no').val(formData.vat_no || '');
                $('#pan_no').val(formData.pan_card || '');
                // Add additional fields as needed
                $('#company').val(formData.company || ''); // 'company' in the response
                $('#phone').val(formData.phone || ''); // 'phone' in the response
                $('#email').val(formData.email || ''); // 'email' in the response
                $('#name').val(formData.name || ''); // 'name' in the response
                $('#gstn_no').val(formData.gstn_no || ''); // 'gstn_no' in the response
                $('#logo').val(formData.logo || ''); // 'logo' in the response (if needed)

            } else {
                console.log('No form data found');
            }

        },
        error: function () {
            alert("Error occurred while fetching data!");
        }
    });





}
function updateData(formData) {

    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'post',
        url: site.base_url + "customers/save_customer",
        data: {
            'formData': formData
        },  
        success: function (response) {
            console.log(response);
            // alert(response);
            // alert("response");
            if (response.status) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function () {
            toastr.error("Something went wrong!");
        }
    });
}
///////// This function create for list customer screen edit action 
function customer_details(phone) {
    var phoneNumber = phone;
    $.ajax({
        url: site.base_url + "customers/getCustomerDetails",
        type: "POST",
        data: { 'phone': phoneNumber },
        dataType: "json",
        success: function (response) {
            console.log(response);
            retrieveFormData(response);
        },
        error: function () {
            alert("Error occurred while fetching data!");
        }
    });
}
$('#').click(function () {
    $('#showDivButton1').trigger('click');

});