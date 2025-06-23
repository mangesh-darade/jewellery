$(document).ready(function () {
  let editIndex = null;
  $("#submitBtn").show();
  $("#submitBtn").click(function () {
    localStorage.removeItem("events");
    var relation = $("#relation").val();
    var customer_id = $("#customer_id").val();
    var name = $("#personName").val();
    var eventType = $("#event_type").val();
    var date = $("#date").val();
    if (relation && eventType && date && name) {
      var eventData = {
        relation_id: $("#relation").val(),
        event_id: $("#event_type").val(),
        name: name,
        companies_id: customer_id,
        date: date,
      };
      CustomerFamilyRelationDataSave(eventData);
    }
  });
  function CustomerFamilyRelationDataSave(eventData) {
    var submitBtn = $("#submitBtn");
    submitBtn.prop("disabled", true).hide();
    $.ajax({
      url: site.base_url + "pos/CustomerFamilyRelationDataSave",
      type: "POST",
      data: { eventData: eventData },
      dataType: "json",
      success: function (response) {
        // toastr.success('added Successfully');
        toastr.success('added Successfully', '', {
          timeOut: 300
        });
        var customer_id = $("#customer_id").val();
        getCustomerRelationDetailById(customer_id);
        setTimeout(function () {
          submitBtn.prop("disabled", false).show();
      }, 0.001);
      },
      error: function () {
        toastr.success('error occured while fatching the data');
        setTimeout(function () {
          submitBtn.prop("disabled", false).show();
      }, 0.001);
      }
    });
  }

});
function getCustomerRelationDetailById() {
  var customer_id = $("#customer_id").val();
  $.ajax({
    url: site.base_url + "pos/getCustomerRelationDetailById",
    type: "POST",
    data: { customer_id: customer_id },
    dataType: "json",
    success: function (response) {
      populateTable(response);
    },
    error: function () {
      toastr.success('error occured');
    }
  });
  function populateTable(data) {
    $("#relation").val('').trigger("change");
    $("#personName").val('');
    $("#event_type").val('').trigger("change");
    $("#date").val('');
    let tableBody = $("#gridContent");
    tableBody.empty(); // Clear previous data

    data.forEach((item, index) => { // Added `index` as a parameter
      let row = `<tr>
                    <td style = "text-align : left;">${item.relation_name}</td>
                    <td style = "text-align : left;">${item.name}</td>
                    <td style = "text-align : left;">${item.event_name}</td>
                    <td  style = "text-align : center;">${item.date}</td>
                    <td class="grid-actions">
                        <button class="edit-btn" id = "edit_btn" data-index="${item.id}">Edit</button>
                        <button class="delete-btn" data-index="${index}" id=${item.id}>Delete</button>
                    </td>
                   </tr>`;
      tableBody.append(row);
    });
  }
}
$(document).on("click", "#editButton", function () {
  // alert("kdhshdgs\gdhsd")
  let formData = {
    compaines_id: $("#customer_id").val(),
    relation: $("#relation").val(),
    personName: $("#personName").val(),
    event_type: $("#event_type").val(),
    date: $("#date").val(),
    id: $("#customer_details_id").val(),
  };
  for (let key in formData) {
    if (formData[key] === "") {
      $("#myButton").prop("disabled", true);
      return; // Stop the function if any field is blank
    } else {
      $("#myButton").prop("disabled", false);
    }
  }
  $.ajax({
    url: "pos/save_data",
    type: "POST",
    data: formData,
    success: function (response) {
      toastr.success('saved Successfully', '', {
        timeOut: 300 
      });
      // toastr.success('saved Successfully');
      $("#editButton").hide();
      $("#submitBtn").show();
      getCustomerRelationDetailById();
    },
    error: function () {
      toastr.success('error occured');

    }
  });
});
$(document).on("click", ".delete-btn", function (event) {
  if (confirm) {
    $.ajax({
      url: site.base_url + "pos/CustomerFamilyRelationDataDelete",
      type: "POST",
      data: { 'details_id': event.target.id },
      success: function (response) {

        toastr.success('Delete Successfully', '', {
          timeOut: 400 // The notification will disappear after 5 seconds (5000 milliseconds)
        });
        getCustomerRelationDetailById();

      },
      error: function () {
        toastr.success('Error occured');
      }
    });
  }
});
$(document).on("click", "#edit_btn", function () {

  $("#submitBtn").hide();
  $("#editButton").show();
  $("#relation").val('').trigger("change");
  $("#personName").val('');
  $("#event_type").val('').trigger("change");
  $("#date").val('');


  let index = $(this).data("index");

  $.ajax({
    url: "pos/getDetailData",
    type: "POST",
    data: { 'detail_id': index },
    success: function (responce) {
      var parsedResponse = JSON.parse(responce);
      $("#relation").val("").trigger("change");
      $("#personName").val("");
      $("#event_type").val("").trigger("change");
      $("#date").val("");
      $("#customer_details_id").val("");
      // Populate fields with new data
      $("#relation").val(parsedResponse.relation_id).trigger("change");
      $("#personName").val(parsedResponse.name);
      $("#event_type").val(parsedResponse.event_id).trigger("change");
      $("#date").val(parsedResponse.date);
      $("#customer_details_id").val(parsedResponse.id);
      // Show modal
      $("#editModal").data("index", index).modal("show");
    },
    error: function () {
      toastr.success('error occured');
    }
  });
  //   let item = data[index];


});
$(document).ready(function () {
  // Show div1 when button 1 is clicked
  $(".payments_mainsection").show();
  $("#showDivButton1").click(function () {
    $(".payments_mainsection").show();
    $(".customerDetails").hide(); // Hide the other div
    $(".family_relation").hide(); // Hide the other div
    $(".profile").hide(); // Hide the other div
  });

  // Show div2 when button 2 is clicked
  $("#showDivButton2").click(function () {
    $(".customerDetails").show();
    $(".profile").show();
    $(".payments_mainsection").hide(); // Hide the other div
    $(".family_relation").hide(); // Hide the other div
  });
  $("#showDivButton3").click(function () {
    $(".customerDetails").show();
    $(".profile").show();
    $(".payments_mainsection").hide(); // Hide the other div
    $(".family_relation").hide(); // Hide the other div
  });
  $("#showDivButton4").click(function () {
    getCustomerRelationDetailById();
    $(".family_relation").show();
    $(".customerDetails").show();
    $(".profile").hide(); // Hide the other div
    $(".payments_mainsection").hide(); // Hide the other div
  });
});
$(document).ready(function () {
  // When the "Add" button is clicked, validate the form
  $('#submitBtn').on('click', function () {
    var isValid = true;

    // Clear previous error messages
    $('.error-message').hide();

    // Validate each field
    if ($('#relation').val() === '') {
      $('#relation-error').show();
      isValid = false;
    }

    if ($('#personName').val().trim() === '') {
      $('#name-error').show();
      isValid = false;
    }

    if ($('#event_type').val() === '') {
      $('#event_type-error').show();
      isValid = false;
    }

    if ($('#date').val() === '') {
      $('#date-error').show();
      isValid = false;
    }
    if (isValid) {
      console.log('Form is valid');
    } else {
      console.log('Form contains errors');
    }
  });

  $('#relation').on('change', function () {
    if ($(this).val() !== '') {
      $('#relation-error').hide();
    }
  });

  $('#personName').on('keyup', function () {
    if ($(this).val().trim() !== '') {
      $('#name-error').hide();
    }
  });

  $('#event_type').on('change', function () {
    if ($(this).val() !== '') {
      $('#event_type-error').hide();
    }
  });

  $('#date').on('change', function () {
    if ($(this).val() !== '') {
      $('#date-error').hide();
    }
  });
});

$(document).ready(function () {
  // When the "save" button is clicked, validate the form
  $('#editButton').on('click', function () {
    var isValid = true;

    // Clear previous error messages
    $('.error-message').hide();

    // Validate each field
    if ($('#relation').val() === '') {
      $('#relation-error').show();
      isValid = false;
    }

    if ($('#personName').val().trim() === '') {
      $('#name-error').show();
      isValid = false;
    }

    if ($('#event_type').val() === '') {
      $('#event_type-error').show();
      isValid = false;
    }

    if ($('#date').val() === '') {
      $('#date-error').show();
      isValid = false;
    }
    if (isValid) {
      console.log('Form is valid');
    } else {
      console.log('Form contains errors');
      event.preventDefault();
    }
  });

  $('#relation').on('change', function () {
    if ($(this).val() !== '') {
      $('#relation-error').hide();
    }
  });

  $('#personName').on('keyup', function () {
    if ($(this).val().trim() !== '') {
      $('#name-error').hide();
    }
  });

  $('#event_type').on('change', function () {
    if ($(this).val() !== '') {
      $('#event_type-error').hide();
    }
  });

  $('#date').on('change', function () {
    if ($(this).val() !== '') {
      $('#date-error').hide();
    }
  });
});
