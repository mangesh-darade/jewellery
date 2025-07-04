<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    var oTable;
    $(document).ready(function () {
        // $('#pdf').click(function (event) {
        //     event.preventDefault();
        //     window.location.href = "<?=site_url('products/getCounts/pdf'.($warehouse_id ? '/'.str_replace(",","_", $warehouse_id): ''))?>";
        //     return false;
        // });
        // $('#xls').click(function (event) {
           
        //     event.preventDefault();
        //     window.location.href = "<?=site_url('products/getCounts/xls'.($warehouse_id ? '/'.str_replace(",","_", $warehouse_id): ''))?>";
        //     return false;
        // });
        function count_type(x) {
            if (x == 'full') {
                return '<div class="text-center"><label class="label label-success"><?= lang('full'); ?></label></div>';
            } else if (x == 'partial') {
                return '<div class="text-center"><label class="label label-primary"><?= lang('partial'); ?></label></div>';
            } else {
                return x;
            }
        }
        oTable = $('#STData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getCounts/file'.($warehouse_id ? '/'.str_replace(",","_", $warehouse_id): '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0]; nRow.className = "count_link"; return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": count_type}, null, null, {"bSortable": false, "mRender": attachment2}, {"bSortable": false, "mRender": attachment}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('brands');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('categories');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('products/getCounts'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i>
            <?= lang('stock_counts') . ' (' . ($warehouse_id ? (isset($warehouse->name)?$warehouse->name:lang('all_warehouses')): lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= site_url('products/count_stock') ?>" class="tip" data-placement="top" title="<?= lang("count_stock") ?>">
                        <i class="icon fa fa-plus tip"></i>
                    </a>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products/stock_counts') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                             $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                              if($Owner || $Admin ){
                                echo '<li><a href="' . site_url('products/stock_counts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                                    echo '<li><a href="' . site_url('products/stock_counts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } 
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
     <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" data-action="export_pdf" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" data-action="export_excel"  title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('list_results'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                

                <div class="table-responsive">
                    <table id="STData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?= lang("date") ?></th>
                            <th class="col-xs-2"><?= lang("reference") ?></th>
                            <th class="col-xs-2"><?= lang("warehouse") ?></th>
                            <th class="col-xs-1"><?= lang("type") ?></th>
                            <th class="col-xs-2"><?= lang("brands") ?></th>
                            <th class="col-xs-2"><?= lang("categories") ?></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-file-o"></i></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-chain"></i></th>
                            <th style="max-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-file-o"></i></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-chain"></i></th>
                            <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
