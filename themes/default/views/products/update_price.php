<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog" id="updatePriceModal">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_price'); ?></h4>
        </div>
        <?php
        $OtherField = '';
        $CSVFileName = '';
        if($this->Settings->pos_type=='restaurant' || $this->Settings->pos_type=='bakery' || $this->Settings->pos_type=='sweets'){
            $OtherField = ', '. lang("UP_Price");
            $CSVFileName = '_restaurant';
        }
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("products/update_price", $attrib); ?>
        <div class="modal-body">

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('message')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('message'); ?></div>
            <?php endif; ?>

            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="well well-small">
                        <a href="<?= base_url('products/exportcsvupdateprice') ?>"
                           class="btn btn-primary pull-right"><i
                           class="fa fa-download"></i> <?= lang("download_sample_file") ?></a>
                        <span class="text-warning"><?= lang("csv1"); ?></span><br/>
                        <?= lang("csv2"); ?> 
                        <span class="text-info">
                            (<?= lang("Code") . ', ' . lang("Price"). ', '. lang("MRP").$OtherField; ?>)
                        </span> 
                        <?= lang("csv3"); ?>
                    </div>

                    <div class="form-group">
                        <label for="csv_file"><?= lang("upload_file"); ?></label>
                        <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false"
                        data-show-preview="false" accept=".xls" required="required"/>
                    </div>

                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('update_price', lang('update_price'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>

<?php if ($this->session->flashdata('modal_error') || $this->session->flashdata('message')): ?>
<script>
    $(document).ready(function () {
        $('#updatePriceModal').modal('show');
    });
</script>
<?php endif; ?>
