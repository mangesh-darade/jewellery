<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#PExData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getExpiryAlerts' . ($warehouse_id ? '/' . str_replace(",","_", $warehouse_id) : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": img_hl}, null, null, null, {"mRender": formatQuantity }, null, {"mRender": fsd}, {}],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('variants');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('batch_number');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-calendar-o"></i><?= lang('product_expiry_alerts') . ' (' . ($warehouse_id ?(isset($warehouse[$this->uri->segment(3)]->name) ? $warehouse[$this->uri->segment(3)]->name : lang('all_warehouses'))  : lang('all_warehouses')) . ')'; ?>
        </h2>
       
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php //if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i>
                        </a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li>
                                <a href="<?= site_url('reports/expiry_alerts') ?>">
                                    <i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <?php
                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                foreach ($warehouses as $warehouse) {
                                    if($Owner || $Admin   ){
                                        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/expiry_alerts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                                        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/expiry_alerts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                }
                            ?>
                        </ul>
                    </li>
                <?php //} ?>
            </ul>
        </div>
         <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
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
                    <table id="PExData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped dfTable reports-table">
                        <thead>
                        <tr class="active">
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?php echo $this->lang->line("product_code"); ?></th>
                            <th><?php echo $this->lang->line("product_name"); ?></th>
                            <th><?php echo $this->lang->line("variants"); ?></th>
                            <th><?php echo $this->lang->line("quantity"); ?></th>
                            <th><?php echo $this->lang->line("warehouse"); ?></th>
                            <th><?php echo $this->lang->line("expiry_date"); ?></th>
                            <th><?php echo $this->lang->line("batch_number"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getExpiryAlerts/'.($warehouse_id ? $warehouse_id : '0').'/pdf')?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getExpiryAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/xls')?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getExpiryAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/0/img')?>";
           /* html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });*/
            return false;
        });
    });
</script>
