$(document).ready(function () {
  const isRefresh = performance.navigation.type === 1 || 
                    (performance.getEntriesByType &&
                     performance.getEntriesByType("navigation")[0]?.type === "reload");

  if (isRefresh) {
    localStorage.removeItem("denominations");
    localStorage.removeItem("returnDenominationsSaveDB");
    localStorage.removeItem("pendingAmount");
  }

  $("#paymentModal").on("shown.bs.modal", function () {
    fetchDenomination();
  });
  $("#yourDivId").hide();
  function fetchDenomination() {
    var invoiceAmount = parseInt($("#amount_1").val()) || 0;
     var invoiceAmount = ($("#amount_1").val()) || 0;
     $("#totalPayableAmount").text(invoiceAmount);
    var returnAmount = $("#returnAmount").text().replace(/[^\d.]/g, '').replace(/^\./, '');
    console.log("returnAmount", returnAmount);
    $.ajax({
      type: "GET",
      url: site.base_url + "pos/get_denominations",
      dataType: "json",
      success: function (data) {
        if (returnAmount > 0) {
          var amt = calculateReturnDenominations(returnAmount, data);
          localStorage.setItem(
            "returnDenominationsSaveDB",
            JSON.stringify(amt)
          );

          if (amt) {
            updateDenominationUI(amt);

            setTimeout(function () {
              $(".denomination-box.highlight").each(function () {
                $(this).find(".remove").removeClass("hidden").show();
              });
            }, 300);
          }
        } else {
          renderDenominations(data);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
      },
    });
  }
function calculateAmounts(isReturnMode = true) {
    let selectedAmount = 0;

    $(".denomination-box.highlight").each(function () {
        const count = parseInt($(this).find(".count").text()) || 0;
        const value = parseFloat($(this).find(".increase").data("value")) || 0;
        selectedAmount += count * value;
    });

    selectedAmount = Math.round(selectedAmount * 100) / 100;

    const depositedAmountAttr = $("#depositedAmount").attr("data-original-collected") || "0";
    const collected_amount = parseFloat(depositedAmountAttr.replace(/[^\d.]/g, "")) || 0;

    const payableAmount = parseFloat($("#totalPayableAmount").text().replace(/[^\d.]/g, "")) || 0;
    const returnAmount = collected_amount - payableAmount;

    const isCollectedMode = $("#CollectedFlag").val() === "CollectedFlag";

    let pendingAmount = 0;
    if (isCollectedMode) {
        pendingAmount = 0;
    } else if (isReturnMode) {
        pendingAmount = selectedAmount - returnAmount;
    } else {
        pendingAmount = returnAmount - selectedAmount;
    }

    pendingAmount = Math.round(pendingAmount * 100) / 100;

    // Update internal fields
    $("#selectedValue").text(selectedAmount.toFixed(2));
    $("#selectedValues").val(selectedAmount.toFixed(2));
    $("#pendingValue").text(pendingAmount.toFixed(2));
    $("#pendingValues").val(pendingAmount.toFixed(2));

    //  Update visible selected amount section (grid style)
    $("#selectedAmount").html(`
        <span style="float:left">Selected Amount:</span>
        <span style="float:right">${selectedAmount.toFixed(2)}</span>
        <div style="clear:both"></div>
    `);

    //  Sync depositedAmount visually too (optional)
    $("#depositedAmount").text(selectedAmount.toFixed(2));

    // Ensure the sections are visible
    $("#selectedAmount").show();

    //  Show/hide pending section based on mode
    if (isCollectedMode) {
        $("#pendingAmount").hide();
    } else {
        $("#pendingAmount").show();
        $("#pendingAmount").html(`
            <span style="float:left">Pending Amount:</span>
            <span style="float:right">${pendingAmount.toFixed(2)}</span>
            <div style="clear:both"></div>
        `);
    }
}



  function renderDenominations(denominations) {
    var container = $("#denomination-container");
    container.empty();

    var currentType = "";
    var grid = $('<div class="grid-container"></div>');
    denominations.forEach(function (item) {
      if (item.type !== currentType) {
        if (grid.children().length > 0) {
          container.append(grid);
          grid = $('<div class="grid-container"></div>');
        }
        container.append(`<h3 class="denomination-title">${item.type =="Bills" ? "Notes": item.type}</h3>`);
        currentType = item.type;
      }
      var formattedCurrencyValue = formatMoneys(item.currency_value);
      var denominationBox = `
      <div class="denomination-box" data-type="${item.type}"> <!-- Store the type here -->
        <span class="denom-label currency-value"> ${formattedCurrencyValue}</span>
        <div class="counter">
            <button class="decrease" data-value="${item.currency_value}" data-type="${item.type}">−</button>
            <span class="count" data-value="${item.currency_value}">0</span>
            <button class="increase" data-value="${item.currency_value}" data-type="${item.type}">+</button>
        </div>
        <button class="remove hidden" data-value="${item.currency_value}" id="cross">✖</button>
      </div>`;

      grid.append(denominationBox);
    });
    container.append(grid);

    $(".increase").click(function () {
      var countSpan = $(this).siblings(".count");
      var newValue = parseInt(countSpan.text()) + 1;
      countSpan.text(newValue);

      if(isReturnMode == true){
        let denominations = JSON.parse(localStorage.getItem("setDenominations"));
        var denominationType = $(this).data("type");
        var denominationValue = parseFloat($(this).data("value"));

        denominations.forEach(function (item) {  
          var currency = item.currency_value;
          var availableCount = Number(item.count) || 0;
          // Format currency to two decimal places
          const fixedCurrency = parseFloat(currency).toFixed(2);
          const $countElem = $(`.denomination-box .count[data-value="${fixedCurrency}"]`);
          
          if ($countElem.length) {
            const $box = $countElem.closest(".denomination-box");
            const $increase = $box.find(".increase");
            const $decrease = $box.find(".decrease");
            const $remove = $box.find(".remove"); // if you have a remove button
            const $label = $box.find(".denom-label");
            
            if (availableCount === newValue &&denominationType === item.type &&denominationValue === parseFloat(item.currency_value)) {
              // Grey out label and count
              $label.css({
                "background-color": "#eee",
                "opacity": "0.6",
                "pointer-events": "none",
              });
              $countElem.css({
                "background-color": "#eee",
                "opacity": "0.6",
                "pointer-events": "none",
              });

              // Disable increase button
              $increase.css({
                "opacity": "0.4",
                "pointer-events": "none",
              });

              // Enable decrease and remove buttons
              $decrease.css({
                "opacity": "1",
                "pointer-events": "auto",
              });

              $remove.css({
                "opacity": "1",
                "pointer-events": "auto",
              });

              // Optional: dim the box background
              $box.css({
                "background-color": "#f5f5f5"
              });
            }

          }
        });
      }
     

      var denominationBox = $(this).closest(".denomination-box");

      denominationBox.addClass("highlight");

      if (newValue > 0) {
        denominationBox.find(".remove").removeClass("hidden").show();
      }

      var denominationType = $(this).data("type");
      var denominationValue = parseFloat($(this).data("value"));
      const $countElem = $(
        `.denomination-box .count[data-value="${denominationValue}"]`
      );
      var collectedFlagValue = $('#CollectedFlag').val();
      if (collectedFlagValue == 'CollectedFlag') {
        localStorage.removeItem("pendingAmount");
      }
      let pendingAmount = JSON.parse(localStorage.getItem("pendingAmount")) || {};
     
      if (denominationType === "Bills") {
        updateDenominationCount(denominationValue, newValue, "Bills");
      } else if (denominationType === "Coins") {
        updateDenominationCount(denominationValue, newValue, "Coins");
      }
      if (isReturnMode) {
       setTimeout(function () {
        calculateAmounts();

        var pendingAmountText = $("#pendingAmount").text().replace(/[^0-9.-]+/g, "");
        var pendingAmount = parseFloat(pendingAmountText) || 0;

        // Make sure this directly updates #balance, not #pendingBalance
        $("#balance").text(formatNumber(pendingAmount));
      }, 10); // slightly longer timeout to ensure calculateAmounts() finishes
      } else {
    // This only runs when NOT in return mode
    setTimeout(function () {
        calculateAmounts();

        var returnAmount = $("#returnAmount").text().replace(/,/g, '') || 0;
        returnAmount = parseFloat(returnAmount) || 0;

        $("#returnBalance").text(formatNumber(returnAmount));
      }, 50);
      }

      var payableAmountText = $("#totalPayableAmount").text().trim();
      var payableAmount = parseFloat(payableAmountText.replace(/[^\d.-]/g, "")) || 0;
      
      $("#totalPayableAmount").text(formatNumber(payableAmount));
      
      var collectedAmount = $("#depositedAmount").text();

      collectedAmount = collectedAmount.replace(/[^\d.]/g, '');

      if (collectedAmount.startsWith('.')) {
        collectedAmount = collectedAmount.substring(1);
      }

      if (collectedAmount > payableAmount) {
        $("#denomination-container").css("border", "9px solid #FFE0A7");
        $("#clearAll").css("border", "9px solid #FFE0A7");
        $(".payment-box").css("border", "9px solid #FFE0A7");
        $(".clear-btn").removeClass("hidden").show();
        $("#Collected").attr("style", "background-color: #FFE0A7 !important; color: #000 !important;");
        $("#amount-section-container").css({ border: "9px solid #FFE0A7" });
      }
      // Add this right after
      var $returnMatButton = $("#returnAmt"); // Replace with your actual button ID

      if (parseFloat(collectedAmount) <= payableAmount) {
    $returnMatButton.prop("disabled", true).css({
        "opacity": "0.6",
        "pointer-events": "none"
    });
} else {
    $returnMatButton.prop("disabled", false).css({
        "opacity": "1",
        "pointer-events": "auto"
    });
}

    });


   $(".decrease").click(function () {
    var countSpan = $(this).siblings(".count");
    var currentCount = parseInt(countSpan.text());
  
    if (currentCount > 0) {
        var newValue = currentCount - 1;
        countSpan.text(newValue);
    
        var denominationBox = $(this).closest(".denomination-box");
    
        if (newValue === 0) {
            denominationBox.removeClass("highlight");
            denominationBox.find(".remove").addClass("hidden");
        }
    
        var hasActive = $(".denomination-box").filter(function () {
            return parseInt($(this).find(".count").text()) > 0;
        }).length > 0;
    
        if (!hasActive) {
            $("#denomination-container").css({
                border: "9px solid #E6F5FF",
            });
    
            $("#clearAll").css({
                border: "9px solid #E6F5FF",
            });
        }
    
        var denominationType = $(this).data("type");
        var denominationValue = parseFloat($(this).data("value"));
        const $countElem = $(
            `.denomination-box .count[data-value="${denominationValue}"]`
        );
    
        var collectedFlagValue = $('#CollectedFlag').val();
        if (collectedFlagValue === 'CollectedFlag') {
            localStorage.removeItem("pendingAmount");
        }
    
        let pendingAmount = JSON.parse(localStorage.getItem("pendingAmount")) || {};
        if (denominationType === "Bills") {
            updateDenominationCount(denominationValue, newValue, "Bills");
        } else if (denominationType === "Coins") {
            updateDenominationCount(denominationValue, newValue, "Coins");
        }
        
        if (isReturnMode) {
            setTimeout(function () {
                calculateAmounts();
                var pendingAmountText = $("#pendingAmount").text().replace(/[^0-9.-]+/g, "");
                var pendingAmount = parseFloat(pendingAmountText) || 0;
                $("#balance").text(formatNumber(pendingAmount));
            }, 10);
        }
        else {
            setTimeout(function () {
                calculateAmounts();
                var returnAmount = $("#returnAmount").text().replace(/,/g, '') || 0;
                returnAmount = parseFloat(returnAmount) || 0;
                $("#returnBalance").text(formatNumber(returnAmount));
            }, 50);
        }
    
        var payableAmountText = $("#totalPayableAmount").text().trim();
        var payableAmount = parseFloat(payableAmountText.replace(/[^\d.-]/g, "")) || 0;
        
        $("#totalPayableAmount").text(formatNumber(payableAmount));
        
        var collectedAmount = $("#depositedAmount").text();
    
        collectedAmount = collectedAmount.replace(/[^\d.]/g, '');
    
        if (collectedAmount.startsWith('.')) {
            collectedAmount = collectedAmount.substring(1);
        }
    
        if (parseFloat(collectedAmount) > payableAmount) {
            $("#denomination-container").css("border", "9px solid #FFE0A7");
            $("#clearAll").css("border", "9px solid #FFE0A7");
            $(".payment-box").css("border", "9px solid #FFE0A7");
            $(".clear-btn").removeClass("hidden").show();
            $("#Collected").css("background-color", "#FFE0A7");
            $("#amount-section-container").css({ border: "9px solid #FFE0A7" });
        }
    }
    
    // Check if all counts are zero
    var allZero = true;
    $(".denomination-box .count").each(function () {
        if (parseInt($(this).text()) > 0) {
            allZero = false;
            return false; // break loop
        }
    });
  
    // Prevent reset in return mode
    if (allZero && !isReturnMode) {
        $("#returnAmount").text("0.00");
        $("#balance").text("0.00");
    }
    
    var $returnMatButton = $("#returnAmt"); 
    var collectedAmount = $("#depositedAmount").text().replace(/[^\d.]/g, '') || 0;
    var payableAmount = $("#totalPayableAmount").text().replace(/[^\d.]/g, '') || 0;
    
    // Disable return button if collected <= payable OR if all counts are zero
    if (parseFloat(collectedAmount) <= parseFloat(payableAmount) || allZero) {
        $returnMatButton.prop("disabled", true).css({
            "opacity": "0.6",
            "pointer-events": "none"
        });
    } else {
        $returnMatButton.prop("disabled", false).css({
            "opacity": "1",
            "pointer-events": "auto"
        });
    }
});
  
    
    // Remove denomination when the cross button is clicked
      $(document).on("click", ".remove", function () {
    var denominationBox = $(this).closest(".denomination-box");
    var countSpan = denominationBox.find(".count");
    var currentCount = parseInt(countSpan.text()) || 0;

    if (currentCount > 0) {
        countSpan.text(0);
        denominationBox.removeClass("highlight-green");
        $(this).addClass("hidden");
    }

    // Get denomination value & type
    var denominationValue = $(this).data("value");
    var denominationType = $(this).closest('div[data-type]').data('type');

    console.log("Clicked remove:", {
        value: denominationValue,
        type: denominationType,
    });

    updateDenominationCount(denominationValue, 0, denominationType);

    // Check if all boxes in the same section are now zero
    var parentContainer = $(this).closest(".denomination-section");
    var allZero = true;

    parentContainer.find(".count").each(function () {
        if (parseInt($(this).text()) > 0) {
            allZero = false;
            return false; // exit loop early
        }
    });

    if (allZero) {
        parentContainer.css("border-color", "#E6F5FF");
        updateDenominationCount(0, 0, denominationType);
    }

    setTimeout(function () {
    calculateAmounts();

    // Check if all denominations are zero
var allZero = true;
$(".denomination-box .count").each(function () {
    if (parseInt($(this).text()) > 0) {
        allZero = false;
        return false; // break loop
    }
});

// Prevent reset in return mode
if (allZero && !isReturnMode) {
    $("#returnAmount").text("0.00");
    $("#balance").text("0.00");
    return;
}

    if (isReturnMode) {
        var pendingAmountText = $("#pendingAmount").text().replace(/[^0-9.-]+/g, "");
        var pendingAmount = parseFloat(pendingAmountText) || 0;
        $("#balance").text(formatNumber(pendingAmount));
    } else {
        var returnAmountText = $("#returnAmount").text().replace(/,/g, '') || 0;
        var returnAmount = parseFloat(returnAmountText) || 0;
        $("#balance").text(formatNumber(returnAmount));
    }
}, 50);

});


  }
  // function formatMoneys(amount) {
  //   amount = parseFloat(amount);
  //   if (isNaN(amount)) {
  //     return "Invalid amount";
  //   }
  //   let formattedAmount = amount.toFixed(2);
  //   return `د.إ ${formattedAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
  // }

  function updateDenominationUI(denominationCounts) {
    $(".count").text("0");
    $(".denomination-box").removeClass("highlight");
    $(".denomination-box .remove").addClass("hidden").hide();
  
    // Combine Coins and Bills with Coins taking priority
    const allDenoms = {};
    if (denominationCounts.Coins) {
      Object.assign(allDenoms, denominationCounts.Coins);
    }
    if (denominationCounts.Bills) {
      Object.entries(denominationCounts.Bills).forEach(([value, count]) => {
        // Only add bill value if it's not already set by coins
        if (!(value in allDenoms)) {
          allDenoms[value] = count;
        }
      });
    }
  
    console.log("allDenoms (Coins prioritized):", allDenoms);
  
    // Update matching DOM elements
    for (let value in allDenoms) {
      let count = parseInt(allDenoms[value]);
      if (count > 0) {
        // Prefer coin denomination-box if multiple exist
        let $boxes = $(".denomination-box").filter(function () {
          return $(this).find(".count").data("value") == value;
        });
  
        let $preferredBox = $boxes.filter("[data-type='coin']").first(); // Prefer coin
        if ($preferredBox.length === 0) {
          $preferredBox = $boxes.first(); // Fallback to any
        }
  
        if ($preferredBox.length) {
          $preferredBox.addClass("highlight");
          $preferredBox.find(".count").text(count);
          $preferredBox.find(".remove").removeClass("hidden").show();
        }
      }
    }
  
    // Fallback handling for old structure (optional)
    if (!allDenoms || Object.keys(allDenoms).length === 0) {
      for (var key in denominationCounts) {
        let countElement = $('.count[data-value="' + key + '"]');
  
        if (countElement.length === 0) {
          console.error("No .count element found for denomination:", key);
        } else {
          countElement.text(denominationCounts[key]);
          if (denominationCounts[key] > 0) {
            countElement.closest(".denomination-box").addClass("highlight");
          } else {
            countElement.closest(".denomination-box").removeClass("highlight");
          }
        }
      }
    }
  
    // Recalculate total if needed
    var returnAmount = $("#returnAmount").text().replace(/,/g, '') || 0;
    if (returnAmount > 0) {
      calculateTotal();
    }
  }
  // function calculateReturnDenominations(amount, denominations) {
  //   var returnDenominations = {
  //     Coins: {},
  //     Bills: {},
  //   };

  //   localStorage.setItem("setDenominations", JSON.stringify(denominations));
  //   // Sort denominations by currency value (highest to lowest)
  //   denominations.sort((a, b) => b.currency_value - a.currency_value);
  //   let originalAmount = amount; // Save the original amount for final comparison

  //   denominations.forEach(function (item) {
  //     var currency = item.currency_value;
  //     var availableCount = Number(item.count) || 0;
  //     var type = item.type;
  //     var requiredCount = Math.floor(amount / currency); // How many are needed

  //     // Format currency to two decimal places
  //     const fixedCurrency = parseFloat(currency).toFixed(2);

  //     const $countElem = $(`.denomination-box .count[data-value="${fixedCurrency}"]`);
  //     if ($countElem.length) {
  //       const $box = $countElem.closest(".denomination-box");
  //       const $increase = $box.find(".increase");
  //       const $decrease = $box.find(".decrease");
  //       const $label = $box.find(".denom-label");

  //       if (
  //         (availableCount < requiredCount && requiredCount > 0) ||
  //         availableCount === 0
  //       ) {
  //         // Apply new style for insufficient or zero denominations
  //         $box.css({
  //           "background-color": "#eee",
  //           "opacity": "0.6",
  //           "pointer-events": "none",
  //         });
  //       } else {
  //         // Restore original style
  //         $box.css({
  //           "background-color": "",
  //           "opacity": "1",
  //           "pointer-events": "auto",
  //         });
  //       }
  //     }

  //     // Calculate how much to use
  //     if (requiredCount > 0 && availableCount > 0) {
  //       var usedCount = Math.min(requiredCount, availableCount);
  //       if (type === "Coins") {
  //         returnDenominations.Coins[currency] = usedCount;
  //       } else if (type === "Bills") {
  //         returnDenominations.Bills[currency] = usedCount;
  //       }
  //       amount -= usedCount * currency;
  //     }
  //   });
    
  //   if (amount > 0) {
  //     let pending = originalAmount - (originalAmount - amount);
  //     localStorage.setItem("pendingAmount", JSON.stringify(pending));
  //     // fetchDenomination(pending); // Uncomment if needed
  //   }

  //   return returnDenominations;
  // }

function calculateReturnDenominations(amount, denominations) {
  var returnDenominations = {
    Coins: {},
    Bills: {},
  };

  localStorage.setItem("setDenominations", JSON.stringify(denominations));

  // Sort denominations by currency value (highest to lowest)
  denominations.sort((a, b) => b.currency_value - a.currency_value);
  let originalAmount = amount; // Save the original amount for final comparison

  denominations.forEach(function (item) {
    var currency = item.currency_value;
    var availableCount = Number(item.count) || 0;
    var type = item.type;
    var requiredCount = Math.floor(amount / currency); // How many are needed

    // Format currency to two decimal places
    const fixedCurrency = parseFloat(currency).toFixed(2);

    const $countElem = $(`.denomination-box .count[data-value="${fixedCurrency}"]`);
    if ($countElem.length) {
      const $box = $countElem.closest(".denomination-box");
      const $increase = $box.find(".increase");
      const $decrease = $box.find(".decrease");
      const $remove = $box.find(".remove"); // if you have a remove button
      const $label = $box.find(".denom-label");

      if (
        (availableCount < requiredCount && requiredCount > 0) ||
        availableCount === 0
      ) {
        //  Grey out label and count
        $label.css({
          "background-color": "#eee",
          "opacity": "0.6",
          "pointer-events": "none",
        });
        $countElem.css({
          "background-color": "#eee",
          "opacity": "0.6",
          "pointer-events": "none",
        });

        //  Disable increase button
        $increase.css({
          "opacity": "0.4",
          "pointer-events": "none",
        });

        //  Enable decrease and remove buttons
        $decrease.css({
          "opacity": "1",
          "pointer-events": "auto",
        });

        $remove.css({
          "opacity": "1",
          "pointer-events": "auto",
        });

        // Optional: dim the box background
        $box.css({
          "background-color": "#f5f5f5"
        });

      } else {
        //  Restore everything
        $label.add($countElem).css({
          "background-color": "",
          "opacity": "1",
          "pointer-events": "auto",
        });

        $increase.add($decrease).add($remove).css({
          "opacity": "1",
          "pointer-events": "auto",
        });

        $box.css({
          "background-color": "",
        });
      }
    }

    // Calculate how much to use
    if (requiredCount > 0 && availableCount > 0) {
      var usedCount = Math.min(requiredCount, availableCount);
      if (type === "Coins") {
        returnDenominations.Coins[currency] = usedCount;
      } else if (type === "Bills") {
        returnDenominations.Bills[currency] = usedCount;
      }
      amount -= usedCount * currency;
    }
  });

  if (amount > 0) {
    let pending = originalAmount - (originalAmount - amount);
    localStorage.setItem("pendingAmount", JSON.stringify(pending));
    // fetchDenomination(pending); // Uncomment if needed
  }

  return returnDenominations;
}

// 👇 Add this after the DOM is ready
$(document).on("click", ".denomination-box .decrease", function () {
  const $box = $(this).closest(".denomination-box");
  const $countElem = $box.find(".count");
  const $increase = $box.find(".increase");
  const $decrease = $box.find(".decrease");
  const $remove = $box.find(".remove");
  const $label = $box.find(".denom-label");

  const currency = parseFloat($countElem.data("value")).toFixed(2);

  // Parse current count
  let currentCount = parseInt($countElem.text()) || 0;

    const stored = JSON.parse(localStorage.getItem("setDenominations") || "[]");
    const denom = stored.find(d => parseFloat(d.currency_value).toFixed(2) === currency);
    const availableCount = denom ? Number(denom.count) : 0;
    
  if (currentCount > 0) {
    currentCount--;
    if (currentCount < availableCount) {
      // Re-enable increase button
      $increase.css({
        "opacity": "1",
        "pointer-events": "auto"
      });

      // Restore label and count styles
      $label.add($countElem).css({
        "background-color": "",
        "opacity": "1",
        "pointer-events": "auto"
      });

      // Restore button styles
      $decrease.add($remove).css({
        "opacity": "1",
        "pointer-events": "auto"
      });

      // Restore box background
      $box.css({
        "background-color": ""
      });
    }

  }else{
    // condition for current count is zero
    if (currentCount < availableCount) {
      // Re-enable increase button
      $increase.css({
        "opacity": "1",
        "pointer-events": "auto"
      });

      // Restore label and count styles
      $label.add($countElem).css({
        "background-color": "",
        "opacity": "1",
        "pointer-events": "auto"
      });

      // Restore button styles
      $decrease.add($remove).css({
        "opacity": "1",
        "pointer-events": "auto"
      });

      // Restore box background
      $box.css({
        "background-color": ""
      });
    }
  }
});

  /////////////////////////////////////////////////////
  function calculateTotal() {
    var total = 0;
    var returnAmount = $("#returnAmount").text().replace(/,/g, '') || 0;

    $(".denomination-box").each(function () {
      var currencyValue = parseFloat(
        $(this).find(".denom-label").text()
          .replace("د.إ", "")
          .replace(/,/g, "") // Remove commas
          .trim()
      );
      var count = parseInt($(this).find(".count").text());

      if (!isNaN(currencyValue) && !isNaN(count)) {
        total += currencyValue * count;
      }
    });


    var pendingAmount = returnAmount - total;
    $("#selectedValue").text((total));
    $("#selectedValues").text((total));
    $("#pendingValue").text((pendingAmount));
    $("#pendingValues").val((pendingAmount));
    var totalPayable = $("#totalPayableAmount").text();
    if (total > totalPayable) {
      // Show the amount div and hide the payment box
      $("#yourDivId").hide();
      $(".payment-box").show();
    }
    //   var totalCollected = $('#returnAmount').text();
    //   var totalPayable = $('#totalPayableAmount').text();
    //   var returnAmount = parseFloat($("#returnAmount").text()) || 0;
    //    var pendingAmount = totalCollected-total;
    //   $("#selectedValue").text((total));
    //   $("#pendingValue").text((pendingAmount));

    return total;
  }

  function saveDenominationCount(currencyValue, count, denominationType) {
    let denominations = JSON.parse(localStorage.getItem("denominations")) || {};

    // Initialize Bills and Coins if they don't exist
    if (!denominations.Bills) denominations.Bills = {};
    if (!denominations.Coins) denominations.Coins = {};

    if (!denominationType || !denominations[denominationType]) {
      console.error("Invalid denominationType:", denominationType);
      return;
    }

    const key = parseFloat(currencyValue).toFixed(2);

    if (count == 0) {
      delete denominations[denominationType][key];
    } else {
      denominations[denominationType][key] = count;
    }

    // Update or remove from localStorage
    if (Object.keys(denominations.Bills).length || Object.keys(denominations.Coins).length) {
      
        if(isReturnMode == true && Restart !== true){
          localStorage.setItem("denominations_return", JSON.stringify(denominations));
        }else{
          localStorage.setItem("denominations", JSON.stringify(denominations));
      }
    } else {
      // localStorage.removeItem("denominations");
    }

    console.log("Saved to localStorage:", denominations);
  }


  let denominationData = { Bills: {}, Coins: {} };

  // Load denomination data from localStorage on page load
  function loadDenominationData() {
    let savedData = JSON.parse(localStorage.getItem("denominations")) || {};

    // Update denominationData with the saved values from localStorage
    denominationData.Bills = savedData.Bills || {};
    denominationData.Coins = savedData.Coins || {};

  }

  // Call loadDenominationData when the page loads
  $(document).ready(function () {
    loadDenominationData();
  });


  // Function to handle denomination updates dynamically
  function updateDenominationCount(currencyValue, count, denominationType) {
    if (!denominationType || (denominationType !== "Bills" && denominationType !== "Coins")) {
      console.error("Invalid or missing denominationType:", denominationType);
      return;
    }

    if (!window.denominationData) {
      window.denominationData = { Bills: {}, Coins: {} };
    }

    if (!denominationData[denominationType]) {
      denominationData[denominationType] = {};
    }

    const key = parseFloat(currencyValue).toFixed(2);

    // Update the count in denominationData
    denominationData[denominationType][key] = count;

    console.log("Updated denominationData:", denominationData);

    // Save updated data to localStorage
    saveDenominationCount(currencyValue, count, denominationType);

    // Calculate total denomination
    // var invoiceAmount = parseInt($("#amount_1").val()) || 0;
    var invoiceAmount = ($("#amount_1").val()) || 0;
    $("#totalPayableAmount").text(invoiceAmount);

    var totalDenomination = 0;

    // Loop through Bills
    for (let key in denominationData.Bills) {
      totalDenomination += parseFloat(key) * denominationData.Bills[key];
    }

    // Loop through Coins
    for (let key in denominationData.Coins) {
      totalDenomination += parseFloat(key) * denominationData.Coins[key];
    }

    // Update UI based on the total denomination
    if (totalDenomination > invoiceAmount) {
      $("#yourDivId").hide();
      $(".payment-box").show();
    } else {
      $("#yourDivId").hide();
      $(".payment-box").show();
    }

    // Calculate and display the return amount
    var returnAmount = totalDenomination - invoiceAmount;
    $("#depositedAmount").text(formatNumber(totalDenomination));
    $("#CollectedAmount").text(formatNumber(totalDenomination));
    $("#returnAmount").text(formatNumber(returnAmount));
    $("#balance").text(formatNumber((returnAmount)));
  }

  /////////////////////////////////////////////  returnAmount //////////////////////////////////////////////
  // Declare the return mode flag globally
let isReturnMode = false;
$("#returnAmt").click(function () {
    // Activate Return Mode
    isReturnMode = true;
    Restart = false; // flag for click event on clear all button
    // Store the current collected amount (to prevent changes)
    const currentCollectedAmount = $("#depositedAmount").text();

    // Get and preserve the original return amount
    var returnAmountValue = $("#returnAmount").text().replace(/,/g, '') || "0";
    var originalReturnAmount = parseFloat(returnAmountValue);

    // Override calculateAmounts to preserve certain values during Return Mode
    const originalCalculateAmounts = calculateAmounts;
    calculateAmounts = function () {
        originalCalculateAmounts(); // Run original logic

        if (isReturnMode) {
            // Lock the collected amount
            $("#depositedAmount").text(currentCollectedAmount);

        // Preserve the original return amount
        $("#returnAmount").text(formatNumber(originalReturnAmount));

        // Fix pending amount calculation
        const selectedAmountText = $("#selectedAmount").text().replace(/[^0-9.-]+/g, "");
        const returnAmountText = $("#returnAmount").text().replace(/[^0-9.-]+/g, "");

        const selectedAmount = parseFloat(selectedAmountText) || 0;
        const returnAmount = parseFloat(returnAmountText) || 0;

        // Correct calculation ( Absolute values)
        var pendingAmount = Math.abs(selectedAmount) - Math.abs(returnAmount);
        

     // Format to always show 2 decimal places
        const formattedValue = pendingAmount.toFixed(2);
        
        // Update display with proper alignment
        $("#pendingAmount").html(`
            <span style="float:left">Pending Amount:</span>
            <span style="float:right">${formattedValue}</span>
            <div style="clear:both"></div>
        `);
    }
};

    $("#CollectedFlag").val("");
    let setDenominations = JSON.parse(localStorage.getItem("setDenominations")) || {};
    console.log("setDenominations", setDenominations);

    $("#selectedAmount").show();
    $("#pendingAmount").show();
    $("#yourDivId").hide();
    $(".payment-box").show();
    $("#returnCompleteBtn").show();

    // Apply styles
    $("#amount-section-container").css({
        display: "flex",
        gap: "5px",
        "margin-top": "10px",
        "justify-content": "space-between",
        "align-items": "center",
        width: "100%",
    });

    $("#selectedAmount, #pendingAmount").css({
        flex: "1",
        padding: "10px",
        "text-align": "center",
        "font-weight": "bold",
        "border-radius": "5px",
    });

    $("#selectedAmount").css("background", "#90EE90");
    $("#pendingAmount").css("background", "#FFC0CB");

    // Lock and gray-out deposited amount
    $("#depositedAmount").text(currentCollectedAmount).css("color", "gray").attr("data-original-collected", currentCollectedAmount);

    fetchDenomination();

    setTimeout(function () {
    calculateAmounts();

    var pendingAmountText = $("#pendingAmount").text().replace(/[^0-9.-]+/g, "");
    var pendingAmount = parseFloat(pendingAmountText) || 0;

    $("#balance").text(formatNumber(pendingAmount));
    if (isReturnMode && pendingAmount < 0) {
        // Show overlay and toast
        $("#returnToastMessage").text(`Currency of ${Math.abs(pendingAmount).toFixed(2)} is not available for issuing Return Amount.`);
        $("#returnOverlay").fadeIn();
        $("#returnToast").fadeIn();
    } else {
        // Hide if not needed
        $("#returnOverlay").hide();
        $("#returnToast").hide();
    }
}, 300);

        $("#selectedAmount").css("margin-left", "");


    $(".denomination-box.highlight").find(".remove").removeClass("hidden").show();

    var previousInterval = $("#returnAmt").data("intervalId");
    if (previousInterval) clearInterval(previousInterval);

    var interval = setInterval(function () {
        $(".denomination-box.highlight").find(".remove").removeClass("hidden").show();
    }, 1000);

    $("#returnAmt").data("intervalId", interval);
});
$(document).on("click", "#returnToastOkBtn", function () {
    $("#returnOverlay").fadeOut();
    $("#returnToast").fadeOut();
});



///////////////////////////////////////////// Collected Mode ////////////////////////////////////////
$("#Collected").click(function () {
    // Disable Return Mode
    isReturnMode = false;

    // Optional: Restore original calculateAmounts if needed
    if (typeof originalCalculateAmounts === "function") {
        calculateAmounts = originalCalculateAmounts;
    }

    let storedDenominations = JSON.parse(localStorage.getItem("denominations")) || {};
    $("#CollectedFlag").val('CollectedFlag');

    // Restore styles
    $("#amount-section-container").css({
        display: "flex",
        gap: "5px",
        "margin-top": "10px",
        "justify-content": "space-between",
        "align-items": "center",
        width: "100%",
    });

    $("#selectedAmount, #pendingAmount").css({
        flex: "1",
        padding: "10px",
        "text-align": "center",
        "font-weight": "bold",
        "border-radius": "5px",
    });

    $("#selectedAmount").css("background", "#90EE90"); // Light green
    $("#pendingAmount").css("background", "#FFC0CB"); // Pink
    $("#denomination-container").css("border", "9px solid #FFE0A7");
    $("#clearAll").css("border", "9px solid #FFE0A7");
    $(".payment-box").css("border", "9px solid #FFE0A7");
    $(".clear-btn").removeClass("hidden").show();
    $("#Collected").attr("style", "background-color: #FFE0A7 !important; color: #000 !important;");
    $("#amount-section-container").css({ border: "9px solid #FFE0A7" });

    // Show relevant UI
    $("#yourDivId").hide();
    $("#selectedAmount").show();
    $("#pendingAmount").hide(); 
    $("#amount-section-container").show();
    $(".payment-box").show();
    $("#returnCompleteBtn").show();

    //  Remove gray-out effect
    $("#depositedAmount").css("color", "black");

    // Reset all denomination boxes
    $(".denomination-box").each(function () {
        $(this).css({
            "background-color": "",
            "opacity": "1",
            "pointer-events": "auto"
        });
    });

    updateDenominationUI(storedDenominations);

    // Update UI after restoring denominations
  setTimeout(function () {
    calculateAmounts();

    var pendingAmount = 0.00;
    var formattedValue = (pendingAmount).toFixed(2); 

    $("#pendingAmount").html(`
        <span style="float:left">Pending Amount:</span>
        <span style="float:right">${formattedValue}</span>
        <div style="clear:both"></div>
    `);

    $("#balance").text(formattedValue);

    $("#pendingAmount").hide(); // <-- hide it again here
    $("#amount-section-container").css({
         display: "grid",          // remove flex
        "text-align": "center",
 })
 $("div#selectedAmount").css({
  "margin-left": "17rem",
 })}, 300);

    // FULL RESET of styles for each denomination box
    $(".denomination-box").each(function () {
        const $box = $(this);
        const $label = $box.find(".denom-label");
        const $countElem = $box.find(".count");
        const $increase = $box.find(".increase");
        const $decrease = $box.find(".decrease");
        const $remove = $box.find(".remove");

        // Clear all inline styles
        $box.css({
            "background-color": "",
            "opacity": "",
            "pointer-events": "",
        });

        $label.add($countElem).css({
            "background-color": "",
            "opacity": "",
            "pointer-events": "",
            "color": ""
        });

        $increase.add($decrease).add($remove).css({
            "opacity": "",
            "pointer-events": "",
        }).prop("disabled", false); // ensure they are not disabled
    });
});


  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  // When "Return & Complete" button is clicked
  $("#returnCompleteBtn").click(function () {
    $("#yourDivId").hide(); // Show the amount div
    $("#amnt").show();
    $(".payment-box").show(); // Hide the "Return & Complete" button

    // Change border color of #denomination-container to blue
    $("#denomination-container,#clearAll,#amount-section-container").css({
      border: "9px solid#E6F5FF", // Blue color
    });
    fetchDenomination();
  });

  // Clear All Button
// Store the original calculateAmounts function globally
// let originalCalculateAmounts = calculateAmounts;

// $("#clearAll").click(function () {
//     // Reset values
//     $("#CollectedFlag").val("");
//     $("#returnAmount").text("0");
//     $("#depositedAmount").text("0").css("color", ""); // Reset color

//     // Unlock and restore calculateAmounts
//     calculateAmounts = originalCalculateAmounts;

//     // Removes the background color
//     $("#Collected").css("background-color", "");

//     // Hide elements
//     $("#yourDivId").hide();
//     $("#selectedAmount").hide();
//     $("#pendingAmount").hide();
//     $("#amount-section-container").hide();

//     // Reset counters and highlights
//     $(".counter .count").text("0");
//     $(".denomination-box").removeClass("highlight");
//     $(".remove").fadeOut(200);

//     // Clear local storage
//     localStorage.removeItem("pendingAmount");
//     localStorage.removeItem("denominations");

//     // Show payment box
//     $(".payment-box").show();
//     $("#amnt").show();

//     // Reset denomination container border
//     $("#denomination-container").css({
//         border: "9px solid #E6F5FF",
//     });

//     // Reset Clear All button border
//     $("#clearAll").css("border", "9px solid #E6F5FF");

//     // Reset payment box border to match
//     $(".payment-box").css("border", "9px solid rgb(230, 245, 255)");

//     // Reset balance
//     $("#balance").text("0");

//     // Reload denomination data
//     loadDenominationData();

//     // Reset styles on each denomination box
//     $(".denomination-box").each(function () {
//         $(this).css({
//             "background-color": "",
//             "opacity": "1",
//             "pointer-events": "auto",
//         });
//         $(this).find(".increase, .decrease").prop("disabled", false);
//         $(this).find(".count, .denom-label").css("color", "");
//     });
// });

// }); 

//////////////////////////////////////////////////////////////////////////////////////////////////
// Clear All Button
// Store the original calculateAmounts function globally
let originalCalculateAmounts = calculateAmounts;
let Restart = false;

$("#clearAll").click(function () {
  Restart = true;
  isReturnMode = false;
    // Reset values
    $("#CollectedFlag").val("");
    $("#returnAmount").text("0");
    $("#depositedAmount").text("0").css("color", ""); // Reset color

    // Unlock and restore calculateAmounts
    calculateAmounts = originalCalculateAmounts;

    // Removes the background color
    $("#Collected").css("background-color", "");

    // Hide elements
    $("#yourDivId").hide();
    $("#selectedAmount").hide();
    $("#pendingAmount").hide();
    $("#amount-section-container").hide();

    // Reset counters and highlights
    $(".counter .count").text("0");
    $(".denomination-box").removeClass("highlight");
    $(".remove").fadeOut(200);

    // Clear local storage
    localStorage.removeItem("pendingAmount");
    localStorage.removeItem("denominations");

    // Show payment box
    $(".payment-box").show();
    $("#amnt").show();

    // Reset borders
    $("#denomination-container").css({
        border: "9px solid #E6F5FF",
    });
    $("#clearAll").css("border", "9px solid #E6F5FF");
    $(".payment-box").css("border", "9px solid rgb(230, 245, 255)");

    // Reset balance
    $("#balance").text("0");

    // Reload denomination data
    loadDenominationData();
    // fetchDenomination();
    // renderDenominations(isReturnMode = false);

    // FULL RESET of styles for each denomination box
    $(".denomination-box").each(function () {
        const $box = $(this);
        const $label = $box.find(".denom-label");
        const $countElem = $box.find(".count");
        const $increase = $box.find(".increase");
        const $decrease = $box.find(".decrease");
        const $remove = $box.find(".remove");

        // Clear all inline styles
        $box.css({
            "background-color": "",
            "opacity": "",
            "pointer-events": "",
        });

        $label.add($countElem).css({
            "background-color": "",
            "opacity": "",
            "pointer-events": "",
            "color": ""
        });

        $increase.add($decrease).add($remove).css({
            "opacity": "",
            "pointer-events": "",
        }).prop("disabled", false); // ensure they are not disabled
    });
});
});
////////////////////////////////////////////////////////////////////////////////////

$(document).on("click", "#returnAmt", function () {
  $(
    ".payment-box, #denomination-container, #clearAll, #amount-section-container"
  ).css({
    border: "9px solid #F9C4DA",
  });

  // Change background color and text color of the return amount row
  $(this).closest(".row").css({
    color: "#fff",
    padding: "2px",
    "border-radius": "5px",
    "margin-left": "-12px;",
  });

  // Style the button itself
  $(this).css({
    "background-color": "#E9136B",
    color: "#fff",
    border: "1px solid #E9136B",
  });
  $("#returnComplete").show();
});
//////////////////-----TOGGLE-----///////////////////////
let divElement = document.getElementById("toToggle");
let togglElement = document.getElementById("arrow-toggle");

document.getElementById("arrow-toggle").addEventListener("click", () => {
  if (divElement.style.height === "100%") {
    closeToggle();
    return;
  }
  divElement.style.height = "100%";
  divElement.style.zIndex = "1000";
  togglElement.style.transform = "rotate(180deg)";
  togglElement.style.transition = "transform 0.3s ease";
});

function closeToggle() {
  divElement.style.height = "13vh";
  togglElement.style.transform = "rotate(0deg)";
  togglElement.style.transition = "transform 0.3s ease";
}

document.getElementById("toToggle").addEventListener("click", (event) => {
  const target = event.target.closest(".payment-method");
  if (target) {
    // console.log("Payment method clicked:", target.textContent);
    closeToggle();
  }
});

//   get data from the localStorage
function loadData() {
    //Set Permissions
    var scanValue = $('#scan_item_qr').val();

    var per_cartunitview = ($('#per_cartunitview').val() == 1) ? true : false;
    var per_cartpriceedit = ($('#per_cartpriceedit').val() == 1) ? true : false;
    var permission_owner = ($('#permission_owner').val() == 1) ? true : false;
    var permission_admin = ($('#permission_admin').val() == 1) ? true : false;
    var add_tax_in_cart_unit_price = ($('#add_tax_in_cart_unit_price').val() == 1) ? true : false;
    var add_discount_in_cart_unit_price = ($('#add_discount_in_cart_unit_price').val() == 1) ? true : false;
    var changeQtyAsPerPrice = ($('#change_qty_as_per_user_price').val() == 1) ? true : false;

    if (localStorage.getItem('positems')) {
        total = 0;
        invoice_total_withtax = 0;      //For Apply Offers
        invoice_total_withouttax = 0;   //For Apply Offers 
        offerCartItems = {};        //For Apply Offers 
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        poscartitems = null;
        item_cart_qty = [];


        $("#getsdta tbody").empty();

        if (java_applet == 1) {
            order_data = "";
            bill_data = "";
            bill_data += chr(27) + chr(69) + "\r" + chr(27) + "\x61" + "\x31\r";
            bill_data += site.settings.site_name + "\n\n";
            order_data = bill_data;
            bill_data += lang.bill + "\n";
            order_data += lang.order + "\n";
            bill_data += $('#select2-chosen-1').text() + "\n\n";
            bill_data += " \x1B\x45\x0A\r\n ";
            order_data += $('#select2-chosen-1').text() + "\n\n";
            order_data += " \x1B\x45\x0A\r\n ";
            bill_data += "\x1B\x61\x30";
            order_data += "\x1B\x61\x30";
        } else {
            $("#order_span").empty();
            $("#bill_span").empty();
            var styles = '<style>table, th, td { border-collapse:collapse; border-bottom: 1px solid #CCC; } .no-border { border: 0; } .bold { font-weight: bold; }</style>';
            // var pos_head1 = '<span style="text-align:center;"><h3>' + site.settings.site_name + '</h3><h4>';
            //var pos_head2 = '</h4><h5> Token No.: ' + tokan_no + ' </h5><h5>' + $('#select2-chosen-1').text() + '<br>' + hrld() + '</h5></span>';
            //$("#order_span").prepend(styles + pos_head1 + ' Order ' + pos_head2);

            var pos_head1 = '<div style="text-align:center;"><strong>' + site.settings.site_name + '</strong><br/>';
            if (site.settings.pos_type == 'restaurant') {
                var pos_head2 = ' Table No: ' + localStorage.getItem('table_name') + '</div>';
                $("#bill_span").prepend(styles + pos_head1 + pos_head2);

            } else {
                var pos_head2 = ' Token No.: ' + tokan_no + ' ' + ',' + hrld() + '</div>';
                $("#bill_span").prepend(styles + pos_head1 + ' Bill ' + pos_head2);

            }
            $("#order_span").prepend(styles + pos_head1 + pos_head2);

            // $("#bill_span").prepend(styles + pos_head1 + ' Bill ' + pos_head2);
            $("#order-table").empty();
            $("#bill-table").empty();
        }

        positems = JSON.parse(localStorage.getItem('positems'));

        console.log('=========positems=============');
        console.log(positems);

        var posItemsCount = Object.keys(positems).length;

        var poscartitems = {};
        /*********************Code For Offers Add Free Items*******************/
        //         console.log('Status addfreeitems: '+localStorage.getItem('addfreeitems'));


        if (localStorage.getItem('addfreeitems') == 'false') {
            var temp_item_id = '';
            //When do not have to add free items in cart but in localstorage have free items then remove from localstorage and cart

            $.each(positems, function () {

                if (this.note == 'Free Items' || this.is_free) {

                    var objitemid = '';
                    var objitemid2 = '';

                    if (this.row.option) {
                        objitemid = this.item_id + this.row.option;
                        objitemid2 = this.item_id + '_' + this.row.option;
                    } else if (this.category) {
                        objitemid = this.item_id + this.category;
                        objitemid2 = this.item_id + '_' + this.category;
                    } else {
                        objitemid = this.item_id;
                        objitemid2 = this.item_id;
                    }

                    delete positems['free_item_' + objitemid2];
                    localStorage.removeItem('free_item_' + objitemid2);

                    delete positems[objitemid];
                    localStorage.removeItem(objitemid);
                } else {

                    temp_item_id = this.id;  //(this.row.option) ?  this.item_id + this.row.option :  this.item_id; // Add new Item to card Not Working
                    poscartitems[temp_item_id] = this;
                }
            });
        } else {
            poscartitems = positems;

            if (localStorage.getItem('posfreeitems')) {
                var freepositems = JSON.parse(localStorage.getItem('posfreeitems'));
                jQuery.extend(poscartitems, freepositems); // Extend cart veriables with free items.
                localStorage.removeItem('posfreeitems');
            }
        }

        /**********************************************************************/

        if (pos_settings.item_order == 1) {
            sortedItems = _.sortBy(poscartitems, function (o) {
                return [parseInt(o.category), parseInt(o.order)];
            });
        } else if (site.settings.item_addition == 1) {
            sortedItems = _.sortBy(poscartitems, function (o) {
                return [parseInt(o.order)];
            })
        } else {
            sortedItems = poscartitems;
        }

        //        console.log('--------------sortedItems---------------------');
        //        console.log(sortedItems);

        //Get the total cart unit items
        var cart_item_unit_count = 0;

        $.each(sortedItems, function () {
            cart_item_unit_count += parseFloat(this.row.qty);
        });

        var category = 0, print_cate = false;
        // var itn = parseInt(Object.keys(sortedItems).length);
        $("#bill-table").append('<tr><th>  Item Code  </th><th>Item Name</th><th>Qty</th><th>Price</th><th style="text-align:right;">Total</th></tr>');
        var previous_row_no = '';

        $('#payment').attr('disabled', false);

        //        console.log('--------------sortedItems---------------------');
        //        console.log(sortedItems);

        $.each(sortedItems, function () {

            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            division_array.push(item.row.divisionid);
            var hsn_code = '';
            if (item.row.hsn_code) {
                hsn_code = item.row.hsn_code;
            }
            // positems[item_id] = item;

            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_desc = item.row.description, item_option = item.row.option, item_code = item.row.code, item_article_code = item.row.article_code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var product_unit = item.row.unit;
            var item_weight = 0;
            if(item.row.storage_type == 'loose'){
                var base_quantity = formatDecimal((parseFloat(item.row.base_quantity) * parseFloat(item.row.qty)),3);
            }else{
                var base_quantity = formatDecimal((parseFloat(item.row.qty)),3);
            }
            var tax_rate = item.row.tax_rate;
            var mrp = item.row.mrp;
            var discount_on_mrp = item.row.discount_on_mrp;
            var pr_var_discount = item.row.pr_var_discount;
            var customer_group_discount = item.row.customer_group_discount; // flag for customer discount apply
            
            // Category Tax
            var category_tax = item.category_tax;
            var fixtax = item.fixtax;

            var warehouse_price_group_id = item.row.warehouse_price_group_id;
            if (!warehouse_price_group_id) {
                var unit_price = parseFloat(item.row.real_unit_price) > 0 ? item.row.real_unit_price : item.row.unit_price;
                if(scanValue){
                    var unit_price = item.row.mrp;
                }
            } else {
                var unit_price = item.row.unit_price;
                // var unit_price = item.row.mrp;
                // var unit_price = item_option == "0" ? item.row.mrp : item.row.unit_price;
            }
            // var customerName = $('#customer_name').val();
            // alert('customerName')
            // alert(customerName)
            // let inputString = "Swarup(8633683837)";
            // let parts = $('#customer_name').val().split('(');
            // let name = parts[0];

            //var base_quantity = (parseFloat(item.row.unit_quantity) * parseFloat(item.row.qty));
            // var unit_price = item.row.real_unit_price;
            var manualedit = (item.row.manualedit) ? item.row.manualedit : ''; // 05-09-19

            item_cart_qty[item.item_id] = parseFloat(item_cart_qty[item.item_id]) > 0 ? (item_cart_qty[item.item_id] + item.row.qty) : item.row.qty;

            var cf1 = item.row.cf1;
            var cf2 = item.row.cf2;
            var cf3 = item.row.cf3;
            var cf4 = item.row.cf4;
            var cf5 = item.row.cf5;
            var cf6 = item.row.cf6;

            var batchno = item.row.batch_number ? item.row.batch_number : '';

            if (item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 6);
                        unit_price = formatDecimal((parseFloat(item.row.base_unit_price) * (unitToBaseQty(1, this))), 6);
                    }
                });
            }
            var sel_opt = '';
            var option_input_hidden = '<input name="product_option[]" type="hidden" class="roption" value="' + item.row.option + '">';

            if (site.settings.attributes == 1) {
                if (item.options !== false) {
                    $.each(item.options, function () {

                        var this_options = this;

                        //If Select multiple options
                        if (jQuery.type(item.row.option) == 'string') {
                            var optionArr = item.row.option.split(",");
                            $.each(optionArr, function (k, opt) {

                                if (this_options.id == opt) {
                                    if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                                        if (manualedit == '') {
                                            // item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.price), 6);
                                            item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.mrp), 6);
                                            unit_price = item_price;
                                            item_aqty = this_options.quantity;
                                        }
                                    }
                                    if (k) {
                                        sel_opt = sel_opt + ',' + this_options.name;
                                    } else {
                                        sel_opt = this_options.name;
                                    }
                                }
                            });
                        } else {
                            if (this_options.id == item.row.option) {
                                if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                                    if (manualedit == '') {
                                        // item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.price)), 6);
                                        item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.mrp)), 6);
                                        unit_price = item_price;
                                        item_aqty = this_options.quantity;
                                    }
                                }
                                sel_opt = this_options.name;
                            }
                        }
                    });
                }
            }
          

            // Order level discount distributed in each items as item discount.
            var posdiscount = localStorage.getItem('posdiscount');

            if (posdiscount) {
                //Order Level Discount Calculations               
                var ods = posdiscount;
                var item_discount_on_mrp = 0;
                var item_order_discount = 0;
                // var mrp = unit_price;

                // calculating unit_price after apply discount on mrp 
                // start
                var ds = discount_on_mrp ? String(discount_on_mrp) : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        item_discount_on_mrp = formatDecimal((parseFloat(((mrp) * parseFloat(pds[0])) / 100)), 6);
                    } else {
                        item_discount_on_mrp = formatDecimal(ds, 6);
                    }
                } else {
                    item_discount_on_mrp = formatDecimal(ds, 6);
                }
                unit_price = formatDecimal(mrp - item_discount_on_mrp, 6);
                // end
              

                if (ods.indexOf("%") !== -1) {
                    var pds = ods.split("%");
                    if (!isNaN(pds[0])) {
                        item_order_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
                        item_ds = ods;
                    } else {
                        item_order_discount = formatDecimal(parseFloat(ods), 6);
                        item_ds = item_order_discount;
                    }
                } else {
                    //If Discount in amount then divided equal in each items unit equally.
                    item_order_discount = formatDecimal((parseFloat(ods) / cart_item_unit_count), 6);
                    item_ds = item_order_discount;
                }
                order_discount += formatDecimal((item_order_discount * item_qty), 6);
                // unit_price = formatDecimal(parseFloat(mrp) - parseFloat(item_discount_on_mrp), 6);
                unit_price = mrp;
                item_discount = item_discount_on_mrp;

                if (offer_categories = localStorage.getItem('offer_on_category')) {
                    var offer_on_category = offer_categories.split(',');
                    if (offer_on_category.indexOf(item.category) != -1) {
                        //alert('found');
                    } else {
                        //alert('not found');
                        if (offer_on_category.indexOf(item.sub_category) != -1) {  //alert('sub found');	
                        } else {
                            item_discount = 0;
                            item_ds = 0;
                            //alert('not sub found');
                        }
                    }
                }
                //Set Order Discount Value null.
                //$('#posdiscount').val('');
                $('#offer_on_category').val(localStorage.getItem('offer_on_category'));
                $('#offer_category').val(localStorage.getItem('offer_category'));
                $('#offer_description').val(localStorage.getItem('offer_description'));

                // alert('offer_category: '+localStorage.getItem('offer_category'));
                // alert('offer_description: '+localStorage.getItem('offer_description'));
                localStorage.setItem('applyOffers', true);
            } else {
                //Item Level Discount Calculations  
                // var ds = item_ds ? String(item_ds) : '0';

                if(manualedit == "1"){
                    var flat_discount = mrp - unit_price;  // Calculate flat discount
                    item_discount = flat_discount.toFixed(0);  
                    discount_on_mrp = item_discount;
                }else{
                    if(customer_group_discount == '1'){ // override customer discount to discount on mrp if customer discount applied
                        discount_on_mrp = item_ds;
                    }
                    var ds = discount_on_mrp ? String(discount_on_mrp) : '0';
                    if (ds.indexOf("%") !== -1) {
                        var pds = ds.split("%");
                        if (!isNaN(pds[0])) {
                            item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
                        } else {
                            item_discount = formatDecimal(ds, 6);
                        }
                    } else {
                        item_discount = formatDecimal(ds, 6);
                    }
                }

            }

            if(item.row.editpopup == 'edititems') {
                
                unit_price = manualedit == "1" ? item.row.unit_price : mrp;

                if(manualedit == "1"){
                    var flat_discount = mrp - unit_price;  // Calculate flat discount
                    item_discount = flat_discount.toFixed(0);  
                }else{
                    if(customer_group_discount == '1'){ // override customer discount to discount on mrp if customer discount applied
                        discount_on_mrp = item.row.discount_on_mrp;
                    }
                    // Discount on mrp
                    if(discount_on_mrp){
                        // item_ds = discount_on_mrp;
                        //Item Level Discount Calculations  
                        var ds = discount_on_mrp ? String(discount_on_mrp) : '0';

                        if (ds.indexOf("%") !== -1) {
                            var pds = ds.split("%");
                            if (!isNaN(pds[0])) {
                                item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
                            } else {
                                item_discount = formatDecimal(ds, 6);
                            }
                        } else {
                            item_discount = formatDecimal(ds, 6);
                        }
                    } 
                }
                
            }
            
           if(posdiscount){
                product_discount += formatDecimal((item_discount_on_mrp * item_qty), 6);
           }else{
                product_discount += formatDecimal((item_discount * item_qty), 6);
           }

            // item.row.discount = formatDecimal(item_discount, 4);
            if (changeQtyAsPerPrice) {
                var cart_user_price = parseFloat(item.row.user_price) > 0 ? parseFloat(item.row.user_price) : 0;
            }

            // unit_price = formatDecimal(unit_price - item_discount, 6);
            if(manualedit == ''){
                unit_price = formatDecimal(unit_price - item_discount, 6);
            }
            if(posdiscount){ // for calculate unit_price after apply order level discount
                unit_price = formatDecimal(unit_price - item_order_discount, 6);
            }

            // var pr_tax = item.tax_rate;
            // var pr_tax_val = 0;
            // if (site.settings.tax1 == 1) {
            //     if (pr_tax !== false) {
            //         if (pr_tax.type == 1) {
            //             if (item_tax_method == '0') {
            //                 pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 6);
            //                 pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
            //             } else {
            //                 pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / 100, 6);
            //                 pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
            //             }
            //         } else if (pr_tax.type == 2) {
            //             pr_tax_val = formatDecimal(pr_tax.rate);
            //             pr_tax_rate = pr_tax.rate;
            //         }
            //         product_tax += pr_tax_val * item_qty;
            //     }
            // }//end if.
            // if(item.row.editpopup == 'edititems') {
            //     unit_price = mrp;
            //     // var unit_price = $('#selling').val();
            // }
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            if (fixtax) {
                                var exptax = fixtax.split("~");
                                pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1]))), 4);
                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                tax_rate = exptax[0];
                            } else {
                                if (category_tax) {
                                    $.each(category_tax, function (k, categorytax) {
                                        var uptocheck = categorytax.upto;
                                        if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                tax_rate = exptax[0];
                                            }
                                        } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                tax_rate = exptax[0];
                                            }

                                        }
                                    });
                                }

                            }
                        } else {
                            if (fixtax) {
                                var exptax = fixtax.split("~");
                                pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / 100), 4);
                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                tax_rate = exptax[0];
                            } else {
                                if (category_tax) {
                                    $.each(category_tax, function (k, categorytax) {
                                        var uptocheck = categorytax.upto;
                                        if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                tax_rate = exptax[0];
                                            }

                                        } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                                            if (uptocheck) {
                                                if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                } else {
                                                    var taxvalue = categorytax.taxratevalue;
                                                    var exptax = taxvalue.split("~");
                                                    pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                    pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                    tax_rate = exptax[0];
                                                }
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                                                pr_tax_rate = formatDecimal(exptax[1]) + '%';
                                                tax_rate = exptax[0];
                                            }

                                        }
                                    });
                                }

                            }
                        }

                    } else if (pr_tax.type == 2) {
                        if (fixtax) {
                            var exptax = fixtax.split("~");
                            pr_tax_val = parseFloat(exptax[1]);
                            pr_tax_rate = exptax[1];
                            tax_rate = exptax[0];
                        } else {
                            if (category_tax) {
                                $.each(category_tax, function (k, categorytax) {
                                    var uptocheck = categorytax.upto;
                                    if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                                        if (uptocheck) {
                                            if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(exptax[1]);
                                                pr_tax_rate = formatDecimal(exptax[1]);
                                                tax_rate = exptax[0];
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(exptax[1]);
                                                pr_tax_rate = formatDecimal(exptax[1]);
                                                tax_rate = exptax[0];
                                            }
                                        } else {
                                            var taxvalue = categorytax.taxratevalue;
                                            var exptax = taxvalue.split("~");
                                            pr_tax_val = formatDecimal(exptax[1]);
                                            pr_tax_rate = formatDecimal(exptax[1]);
                                            tax_rate = exptax[0];
                                        }

                                    } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {

                                        if (uptocheck) {
                                            if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(exptax[1]);
                                                pr_tax_rate = formatDecimal(exptax[1]);
                                                tax_rate = exptax[0];
                                            } else {
                                                var taxvalue = categorytax.taxratevalue;
                                                var exptax = taxvalue.split("~");
                                                pr_tax_val = formatDecimal(exptax[1]);
                                                pr_tax_rate = formatDecimal(exptax[1]);
                                                tax_rate = exptax[0];
                                            }
                                        } else {
                                            var taxvalue = categorytax.taxratevalue;
                                            var exptax = taxvalue.split("~");
                                            pr_tax_val = formatDecimal(exptax[1]);
                                            pr_tax_rate = formatDecimal(exptax[1]);
                                            tax_rate = exptax[0];
                                        }

                                    }
                                });
                            }

                        }
                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            // if(item.row.editpopup == 'edititems') {
            //     unit_price = item_tax_method == 0 ? formatDecimal((parseFloat(unit_price)), 4) : formatDecimal((parseFloat(unit_price)), 4);
            // }
            if(posdiscount){ // for adding order level discount in price again
                unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_order_discount), 6);
            }
            item_price = item_tax_method == 0 ? formatDecimal((unit_price - pr_tax_val), 6) : formatDecimal(unit_price, 6);
            // unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_discount), 6);
            if(manualedit == ''){
                unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_discount), 6);
            }
            /********************************************/
            if (item_tax_method == 0) {
                offerCartItems[item.row.id] = JSON.parse('{"item_id":"' + item.row.id + '", "price_with_tax":"' + unit_price + '", "price_without_tax":"' + (parseFloat(unit_price) - parseFloat(pr_tax_val)) + '", "qty":"' + item_qty + '", "category":"' + item.row.category_id + '", "discount":"' + item.row.discount + '"}');
            } else {
                offerCartItems[item.row.id] = JSON.parse('{"item_id":"' + item.row.id + '", "price_with_tax":"' + (parseFloat(unit_price) + parseFloat(pr_tax_val)) + '", "price_without_tax":"' + unit_price + '", "qty":"' + item_qty + '", "category":"' + item.row.category_id + '", "discount":"' + item.row.discount + '"}');
            }
            /************************************************/

            if (pos_settings.item_order == 1 && category != item.row.category_id) {
                category = item.row.category_id;
                print_cate = true;
                var newTh = $('<tr id="category_' + category + '"></tr>');
                newTh.html('<td colspan="100%"><strong>' + item.row.category_name + '</strong></td>');
                newTh.prependTo("#getsdta");
            } else {
                print_cate = false;
            }

            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');

            item_weight = (item.row.unit_weight) ? (parseFloat(item_qty) * parseFloat(item.row.unit_weight)) : '';

            var tr_html = '<td><input name="row[]" type="hidden" id="item_' + item_id + '" class="roid" value="' + row_no + '">';
            tr_html += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">';
            tr_html += '<input name="hsn_code[]" type="hidden" class="rid hsn_code" value="' + hsn_code + '">';
            tr_html += '<input name="product_type[]" type="hidden" class="rtype product_type"  value="' + item_type + '">';
            tr_html += '<input name="product_code[]" type="hidden" class="rcode product_code" value="' + item_code + '">';
            tr_html += '<input name="article_code[]" type="hidden" class="rcode article_code" value="' + item_article_code + '">';
            tr_html += '<input name="product_name[]" type="hidden" class="rname product_name" value="' + item_name + '">';
            tr_html += '<input name="productids[]" type="hidden" class="productids" value="' + item.row.id + '">';
            tr_html += '<input name="manualedit[]"   type="hidden" class="rmanualedit" value="' + manualedit + '">';
            tr_html += '<input name="item_weight[]"  type="hidden" class="rweight" value="' + item_weight + '">';
            tr_html += '<input name="return_ref_no[]" type="hidden" class="return_ref_no" value="' + item.row.return_ref_no + '">';
            tr_html += '<input  name="customerRefNo" type="hidden" class="customerRefNo" value=" ' + scanValue + '">';

            // tr_html += '<input  name="customerRefNo" type="hidden" class="customerRefNo" value=" ' + scanValue +'">';

            //Options Input Hiddens 
            tr_html += option_input_hidden;

            tr_html += '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ((item.note == '') ? item.note : ': ' + item.note) + ')' : '') + '</span>';

            //Hide Item Edit Options if Items is free
            if ((item.note == 'Free Items')) {
                var item_disabled = ' readonly="readonly" ';
                tr_html += '</td>';
            } else {
                var item_disabled = '';
                // tr_html += '<i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            }

            //tr_html += '<i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            item.note = (item.note == undefined) ? '' : item.note;
            tr_html += '<input name="item_note[]" type="hidden" class="rid" value="' + item.note + '">';
            tr_html += '<input name="cf1[]" type="hidden" class="rid" value="' + cf1 + '">';
            tr_html += '<input name="cf2[]" type="hidden" class="rid" value="' + cf2 + '">';
            tr_html += '<input name="cf3[]" type="hidden" class="rid" value="' + cf3 + '">';
            tr_html += '<input name="cf4[]" type="hidden" class="rid" value="' + cf4 + '">';
            tr_html += '<input name="cf5[]" type="hidden" class="rid" value="' + cf5 + '">';
            tr_html += '<input name="cf6[]" type="hidden" class="rid" value="' + cf6 + '">';
            tr_html += '<input name="batch_number[]" type="hidden" class="rid" value="' + batchno + '">';

            tr_html += '<td class="text-right">';

            if (site.settings.product_serial == 1) {
                tr_html += '<input class="form-control input-sm rserial" name="serial[]" type="hidden" id="serial_' + row_no + '" value="' + item_serial + '">';
            }
            if (site.settings.product_discount == 1) {
                tr_html += '<input class="form-control input-sm rdiscount product_discount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '">';
            }
           
            if (site.settings.tax1 == 1) {
                pr_tax.id = (tax_rate > 0) ? tax_rate : pr_tax.id;
                tr_html += '<input class="form-control input-sm text-right rproduct_tax product_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><input type="hidden" class="sproduct_tax" id="sproduct_tax_' + row_no + '" value="' + formatMoney(pr_tax_val * item_qty) + '">';
            }
            item_desc = item_desc == undefined ? '' : item_desc;
            tr_html += '<input class="rdescription" name="item_description[]" type="hidden" id="description_' + row_no + '" value="' + item_desc + '">';
            tr_html += '<input class="rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '">';
            tr_html += '<input class="ruprice unitprices" name="unit_price[]" type="hidden" value="' + unit_price + '">';
            tr_html += '<input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '">';
            tr_html += '<input class="rmrp mrp" name="mrp[]" type="hidden" value="' + mrp + '">';
            tr_html += '<input class="rmrpdiscount mrpdiscount" name="discount_on_mrp[]" type="hidden" value="' + discount_on_mrp + '">';
            tr_html += '<input class="rtaxrate rtaxrate" name="taxrate[]" type="hidden" value="' + tax_rate + '">';
            tr_html += '<input class="reditpopup reditpopup" name="editpopup[]" type="hidden" value="' +  item.row.editpopup + '">';

           
            // var cart_item_price =  (add_tax_in_cart_unit_price == true) ? (parseFloat(item_price) + parseFloat(pr_tax_val)) : parseFloat(item_price);
            //alert(cart_item_price);

            var cart_item_price = 0;

            if (add_tax_in_cart_unit_price == true && add_discount_in_cart_unit_price == true) {
                
                cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val) + parseFloat(item_discount); //item_ds
            } else if (add_tax_in_cart_unit_price == true) {
                cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val);
            } else if (add_discount_in_cart_unit_price == true) {
                cart_item_price = parseFloat(item_price) + parseFloat(item_discount);
            } else {
                cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val);
            }

            if (permission_admin || permission_owner || per_cartpriceedit) {
                if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose') {
                    tr_html += '<input type="text" maxlength="10" name="item_user_price[]" id="suserprice_' + row_no + '" value="' + ((cart_user_price > 0) ? parseInt(cart_user_price) : parseInt(cart_item_price)) + '"  class="form-control input-sm kb-pad text-center userprice" />';
                    tr_html += (cart_user_price > 0) ? '<small class="text-left">' + parseInt(cart_item_price) + '/qty</small>' : '';
                    tr_html += '<input type="hidden" name="item_price[]" id="sprice_' + row_no + '" value="' + (formatMoney(cart_item_price)) + '" />';
                } else {
                      tr_html += '<span class="item_price userprice" data-id="' + row_no + '" id="sprice_' + row_no + '" ' + item_disabled + '>' + formatMoney(cart_item_price) + '</span>';
                    // tr_html += '<input type="text" maxlength="10" name="item_price[]" id="sprice_' + row_no + '" value="' + (formatMoney(cart_item_price)) + '"  ' + item_disabled + '  class="form-control input-sm kb-pad text-center item_price userprice" />';
                }
            } else {
                tr_html += formatMoney(parseFloat(cart_item_price)) + '<input type="hidden"  maxlength="10" name="item_price[]" id="sprice_' + row_no + '" value="' + formatMoney(cart_item_price) + '" onchange="return false" class="form-control input-sm kb-pad text-center  item_price userprice" />';
            }
            tr_html += '</td>';

            // tr_html += '<td>';
            // tr_html += '<table style="border: none;"><tr ><td style="border-bottom: 0px !important;"> ';
            // if (oldProductSearch(item_id)) {
            //     // tr_html += '<button onclick="qtyMinus(\'' + item_id + '\')" type="button" style="border: 0; background: none;" ><i class="fa fa-minus"></i> </button>';
            // }
            // tr_html += ' &nbsp;  </td>';
            tr_html += '<td style="border-bottom: 0px !important; text-align: center;">';


            tr_html += '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">';
            tr_html += '<input name="product_base_quantity[]" maxlength="6" type="hidden" class="rbase_quantity product_base_quantity" value="' + base_quantity + '">';


            if (permission_admin || permission_owner || per_cartpriceedit) {

                var qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(item_aqty, 0) : 1000;

                if (item.row.type == 'combo') {
                    var cmax = 1000, cimax = '';
                    $.each(combo_items, function () {
                        cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
                        cmax = (cimax > cmax) ? cmax : cimax;
                    });
                    qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax, 0) : 1000;
                }//end if.

                if (item.row.type == 'Bundle') {
                    var cmax = 1000, cimax = '';
                    $.each(combo_items, function () {
                        cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
                        cmax = (cimax > cmax) ? cmax : cimax;
                    });
                    qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax, 0) : 1000;
                }//end if.

                if (item.row.storage_type == 'packed') {
                    var qotp = '', selected = '';
                    for (var q = 1; q <= (qmax ? qmax : 1); q++) {
                        selected = '';
                        if (formatDecimal(item_qty, 0) == q) {
                            selected = ' selected="selected" ';
                        }
                        qotp += '<option ' + selected + '>' + q + '</option>';
                    }//end for
                    // tr_html += '<select class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" >' + qotp + '</select>';
                        tr_html += '<span class="returnquantity rquantity" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '">' + item_qty + '</span>' ,style="width: 43px";
                } else {
                    if (changeQtyAsPerPrice == true && cart_user_price > 0) {
                        tr_html += formatDecimal(item_qty, 3) + '<input style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + ' type="hidden" value="' + formatDecimal(item_qty, 3) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
                    } else {
                        tr_html += '<input style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + ' type="text"    value="' + formatDecimal(item_qty, 3) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
                    }
                }

            } else {
                tr_html += '<input readonly="readonly" style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + '  type="text" value="' + item_qty + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
            }
            // tr_html += ' </td><td style="border-bottom: 0px !important;">';
            // // tr_html += '&nbsp;  <button type="button" onclick="qtyPlus(\'' + item_id + '\')"  style="border: 0; background: none; bottom:7px!important; padding:4px;"> <i class="fa fa-plus"></i> </button> ';
            // tr_html += '</td></table>';
            // tr_html += '</td>';

            var item_sale_unit = (item_name == 'Gift Card') ? 'pcs' : '';
            if (item.row.sale_unit) {
                $.each(item.units, function () {
                    if (this.id == item.row.sale_unit) {
                        item_sale_unit = this.code;
                    }
                });
            }
            //Show/Hide Cart Unit
            // if(permission_admin || permission_owner || per_cartunitview){
            tr_html += '<td class="text-center"><small>' + item_sale_unit + '</small></td>';
            //}            
            //tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';

            //Hide Item Edit Options if Items is free
            if ((item.note == 'Free Items')) {
                tr_html += '<td class="text-center" colspan="2" style="color:green;">Offer Free Item</td>';
            } else {
                if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose' && cart_user_price > 0) {
                    tr_html += '<td class="text-right"><span class="text-right ssubtotal returntotal returnsubtotal" id="subtotal_' + row_no + '">' + formatMoney(cart_user_price) + '</span></td>';
                    tr_html += '<input class="returntotal returnsubtotal"  type="hidden" value="' + formatMoney(cart_user_price) + '">';
               
                } else {
                    tr_html += '<input class="returntotal returnsubtotal"  type="hidden" value="' + formatMoney(parseFloat(cart_item_price) * parseFloat(item_qty)) + '">';
                    tr_html += '<td class="text-right" style="font-size: 13px;"><span class="text-right ssubtotal returntotal returnsubtotal" id="subtotal_' + row_no + '">' + formatMoney(parseFloat(cart_item_price) * parseFloat(item_qty)) + '</span></td>';
                }
                // tr_html += '<td class="text-center"><i class="fa fa-times tip pointer posdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            }

            newTr.html(tr_html);
            if (pos_settings.item_order == 1) {
                //newTr.prependTo("#getsdta");
                $('#getsdta').find('#category_' + category).after(newTr);
            } else if (pos_settings.item_order == 2) { // This is the new else if block for adding the "conduit"
                if (previous_row_no == '') {
                    newTr.prependTo("#getsdta");
                } else {
                    $('#getsdta').find('#row_' + previous_row_no).after(newTr);
                }
            } else {
                if (previous_row_no == '') {
                    newTr.prependTo("#getsdta");
                } else {
                    $('#getsdta').find('#row_' + previous_row_no).before(newTr);
                }
            }
            previous_row_no = row_no;

            invoice_total_withtax += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 6);
            invoice_total_withouttax += formatDecimal((parseFloat(item_price) * parseFloat(item_qty)), 6);

            if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose' && cart_user_price > 0) {
                total += formatDecimal(cart_user_price, 6);
            } else {
                total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 6);
            }
            item_qtys = Math.abs(item_qty);
            count += parseFloat(item_qtys);
            an++;

            if (item_type == 'standard' && item.options !== false) {

                $.each(item.options, function () {
                    if (this.id == item_option && (base_quantity > this.quantity || item_cart_qty[item.item_id] > this.quantity)) {
                        $('#row_' + row_no).addClass('danger');
                        if (site.settings.overselling != 1) {
                            $('#payment').attr('disabled', true);
                        }
                    }
                });
            } else if (item_type == 'standard' && (base_quantity > item_aqty || item_cart_qty[item.item_id] > item_aqty)) {
                $('#row_' + row_no).addClass('danger');
                if (site.settings.overselling != 1) {
                    $('#payment').attr('disabled', false);
                }
            } else if (item_type == 'combo') {
                if (combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                    if (site.settings.overselling != 1) {
                        $('#payment').attr('disabled', true);
                    }
                } else {
                    $.each(combo_items, function () {
                        if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling != 1) {
                                $('#payment').attr('disabled', true);
                            }
                        }
                    });
                }
            } else if (item_type == 'Bundle') {
                if (combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                    if (site.settings.overselling != 1) {
                        $('#payment').attr('disabled', true);
                    }
                } else {
                    $.each(combo_items, function () {
                        if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                            $('#row_' + row_no).addClass('danger');
                            if (site.settings.overselling != 1) {
                                $('#payment').attr('disabled', true);
                            }
                        }
                    });
                }
            }


            if (java_applet == 1) {
                bill_data += "#" + (an - 1) + " " + item_name + "\n";
                bill_data += printLine(item_qty + " x " + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + ": " + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)))) + "\n";
                order_data += printLine("#" + (an - 1) + " " + item_name + ":" + formatDecimal(item_qty)) + item.row.unit_lable + "\n";
            } else {
                if (pos_settings.item_order == 1 && print_cate) {
                    var bprTh = $('<tr></tr>');
                    bprTh.html('<td colspan="100%" class="no-border"><strong>' + item.row.category_name + '</strong></td>');
                    var oprTh = $('<tr></tr>');
                    oprTh.html('<td colspan="100%" class="no-border"><strong>' + item.row.category_name + '</strong></td>');
                    $("#order-table").append(oprTh);
                    //$("#bill-table").append(bprTh);
                }
                var bprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td> ' + item_code + ' </td><td class="no-border">  ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item.options ? '(' + item.row.option + ')' : '') + '</td><td>' + formatDecimal(item_qty) + ' ' + item.row.unit_lable + '</td> <td>' + (item_discount != 0 ? '<del>' + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount) + '</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + '</td><td style="text-align:right;">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</td></tr>';
                //var bprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td colspan="2" class="no-border">#'+(an-1)+' '+ item_name + ' (' + item_code + ')</td></tr>';
                //bprTr += '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>(' + formatDecimal(item_qty) + ' x ' + (item_discount != 0 ? '<del>'+formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount)+'</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val))+ ')</td><td style="text-align:right;">'+ formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) +'</td></tr>';
                var oprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>#' + (an - 1) + ' ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item.options ? '(' + item.row.option + ')' : '') + ')</td><td>' + formatDecimal(item_qty) + '</td></tr>';
                $("#order-table").append(oprTr);
                $("#bill-table").append(bprTr);
            }
        });

        // Order level discount calculations
        /* if (posdiscount = localStorage.getItem('posdiscount')) {
             var ds = posdiscount;
             if (ds.indexOf("%") !== -1) {
                 var pds = ds.split("%");
                 if (!isNaN(pds[0])) {
                     order_discount = formatDecimal((parseFloat(((total) * parseFloat(pds[0])) / 100)), 4);
                 } else {
                     order_discount = parseFloat(ds);
                 }
             } else {
                 order_discount = parseFloat(ds);
             }
             total_discount += parseFloat(order_discount);
         }*/


        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (postax2 = localStorage.getItem('postax2')) {
                $.each(tax_rates, function () {
                    if (this.id == postax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 6);
                        }
                    }
                });
            }
        }

        total = formatDecimal(total, 2);
        product_tax = formatDecimal(product_tax, 2);
        total_discount = formatDecimal(order_discount + product_discount, 2);
      

        // Totals calculations after item addition
        gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
        $('#total1').text(formatMoney(total + product_discount));
        $('#titems1').text((an - 1) + ' (' + formatDecimal(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        // $('#tds').text('(' + formatMoney(product_discount) + ') ' + formatMoney(order_discount));
        $('#tds1').text(formatMoney(product_discount + order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax21').text('(' + formatMoney(product_tax) + ') ' + formatMoney(invoice_tax))
        }else{
            $('#ttax21').text('(' + formatMoney(product_tax) + ') ')
        }
        $('#gtotal1').text(formatMoney(gtotal));
        if (java_applet == 1) {
            bill_data += "\n" + printLine(lang_total + ': ' + formatMoney(total)) + "\n";
            bill_data += printLine(lang_items + ': ' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')') + "\n";
            if (total_discount > 0) {
                bill_data += printLine(lang_discount + ': (' + formatMoney(product_discount) + ') ' + formatMoney(order_discount)) + "\n";
            }
            if (site.settings.tax2 != 0 && invoice_tax != 0) {
                bill_data += printLine(lang_tax2 + ': ' + formatMoney(invoice_tax)) + "\n";
            }
            bill_data += printLine(lang_total_payable + ': ' + formatMoney(gtotal)) + "\n";
        } else {
            var bill_totals = '';
            bill_totals += '<tr class="bold"><td>' + lang_total + '</td><td></td><td style="text-align:right;">' + formatMoney(total) + '</td></tr>';
            bill_totals += '<tr class="bold"><td>' + lang_items + '</td><td></td><td style="text-align:right;">' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')</td></tr>';
            if (order_discount > 0) {
                bill_totals += '<tr class="bold"><td>' + lang_discount + '</td><td></td><td style="text-align:right;">' + formatMoney(order_discount) + '</td></tr>';
            }
            if (site.settings.tax2 != 0 && invoice_tax != 0) {
                bill_totals += '<tr class="bold"><td>' + lang_tax2 + '</td><td></td><td style="text-align:right;">' + formatMoney(invoice_tax) + '</td></tr>';
            }
            bill_totals += '<tr class="bold"><td>' + lang_total_payable + '</td><td></td><td style="text-align:right;">' + formatMoney(gtotal) + '</td></tr>';

            if (site.settings.pos_type == 'restaurant') {
                bill_totals += '<tr><td>Waiter </td><td> ' + $('#sales_person').find('option:selected').text() + '</td><td></td>';
                bill_totals += '<tr><td>Date and Time</td><td> ' + hrld() + '</td><td></td>';
            }

            $('#bill-total-table').empty();
            $('#bill-total-table').append(bill_totals);
        }
        if (count > 1) {
            $('#poscustomer').select2("readonly", true);
            $('#poswarehouse').select2("readonly", true);
        } else {
            $('#poscustomer').select2("readonly", false);
            $('#poswarehouse').select2("readonly", false);
        }

        // Hide Keybord on mobile and Android device
        /* if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
         $('input').attr("onfocus","blur()");
         KB = true;
         }
         if (KB) {
         display_keyboards();
         }
         if (site.settings.set_focus == 1) {
         $('#add_item').attr('tabindex', an);
         //  $('[tabindex='+(an-1)+']').focus().select();
         } else {
         $('#add_item').attr('tabindex', 1);
         // $('#add_item').focus();
         }*/
    }
    var customerName = document.getElementById('customer_name').value;
    // Loop through items
    var isExchange = localStorage.getItem("isExchange");
    if(isExchange === true)
    {
        exchangeOperation(isExchange);
    }
}

// Click event for the remove (cross) buttonincrease
$(document).on("click", ".remove", function () {
    var denominationBox = $(this).closest(".denomination-box");
    var countElement = denominationBox.find(".count");
    countElement.text("0"); // Reset count
    denominationBox.removeClass("highlight"); // Remove highlight effect
    $(this).hide(); // Hide remove button again

    $(".denomination-box").each(function () {
        const $box = $(this);
        const $label = $box.find(".denom-label");
        const $countElem = $box.find(".count");
        const $increase = $box.find(".increase");
        const $decrease = $box.find(".decrease");
        const $remove = $box.find(".remove");

        // Clear all inline styles
        $box.css({
            "background-color": "",
            "opacity": "",
            "pointer-events": "",
        });

        $label.add($countElem).css({
            "background-color": "",
            "opacity": "",
            "pointer-events": "",
            "color": ""
        });

        $increase.add($decrease).add($remove).css({
            "opacity": "",
            "pointer-events": "",
        }).prop("disabled", false); // ensure they are not disabled
    });

    //  Inline logic for enabling/disabling return button
   const returnAmt = parseFloat($("#returnAmt").text().replace(/[^\d.-]/g, '')) || 0;
    const collectedAmt = parseFloat($("#Collected").text().replace(/[^\d.-]/g, '')) || 0;

    if (returnAmt === 0 && collectedAmt === 0) {
        $("#returnAmt").prop("disabled", true);
    } else {
        $("#returnAmt").prop("disabled", false);
    }
});

document.addEventListener("DOMContentLoaded", function () {
  // Payment methods that should hide denomination box
  const excludedPayments = [
    "Cheque",
    "award_point",
    "deposit",
    "other",
    "gift_card",
    "NEFT",
    "DC",
    "CC",
    "payswiff",
    "PAYTM",
    "Googlepay",
    "magicpin",
    "complimentry",
    "UPI_QRCODE",
    "ubereats",
    "razorpay",
    "zomato",
    "paytm",
    "swiggy",
    "payumoney",
    "ccavenue",
    "authorize",
    "instamojo",
    "stripe",
    "ppp",
  ];

  // Select DOM elements
  const paymentRadios = document.querySelectorAll('input[name="colorRadio"]');
  const denominationContainer = document.getElementById(
    "denomination-container"
  );
  const clearButton = document.getElementById("clearAll");
  const amountOuterDiv = document.getElementById("yourDivId");
  const paymentBox = document.getElementById("payment-box");

  if (!denominationContainer || !clearButton || !amountOuterDiv) {
    console.error(
      "Error: #denomination-container, #clearAll or #yourDivId not found in the DOM."
    );
    return;
  }

  function toggleDenominationBox(selectedValue) {
    if (excludedPayments.includes(selectedValue)) {
      // Hide denomination container and clear button for excluded payments
      denominationContainer.style.display = "none";
      clearButton.style.display = "none";
  
      amountOuterDiv.style.display = "block";
      paymentBox.style.setProperty("display", "none", "important");
  
      const rect = denominationContainer.getBoundingClientRect();
  
      amountOuterDiv.style.position = "absolute";
      amountOuterDiv.style.top = "10px";
      amountOuterDiv.style.left = "-57rem";
      amountOuterDiv.style.width = "100%";
  
    } else {
      denominationContainer.style.display = "block";
      clearButton.style.display = "block";
  
      if (selectedValue === "cash") {
        // Hide amountOuterDiv (#yourDivId) and show paymentBox
        amountOuterDiv.style.position = "relative";
        amountOuterDiv.style.top = "0px";
        amountOuterDiv.style.left = "0px";
        amountOuterDiv.style.width = "";
  
        amountOuterDiv.style.setProperty("display", "none", "important");
        paymentBox.style.setProperty("display", "block", "important");
  
      } else {
        // Apply custom positioning for non-cash payments
        const rect = denominationContainer.getBoundingClientRect();
  
        amountOuterDiv.style.position = "absolute";
        amountOuterDiv.style.top = `${rect.top + window.scrollY}px`;
        amountOuterDiv.style.left = "-38em"; // or adjust based on design
        amountOuterDiv.style.width = `${rect.width}px`;
  
        amountOuterDiv.style.setProperty("display", "block", "important");
        paymentBox.style.setProperty("display", "none", "important");
      }
    }
  }

  // Attach event listeners to payment radio buttons
  paymentRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      toggleDenominationBox(this.value);
    });
  });

  // Run on page load to check initial state
  const checkedRadio = document.querySelector(
    'input[name="colorRadio"]:checked'
  );
  if (checkedRadio) {
    toggleDenominationBox(checkedRadio.value);
  }
});

