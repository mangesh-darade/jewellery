<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    $('#CURData').dataTable({
        "aaSorting": [
            [1, "asc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "<?= lang('all') ?>"]
        ],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= site_url('system_settings/getVariants') ?>',
        'fnServerData': function(sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
        },
        "aoColumns": [{
            "bSortable": false,
            "mRender": checkbox
        }, null, {
            "bSortable": false
        }, null]
    });
});
</script>

<?php if ($Owner || $GP['bulk_actions']) {
	    echo form_open('system_settings/varient_action', 'id="action-form"');
	}
?>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-list"></i><?= $page_title ?></h2>

        <!--<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?php echo site_url('system_settings/add_variant'); ?>"
                                        data-toggle="modal" data-target="#myModal"><i class="icon fa fa-plus"></i></a>
                </li>
            </ul>
        </div>-->
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo site_url('system_settings/add_variant'); ?>" data-toggle="modal"
                                data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('Add_Variant') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('system_settings/import_varient'); ?>" data-toggle="modal"
                                data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('Import_Variants') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?=lang("delete_purchases")?></b>"
                                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('Delete Variants')?>
                            </a>
                        </li>

                    </ul>
                </li>
            </ul>
        </div>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext" style="display:none;"><?php echo $this->lang->line("list_results"); ?></p>

                <div class="table-responsive">
                    <table id="CURData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?php echo $this->lang->line("name"); ?></th>
                                <th><?php echo $this->lang->line("Variants_Group"); ?></th>

                                <th style="width:65px;"><?php echo $this->lang->line("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {?>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
</div>
<?=form_close()?>
<?php }
?>