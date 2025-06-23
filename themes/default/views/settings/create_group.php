<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('create_group'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/create_group", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang("Group_Name *", "Group_Name"); ?></label>
                <?php echo form_input('group_name', '', 'class="form-control" id="group_name" required="required"'); ?>
            </div>
           <div class="form-group">
                <label for="screen_selector"><?= lang("Select_Screen", "screen_selector"); ?></label>
                <div class="controls">
                    <?php
                    $options = [];
                    if (!empty($getAllStartUpScreens)) {
                        foreach ($getAllStartUpScreens as $screen) {
                            $options[$screen->screen_name] = $screen->label;
                        }
                    }
                    echo form_dropdown(
                        'screen_selector',
                        $options,
                        set_value('screen_selector', isset($_POST['screen_selector']) ? $_POST['screen_selector'] : ''),
                        'class="form-control tip scrollable-dropdown" id="screen_selector" style="width:100%;" size="5"'
                    );
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?= lang("Description *", "Description"); ?></label>
                <?php echo form_input('description', '', 'class="form-control" id="description" required="required"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('create_group', lang('create_group'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
  $(document).ready(function() {
    $('#screen_selector').select2({
      placeholder: '-- Select Screen --',
      width: '100%',
      dropdownParent: $('#myModal') // Replace with your actual modal ID
    });
  });
</script>
<?= $modal_js ?>