$(document).ready(function () {
  $(".final-submit-btn").click(function () {
    //  var formattedData =  saveDenominationDb();
    let CollectedDenomination =
      JSON.parse(localStorage.getItem("denominations")) || {};
    let ReturnsDwnominations =
      JSON.parse(localStorage.getItem("returnDenominationsSaveDB")) || {};

    // Send the data to the server using AJAX
    $.ajax({
      url: site.base_url + "pos/saveDenominations",
      type: "POST",
      data: {
        CollectedDenomination: JSON.stringify(CollectedDenomination),
        ReturnsDwnominations: JSON.stringify(ReturnsDwnominations),
      },
      success: function (response) {
        localStorage.removeItem("denominations");
        localStorage.removeItem("returnDenominationsSaveDB");
        console.log(response);
      },
      error: function (xhr, status, error) {
        // Handle errors
        alert("There was an error saving the data.");
        console.log(error);
      },
    });
  });
});

function resetReturnDenominationUI(loadData) {
  // Reset all denomination counts and highlights
  $("#returnAmount").text("0");
  $("#depositedAmount").text("0");
let totalPayableAmt = loadData();

// If it's a string, remove non-numeric characters and convert
if (typeof totalPayableAmt === 'string') {
    totalPayableAmt = parseFloat(totalPayableAmt.replace(/[^\d.]/g, '')); 
}

// Fallback to 0 if still not a number
if (isNaN(totalPayableAmt)) {
    totalPayableAmt = 0;
    console.error("Failed to parse amount. Setting to 0.");
}
    var invoiceAmount = ($("#amount_1").val()) || 0;
    $("#totalPayableAmount").text(invoiceAmount);
  $(".count").text("0");
  $(".denomination-box").removeClass("highlight highlight-green");
  $(".denomination-box .remove").addClass("hidden").hide();

  // Clear localStorage for denomination data
  localStorage.removeItem("returnDenominationsSaveDB");
  localStorage.removeItem("denominations");

  // Reset UI labels to default values with dynamic currency
  const currencySymbol =
    $(".denom-label")
      .first()
      .text()
      .match(/[^\d.,\s]+/)?.[0] || "";
  $("#selectedValue").text(`${currencySymbol} 0.00`);
  $("#selectedValues").val(`${currencySymbol} 0.00`);
  $("#pendingValue").text(`${currencySymbol} 0.00`);
  $("#pendingValues").val(`${currencySymbol} 0.00`);
  // UI changes
  $(".payment-box").hide();
  $("#yourDivId").hide();
  $("#denomination-container").css("border", "12px solid rgb(230, 245, 255)");
  $("#clearAll").css("border", "12px solid rgb(230, 245, 255)");
}

