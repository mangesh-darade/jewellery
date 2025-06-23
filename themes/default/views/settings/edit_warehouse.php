<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Edit_location'); ?></h4>
        </div>
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_warehouse/" . $id, $attrib);
        ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label" for="code"><?php echo $this->lang->line("Code"); ?></label>
                        <?php echo form_input('code', $warehouse->code, 'class="form-control" id="code" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="name"><?php echo $this->lang->line("Name"); ?></label>
                        <?php echo form_input('name', $warehouse->name, 'class="form-control" id="name" required="required" pattern="^[A-Za-z\s]+$"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="country"><?php echo $this->lang->line("Country"); ?></label>
                        <?php echo form_input('country', ($warehouse->country ? $warehouse->country : 'India'), 'class="form-control" id="country"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="city"><?php echo $this->lang->line("City"); ?> Name</label>
                        <?php echo form_input('city', $warehouse->city, 'class="form-control" required="required" id="city" required="required" pattern="^[A-Za-z\s]+$"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="state"><?php echo $this->lang->line("State"); ?></label>
                        <?php
                        $state_list[''] = lang('select') . ' ' . lang('state');
                        foreach ($states as $state) {
                            $state_list[$state->id . '~' . $state->code] = $state->name;
                            if ($warehouse->state == $state->id) {
                                $selectSt = $state->id . '~' . $state->code;
                            }
                        }
                        echo form_dropdown('state', $state_list, $selectSt, 'class="form-control tip select" required="required" id="state" style="width:100%;"');
                        ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="owned_by"><?php echo $this->lang->line("Owned_By"); ?></label>
                        <?php
                            $name[''] = lang('select').' '.lang('Select Owned By');
                            foreach ($owned_by as $owned_by) {
                                $name[$owned_by->id] = $owned_by->name;
                            }
                             echo form_dropdown('owned_by', $name, $warehouse->owned_by, 'class="form-control tip select" id="owned_by" style="width:100%;"');
                        ?>
                    </div>

                    <!-- <div class="form-group">
                        <label class="control-label" for="batch_production">Batch Production</label>
                        <select name="batch_production" class="form-control" id="batch_production" required>
                            <option value="">Select an option</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div> -->


                    <div class="form-group">
                        <label class="control-label" for="batch_production">Batch Production</label>
                        <select name="batch_production" class="form-control" id="batch_production" required>
                            <option value="">Select an option</option>
                            <option value="yes"
                                <?php echo isset($warehouse->batch_production) && $warehouse->batch_production == 1 ? 'selected' : ''; ?>>
                                Yes</option>
                            <option value="no"
                                <?php echo isset($warehouse->batch_production) && $warehouse->batch_production == 0 ? 'selected' : ''; ?>>
                                No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="address_line1"><?php echo $this->lang->line("Address_Line1"); ?></label>
                        <?php echo form_input('address_line1', $warehouse->address_line1, 'class="form-control" id="address_line1" '); ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                            for="contact_person_name"><?php echo $this->lang->line("Contact Person*"); ?></label>
                        <?php echo form_input('contact_person_name',  $warehouse->contact_person_name, 'class="form-control" id="contact_person_name" '); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="primary_biller"><?php echo $this->lang->line("Primary_biller*"); ?></label>
                        <?php
                             $biller_name[''] = lang('select').' '.lang('Primary_Biller');
                            foreach ($billers_data as $biller_data) {
                                $biller_name[$biller_data['id']] = $biller_data['name'];
                            }                        
                            echo form_dropdown('primary_billers_disabled', $biller_name, $warehouse->primary_biller_id, 'class="form-control tip select" id="primary_biller" style="width:100%;" disabled');
                            // Hidden input to submit the value
                            echo form_hidden('primary_billers', $warehouse->primary_biller_id);
                        ?>
                    </div>



                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label"
                            for="price_group"><?php echo $this->lang->line("Price_group"); ?></label>
                        <?php
                        $pgs[''] = lang('select') . ' ' . lang('price_group');
                        foreach ($price_groups as $price_group) {
                            $pgs[$price_group->id] = $price_group->name;
                        }
                        echo form_dropdown('price_group', $pgs, $warehouse->price_group_id, 'class="form-control tip select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="phone"><?php echo $this->lang->line("Phone*"); ?></label>
                        <?php echo form_input('phone', $warehouse->phone, 'class="form-control" id="phone" minlength="2" maxlength="10" pattern="^[0-9]+$"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="email"><?php echo $this->lang->line("Email"); ?></label>
                        <?php echo form_input('email', $warehouse->email, 'class="form-control" id="email"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="postal_code">Pincode</label>
                        <?php echo form_input('postal_code', $warehouse->postal_code, 'class="form-control" required="required" id="postal_code" pattern="^[0-9]+$"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="state_code">State Code</label>
                        <?php echo form_input('state_code', $warehouse->state_code, 'class="form-control" readonly " id="state_code"'); ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                            for="location_type"><?php echo $this->lang->line("Location_Type"); ?></label>
                        <?php
                            $type[''] = lang('select').' '.lang('Select Location Type');
                            foreach ($location_types as $location_type) {
                                $type[$location_type->id] = $location_type->type;
                            }
                             echo form_dropdown('location_type', $type, $warehouse->location_type, 'class="form-control tip select" id="location_type" style="width:100%;"');
                        ?>
                    </div>

                    <!-- <div class="form-group">
                        <label class="control-label" for="retail_production">Retail Production</label>
                        <select name="retail_production" class="form-control" id="retail_production" required>
                            <option value="">Select an option</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div> -->


                    <div class="form-group">
                        <label class="control-label" for="retail_production">Retail Production</label>
                        <select name="retail_production" class="form-control" id="retail_production" required>
                            <option value="">Select an option</option>
                            <option value="yes"
                                <?php echo isset($warehouse->retail_production) && $warehouse->retail_production == 1 ? 'selected' : ''; ?>>
                                Yes</option>
                            <option value="no"
                                <?php echo isset($warehouse->retail_production) && $warehouse->retail_production == 0 ? 'selected' : ''; ?>>
                                No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="address_line2"><?php echo $this->lang->line("Address_Line2"); ?></label>
                        <?php echo form_input('address_line2', $warehouse->address_line2, 'class="form-control" id="address_line2" '); ?>
                    </div>
                     <?php if ($Settings->use_invoice_number_prefix) { ?>
                    <div class="form-group">
                        <label class="control-label" for="bill_prefix">
                            <?php echo $this->lang->line("Bill_Prefix"); ?>
                        </label>
                        <?php echo form_input('bill_prefix', $warehouse->bill_prefix, 'class="form-control" pattern="[A-Za-z]+" title="Letters only" id="bill_prefix" readonly'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label"
                            for="other_billers"><?php echo $this->lang->line("Other_biller"); ?></label>(Select a
                        primary biller first.)
                        <?php
                             $biller_name[''] = '';
                            foreach ($billers_data as $biller_data) {
                                $biller_name[$biller_data['id']] = $biller_data['name'];
                            }
                            echo form_dropdown('other_billers[]',$biller_name,explode(',', $warehouse->other_biller_id),'class="form-control tip select" id="other_billers" multiple="multiple" style="width:100%;"disabled');
                        ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 form-group">
                    <label class="control-label" for="address"><?php echo $this->lang->line("Address"); ?></label>
                    <?php echo form_textarea('address', $warehouse->address, 'class="form-control" id="address" required="required"'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 form-group">
                    <?= lang("Location_Map", "image") ?>
                    <input id="image" type="file" data-browse-label="<?= lang('Mrowse'); ?>" name="userfile"
                        data-show-upload="false" data-show-preview="false" class="form-control file">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label class="control-label" for="is_active"><?php echo $this->lang->line("Status"); ?></label>
                    <?php
                         $status = ["0" => "Deactive", "1" => "Active"];

                         echo form_dropdown('is_active', $status, $warehouse->is_active, 'class="form-control tip select" id="is_active" style="width:100%;"');
                         ?>
                </div>
                <div class="col-sm-6 form-group">
                    <label class="control-label"
                        for="is_disabled"><?php echo $this->lang->line("POS Status"); ?></label>
                    <?php
                    $posstatus = ["1" => "Disabled", "0" => "Enable"];

                    echo form_dropdown('is_disabled', $posstatus, $warehouse->is_disabled, 'class="form-control tip select" disabled="disabled" id="is_disabled" style="width:100%;"');
                    ?>
                </div>
            </div>
            <?php if ($eshop_setting->active_multi_outlets) { ?>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label"
                            for="in_eshop"><?php echo $this->lang->line("In Eshop as Outlet"); ?></label>
                        <?php
                            $ineshop = ["0" => "Deactive", "1" => "Active"];

                            echo form_dropdown('in_eshop', $ineshop, $warehouse->in_eshop, 'class="form-control tip select" id="in_eshop" style="width:100%;"');
                            ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label"
                            for="eshop_biller_id"><?php echo $this->lang->line("Select Outlet Biller"); ?></label>
                        <?php
                            if (is_array($billers)) {
                                foreach ($billers as $key => $biller) {
                                    $eshop_billers[$biller['id']] = $biller['name'];
                                }
                            }
                            echo form_dropdown('eshop_biller_id', $eshop_billers, $warehouse->eshop_biller_id, 'class="form-control tip select" id="eshop_biller_id" style="width:100%;"');
                            ?>
                    </div>
                </div>

            </div>


            <?php }//end if.   ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_warehouse', lang('Edit_Location'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/modal.js"></script>
<script>
$(document).ready(function() {
    const allOptions = $('#other_biller option').clone();
    $('#other_biller').prop('disabled', true);
    $('#primary_biller').on('change', function() {
        const selected = $(this).val();
        if (selected === '') {
            $('#other_biller').prop('disabled', true);
            $('#other_biller').select2({
                placeholder: "Select other biller",
                allowClear: true
            });
            return;
        }
        $('#other_biller').html(allOptions); // Reset all options
        $('#other_biller').prop('disabled', false);
        // Remove the selected value from Other Biller
        $('#other_biller option').each(function() {
            if ($(this).val() === selected && selected !== '') {
                $(this).remove();
            }
        });
    });
    $('#other_biller').select2({
        placeholder: "Select other_biller"
    });

});
$(document).on('input', '#bill_prefix', function() {
    this.value = this.value.replace(/[^a-zA-Z]/g, '');
});
</script>