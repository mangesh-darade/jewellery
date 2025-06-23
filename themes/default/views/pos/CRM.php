<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Required CSS & JS -->
<script type="text/javascript" src="<?= $assets ?>pos/js/customer_family_relation.js"></script>
<script type="text/javascript" src="<?= $assets ?>pos/js/edit_customer_details.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="<?= $assets ?>pos/css/customer_relation.css" type="text/css" />

<!-- Styling -->
<style>
.tab-nav {
    display: flex;
    list-style: none;
    padding-left: 0;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 10px;
}

.tab-nav li {
    margin-right: 10px;
}

.tab-button {
    background: none;
    border: none;
    padding: 10px 20px;
    font-weight: bold;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    color: #333;
    transition: all 0.2s ease;
}

.tab-button.active {
    color: #007bff;
    border-bottom: 3px solid #007bff;
    background-color: #f5f5f5;
}

.hidden_div {
    display: none;
}

@media (min-width: 1441px) and (max-width: 1600px) {
    .form-container {
        gap: 3px !important;
        padding: 8px !important;
        /* margin-left: 10px; */
    }

    div#name-error {
        position: absolute;
        margin-top: 54px;
        margin-left: 21rem !important;
    }

    div#event_type-error {
        position: absolute;
        margin-top: 55px;
        margin-left: 46rem !important;
    }

    div#date-error {
        position: absolute;
        margin-top: 57px;
        margin-left: 65rem !important;
    }

    input#personName {
        width: 160px !important;
    }

}
</style>

<!-- Modal Structure -->
<div class="container">
    <div class="mymodal" id="modal-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                        <span class="sr-only"><?= lang('close'); ?></span>
                    </button>

                    <ul class="tab-nav sub-tab-nav">
                        <li><button id="showDivButton3" class="tab-button active">Profile</button></li>
                        <li><button id="showDivButton4" class="tab-button">Family & Relations</button></li>
                    </ul>
                </div>

                <div class="modal-body profile" id="profileSection" style="display: block;">
                    <?php $this->load->view($this->theme . 'pos/edit_customer_details', $this->data); ?>
                </div>

                <div class="modal-body family_relation" id="relationSection" style="display: none;">
                    <?php $this->load->view($this->theme . 'pos/customer_family_relation', $this->data); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Behavior Script -->
<script>
var phone = <?= json_encode($phone); ?>;
customer_details(phone);
document.addEventListener("DOMContentLoaded", function() {
    const btnProfile = document.getElementById("showDivButton3");
    const btnRelation = document.getElementById("showDivButton4");
    const profileSection = document.getElementById("profileSection");
    const relationSection = document.getElementById("relationSection");

    profileSection.style.display = "block";
    relationSection.style.display = "none";

    // When 'Profile' tab is clicked
    btnProfile.addEventListener("click", function () {
        if (!btnProfile.classList.contains("active")) {
            btnProfile.classList.add("active");
            btnRelation.classList.remove("active");

            profileSection.style.display = "block";
            relationSection.style.display = "none";
        }
    });

    // When 'Family & Relations' tab is clicked 
    btnRelation.addEventListener("click", function () {
        if (!btnRelation.classList.contains("active")) {
            btnRelation.classList.add("active");
            btnProfile.classList.remove("active");

            profileSection.style.display = "none";
            relationSection.style.display = "block";
        }
    });

});
</script>
<script>
    
</script>