$(document).on("click", ".reset-denominations", function () {
  // console.log('Reset triggered from close button');

  // Remove stored denomination data
  localStorage.removeItem("denomination_counts");
  localStorage.removeItem("denomination_highlight");
  localStorage.removeItem("denomination_total");

  // Reset inputs
  $(".denomination-count").val(0);
  $(".collected-count").val(0);

  // Remove highlight
  $(".denomination-box").removeClass("highlight");

  // Reset total display areas
  $("#denomination-total").text("0.00");
  $("#collected-total").text("0.00");
  $("#difference-total").text("0.00");
});

$("#payment").on("click", function () {
  // console.log('Checkout clicked, clearing old denomination data');

  // Clear any stored highlights/amounts
  localStorage.removeItem("denomination_counts");
  localStorage.removeItem("denomination_highlight");
  localStorage.removeItem("return_amount_selected");

  $(".denomination-box").removeClass("highlight");
  $(".denomination-count, .collected-count").val("0");
  $("#denomination-total, #collected-total, #difference-total").text("0.00");
  resetReturnDenominationUI(loadData);
});

// Clear data and UI when the modal is fully hidden (not just on button click)
$("#paymentModal").on("hidden.bs.modal", function () {
  // console.log('Modal closed, resetting denomination state.');

  // Clear localStorage (or sessionStorage if you use that)
  localStorage.removeItem("denomination_counts");
  localStorage.removeItem("denomination_highlight");
  localStorage.removeItem("return_amount_selected");

  // Reset input fields
  $(".denomination-count").val(0);
  $(".collected-count").val(0);

  // Remove any highlights
  $(".denomination-box").removeClass("highlight");

  // Reset amount displays (adjust these IDs if yours are different)
  $("#denomination-total, #collected-total, #difference-total").text("0.00");
});


