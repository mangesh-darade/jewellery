<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = "";
if($this->input->post('category'))
{
    $v .= "&category=" . $this->input->post('category');
}
if($this->input->post('brand'))
{
    $v .= "&brand=" . $this->input->post('brand');
}
if($this->input->post('warehouse'))
{
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
?>
<style>
    thead th:nth-child(3) .yadcf-filter-wrapper input,
thead th:nth-child(4) .yadcf-filter-wrapper input {
    text-align: left;
}

tbody td:nth-child(3),
tbody td:nth-child(4) {
    text-align:left;
}
</style>
<style>
    .variant-col {
        text-align: center !important;
    }
    .variant-left{
        text-align: left !important;
    }
</style>
<script> 
    $(document).ready(function () {
		<?php $j=4; ?>
		//alert('<?php echo base_url(); ?>reports/load_product_varient_report');
		$('#PrRData').DataTable({
		"processing": true,
        "serverSide": true,
		"ordering": false,
		"lengthMenu": [20, 40, 60, 80, 100],
        "pageLength": 20,
		'rowsGroup': [2],
        "ajax": {
			'type': 'POST',
            "url": '<?= site_url('reports/load_product_varient_stock_report/?v=1'. $v) ?>',
			//"dataSrc": "",
            'data': {
			   action: 'List',
			},
			error: function (xhr, error, code)
            {
                console.log(xhr);
                console.log(code);
                console.log(error);
            },
        },
        "columns": [
            { "data": "name" },
            { "data": "code" },
            { "data": "cat_name", "className": "text-left variant-left" },
            { "data": "brand_name", "className": "text-left variant-left" },
            { "data": "qty_product_cost", "mRender": formatQuantity },
			<?php foreach($varient_name as $key_varient_name =>$value_varient_name) { ?>
				{ "data": "v_<?php echo $value_varient_name['id']; ?>",
                    "className": "text-center variant-col",
                    "mRender": formatQuantity
                },
			<?php }?>
        ],
		
		"fnCreatedRow": function( nRow, aData, iDataIndex ) {
			$(nRow).attr('id', 'tr_'+aData['product_id']);
			$("#PrRData tr td").each(function() {
				var cellText = $.trim($(this).text());
				if (cellText.length == 0) {
					//$(this).parent().addClass('sdsdsd');
				}
			});
		}
		
    });
	$('.search_right').css('text-align', 'left');
    });
</script>



<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Product_Varient_Stock_Report'); ?> <?php
            if($this->input->post('start_date'))
            {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
		</h2>
		<div  >
                <?php $startcount=0;$count=$limit_product_varient;$addcount =500;$endcount=500;$seccount=0;
                ?>
                <div class="col-sm-2" style="position:absolute; right:222px;">
                    <span class="control-label" for="sales" style="display: inline-block; margin-top: 6px; margin-right: 4px;">Excel download limit</span>
                <select class="form-control" name="limitpdf" id="limitpdf" style="width:200px; position:absolute;">
                <option value="0">Select</option>
                <?php
                    for ( $startcount=0; $count>=$startcount; $startcount = $startcount+$endcount ) {
                        $seccount = $startcount + $endcount;
                ?>
                <option value="<?php echo $startcount.'-'.$endcount; ?>"><?php echo $startcount.'-'.$seccount; ?></option>
               <?php  }  ?>
            </select>
            </div>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <!--<li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>-->
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
               <!-- <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>-->
            </ul>
        </div>
    </div>
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form" >

                    <?php echo form_open("reports/product_varient_stock_report"); ?>
                    <div class="row"> 
						 <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select') . ' ' . lang('warehouse');
                                foreach($warehouses as $warehouse)
                                {
                                	if($Owner || $Admin ){
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }else if(in_array($warehouse->id,$permisions_werehouse)){
                                           $wh[$warehouse->id] = $warehouse->name;
                                        }    
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select') . ' ' . lang('category');
                                foreach($categories as $category)
                                {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("brand", "brand") ?>
                                <?php
                                $bt[''] = lang('select') . ' ' . lang('brand');
                                foreach($brands as $brand)
                                {
                                    $bt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						 <div class="col-sm-2">
                            <div class="form-group margin-top-30">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <!--<input type="button" id="report_reset" data-value="<?= base_url('reports/products'); ?>"
                                   name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                                <a href="reports/restbutton" class="btn btn-success">Reset</a>

                        </div>
                    </div>
                        </div>
                    </div>
                   
                    <?php echo form_close(); ?>

                </div>
                </div>
                </div>
                <div class="clearfix"></div>
<div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
					<?php //foreach($varient_name as $key_varient_name =>$value_varient_name) { echo $value_varient_name['id'].' '.$value_varient_name['name']; }?>
                    <table id="PrRData" class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr >
                           <th><?= lang("Product_Name"); ?></th>
                            <th><?= lang("Code"); ?></th>
                           <th><?= lang("Category"); ?></th>
                            <th><?= lang("Brand"); ?></th>
                           <th><?= lang("Total Qty"); ?></th>
						   <?php if(!empty($varient_name)){ foreach($varient_name as $key_varient_name =>$value_varient_name) { echo '<th >'.$value_varient_name['name'].'</th>'; }} ?>
                        </tr>
                        </thead>
                       
                        <tfoot>
                        <tr >
                           <th><?= lang("Product_Name"); ?></th>
                            <th><?= lang("Code"); ?></th>
                           <th><?= lang("Category"); ?></th>
                            <th><?= lang("Brand"); ?></th>
                           <th><?= lang("Total Qty"); ?></th>
						<?php if(!empty($varient_name)){ foreach($varient_name as $key_varient_name =>$value_varient_name) { echo '<th >'.$value_varient_name['name'].'</th>'; }} ?>
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
		
       /* $('#pdf').click(function (event) {
            event.preventDefault();
			var limitcnt =  $("#limitpdf option:selected").val();
            if(limitcnt=='0'){
              alert('Please Select Pdf/Excel limit');
            }else{
              <?php 
              $v .= "&strtlimit="?>
               window.location.href = "<?=site_url('reports/load_product_varient_stock_report/pdf/?v=export' . $v)?>"+limitcnt;
               $("#limitpdf").val(0).change();
              return false;
            }
			
        });*/
        $('#xls').click(function (event) {
            event.preventDefault();
			var limitcnt =  $("#limitpdf option:selected").val();
            if(limitcnt=='0'){
              alert('Please Select Pdf/Excel limit');
            }else{
               window.location.href = "<?=site_url('reports/load_product_varient_stock_report/xls/?v=export' . $v)?>&strtlimit="+limitcnt;
               $("#limitpdf").val(0).change();
              return false;
            }
			
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/load_product_varient_stock_report/img/?v=export' . $v)?>";
            /*html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });*/
            return false;
        });
    });
</script>
