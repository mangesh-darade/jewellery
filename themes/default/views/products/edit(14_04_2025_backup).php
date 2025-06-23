<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
if (!empty($variants)) {
    foreach ($variants as $variant) {
        $vars[] = addslashes($variant->name);
    }
} else {
    $vars = array();
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#category').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });
        $('#code').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('update_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/edit/" . $product->id, $attrib)
                ?>
                <input type="hidden" name="pos_type" id="pos_type" value="<?= $Settings->pos_type ?>" />
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <?= lang("Product Type", "product type") ?>
                                <?php
                                $opts = array('standard' => lang('standard'), 'combo' => lang('combo'),'Bundle' => lang('Bundle'), 'digital' => lang('digital'), 'service' => lang('service'));
                                echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">                            
                            <div class="form-group all">
                                <?= lang("Storage Type", "storage_type") ?>
                                <?php                        
                                $ist = ['packed'=>'Packed Products', 'loose'=>'Loose Products'];
                                echo form_dropdown('storage_type', $ist, (isset($_POST['storage_type']) ? $_POST['storage_type'] : $product->storage_type), 'class="form-control select" id="storage_type" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                ?>
                            </div>                           
                        </div>
                    </div>
                    <?php if ($Settings->pos_type == 'restaurant') { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group all">
                                <?= lang("Division", "division") ?>

                                <?php
                                $div[''] = "";
                                foreach ($division as $division) {
                                    $div[$division->id] = $division->name;
                                }
                                echo form_dropdown('division', $div, (isset($_POST['divisionid']) ? $_POST['divisionid'] : ($product ? $product->divisionid : $_SESSION['divisionid'])), 'class="form-control select" id="division" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group all">
                                <?= lang("product_name", "name") ?>
                                <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_code", "code") ?>
                                <div class="input-group">
                                    <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code"  required="required"') ?>
                                    <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                        <i class="fa fa-random"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                     </div>
                    <div class="form-group all">
                        <?= lang("Product_Weight", 'weight') ?>
                        <div class="input-group">
                            <?= form_input('weight', (isset($_POST['weight']) ? $_POST['weight'] : ($product ? $product->weight : '')), 'class="form-control" id="weight" size="6" ') ?>
                            <span class="input-group-addon" style="padding: 1px 10px;">
                                / Kilogram (KG)
                            </span>
                        </div>  
                        <span class="help-block"><?= lang('Products weight should be in Kilogram (Ex. 1Kg = 1 | 500Gm = 0.500 | 250Gm = 0.250, etc.)') ?></span>
                    </div>
                    <!-- 12-03-19 -->
                    
                    <script>
                        $("document").ready(function () {
                            setTimeout(function () {
                                var url = "<?= site_url('products/add') ?>";
                                //alert(url);
                                //$("[id*='random_num']").trigger('click');
                                //window.location.href='products/add';
                                return false;
                            }, 10);
                        });
                    </script>
                    
                    <div class="row">
                     <div class="col-md-6">
                        <div class="form-group standard">
                        <?= lang("alert_quantity", "alert_quantity") ?>
                        <div
                            class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->sma->formatDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                            <span class="input-group-addon">
                                <input type="checkbox" name="track_quantity" id="inlineCheckbox1"
                                       value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                            </span>
                        </div>
                        </div>
                     </div>
                    <?php
                    if (!empty($brands) && is_array($brands)) {
                    ?>
                    <div class="col-md-6">
                    <div class="form-group all">                         
                        <?= lang("brand", "brand") ?>
                        <?php
                        $br[''] = "";
                        foreach ($brands as $brand) {
                            $br[$brand->id] = $brand->name;
                        }
                        echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                        
                        ?>
                    </div>
                    </div>
                    <?php } ?>
                    </div>
                    <div class="row">
                    <div class="col-md-6">
                    <div class="form-group all">
                       <?= lang("category", "category") ?>
                        <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                        ?>
                    </div>
                    </div>
                     <div class="col-md-6">
                    <div class="form-group all">
                       <?= lang("subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                    </div>
                    </div>
                   <div class="row">
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("Article Number", "Article Number") ?>
                                 <?= form_input('article_code', (isset($_POST['article_code']) ? $_POST['article_code'] : ($product ? $product->article_code : '')), 'class="form-control" id="article_no"  ') ?>
                            </div> 
                        </div>  
                    <!-- End 12-03-19 -->
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("hsn_code", "hsn_code") ?> Code
                                  <?= form_input('hsn_code', (isset($_POST['hsn_code']) ? $_POST['hsn_code'] : ($product ? $product->hsn_code : '')), 'class="form-control" id="hsn_code"  '); ?>
                            </div>
                        </div> 
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("barcode_symbology", "barcode_symbology") ?>
                                <?php
                                $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E');
                                echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" readonly="readonly" ');
                                ?>
                            </div>
                        </div>
                    </div>
                    
                     <div class="row">
                     <div class="col-md-4">
                    <div class="form-group standard">
                        <?= lang('product_unit', 'unit'); ?>
                        <?php
                        $pu[''] = lang('select') . ' ' . lang('unit');
                        foreach ($base_units as $bu) {
                            $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                        }
                        ?>
                        <?= form_dropdown('unit', $pu, set_value('unit', $product->unit), 'class="form-control tip" required="required" id="unit" style="width:100%;"'); ?>
                    </div>
                    </div>
                    <div class="col-md-4">
                    <div class="form-group standard">
                          <?= lang('Sale_Unit', 'default_sale_unit'); ?>
                        <?php
                        $uopts[''] = lang('select') . ' ' . lang('unit');
                        foreach ($subunits as $sunit) {
                            $uopts[$sunit->id] = $sunit->name . ' (' . $sunit->code . ')';
                        }
                        ?>
                        <?= form_dropdown('default_sale_unit', $uopts, $product->sale_unit, 'class="form-control" id="default_sale_unit" style="width:100%;"'); ?>
                    </div>
                    </div>
                    <div class="col-md-4">
                    <div class="form-group standard">
                        <?= lang('Purchase_Unit', 'default_purchase_unit'); ?>
                        <?= form_dropdown('default_purchase_unit', $uopts, $product->purchase_unit, 'class="form-control" id="default_purchase_unit" style="width:100%;"'); ?>
                    </div>
                    </div>
                    </div>
                    <!--
                    <div class="form-group standard">
                        <?= lang("product_cost", "cost") ?> *
                        <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->sma->formatDecimal($product->cost) : '')), 'class="form-control tip custom_price" id="cost" required="required"') ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_price", "price") ?>
                        <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price" required="required"') ?>
                    </div>
                    
                     

                    <div class="form-group all">
                        <?= lang("product_mrp", "price") ?>
                        <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                    </div>
                    -->
                    <div class="row"> 
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_mrp", "mrp") ?>
                                <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                            </div>
                        </div>  
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_price", "price") ?>
                                <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price"') ?>
                            </div>
                        </div>
                    
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group all">
                                <label for="discount_on_mrp"><?= lang("Discount On MRP", "discount_on_mrp") ?></label>
                                <!-- <?= form_input('discount_on_mrp', $product->discount_on_mrp, 'class="form-control" id="discount_on_mrp" placeholder=""'); ?> -->
                                <?= form_input('discount_on_mrp', (isset($product->discount_on_mrp) ? $product->discount_on_mrp: ''), 'class="form-control tip" id="discount_on_mrp"') ?>
                            
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang("product_cost", "cost") ?> *
                                <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->sma->formatDecimal($product->cost) : '')), 'class="form-control tip custom_price" id="cost" required="required"') ?>
                            </div>
                        </div>
                        
                    </div>  
                    
                    
                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group all">
                                  <?= lang("Repeat Sale Discount Rate", "repeat_sale_discount_rate") ?>
                                <input type="text" id="repeat_sale_discount_rate" value="<?= (isset($_POST['repeat_sale_discount_rate']) ? $_POST['repeat_sale_discount_rate']:$product->repeat_sale_discount_rate) ?>" placeholder="Repeat Sale Discount Rate" class="form-control" name="repeat_sale_discount_rate" />
                                
                            </div>    
                        </div>
                         <div class="col-md-6">
                              <?= lang("Repeat_Sale_Validity", "repeat_sale_validity") ?>
                             <input type="number" min="1" id="repeat_sale_validity" placeholder="Repeat Sale Validity In Days" value="<?= (isset($_POST['repeat_sale_validity']) ? $_POST['repeat_sale_validity']:$product->repeat_sale_validity) ?>" class="form-control" name="repeat_sale_validity" />
                                
                        </div>
                    </div>
                    
                    <div style="display: flex" ;>
                            <div class="form-group col-xs-7" style="padding-left: 0 !important;">
                             <?= lang("Product_Rank", "Product Rank") ?>
                             <input type="number" name="rank" class="product-rank" id="rank"
                                 value="<?= isset($_POST['rank']) ? $_POST['rank'] : ($product ? $product->rank : '') ?>"
                                 min="0" >
                            </div>
                            <div class="form-group col-xs-5">
                             <input type="checkbox" class="checkbox" value="1" name="flag_visible"
                                 <?= isset($product->flag_visible) && $product->flag_visible == 1 ? 'checked="checked"' : ''; ?>>
                             <label for="flag_visible" class="padding05">
                                 <?= lang("Flag_Visible", "Flag Visible"); ?>
                             </label>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group all">
                                <?= lang("Shelf_Life", "shelf_life") ?>
                                <?= form_input('shelf_life', (isset($_POST['shelf_life']) ? $_POST['shelf_life'] : ($product ? $product->shelf_life : '')), 'class="form-control" id="shelf_life"'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group all">
                         <?= lang("Season", "season") ?>
                         <?php 
                            $br1[''] = lang('select').' '.lang('season');
                            if (!empty($seasons) && is_array($seasons)) {
                                foreach ($seasons as $season) {
                                    $br1[$season->id] = $season->SeasonName; 
                                }
                            }
                            echo form_dropdown( 'season', $br1, set_value('season', $product->season_id),  'class="form-control tip" required="required" id="season" style="width:100%;"');
                            ?>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion" <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                        <label for="promotion" class="padding05">
                            <?= lang('promotion'); ?>
                        </label>
                    </div>

                    <div id="promo"<?= $product->promotion ? '' : ' style="display:none;"'; ?>>
                        <div class="well well-sm">
                            <div class="row">
                            <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('promo_price', 'promo_price'); ?>
                                <?= form_input('promo_price', set_value('promo_price', $product->promo_price ? $this->sma->formatDecimal($product->promo_price) : ''), 'class="form-control tip" id="promo_price"'); ?>
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?= form_input('start_date', set_value('start_date', $product->start_date ? $this->sma->hrld($product->start_date) : ''), 'class="form-control tip datetime" id="start_date"'); ?>
                            </div>
                                </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?= form_input('end_date', set_value('end_date', $product->end_date ? $this->sma->hrld($product->end_date) : ''), 'class="form-control tip datetime" id="end_date"'); ?>
                            </div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($Settings->tax1) { ?>
                         <div class="row">
                        <div class="col-md-8">
                        <div class="form-group all" >
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tx[""] = "";
                            foreach ($tax_rates as $tax) {
                                if($tax->is_substitutable == 0) { 
                                    $tx[$tax->id] = $tax->name;
                                }
                            }
                            echo form_dropdown('tax_rate', $tx, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                            ?>
                        </div>
                        </div>
                         <div class="col-md-4">
                        <div class="form-group all"  >
                            <?= lang("tax_method", "tax_method") ?>
                            <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                            ?>
                        </div>
                         </div>
                        </div>
                        <!--<div class="form-group all">
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("tax_method", "tax_method") ?>
                            <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                            ?>
                        </div>--->
                    
                    
                    <?php } ?>
                    <!---<div class="form-group standard">
                        <?= lang("alert_quantity", "alert_quantity") ?>
                        <div
                            class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->sma->formatDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                            <span class="input-group-addon">
                                <input type="checkbox" name="track_quantity" id="inlineCheckbox1"
                                       value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                            </span>
                        </div>
                    </div--->

                    <div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>

                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile[]" multiple="true" data-show-upload="false"
                               data-show-preview="false" class="form-control file" accept="image/*">
                    </div>
                    <div id="img-details"></div>

                    <!--- Restaurant Type POS  ---->                    
                    <?php if ($Settings->pos_type == 'restaurant' || $Settings->pos_type == 'bakery') { ?>

                        <?php if ($Settings->product_external_platform == '1') { ?>
                            <!--- Urbanpiper  ----> 
                            <div class="form-group all">
                                <?= lang("Used By External Platform (Ex. Zomato, Swiggy, Etc.)", "UrbanPiper Products") ?> <img src="http://localhost/pos_in/themes/default/assets/images/new.gif" height="30px" alt="new">
                                <select class="form-control" name="up_items" id="urbanpiperitem">
                                    <?php
                                    $selected = 'select_' . $product->up_items;
                                    $$selected = ' selected="selected" ';
                                    ?>
                                    <option value="1" <?= $select_1 ?> >Yes</option>
                                    <option value="0" <?= $select_0 ?> > No </option>
                                </select>     
                            </div>

                            <div id="urbanpipercontain" style="<?= ($product->up_items == '1') ? 'display:block' : 'display:none' ?>">
                                <input type="hidden" name="up_products_data_id" value="<?= $urbanbpiper_Data->id ?>" />
                                <fieldset style=" border: 1px solid #ccc; padding: 5px;">
                                    <legend style="width: auto; padding: 0px 10px; border-bottom: 0;margin-bottom: 0px;">External Platform Options <img src="http://localhost/pos_in/themes/default/assets/images/new.gif" height="30px" alt="new"></legend>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Price ", "Price") ?>
                                                <input  class="form-control" type="text" name="upprice" id="upprice" value="<?= $urbanbpiper_Data->price ?>" />                                 
                                            </div> 
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Food Type ", "Food Type") ?>
                                                <select class="form-control" name="up_food_type" id="up_food_type">
                                                    <option value="">--Select--</option>
                                                    <?php foreach ($foodtype as $foodtype_value) { ?>
                                                        <option value="<?= $foodtype_value->id ?>" <?php echo ($urbanbpiper_Data->food_type_id == $foodtype_value->id) ? ' selected="selected" ' : '' ?> ><?= $foodtype_value->food_type ?></option>
                                                    <?php } ?>
                                                </select>    
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Is Available", "Is Available") ?>
                                                <?php
                                                $available_selected = 'available_' . $urbanbpiper_Data->available;
                                                $$available_selected = ' selected="selected" ';
                                                ?>
                                                <select class="form-control" name="available" id="available">
                                                    <option value="1" <?= $available_1 ?> >Yes</option>
                                                    <option value="0" <?= $available_0 ?> >No</option>
                                                </select>    
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Sold At Store", "Sold At Store") ?>
                                                <?php
                                                $at_store_selected = 'at_store_' . $urbanbpiper_Data->sold_at_store;
                                                $$at_store_selected = ' selected="selected" ';
                                                ?>
                                                <select class="form-control" name="sold_at_store" id="sold_at_store">
                                                    <option value="1" <?= $at_store_1 ?>>Yes</option>
                                                    <option value="0" <?= $at_store_0 ?>>No</option>
                                                </select>    
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group ">
                                                <?= lang("Is Recommended", "Is Recommended") ?>
                                                <?php
                                                $recommended_selected = 'recommended_' . $urbanbpiper_Data->recommended;
                                                $$recommended_selected = ' selected="selected" ';
                                                ?>
                                                <select class="form-control" name="recommended" id="recommended">
                                                    <option value="1" <?= $recommended_1 ?>>Yes</option>
                                                    <option value="0" <?= $recommended_0 ?>>No</option>
                                                </select>    
                                            </div>
                                        </div>                                
                                        <div class="col-sm-6">
                                           <div class="form-group ">
                                                <?= lang("Manage_stock", "Manage_stock") ?>
                                                                                              
                                                <select class="form-control" name="manage_stock" id="manage_stock">
                                                    <option value="1" <?= ( $urbanbpiper_Data->manage_stock == 1? 'selected':'') ?>>Yes</option>
                                                    <option value="0" <?= ( $urbanbpiper_Data->manage_stock == 0? 'selected':'') ?>>No</option>
                                                </select>    
                                            </div>             

                                        </div>
                                    </div>                          
                                    <div class="row">

                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Default ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <select class="form-control" name="default_tag" id="default_tag" data-role="tagsinput">
                                                    <option value="packaged-good"> Packaged Good</option> 
                                                </select>      
                                               
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Zomato ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_zomato" id="tag_zomato" data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_zomato ?>"  />
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Swiggy ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_swiggy" id="tag_swiggy" data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_swiggy ?>"  />
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Food Panda ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_foodpanda" id="tag_foodpanda" data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_foodpanda ?>"  />
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group ">
                                                <?= lang("Tags for Uber Eats ", "Tags") ?> <small class="text-info">(*Use comma for multiple tags)</small><br/> 
                                                <input class="form-control" type="text" name="tag_ubereats" id="tag_ubereats" data-role="tagsinput" value="<?= $urbanbpiper_Data->plat_ubereats ?>"  />
                                            </div>
                                        </div>
                                    </div>                          

                                </fieldset>    
                                <br>
                            </div>    
                        <?php } ?>
                        <!---- End Urbanpiper ----->
                    <?php } ?>
                    <!--- End Restaurant Type POS  ----> 
                </div>
                <div class="col-md-6">
                    <div class="standard">
                        <div>
                            <?php
                            if (!empty($warehouses) || !empty($warehouses_products)) {
                                echo '<div class="row"><div class="col-md-12"><div class="well">';
                                echo '<p><strong>' . lang("warehouse_quantity") . '</strong></p>';
                                if (!empty($warehouses_products)) {

                                    $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                                    foreach ($warehouses_products as $wh_pr) {
                                        if ($Owner || $Admin) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <input type="hidden" value="' . $this->sma->formatDecimal($wh_pr->quantity) . '" id="vwh_qty_' . $wh_pr->id . '"><span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->sma->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        } elseif (in_array($wh_pr->id, $permisions_werehouse)) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <input type="hidden" value="' . $this->sma->formatDecimal($wh_pr->quantity) . '" id="vwh_qty_' . $wh_pr->id . '"><span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->sma->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        }
                                    }
                                }
                                echo '<div class="clearfix"></div></div></div></div>';
                            }
                            ?>
                        </div>
                        <div class="clearfix"></div>

                        <div id="attrs"></div>
                        <div class="well well-sm">
                            <?php if ($product_options) { ?>
                                <table class="table table-bordered table-condensed table-striped"
                                       style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?> margin-top: 10px;">
                                    <thead>
                                        <tr class="active">
                                            <th><?= lang('name') ?></th>
                                            <th><?= lang('warehouse') ?></th>
                                            <th><?= lang('quantity') ?></th>
                                            <!-- <th><?= lang('price_addition') ?></th> -->
                                            <th><?= lang('MRP') ?></th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($product_options as $option) {
                                            echo '<tr><td class="col-xs-3"><input type="hidden" name="attr_id[]" value="' . $option->id . '"><span>' . $option->name . '</span></td><td class="code text-center col-xs-3"><span>' . $option->wh_name . '</span></td><td class="quantity text-center col-xs-2"><span>' . $this->sma->formatQuantity($option->wh_qty) . '</span></td><td class="price text-right col-xs-2">' . $this->sma->formatMoney($option->price) . '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                            }
                            if ($product_variants) {
                                ?>
                                <h3 class="bold"><?= lang('update_variants'); ?></h3>
                                <table class="table table-bordered table-condensed table-striped" style="margin-top: 10px;">
                                    <thead>
                                        <tr class="active">
                                            <th class="col-xs-1"><?= lang('Pri ma ry') ?></th>
                                            <th ><?= lang('Varient Name') ?></th>
                                            <th class="col-xs-2"><?= lang('Cost_Addition') ?></th>
                                            <!-- <th class="col-xs-2"><?= lang('Price_Addition') ?></th> -->
                                            <th class="col-xs-2"><?= lang('MRP') ?></th>

                                            <?php if ($Settings->pos_type == 'restaurant') { ?>
                                            <th class="col-xs-3"><?= lang('Urbanpiper_Price_Addition') ?></th>
                                            <?php } ?>
                                            <th class="col-xs-2"><?= lang('Unit_Quantity') ?></th>
                                            <th class="col-xs-2"><?= lang('Unit_Weight (In KG)') ?></th>
                                            <th><i class="fa fa-trash attr-remove-all"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($product_variants as $pv) {
                                            $pv_checked = ($product->primary_variant == $pv->id) ? ' checked="checked" ' : '';
                                            echo '<tr>'
                                            . '<td title="Set Primary Variable"><input type="radio" name="primary_variant" value="' . $pv->id . '" '.$pv_checked.'/></td>'
                                            . '<td><input type="hidden" name="variant_id_' . $pv->id . '" value="' . $pv->id . '"><input type="text" name="variant_name_' . $pv->id . '" value="' . $pv->name . '" class="form-control"></td>'
                                            . '<td><input type="text" name="variant_cost_' . $pv->id . '" value="' . number_format($pv->cost,2) . '" class="form-control"></td>'
                                            . '<td><input type="text" name="variant_price_' . $pv->id . '" value="' . number_format($pv->price,2) . '" class="form-control"></td>';

if ($Settings->pos_type == 'restaurant') { 
                                            echo '<td><input type="text" name="variant_upprice_' . $pv->id . '" value="' . number_format($pv->up_price,2) . '" class="form-control"></td>';
                                           }
                                            echo  '<td><input type="text" name="unit_quantity_' . $pv->id . '" value="' . number_format($pv->unit_quantity,3) . '" class="form-control"></td>'
                                            . '<td><input type="text" name="unit_weight_' . $pv->id . '" value="' . number_format($pv->unit_weight,3) . '" class="form-control"></td>'
                                            . '<td class="text-center"> <a href="javascript:void(0);" class="DeleteVarient" onclick="return deleteVarient(' . $pv->id . ');" title="Delete"><i class="fa fa-trash" aria-hidden="true " style="cursor: pointer;" id="row_'.$pv->id.'"></i></a></td>'
                                            . '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                            }
                            ?>
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" name="attributes" id="attributes" <?= $this->input->post('attributes') ? 'checked="checked"' : ''; ?>>
                                <label for="attributes" class="padding05"><?= lang('add_more_variants'); ?></label>
                                <?= lang('eg_sizes_colors'); ?>
                            </div>

                            <div id="attr-con" <?= $this->input->post('attributes') ? '' : 'style="display:none;"'; ?>>
                                <div class="form-group" id="ui" style="margin-bottom: 0;">
                                    <div class="input-group">
                                        <?php echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line("enter_attributes") . '"'); ?>
                                        <div class="input-group-addon" style="padding: 2px 5px;">
                                            <a href="#" id="addAttributes">
                                                <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                                <div class="table-responsive">
                                    <table id="attrTable" class="table table-bordered table-condensed table-striped" style="margin-bottom: 0; margin-top: 10px;">
                                        <thead>
                                            <tr class="active">
                                                <th><?= lang('name') ?></th>
<!--                                                <th><?= lang('warehouse') ?></th>
                                                <th><?= lang('quantity') ?></th>-->
                                                <th><?= lang('Cost') ?></th>
                                                <!-- <th><?= lang('price') ?></th> -->
                                                <th><?= lang('MRP') ?></th>

                                                <?php if ($Settings->pos_type == 'restaurant') { ?>               
                                                    <th><?= lang('Urbanpier_Price_Addition') ?></th>
                                                <?php } ?>
                                                <th><?= lang('Unit Quantity') ?></th>
                                                <th><?= lang('Unit Weight') ?></th>
                                                <th><i class="fa fa-times attr-remove-all"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody><?php
                                            if ($this->input->post('attributes')) {
                                                $a = sizeof($_POST['attr_name']);
                                                for ($r = 0; $r <= $a; $r++) {
                                                    if (isset($_POST['attr_name'][$r]) && (isset($_POST['attr_warehouse'][$r]) || isset($_POST['attr_quantity'][$r]))) {
                                                        echo '<tr class="attr">
                                                            <td><input type="hidden" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span>
                                                            <input type="hidden" name="attr_warehouse[]" value="' . (isset($_POST['attr_warehouse'][$r]) ? $_POST['attr_warehouse'][$r] : '') . '">
                                                            <input type="hidden" name="attr_wh_name[]" value="' . (isset($_POST['attr_wh_name'][$r]) ? $_POST['attr_wh_name'][$r] : '') . '">
                                                            <input type="hidden" name="attr_quantity[]" value="' . $_POST['attr_quantity'][$r] . '"></td>
                                                            <td class="cost text-right"><input type="text" name="attr_cost[]" value="' . $_POST['attr_cost'][$r] . '"><span>' . $_POST['attr_cost'][$r] . '</span></span></td>
                                                            <td class="price text-right"><input type="text" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td>';

                                                            if ($Settings->pos_type == 'restaurant') {
                                                                echo '<td class="upprice text-right"><input type="text" name="attr_upprice[]" value="' . $_POST['attr_upprice'][$r] . '"><span>' . $_POST['attr_upprice'][$r] . '</span></span></td>';
                                                            }

                                                            echo '<td class="unit_quantity text-center"><input type="text" name="attr_unit_quantity[]" value="' . $_POST['attr_unit_quantity'][$r] . '"><span>' . $_POST['attr_unit_quantity'][$r] . '</span></td>
                                                            <td class="unit_weight text-center"><input type="text" name="attr_unit_weight[]" value="' . $_POST['attr_unit_weight'][$r] . '"><span>' . $_POST['attr_unit_weight'][$r] . '</span></td>
                                                            <td class="text-center"><i class="fa fa-times delAttr"></i></td>
                                                         </tr>';
                                                    }
                                                }
                                            }
                                            ?></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                    </div>
                    <div class="combo" style="display:none;">

                        <div class="form-group">
                            <!-- <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?> -->
                            <?= lang("add_product", "add_item"); ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>
                            <!--<div class="row"><div class="ccol-md-10 col-sm-10 col-xs-10"><label class="table-label" for="combo"><?= lang("combo_products"); ?></label></div>
                            <div class="ccol-md-2 col-sm-2 col-xs-2"><div class="form-group no-help-block" style="margin-bottom: 0;"><input type="text" name="combo" id="combo" value="" data-bv-notEmpty-message="" class="form-control" /></div></div></div>-->
                            <div class="controls table-controls">
                                <table id="prTable"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th class="col-md-5 col-sm-5 col-xs-5"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                            <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                            <th class="col-md-3 col-sm-3 col-xs-3 bundel" style="display:none;"><?= lang("unit_price"); ?></th>
                                            <th class="col-md-3 col-sm-3 col-xs-3 bundel" style="display:none;"><?= lang("Price"); ?></th>
                                            <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="digital" style="display:none;">
                        <div class="form-group digital">
                            <?= lang("digital_file", "digital_file") ?>
                            <input id="digital_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="digital_file" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>
                    </div>

                    <div class="form-group standard">
                        <div class="form-group">
                            <?= lang("supplier", "supplier") ?>
                            <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <!--
                        <div class="col-xs-12">
                                <div class="form-group">
                        <?php
                        echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                        ?>
                                </div>
                        </div>
                        -->
                        <div class="row" id="supplierrow_1"><!--  supplier_con-->
                            <div>
                                <div class="col-xs-11">
                                    <div class="form-group">
                                        <?php
                                        echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-xs" >
                                    <i class="fa fa-times deleteSupplier"  id="1"  style="cursor:pointer;"></i>
                                </button>
                            </div>    
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <?= form_input('supplier_part_no', (isset($_POST['supplier_part_no']) ? $_POST['supplier_part_no'] : ""), 'class="form-control tip" id="supplier_part_no" placeholder="' . lang('supplier_part_no') . '"'); ?>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <?= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ""), 'class="form-control tip" id="supplier_price" placeholder="' . lang('supplier_price') . '"'); ?>
                                </div>
                            </div>
                        </div>
                        <div id="ex-suppliers"></div>
                    </div>

                </div>

                <div class="col-md-12">
                    <?php
                    
                    $active_cf = FALSE;
                    for($i=1; $i<=6; $i++){
                        if($custome_fields->{"cf$i"}){
                            $active_cf = TRUE;
                            break;
                        }
                    }
                    ?>
                    <div class="form-group">
                        <input name="cf" type="checkbox" class="checkbox" id="extras" value="1" <?= ($active_cf) ? 'checked="checked" disabled="disabled"' : '' ?> />
                        <label for="extras" class="padding05"><?= lang('custom_fields') ?></label>
                    </div>
                    
                    <div class="row" id="extras-con" style="<?=  ($active_cf) ? 'display: block;' : 'display: none;' ?>">
                       <div class="well well-sm">
                           <div class="row">
                        <div class="col-md-4">
                            <div class="form-group all">
                               <?php  echo (!empty($custome_fields->cf1) ? lang($custome_fields->cf1, 'pcf1') : lang('pcf1', 'pcf1')) ?> 
                               <?php                        
                                if ($custome_fields->cf1_input_type == 'list_box' && $custome_fields->cf1_input_options != '') {                            
                                    echo form_dropdown('cf1', (json_decode($custome_fields->cf1_input_options, TRUE)) , (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control tip" id="cf1"'. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control" id="cf1" '. ((strpos($custome_fields->cf1, '*')) ? ' required="required" ' : '')); 
                                }
                                ?> 
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php  echo (!empty($custome_fields->cf2) ? lang($custome_fields->cf2, 'pcf2') : lang('pcf2', 'pcf2')) ?> 
                                <?php                        
                                if ($custome_fields->cf2_input_type == 'list_box' && $custome_fields->cf2_input_options != '') {                            
                                    echo form_dropdown('cf2', (json_decode($custome_fields->cf2_input_options, TRUE)) , (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control tip" id="cf2"'. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control" id="cf2" '. ((strpos($custome_fields->cf2, '*')) ? ' required="required" ' : ''));
                                }  ?> 
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php  echo (!empty($custome_fields->cf3) ? lang($custome_fields->cf3, 'pcf3') : lang('pcf3', 'pcf3')) ?>
                                <?php                        
                                if ($custome_fields->cf3_input_type == 'list_box' && $custome_fields->cf3_input_options != '') {                            
                                    echo form_dropdown('cf3', (json_decode($custome_fields->cf3_input_options, TRUE)) , (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control tip" id="cf3"'. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control" id="cf3" '. ((strpos($custome_fields->cf3, '*')) ? ' required="required" ' : '')); 
                                }    
                                ?> 
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf4) ? lang($custome_fields->cf4, 'pcf4') : lang('pcf4', 'pcf4')) ?>
                                <?php                        
                                if ($custome_fields->cf4_input_type == 'list_box' && $custome_fields->cf4_input_options != '') {                            
                                    echo form_dropdown('cf4', (json_decode($custome_fields->cf4_input_options, TRUE)) , (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control tip" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control" id="cf4"'. ((strpos($custome_fields->cf4, '*')) ? ' required="required" ' : '')); 
                                }   
                                ?> 
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf5) ? lang($custome_fields->cf5, 'pcf5') : lang('pcf5', 'pcf5')) ?>
                                <?php                        
                                if ($custome_fields->cf5_input_type == 'list_box' && $custome_fields->cf5_input_options != '') {                            
                                    echo form_dropdown('cf5', (json_decode($custome_fields->cf5_input_options, TRUE)) , (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control tip" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control" id="cf5"'. ((strpos($custome_fields->cf5, '*')) ? ' required="required" ' : '')); 
                                }    
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?php echo (!empty($custome_fields->cf6) ? lang($custome_fields->cf6, 'pcf6') : lang('pcf6', 'pcf6')) ?>
                                <?php                        
                                if ($custome_fields->cf6_input_type == 'list_box' && $custome_fields->cf6_input_options != '') {                            
                                    echo form_dropdown('cf6', (json_decode($custome_fields->cf6_input_options, TRUE)) , (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control tip" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : ''));
                                } else {
                                    echo form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control" id="cf6"'. ((strpos($custome_fields->cf6, '*')) ? ' required="required" ' : '')); 
                                }   
                                ?>
                            </div>
                        </div>
                    </div>
                       </div>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="details"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details_for_invoice", "details") ?>
                        <?= form_textarea('details', (isset($_POST['details']) ? $_POST['details'] : ($product ? $product->details : '')), 'class="form-control" id="details"'); ?>
                    </div>

                    <div class="form-group">
                        <?php echo form_submit('edit_product', $this->lang->line("edit_product"), 'class="btn btn-primary"'); ?>
                    </div>

                </div>
                <?= form_close(); ?>

            </div>

        </div>
    </div>
</div>
<div class="modal  modalvarient" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="modalClose('modalvarient')" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modal title</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="modalClose('modalvarient')" class="btn btn-default"
                        data-toggle="modal">Close</button>
                    <!--button type="button" class="btn btn-primary" onclick="addProductToVarientProduct('modalvarient')">Save changes</button -->
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
    $(document).ready(function () {
    $.ajaxSetup({
        beforeSend: function (jqXHR, settings) {
            if (settings.url.includes("products/suggestions")) {
                const type = $('#type').val();
                const url = new URL(settings.url, window.location.origin);
                url.searchParams.set('type', type);
                settings.url = url.toString();
            }
        }
    });
});
    $(document).ready(function () {
        var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
        var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
        var items = {};
<?php
if ($combo_items) {
    echo '
                var ci = ' . json_encode($combo_items) . ';
                $.each(ci, function() { add_product_item(this); });
                ';
}
?>
<?= isset($_POST['cf']) ? '$("#extras").iCheck("check");' : '' ?>
        $('#extras').on('ifChecked', function () {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function () {
            $('#extras-con').slideUp();
        });

<?= isset($_POST['promotion']) || $product->promotion ? '$("#promotion").iCheck("check");' : '' ?>
        $('#promotion').on('ifChecked', function (e) {
            $('#promo').slideDown();
        });
        $('#promotion').on('ifUnchecked', function (e) {
            $('#promo').slideUp();
        });

        $('.attributes').on('ifChecked', function (event) {
            $('#options_' + $(this).attr('id')).slideDown();
        });
        $('.attributes').on('ifUnchecked', function (event) {
            $('#options_' + $(this).attr('id')).slideUp();
        });
        //$('#cost').removeAttr('required');
        $('#type').change(function () {
            var t = $(this).val();
            $('.bundel').show();
            if (t !== 'standard') {
                $('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#digital_file').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
            } else {
                $('.digital').slideDown();
                $('#digital_file').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
            }
            if (t !== 'combo' ) {
                $('.combo').slideUp();
            } else {
                $("#prTable tbody").empty();
                $('.combo').slideDown();
            }
            if (t == 'Bundle') {
                $("#prTable tbody").empty();
                $('.combo').slideDown();
                $('.bundel').hide();
            } 
        });

        $("#add_item").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 5,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.options) {
                    product_option_model_call(ui.item);
                    $(this).val('');
                    return true;
                }
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                        $('#add_item').removeAttr('required');
                        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        $('#add_item').removeAttr('required');
        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');

        function add_product_item(item) {
           if (item == null) {
               return false;
           }
           item_id = item.id;
           if (items[item_id]) {
               items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
           } else {
               items[item_id] = item;
           }
           $("#prTable tbody").empty();
           $.each(items, function () {
               var selectedValue = document.getElementById("type").value;
               var row_no = this.id;
               var displayName = this.name + ' (' + this.code + ')';
               if (this.product_name) {
                   displayName = this.product_name + ' (' + this.name + ') (' + this.code + ')';
               }
               var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '"></tr>');
               tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + displayName + '</span></td>';
               tr_html += '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
               if (selectedValue != "Bundle") {
                   tr_html += '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
                   tr_html += '<td><input class="form-control text-center rtprice" name="combo_item_total_price[]" type="text" value="' + (formatDecimal(this.price) * formatDecimal(this.qty)) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_total_price_' + row_no + '" onClick="this.select();"></td>';
               }
               tr_html += '<input name="combo_item_variant_id[]" type="hidden" value="' + (this.variant_id ? this.variant_id : '') + '">';
               tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
               newTr.html(tr_html);
               newTr.prependTo("#prTable");
           });
           $('.item_' + item_id).addClass('warning');
           return true;

        }
        window.addProductToVarientProduct = function(option_id, option_name, currentType) {
            var note = '';
            if (option_name.toLowerCase() == 'note') {
                note = prompt("Please enter your note");
                if (note == null) {
                    return false;
                }
            }
            var itemId = $(".modalvarient").find('.product_item_id').attr("value")
            var term = $(".modalvarient").find('.product_term').val() +
                "<?php echo $this->Settings->barcode_separator; ?>" + option_id;
            wh = $('#poswarehouse').val(),
                cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?= site_url('products/suggestions') ?>",
                data: {
                    term: term,
                    option_id: option_id,
                    warehouse_id: wh,
                    customer_id: cu,
                    option_note: note
                },
                dataType: "json",
                success: function(data) {
                    if (data !== null) {
                        add_product_item(data[0]);
                        $(this).val('');
                        $('.modalvarient').hide();
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                        $('.modalvarient').hide();
                    }
                }
            });
        }
        function product_option_model_call(product) {
            var subdomain = new URL(window.location.href).hostname.split('.')[0];
            var product_options = '';
            product_options = "" +
                "<div class='row'>" +
                "<div class='col-sm-12'>";
                var optionsArray = Object.values(product.options);
                console.log("Options Array:", optionsArray);
                $.each(optionsArray, function(index, element) {
                if (element.name.toLowerCase() == 'note') {
                    product_options +=
                        '</div><div style="clear:both"></div></div><div class="note-btn"><button onclick="addProductToVarientProduct(\'' +
                        element.id + '\',\'' + element.name +
                        '\')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>Note</button></div>';
                } else {
                    product_options += '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' +
                        element.name + '\')" type="button"  title="' + element.name +
                        '" class="btn-prni btn-info pos-tip" tabindex="-1"><img src="assets/mdata/' + subdomain +
                        '/uploads/thumbs/no_image.png" alt="' + element.name +
                        '" style="width:33px;height:33px;" class="img-rounded"><span>' + element.name +
                        '</span></button>';
                }
            });
            product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.id + "' >";
            product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.code +
                "' >";
            $('.modalvarient').find('.modal-title').html(product.name);
            $('.modalvarient').find('.modal-body').empty();
            $('.modalvarient').find('.modal-body').append(product_options);
            $('.modalvarient').show();
            return true;
        }
        window.modalClose = function(modalClass) {
            $('.' + modalClass).hide();
        };
        function calculate_price() {

            var rows = $('#prTable').children('tbody').children('tr');
            var pp = 0;
            var row_total = 0
            $.each(rows, function () {
                row_total = formatDecimal(parseFloat($(this).find('.rprice').val()) * parseFloat($(this).find('.rquantity').val()));
                $(this).find('.rtprice').val(row_total);
                //console.log(this);
                //alert(row_total);
                pp += row_total;
            });
            //console.log(pp);
            //$('#price').val(pp);
            return true;
        }

        $(document).on('change textchange', '.rquantity, .rprice', function () {
            calculate_price();
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            $(this).closest('#row_' + id).remove();
            $.each(items, function (i, v) {
                if (v.id == id) {
                    delete items[i];
                }
            });
        });
        var su = 2;
        /*
         $('#addSupplier').click(function () {
         if (su <= 5) {
         $('#supplier_1').select2('destroy');
         var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>" style="width:100%;display: block !important;" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_part_no" class="form-control tip" id="supplier_' + su + '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
         $('#ex-suppliers').append(html);
         var sup = $('#supplier_' + su);
         suppliers(sup);
         su++;
         } else {
         bootbox.alert('<?= lang('max_reached') ?>');
         return false;
         }
         });*/

        $('#addSupplier').click(function () {//onClick="delete_supplier(' + su + ')"
            if (su <= 5) {
                //$('#supplier_1').select2('destroy');//style="width:100%;display: block !important;"
                var html = '<div style="clear:both;height:5px;" ></div><div class="row" id="supplierrow_' + su + '"><div><div class="col-xs-11"><div class="form-group"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>"  /></div></div><div><button type="button" class="btn btn-primary btn-xs" ><i class="fa fa-times deleteSupplier"  id="' + su + '"  style="cursor:pointer;"></i></button></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_part_no" class="form-control tip" id="supplier_' + su + '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
                $('#ex-suppliers').append(html);
                var sup = $('#supplier_' + su);
                suppliers(sup);
                su++;
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
        });


        $(document).on('click', '.deleteSupplier', function () {
            var id = $(this).attr('id');
            console.log(id);
            su--;
            $(this).closest('#supplierrow_' + id).remove();
        });



        var _URL = window.URL || window.webkitURL;
        $("input#images").on('change.bs.fileinput', function () {
            var ele = document.getElementById($(this).attr('id'));
            var result = ele.files;
            $('#img-details').empty();
            for (var x = 0; x < result.length; x++) {
                var fle = result[x];
                for (var i = 0; i <= result.length; i++) {
                    var img = new Image();
                    img.onload = (function (value) {
                        return function () {
                            ctx[value].drawImage(result[value], 0, 0);
                        }
                    })(i);

                    img.src = 'images/' + result[i];
                }
            }
        });
        var variants = <?= json_encode($vars); ?>;
        $(".select-tags").select2({
            tags: variants,
            tokenSeparators: [","],
            multiple: true
        });
        $(document).on('ifChecked', '#attributes', function (e) {
            $('#attr-con').slideDown();
            $('#price').val('00');
            $('#mrp').val('00');
            $('#discount_on_mrp').val('0%');
            $('#price').attr('readonly', true);
            $('#mrp').attr('readonly', true);
            $('#discount_on_mrp').attr('readonly', true);
        });
        $(document).on('ifUnchecked', '#attributes', function (e) {
            $(".select-tags").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();
        });
        $('#addAttributes').click(function (e) {
            e.preventDefault();
            var attrs_val = $('#attributesInput').val(), attrs;
            attrs = attrs_val.split(',');
            for (var i in attrs) {
                if (attrs[i] !== '') {
                    //$('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value=""><span></span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value=""><span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="unit_quantity text-center"><input type="hidden" name="attr_unit_quantity[]" value="1"><span>1.00</span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');

                   <?php  if ($Settings->pos_type == 'restaurant') { ?> 

                    // $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span><input type="hidden" name="attr_warehouse[]" value=""><input type="hidden" name="attr_quantity[]" value=""></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="0"><span>0</span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="upprice text-right"><input type="hidden" name="attr_upprice[]" value="0"><span>0</span></span></td><td class="unit_quantity text-center"><input type="hidden" name="attr_unit_quantity[]" value="1"><span>1.00</span></td><td class="unit_weight text-center"><input type="hidden" name="attr_unit_weight[]" value="0"><span>0.00</span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                    $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="cost text-right"><input type="number" class="form-control cost text-right" name="attr_cost[]" value="0" oninput="updateHiddenInput(this, \'attr_cost\')"></span></td><td class="price text-right"><input type="number" class="form-control price text-right" name="attr_price[]" value="0" oninput="updateHiddenInput(this, \'attr_price\')"></span></td><td class="upprice text-right"><input type="number" class="form-control upprice text-right" name="attr_upprice[]" value="0" oninput="updateHiddenInput(this, \'attr_upprice\')"></span></td><td class="unit_quantity text-right"><input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" oninput="updateHiddenInput(this, \'attr_unit_quantity\')"></span></td><td class="unit_quantity text-right"><input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="0" oninput="updateHiddenInput(this, \'attr_unit_weight\')"></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                    <?php } else { ?>
                    //   $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span><input type="hidden" name="attr_warehouse[]" value=""><input type="hidden" name="attr_quantity[]" value=""></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="0"><span>0</span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="unit_quantity text-center"><input type="hidden" name="attr_unit_quantity[]" value="1"><span>1.00</span></td><td class="unit_weight text-center"><input type="hidden" name="attr_unit_weight[]" value="0"><span>0.00</span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                    $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" class="attr_name" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="cost text-right"><input type="number" class="form-control cost text-right" name="attr_cost[]" value="0" oninput="updateHiddenInput(this, \'attr_cost\')"></span></td><td class="price text-right"><input type="number" class="form-control price text-right" name="attr_price[]" value="0" oninput="updateHiddenInput(this, \'attr_price\')"></span></td><td class="unit_quantity text-right"><input type="number" class="form-control unit_quantity text-right" name="attr_unit_quantity[]" value="1" oninput="updateHiddenInput(this, \'attr_unit_quantity\')"></span></td><td class="unit_quantity text-right"><input type="number" class="form-control unit_weight text-right" name="attr_unit_weight[]" value="0" oninput="updateHiddenInput(this, \'attr_unit_weight\')"></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                    <?php } ?>   


                }
            }
        });
        $(document).on('click', '.delAttr', function () {
            $(this).closest("tr").remove();
        });
        $(document).on('click', '.attr-remove-all', function () {
            $('#attrTable tbody').empty();
            $('#attrTable').hide();
        });
        var row, warehouses = <?= json_encode($warehouses); ?>;
        // $(document).on('click', '.attr td:not(:last-child)', function () {
        $(document).on('click', '.attr td:first-child', function(event) {

            row = $(this).closest("tr");
            $('#aModalLabel').text(row.children().eq(0).find('span').text());
//            $('#awarehouse').select2("val", (row.children().eq(1).find('input').val()));
//            $('#aquantity').val(row.children().eq(2).find('span').text());
            $('#acost').val(row.children().eq(1).find('span').text());
            $('#aprice').val(row.children().eq(2).find('span').text());
<?php if ($Settings->pos_type == 'restaurant') { ?>

 $('#aupprice').val(row.children().eq(3).find('span').text());
$('#uquantity').val(row.children().eq(4).find('span').text());
            $('#uweight').val(row.children().eq(5).find('span').text());

<?php }else{ ?>
            $('#uquantity').val(row.children().eq(3).find('span').text());
            $('#uweight').val(row.children().eq(4).find('span').text());
<?php } ?>
            $('#aModal').appendTo('body').modal('show');
        });

        $(document).on('click', '#updateAttr', function () {
//            var wh = $('#awarehouse').val(), wh_name;
//            $.each(warehouses, function () {
//                if (this.id == wh) {
//                    wh_name = this.name;
//                }
//            });
            // row.children().eq(1).html('<input type="hidden" name="attr_warehouse[]" value="' + wh + '"><input type="hidden" name="attr_wh_name[]" value="' + wh_name + '"><span>' + wh_name + '</span>');
            //  row.children().eq(2).html('<input type="hidden" name="attr_quantity[]" value="' + $('#aquantity').val() + '"><span>' + $('#aquantity').val() + '</span>');
            row.children().eq(1).html('<input type="text" name="attr_cost[]" value="' + $('#acost').val() + '" size="5">');
            row.children().eq(2).html('<input type="text" name="attr_price[]" value="' + $('#aprice').val() + '" size="5">');
 <?php if ($Settings->pos_type == 'restaurant') { ?>

 row.children().eq(3).html('<input type="text" name="attr_upprice[]" value="' + $('#aupprice').val() + '" size="5">');

            row.children().eq(4).html('<input type="text" name="attr_unit_quantity[]" value="' + $('#uquantity').val() + '" size="5">');
            row.children().eq(5).html('<input type="text" name="attr_unit_weight[]" value="' + $('#uweight').val() + '" size="5">');
<?php }else{ ?>

            row.children().eq(3).html('<input type="text" name="attr_unit_quantity[]" value="' + $('#uquantity').val() + '" size="5">');
            row.children().eq(4).html('<input type="text" name="attr_unit_weight[]" value="' + $('#uweight').val() + '" size="5">');
 <?php } ?> 
            $('#aModal').modal('hide');
        });
    });

<?php if ($product) { ?>
        $(document).ready(function () {
            $('#enable_wh').click(function () {
                var whs = $('.wh');
                $.each(whs, function () {
                    $(this).val($('#v' + $(this).attr('id')).val());
                });
                $('#warehouse_quantity').val(1);
                $('.wh').attr('disabled', false);
                $('#show_wh_edit').slideDown();
            });
            $('#disable_wh').click(function () {
                $('#warehouse_quantity').val(0);
                $('#show_wh_edit').slideUp();
            });
            $('#show_wh_edit').hide();
            $('.wh').attr('disabled', true);
            var t = "<?= $product->type ?>";
            if (t !== 'standard') {
                $('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#digital_file').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
            } else {
                $('.digital').slideDown();
                $('#digital_file').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
            }
            if (t !== 'combo') {
                $('.combo').slideUp();
            } else {
                $('.combo').slideDown();
            }
            if (t == 'Bundle') {
                $('.combo').slideDown();

            } 
            $('#add_item').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
            //$("#code").parent('.form-group').addClass("has-error");
            //$("#code").focus();
            $("#product_image").parent('.form-group').addClass("text-warning");
            $("#images").parent('.form-group').addClass("text-warning");
            $.ajax({
                type: "get", async: false,
                url: "<?= site_url('products/getSubCategories') ?>/" + <?= $product->category_id ?>,
                dataType: "json",
                success: function (scdata) {
                    if (scdata != null) {
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                            placeholder: "<?= lang('select_category_to_load') ?>",
                            data: scdata
                        });
                    }
                }
            });
    <?php if ($product->supplier1) { ?>
                select_supplier('supplier1', "<?= $product->supplier1; ?>");
                $('#supplier_price').val("<?= $this->sma->formatDecimal($product->supplier1price); ?>");
                $('#supplier_part_no').val("<?= $product->supplier1_part_no; ?>");
    <?php } else { ?>
                $('#supplier1').addClass('rsupplier');
    <?php } ?>
    <?php if ($product->supplier2) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_2', "<?= $product->supplier2; ?>");
                $('#supplier_2_price').val("<?= $this->sma->formatDecimal($product->supplier2price); ?>");
                $('#supplier_2_part_no').val("<?= $product->supplier2_part_no; ?>");
    <?php } ?>
    <?php if ($product->supplier3) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_3', "<?= $product->supplier3; ?>");
                $('#supplier_3_price').val("<?= $this->sma->formatDecimal($product->supplier3price); ?>");
                $('#supplier_3_part_no').val("<?= $product->supplier3_part_no; ?>");
    <?php } ?>
    <?php if ($product->supplier4) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_4', "<?= $product->supplier4; ?>");
                $('#supplier_4_price').val("<?= $this->sma->formatDecimal($product->supplier4price); ?>");
                $('#supplier_4_part_no').val("<?= $product->supplier4_part_no; ?>");
    <?php } ?>
    <?php if ($product->supplier5) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_5', "<?= $product->supplier5; ?>");
                $('#supplier_5_price').val("<?= $this->sma->formatDecimal($product->supplier5price); ?>");
                $('#supplier_5_part_no').val("<?= $product->supplier5_part_no; ?>");
    <?php } ?>
            function select_supplier(id, v) {
                $('#' + id).val(v).select2({
                    minimumInputLength: 1,
                    data: [],
                    initSelection: function (element, callback) {
                        $.ajax({
                            type: "get", async: false,
                            url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                            dataType: "json",
                            success: function (data) {
                                callback(data[0]);
                            }
                        });
                    },
                    ajax: {
                        url: site.base_url + "suppliers/suggestions",
                        dataType: 'json',
                        quietMillis: 15,
                        data: function (term, page) {
                            return {
                                term: term,
                                limit: 10
                            };
                        },
                        results: function (data, page) {
                            if (data.results != null) {
                                return {results: data.results};
                            } else {
                                return {results: [{id: '', text: 'No Match Found'}]};
                            }
                        }
                    }
                });
            }
        });
<?php } ?>
    $(document).ready(function () {
        $('#enable_wh').trigger('click');
        $('#unit').change(function (e) {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubUnits') ?>/" + v,
                    dataType: "json",
                    success: function (data) {
                        $('#default_sale_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $('#default_purchase_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $.each(data, function () {
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_sale_unit'));
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_purchase_unit'));
                        });
                        $('#default_sale_unit').select2('val', v);
                        $('#default_purchase_unit').select2('val', v);
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $('#default_sale_unit').select2("destroy").empty();
                $('#default_purchase_unit').select2("destroy").empty();
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_sale_unit'));
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_purchase_unit'));
                $('#default_sale_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
                $('#default_purchase_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
            }
        });
    });
</script>

<div class="modal" id="aModal" tabindex="-1" role="dialog" aria-labelledby="aModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="aModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
<!--                    <div class="form-group">
                        <label for="awarehouse" class="col-sm-4 control-label"><?= lang('warehouse') ?></label>
                        <div class="col-sm-8">
                            <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, '', 'id="awarehouse" class="form-control"');
                            ?>
                        </div>
                    </div>-->
                    <!--                     <div class="form-group">
                                             <label for="aquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                                             <div class="col-sm-8">
                                                 <input type="text" class="form-control" id="aquantity">
                                             </div>
                                         </div> -->
                    <div class="form-group">
                        <label for="acost" class="col-sm-4 control-label"><?= lang('cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="acost">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('MRP') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice">
                        </div>
                    </div>
 <div class="form-group">
                        <label for="aupprice" class="col-sm-4 control-label"><?= lang('Urbanpiper price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aupprice">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="uquantity" class="col-sm-4 control-label"><?= lang('Unit Quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" min="0" max="1000" step="0.125" id="uquantity" value="1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="uweight" class="col-sm-4 control-label"><?= lang('Unit Weight (In KG)') ?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" min="0" max="1000" step="0.125" id="uweight">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="updateAttr"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>



<!-- Urbanpiper ---->

<script type="text/javascript">
    $(document).ready(function () {
        if ('<?= $product->up_items ?>' == '1') {
            $('#urbanpipercontain').show();
        } else {
            $('#urbanpipercontain').hide();
        }

    });

    $('#urbanpiperitem').click(function () {
        let values = $(this).val();
        if (values == '1') {
            $('#urbanpipercontain').show();
        } else {
            $('#urbanpipercontain').hide();
        }
    });
     
    function deleteVarient(del_id){
      var con = confirm('Are you sure you want to delete?');
      var id = del_id;
      if(!con){
        return false;
      }
      
      $.ajax({
      type: "get", async: false,
      url: "<?= site_url('products/deleteVariant') ?>/" + id,
      //dataType: "json",
      success:function(response){
        //console.log(response);
        //bootbox.alert('response')');
        //$('#variant_'+id).hide();
        location.reload();
       },
      });
  }
  function handlePriceInput() {
        // For each input, get its value and log it on load
        $('.variant-price').each(function() {
            var newValue = $(this).val();
            var mrpValue = parseFloat($('#mrp').val());
            var priceValue = parseFloat($('#price').val());
            var productPrice = newValue + priceValue;
            calculateDiscount(newValue,mrpValue,priceValue);
           
        });
        $('.variant-price').on('input', function() {
            var newValue = $(this).val();
            var mrpValue = parseFloat($('#mrp').val());
            var priceValue = parseFloat($('#price').val());
            var productPrice = newValue + priceValue;
            calculateDiscount(newValue,mrpValue,priceValue);
        });
    }
    function calculateDiscount(priceValue,mrpValue,optionPrice){
    if (!isNaN(priceValue) && !isNaN(mrpValue) && mrpValue >= 0) {
         priceValue = optionPrice + priceValue;
        var discountValue, discountType;
            if (mrpValue >= priceValue) {
                if(priceValue == '0'){
                    var calculatedDiscount = 0;
                    $('#discount_on_mrp').val(calculatedDiscount + '%');

                }else{
                    var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                    $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                }
                
                if (!isNaN(discountValue) && discountValue >= 0) {
                    var calculatedPrice;
                    if (discountType === 'percentage') {
                        calculatedPrice = mrpValue - (mrpValue * (discountValue / 100));
                        $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                    } else {
                        calculatedPrice = mrpValue - discountValue;
                        $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                    }
                    $('#price').val(calculatedPrice.toFixed(0));
                }
            } else {
                $('#discount_on_mrp').val('0%');
                // alert('MRP should be greater than Price.');
            }
        } else {
            $('#discount_on_mrp').val('0%');
        }
} 
$(document).on('change', '.attr_price', function() {
    var newValue = $(this).val();
    const priceSpan = $(this).next('span'); // Get the associated span for displaying price
    checkvarientPrice(newValue);
}); 
function checkvarientPrice(newValue) { 
    var priceValue = parseFloat($('#price').val());
    var  calculatePrice = (priceValue + newValue )
    var mrpValue = parseFloat($('#mrp').val());
    if (calculatePrice >= mrpValue) {
        alert('Price should be smaller than MRP.');
        $('#attr_price').val('');
    }
   return;
}
$(document).ready(function() {
    discountDisebled();
    $(' #mrp, #discount_on_mrp').on('change', function() {
        var priceValue = parseFloat($('#price').val());
        var mrpValue = parseFloat($('#mrp').val());
        var discountInput = $('#discount_on_mrp').val();

        mrpValue = isNaN(mrpValue) || mrpValue === null ? 0 : mrpValue;
        priceValue = isNaN(priceValue) || priceValue === null ? 0 : priceValue;
        // discountInput = isNaN(discountInput) || discountInput === null ? 0 : discountInput;

        var discountValue, discountType;
        if (discountInput.endsWith('%')) {
            discountValue = parseFloat(discountInput.slice(0, -1));
            discountType = 'percentage';
        } else {
            discountValue = parseFloat(discountInput);
            discountType = 'absolute';
        }
        if (!isNaN(priceValue) && !isNaN(mrpValue) && mrpValue > 0) {
            if (mrpValue >= priceValue && priceValue > 0 ) {
                var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
                $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                
                if(discountInput == ''){
                    discountValue = 0;
                }
                if (!isNaN(discountValue) && discountValue >= 0) {
                    var calculatedPrice;
                    //validation for discount input
                    if(discountValue > '100'){
                        var flag = true;
                        discountValue = 0;
                        alert("Discount should be correct")
                    }
                    if (discountType === 'percentage') {
                        calculatedPrice = mrpValue - (mrpValue * (discountValue / 100));
                        $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                        
                    } else {
                        calculatedPrice = mrpValue - discountValue;
                        $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
                    }
                    $('#price').val(calculatedPrice.toFixed(0));
                }
               
            } else {
                $('#discount_on_mrp').val('0%');
                $('#mrp').val('');
                alert('MRP should be greater than Price.');
            }
        } else {
            $('#discount_on_mrp').val('0%');
        }
        if ( discountInput > "0%" || discountInput > 0  ) {
           if (discountInput > "0%") {
            if(flag == true){
                    discountInput = "0%";
                }
            $('#discount_on_mrp').val(discountInput);
           }
           if (discountInput > 0) {
            if(flag == true){
                    discountInput = "0%";
                }
            $('#discount_on_mrp').val(discountInput );
            
           }
        }
        if(discountInput == "0%"){
            $('#discount_on_mrp').val(discountInput );
        }
        if(discountInput == ''){
            $('#discount_on_mrp').val('0%');
        }
        discountDisebled();

    });
    $('#price').on('change', function() {
        var priceValue = parseFloat($(this).val());
        var mrpValue = parseFloat($('#mrp').val());

        // Recalculate discount if MRP is valid
        if (!isNaN(priceValue) && !isNaN(mrpValue) && mrpValue > 0) {
            var calculatedDiscount = ((mrpValue - priceValue) / mrpValue) * 100;
            if(priceValue > mrpValue){
                alert('MRP should be greater than Price.');
                $('#price').val('0.00');
                $('#discount_on_mrp').val('0%');
            }else{
                $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
            }
            // $('#discount_on_mrp').val(calculatedDiscount.toFixed(0) + '%');
        }
        if (priceValue > 0) {
           discountDisebled();   
        }
    });
    $('#product_discount, #discount_on_mrp').on('change', function() {
        discountDisebled();
    });
    
});
function discountDisebled(){
       var product_discount = parseFloat($('#product_discount').val()) || 0;
        var discount_on_mrp = parseFloat($('#discount_on_mrp').val()) || 0;
        if (product_discount > 0) {
            $('#discount_on_mrp').attr('readonly', true);
            $('#discount_on_mrp').val('0%');

        } else {
            $('#discount_on_mrp').attr('readonly', false);
        }
        if (discount_on_mrp > 0) {
            $('#product_discount').attr('readonly', true);
            $('#product_discount').val('0%');

        } else {
            $('#product_discount').attr('readonly', false);
        }
}
    handlePriceInput();
</script>  
<!-- End Urbanpiper --->