function formatMoneys(x, symbol) {
  if (!symbol) {
    symbol = "";
  }
  if (site.settings.sac == 1) {
    return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
      '' + (parseFloat(x).toFixed(site.settings.decimals)) +
      (site.settings.display_symbol == 2 ? site.settings.symbol : '');
  }
  var fmoney = accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
  fmoney = (fmoney == '-0.00') ? '0.00' : fmoney; //convert -0.00 to 0.00
  return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
    fmoney +
    (site.settings.display_symbol == 2 ? site.settings.symbol : '');
}

///// ayush disable amount field//////////////////////////////
function toggleCashButtons() {
  var selected = $('input[name="colorRadio"]:checked').val();
  var denominationSelectedCount = $('.denomination-box.selected').length;
  let deno = localStorage.getItem("denominations");
  if (selected === 'cash') {
    $('input[name="amount[]"]').prop('', true);  
    if (deno) {
      $('.disable-on-cash').prop('disabled', false);
      $('#edt').prop('disabled', false); 
    } else {
      $('.disable-on-cash').prop('disabled', true);
      $('#edt').prop('disabled', true); 
    }
  } else {
    $('input[name="amount[]"]').prop('', false);  
    $('.disable-on-cash').prop('disabled', false);
    $('#edt').prop('disabled', false); 
  }
}


$('input[name="colorRadio"]').on('change', function () {
  toggleCashButtons();
});

$('#paymentModal').on('shown.bs.modal', function () {
  toggleCashButtons();
});

$(document).on('click', '.denomination-box ', function () {
  $(this).toggleClass('selected');
  toggleCashButtons();
});
$('#clearAll').on('click', function () {
  $('.denomination-box').removeClass('selected');
  localStorage.removeItem("denominations");

  toggleCashButtons();
});
//////////////////////////Splitpay && SplitCheck Disable //////////////////////
$(document).ready(function() {
  setInterval(function() {
      $('#splitpay, #split-check').prop('disabled', true);
  }, 100); 
});
////////////////////////Update Register Modal Trigger //////////////////////
$(document).on('click', '.UpdtRegister', function () {
  // Close the paymentModal
  $('#paymentModal').modal('hide');

  // When the modal is fully hidden
  $('#paymentModal').one('hidden.bs.modal', function () {
    // Trigger the update_register_button
    $('#update_register_button').click();
  });
});
/////////////////////////////CROSSS////////////////////////////////////////////
$(document).on("click", ".reset-denominations", function () {
                location.reload();
                $('#clearAll').trigger('click');
                localStorage.clear(); // This removes ALL keys across your app or domain

});
///////////////////////////PAYMENT METHOD ////////////////////////////////////
$(document).ready(function () {
    $('input[name="colorRadio"]').on('change', function () {
        if ($(this).val() === 'cash') {
            // Simulate click on clearAll when Cash is selected
            $('#clearAll').trigger('click');
            localStorage.clear(); // This removes ALL keys across your app or domain
        }
    });
});
$("#backToPOS").click(function () {
  localStorage.removeItem("denominations");
  localStorage.removeItem("returnDenominationsSaveDB");
  localStorage.removeItem("pendingAmount");
  localStorage.removeItem("denomination_counts");
  localStorage.removeItem("denomination_highlight");
  localStorage.removeItem("denomination_total");
  localStorage.removeItem("return_amount_selected");
});
///////////////////Return button click only when return items are selected///////////////
$(document).ready(function () {
    // Disable the Return Amount button on page load
    $("#returnAmt").prop("disabled", true);

    // Function to enable the Return Amount button
    function enableReturnButton() {
        $("#returnAmt").prop("disabled", false);
    }

    // Function to disable the Return Amount button
    function disableReturnButton() {
        $("#returnAmt").prop("disabled", true);
    }

    // Enable button on these clicks
    $("#Collected").click(enableReturnButton);
    $(document).on('click', '.increase', enableReturnButton);
    $(document).on('click', '.decrease', enableReturnButton);

    // Disable button on clearAll click
    $("#clearAll").click(disableReturnButton);

});
$(document).on('change', 'input[name="colorRadio"]', function () {
    // Clear specific localStorage items
    localStorage.removeItem("denomination_counts");
    localStorage.removeItem("denomination_highlight");
    localStorage.removeItem("return_amount_selected");

    // Set balance value to 0.00 (assuming an element with id "balance")
    $('#balance').val('0.00'); 
    $('#balance').text('0.00'); 
});


