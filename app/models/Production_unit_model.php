<?php
class Production_Unit_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->created_by      = $this->session->userdata('user_id');
       
    }
    // get location data
    public function getLocations() {

        $this->db->where_in('location_type', array('3', '4')); //location_type != Production Unit and HO, means show only Retail Outlet and Stockist
        $q = $this->db->get('warehouses');
            if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    //get order details
    public function getProcurementRefrenceNo($locationName, $Filter = Null) {
        
        $this->db->select('poi.id as itemId, poi.procurement_order_ref_no,poi.order_creation_date,poi.location_name,poi.location_code, poi.status,  poi.note, poi.planned_delivery_datetime');
        $this->db->from('procurement_orders poi');
        // $this->db->order_by("poi.id", "desc");
        $this->db->where('poi.status !=', 'Received');
        if (!$this->Owner || !$this->Admin) {
            if ($created_by) {
                $this->db->where('po.created_by', $created_by); 
            }
        }
        if ($locationName) {
            $this->db->where('poi.location_name', $locationName);
        }
        if ($Filter == 'Oldest') {
            $this->db->order_by('poi.order_creation_date', 'asc');
        } elseif($Filter == 'Newest') {
            $this->db->order_by('poi.order_creation_date', 'desc');
        }else{
            $this->db->order_by("poi.id", "asc");
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
              
                $data[] = $row;

            }
            return $data;
        }
        return FALSE;

    }
    //  get order items details
    public function getProcurementdetails($procurmentRefNo = null,$status= null,$itemId= null, $location_id) {
        
        $this->db->select('poi.id as itemId, poi.procurement_orders_id, poi.product_id, poi.product_name, poi.order_quantity,poi.allot_quantity, poi.product_code,poi.item_status,poi.production_unit_name, po.procurement_order_ref_no ,po.location_name,po.status, productionunit_products.stock_quantity as stock_quantity, productionunit_products.open_order_quantity, po.note, poi.production_unit_id');
        $this->db->from('procurement_order_items poi');
        // $this->db->join('procurement_orders po', 'po.procurement_order_ref_no = poi.procurement_orders_id', 'left');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id', 'left');
        $this->db->join('products ', 'products.id = poi.product_id', 'left');
        $this->db->join('productionunit_products ', 'productionunit_products.product_id = products.id', 'left');
        
        // for showing latest order items data initially
        $procurement_orders_id = $procurmentRefNo[0];
        if ($procurement_orders_id) {
            $this->db->where('poi.procurement_orders_id', $procurement_orders_id);
        }
        if ($itemId) {
            $this->db->where('poi.id', $itemId);
        }
        if ($procurmentRefNo) {
            $this->db->where_in('poi.procurement_orders_id',  $procurmentRefNo);
        }
        if ($status) {
            $this->db->where('poi.item_status', $status);
        }
        if ($location_id) {
            $this->db->where('poi.production_unit_id', $location_id);
        }
        if (!$this->Owner || !$this->Admin) {
            if ($created_by) {
                $this->db->where('po.created_by', $created_by);

            }
        }
        $this->db->order_by("po.id", "desc");

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
   #===========================================   PROCURMENT ORDER SCREEN  ============================================
    public function getProductCategoriesList() {

        $query = $this->db->query("SELECT parent.id AS parent_id, parent.name AS parent_name, sub.id AS subcategory_id, sub.name AS subcategory_name 
        FROM sma_categories AS parent 
        LEFT JOIN sma_categories AS sub ON parent.id = sub.parent_id");

            if ($query->num_rows() > 0) {
            $categories = array();
            foreach ($query->result_array() as $row) {
            $parent_id = $row['parent_id'];
            $subcategory_id = $row['subcategory_id'];

            if (!isset($categories[$parent_id])) {
            $categories[$parent_id] = array(
            'category_id' => $parent_id,
            'category_name' => $row['parent_name'],
            'subcategories' => array()
            );
            }
            if ($subcategory_id !== null) {
            $categories[$parent_id]['subcategories'][] = array(
            'subcategory_id' => $subcategory_id,
            'subcategory_name' => $row['subcategory_name']
            );
            }
            }

            // Sort categories and subcategories alphabetically
            foreach ($categories as &$category) {
            usort($category['subcategories'], function($a, $b) {
            return strcmp($a['subcategory_name'], $b['subcategory_name']);
            });
            }
            unset($category); // unset reference

            // Sort categories by category_name
            usort($categories, function($a, $b) {
            return strcmp($a['category_name'], $b['category_name']);
            });

            return $categories;
            } else {
            return FALSE;
            }

        // echo json_encode($categories);


    }
    ////////////////////////////////   getProductByCategories //////////////////////////////////////////
    public function getProductByCategories($categoryID = null, $subcategory_id = null) {
    
        // Initialize data array
        $data = array();
        
        $query = "
            SELECT 
                products.*, 
                categories.name AS categoryName,  
                units.name AS unitName, 
                tax_rates.name AS tax_rate, 
                COALESCE(pp_sub.price, products.price) AS c_price
            FROM 
                sma_products AS products
            JOIN 
                sma_tax_rates AS tax_rates ON tax_rates.id = products.tax_rate
            JOIN 
                sma_categories AS categories ON categories.id = products.category_id
            LEFT JOIN (
                SELECT * FROM sma_productionunit_products
            ) AS pup_sub ON pup_sub.product_id = products.id
            LEFT JOIN 
                sma_warehouses AS w ON w.id = pup_sub.location_id
            LEFT JOIN (
                SELECT 
                    product_id, price_group_id, price
                FROM 
                    sma_product_prices
            ) AS pp_sub 
                ON pp_sub.product_id = products.id 
                AND (
                pp_sub.price_group_id = w.price_group_id 
                OR w.price_group_id IS NULL
            )
            JOIN 
                sma_units AS units ON units.id = products.unit
            WHERE 1=1
        ";

            // Optional filters
            if (!empty($categoryID)) {
                $query .= " AND products.category_id = " . (int)$categoryID;
            }
            if (!empty($subcategory_id)) {
                $query .= " AND products.subcategory_id = " . (int)$subcategory_id;
            }

            // Final order clause
            $query .= " ORDER BY products.name ASC";

            $q = $this->db->query($query);
            // return $result->result();
       
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        
        return FALSE;
    } 
    public function PlaceProcurementOrder($order, $Items){
       
        if ($this->db->insert('procurement_orders', $order)) {
            $orderId['procurement_orders_id'] = $this->db->insert_id();
            $merged_array = array();
            // Merge array1
            foreach ($Items as $item) {
                $merged_array[] = array_merge($item, $orderId);
            }         
            if ($this->db->insert_batch('procurement_order_items', $merged_array)) {
                return true ;
            }
        }
        return false;
    }
    public function getProcurmentOrders($status, $order_dispatch){

      
        // // $this->db->select('po.procurement_order_ref_no as orderNo, po.id as orderId, po.status as orderStatus, po.created_by as placedBy, po.order_creation_date as placedOn, po.actual_delivery_date as receivedOn,poi.product_name as product_Name, COUNT(poi.id) as itemCount');
        // $this->db->select('po.procurement_order_ref_no as orderNo, po.id as orderId, po.status as orderStatus, po.created_by as placedBy, po.order_creation_date as placedOn, po.actual_delivery_date as receivedOn,createdByUsers.first_name as placedBy,  COUNT(DISTINCT poi.id) as itemCount');
        // $this->db->from('procurement_orders po');
        // $this->db->join('procurement_order_items poi', 'po.id = poi.procurement_orders_id', 'left');
        // $this->db->join('units', 'units.id = poi.product_unit_id');
        // $this->db->join('products', 'products.id = poi.product_id');
        // $this->db->join('categories cat', 'cat.id = products.category_id');
        // // $this->db->join('users', 'users.warehouse_id = po.location_id');
        // $this->db->join('users createdByUsers', 'createdByUsers.id = po.created_by');
        // $this->db->join('users', 'users.warehouse_id = po.location_id', 'left');
        $this->db->select('
            po.procurement_order_ref_no as orderNo, 
            po.id as orderId, 
            po.status as orderStatus, 
            po.created_by as createdById, 
            od.courier as courier, 
            od.tracking_number as tracking_number, 
            od.attachment as attachment,  
            po.order_creation_date as placedOn, 
            po.actual_delivery_date as receivedOn, 
            createdByUsers.first_name as createdByFirstName, 
            COUNT(DISTINCT poi.id) as itemCount'
        );
        $this->db->from('procurement_orders po');
        $this->db->join('procurement_order_items poi', 'po.id = poi.procurement_orders_id', 'left');
        $this->db->join('units', 'units.id = poi.product_unit_id', 'left');
        $this->db->join('products', 'products.id = poi.product_id', 'left');
        $this->db->join('categories cat', 'cat.id = products.category_id', 'left');
        $this->db->join('users createdByUsers', 'createdByUsers.id = po.created_by', 'left');
        $this->db->join('users warehouseUsers', 'warehouseUsers.warehouse_id = po.location_id', 'left');
        $this->db->join('orderdispatchdetails od', 'od.procurement_order_ref_no = po.procurement_order_ref_no', 'left');
        $this->db->group_by('po.id');
        // $this->db->group_by('poi.id');

        if ($status == 'previous_order') {
            $status_conditions = array('Locked', 'Completed', 'Received','Rejected','partially_completed','Dispatched');
            $this->db->where_in('po.status', $status_conditions);
        }
        if ($status == 'partially') {
            $Item_status = array('pending','partially_completed');
            $this->db->where_in('poi.item_status', $Item_status);
        }
        if ($status == 'Open') {
            $this->db->where('po.status', 'Open'); 
        }
        if ($status == 'Received') {
            if($order_dispatch == '1'){
                // $Item_status = array('Completed','Dispatched','Received','partially_completed');
                $Item_status = array('Dispatched','partially_completed');

            }else{
                $Item_status = array('Completed','partially_completed');
            }
            // $Item_status = array('partially_completed','Completed');
            $this->db->where_in('po.status', $Item_status);
            $this->db->where_in('poi.item_status', $Item_status);
            // $this->db->where('po.actual_delivery_date', '0000-00-00 00:00:00');
        }
        if (!$this->Owner || !$this->Admin) {
            if ($this->created_by) {
                $this->db->where('po.created_by', $this->created_by);

            }
        }
        $this->db->order_by("po.id", "DESC");

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;    
    }
    public function getOrdersByItems($order_id,$status ){
      
        $this->db->select('poi.id as order_id,poi.unit_price as unit_price, poi.received_quantity as received_quantity,po.id as itemsId,  poi.allot_quantity as allot_quantity,   po.note as order_note,poi.product_name,poi.order_quantity,poi.subtotal,poi.item_status,units.name as unitName,cat.name as categoryName,po.procurement_order_ref_no,po.created_by as placedBy,po.order_creation_date as placedOn,po.actual_delivery_date as receivedOn,po.status as orderStatus,products.name as product_name,products.tax_method as tax_method,tax_rates.name as tax_rate,poi.order_quantity as quantity, poi.adjustment_price');        
        $this->db->from('procurement_order_items poi');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id','left');
        $this->db->join('units', 'units.id = poi.product_unit_id');
        $this->db->join('products', 'products.id = poi.product_id');
        $this->db->join('tax_rates', 'tax_rates.id = products.tax_rate');
        $this->db->join('categories cat', 'cat.id = products.category_id');
        // if ($order_id) {
            $this->db->where('poi.procurement_orders_id', $order_id); 
        if ($status == 'partial_order_item') {
            $Item_status = array('pending','partially_completed');
            $this->db->where_in('poi.item_status', $Item_status);
        }
        // if ($status == 'Received_orders') {
        //     $this->db->where('po.actual_delivery_date', '0000-00-00 00:00:00');
        // }
            if (!$this->Owner || !$this->Admin) {
                if ($this->created_by) {
                    $this->db->where('po.created_by', $this->created_by);
                }
            }
        // }
        // $this->db->order_by("poi.procurement_orders_id", "DESC");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;    
    }
    public function getOrderByItems($order_id,$product_id){
      
        $this->db->select('poi.id as order_id,poi.unit_price as unit_price, poi.received_quantity as received_quantity,po.id as itemsId, po.location_id as location_id, poi.allot_quantity as allot_quantity,   po.note as order_note,poi.product_name,poi.order_quantity,poi.subtotal,poi.item_status,units.name as unitName,cat.name as categoryName,po.procurement_order_ref_no,po.created_by as placedBy,po.order_creation_date as placedOn,po.actual_delivery_date as receivedOn,po.status as orderStatus,products.name as product_name,products.tax_method as tax_method,tax_rates.name as tax_rate,poi.order_quantity as quantity');        
        $this->db->from('procurement_order_items poi');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id','left');
        $this->db->join('units', 'units.id = poi.product_unit_id');
        $this->db->join('products', 'products.id = poi.product_id');
        $this->db->join('tax_rates', 'tax_rates.id = products.tax_rate');
        $this->db->join('categories cat', 'cat.id = products.category_id');
        // if ($order_id) {
            $this->db->where('poi.procurement_orders_id', $order_id); 
            $this->db->where('poi.product_id', $product_id); 
            if (!$this->Owner || !$this->Admin) {
                if ($this->created_by) {
                    $this->db->where('po.created_by', $this->created_by);
                }
            }
        // }
        // $this->db->order_by("poi.procurement_orders_id", "DESC");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                // $data = $row->order_id;
                $data = $row;
            }
            return $data;
        }
        return FALSE;    
    }
  ///////////////////////////// UPDATE ORDER /////////////////////////////////////////////////////
    public function updateProcurementOrder($id, $order, $Items, $selectedHorizonatlTabValue) {
      
        if ($selectedHorizonatlTabValue === 'received_order') {
          
            $this->db->where('id', $id);
            if ($this->db->update('procurement_orders', $order)) {
            }
            $merged_array = array();
            $data = array(
                'procurement_orders_id' => $id,
            );
            foreach ($Items as $item) {
                // Merge each $item with $data
                $merged_array[] = array_merge($item, $data);
                $received_quantity = $item['received_quantity'];
                $product_id       = $item['product_id'];
                $location_id      = $order['location_id'];

                // $WarehouseProductsData = $this->site->getWarehouseProducts($product_id, $location_id); //get warehouse product data
                // $WarehouseProductsQuantity = 0;

                // if (!empty($WarehouseProductsData)) {
                //     foreach ($WarehouseProductsData as $WarehouseProducts) {
                //         $WarehouseProductsQuantity = $WarehouseProducts->quantity;
                //     }
                //     $WarehouseProductsStock = $received_quantity + $WarehouseProductsQuantity;

                //     $this->db->where('product_id', $product_id);
                //     $this->db->where('warehouse_id', $location_id);
                //     $this->db->update('sma_warehouses_products', array('quantity' => $WarehouseProductsStock));
                // } else {

                //     $this->db->insert('sma_warehouses_products', array(
                //         'product_id' => $product_id,
                //         'warehouse_id' => $location_id,
                //         'quantity' => $received_quantity
                //     ));
                // }

                // // Update the quantity in the products table
                // $this->db->set('quantity', 'quantity + ' . (int)$received_quantity, FALSE);
                // $this->db->where('id', $product_id);
                // $this->db->update('sma_products');
            }

            try {
                $this->db->update_batch('procurement_order_items', $merged_array, 'id');
                return TRUE;
            } catch (Exception $e) {
                // Handle exceptions or errors
                log_message('error', 'Error updating batch: ' . $e->getMessage());
                return FALSE;
            }
        }elseif ($selectedHorizonatlTabValue === 'reject') {
          
            $this->db->where('id', $id);
            if ($this->db->update('procurement_orders', $order)) {
            }
            $merged_array = array();
            $data = array(
                'procurement_orders_id' => $id,
            );
            foreach ($Items as $item) {
                // Merge each $item with $data
                $merged_array[] = array_merge($item, $data);
                $received_quantity = $item['received_quantity'];
                $product_id       = $item['product_id'];
                $location_id      = $order['location_id'];

                $WarehouseProductsData = $this->site->getWarehouseProducts($product_id, $location_id); //get warehouse product data
                foreach ($WarehouseProductsData as $WarehouseProducts) { 
                    $WarehouseProductsQuantity = $WarehouseProducts->quantity;
                }
                $WarehouseProductsStock = $received_quantity + $WarehouseProductsQuantity;
               
                $this->db->where('product_id', $product_id);
                $this->db->where('warehouse_id', $location_id);
                $this->db->update('sma_warehouses_products', array('quantity' => $WarehouseProductsStock)); // stock update against location and product
            }

            try {
                $this->db->update_batch('procurement_order_items', $merged_array, 'id');
                return TRUE;
            } catch (Exception $e) {
                // Handle exceptions or errors
                log_message('error', 'Error updating batch: ' . $e->getMessage());
                return FALSE;
            }
        }else{
          
            if ($this->db->update('procurement_orders', $order,array('id' => $id))) {
                $this->db->delete('procurement_order_items', array('procurement_orders_id' => $id));
            }
            $merged_array = array();
            $data = array(
                'procurement_orders_id' => $id,
            );
            foreach ($Items as $item) {
                $merged_array[] = array_merge($item, $data);
            }  
               
            if ($this->db->insert_batch('procurement_order_items', $merged_array)) {
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;

    }

    public function getOrder($location_code) {
        
        $this->db->select('procurement_order_ref_no');
        if($location_code){
            $this->db->like('procurement_order_ref_no', $location_code, 'after'); 
        }
        $this->db->order_by('procurement_orders.id', 'DESC'); // Order by procurement_orders.id in descending order
        $q = $this->db->get('procurement_orders'); 
        
        if ($q->num_rows() > 0) {
            return $q->row(); 
        }
        return FALSE;
        
    }
    public function getProductionUnitDetailsForProduct($product_id) {
        $q = $this->db->get_where('productionunit_products', ['product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
        return FALSE;
    }
    public function getWarehouseByID($id) {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
      return FALSE;
    }

    public function getProductDetailsByName($name = Null, $product_id = Null) {    
        $product_id = (int)trim($product_id, '"');
        

        $this->db->select('products.*, units.name as unit_name'); 
        $this->db->join('units', 'products.unit = units.id', 'left');
        if ($product_id) {
            $this->db->where('products.id', $product_id);
        } else {
            $this->db->where('products.name', $name);
        }
        $q = $this->db->get('products', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        } else {
            return FALSE;
        }
    }
    public function getUnitById($id) {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
   #=========================================== END  PROCURMENT ORDER SCREEN  ============================================

    // update item stock and allot quantity
    public function updateOrderItemStock($quantity, $itemId, $itemData, $procurmentDetails,$checkbox, $Complete_unit_order, $datetime,  $status) {
  
        $order_data =  $this->getProcurementdetails($procurmentId = null, null, $itemId);
        foreach($order_data as $data){
           
            $product_id            = $data->product_id;
            $stock_quantity        = $data->stock_quantity;
            $procurement_orders_id = $data->procurement_orders_id;
        }
        $new_stock_quantity = $stock_quantity - $quantity;  
        $old_stock_quantity = $stock_quantity + $quantity;  //increase stock 

        // update allot quantity,item status and item stock if order is locked and click on checkbox
        if (!empty($quantity)) {
            if($checkbox == 0){ 
                $this->db->update('sma_productionunit_products', array('stock_quantity' => $old_stock_quantity), array('product_id' => $product_id)); //update stock if uncheck allot qty checkbox
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => '0', 'item_status' => 'Locked'), array('id' => $itemId));
            }else{
                $this->db->update('sma_productionunit_products', array('stock_quantity' => $new_stock_quantity), array('product_id' => $product_id)); //update stock if check allot qty checkbox
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => $quantity, 'item_status' => 'Committed'), array('id' => $itemId));
            }
        }
        // update order and order item status if order is locked and also update status after status is Committed
        if($procurmentDetails){
            foreach($procurmentDetails as $value){
                $procurement_orders_id = $value->procurement_orders_id;
                $itemId                = $value->itemId;
                $item_status           = $value->item_status;
                $allot_quantity        = $value->allot_quantity;
                $order_quantity        = $value->order_quantity;

                // If item status is 'open', update status to 'locked'
                if($status == 'Locked'){
                    $this->db->update('sma_procurement_order_items', array('item_status' => 'Locked'), array('id' => $itemId));
                    $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurement_orders_id));
                }
                if ($Complete_unit_order == 'true') {
                    // If item status is 'Committed', update item status to 'completed'(for complete order)
                    if($allot_quantity == $order_quantity){
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'Completed'), array('id' => $itemId));
                        // $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                    
                    }elseif($allot_quantity < $order_quantity){
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'partially_completed'), array('id' => $itemId));
                        // $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                    }
                    else{
                        $this->db->update('sma_procurement_order_items', array('item_status' => 'Pending'), array('id' => $itemId));
                    }
                    // check all kitchen items status then update order status
                    // if($item_status == 'Completed' || $item_status == 'partially_completed'){
                    //     $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                    // }else{
                    //     $this->db->update('sma_procurement_orders', array('status' => 'partially_completed'), array('id' => $procurement_orders_id));
                    // }
                }
                 // check all kitchen items status then update order status
                $this->db->where('procurement_orders_id', $procurement_orders_id); // Determine overall order status based on item status
                $item_query = $this->db->get('sma_procurement_order_items');

                $complete_order = true;
                foreach ($item_query->result() as $item) {
                    if ($item->item_status != 'Completed' && $item->item_status != 'partially_completed') {
                        $complete_order = false;
                        break; 
                    }
                    if($item->item_status == 'Open'){
                        $open_order = true;
                        break;
                    }
                }
                // Update order status based on item status
                if ($complete_order) {
                    $this->db->update('sma_procurement_orders', array('status' => 'Completed'), array('id' => $procurement_orders_id));
                }else {
                    if($item->item_status != 'Open'){
                        $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurement_orders_id));
                    }
                    // $this->db->update('sma_procurement_orders', array('status' => 'Locked'), array('id' => $procurement_orders_id));
                }
            }
            return $procurmentDetails;
        }

        // for bulk action update allot quantity and item_status after order locked and click on checkbox
        foreach ($itemData as $item) {
            $itemId = $item['itemId'];
            $quantity = $item['quantity'];
            
            // Update stock quantity for the item in bulk
            $order =  $this->getProcurementdetails($procurmentRefNo = null,$status= null,$itemId);
            foreach($order as $data){
           
                $product_id            = $data->product_id;
                $stock_quantity        = $data->stock_quantity;
                $procurement_orders_id = $data->procurement_orders_id;
            }
            $bulk_stock_quantity = $stock_quantity - $quantity; // reduce stock
            $old_bulk_stock_quantity = $stock_quantity + $quantity;  //increase stock 

            if($checkbox == 0){
                $this->db->update('sma_productionunit_products', array('stock_quantity' => $old_bulk_stock_quantity), array('product_id' => $product_id));//update stock if uncheck allot qty checkbox(for bulk)
                
                $this->db->where('id', $itemId);
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => '0', 'item_status' => 'Locked')); //update status if uncheck allot qty checkbox(for bulk)
            
            }else{
                $this->db->update('sma_productionunit_products', array('stock_quantity' => $bulk_stock_quantity), array('product_id' => $product_id));//update stock if check allot qty checkbox(for bulk)
                
                $this->db->where('id', $itemId);
                $this->db->update('sma_procurement_order_items', array('allot_quantity' => $quantity, 'item_status' => 'Committed'));//update status if check allot qty checkbox(for bulk)
    
            }

        }
        //update planned_delivery_datetime after set delivery time for orders
        if(!empty($datetime)){
            $this->db->update('sma_procurement_orders', array('planned_delivery_datetime' =>  $datetime['planned_delivery_datetime']), array('id' =>  $datetime['id'])); // for orders
            $this->db->update('sma_procurement_order_items', array('planned_delivery_datetime' =>  $datetime['planned_delivery_datetime']), array('procurement_orders_id' =>  $datetime['id'])); // for orders
            
        }     
    }

    #=========================================== Production Manager Dashboard Screen  ============================================
    
    // Production Manager Dashboard
    // public function getproductDetails() {
    //     $this->db->from('products');
    //     return $products = $this->db->get()->result();
    // }
    // public function getproductDetailsByLocation($locationName) {

    //     $this->db->select('products.*, productionunit_products.location_id, warehouses.name as location_name');
    //     $this->db->from('products');
    //     $this->db->join('productionunit_products', 'productionunit_products.product_id = products.id', 'left');
    //     $this->db->join('warehouses', 'warehouses.id = productionunit_products.location_id', 'left');
    //     $this->db->where('warehouses.name', $locationName);
    //     return $products = $this->db->get()->result();
    // }
    public function getProductDetailsByLocation($locationName) {

        $this->db->distinct(); 
        $this->db->select('p.*, pu.stock_quantity, w.name as location_name, COALESCE(poi.total_order_quantity, 0) as order_quantity');
        $this->db->from('products p');
        $this->db->join('productionunit_products pu', 'pu.product_id = p.id', 'left');
        $this->db->join('warehouses w', 'w.id = pu.location_id', 'left');
        $this->db->join(
            '(SELECT product_id, SUM(order_quantity) as total_order_quantity FROM sma_procurement_order_items WHERE item_status IN ("Committed", "Locked") GROUP BY product_id) poi', 'poi.product_id = p.id', 'left'
        );
       
        $this->db->where('w.name', $locationName);
        $this->db->group_by('p.id');
    
        // Order by total_order_quantity in descending order
        $this->db->order_by('COALESCE(poi.total_order_quantity, 0) DESC');
        
        return $this->db->get()->result();
    }
    public function getProductWiseOrderdetails($product_id) {
        
        $this->db->select('poi.id as itemId, poi.procurement_orders_id, poi.product_id, poi.product_name, poi.order_quantity,poi.allot_quantity, poi.item_status,poi.production_unit_name, po.procurement_order_ref_no ,po.location_name, po.order_creation_date, productionunit_products.stock_quantity as stock_quantity, productionunit_products.open_order_quantity, po.note');
        $this->db->from('procurement_order_items poi');
        $this->db->join('procurement_orders po', 'po.id = poi.procurement_orders_id', 'left');
        $this->db->join('products ', 'products.id = poi.product_id', 'left');
        $this->db->join('productionunit_products ', 'productionunit_products.product_id = products.id', 'left');
        $this->db->where_in('poi.item_status', array('Locked', 'Committed'));
        $this->db->where('po.status', 'Locked');
        
        if ($product_id) {
            $this->db->where('poi.product_id', $product_id);
        }
        $this->db->order_by("po.id", "desc");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductStock($product_id) {
        $q = $this->db->get_where('productionunit_products', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }

     public function getProductStockinProductionDashboard($product_id) {
       
        $q = $this->db->query("SELECT stock_quantity FROM sma_productionunit_products WHERE product_id = $product_id")->result_array();
        if (count($q) > 0) {                        
            return $q[0]["stock_quantity"];
        }
        return FALSE;
    }
    public function getProductBatches($product_id) {

        $this->db->select('product_batches.*, units.name as unit_name, products.name as product_name'); 
        $this->db->from('product_batches');
        $this->db->join('products', 'product_batches.product_id = products.id', 'left'); 
        $this->db->join('units', 'products.unit = units.id', 'left');
        $this->db->where('product_batches.product_id', $product_id);

        $q = $this->db->get();
            if ($q->num_rows() > 0) {
                return $q->result(); 
            }
            return FALSE;
    }
    public function getLatestProductBatches($product_id) {

        $this->db->select('product_batches.*, units.name as unit_name'); 
        $this->db->from('product_batches');
        $this->db->join('products', 'product_batches.product_id = products.id', 'left'); 
        $this->db->join('units', 'products.unit = units.id', 'left');
        $this->db->where('product_batches.product_id', $product_id);
        $this->db->order_by('product_batches.created_at', 'desc'); 
        $this->db->limit(5); 
    
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result(); 
        }
        return FALSE;
    }
    //Insert batches
    public function addProductBatches($data){

        $product_id = $data['product_id'];
        $quantity   = $data['quantity'];
        
        $productstockdata = $this->getProductStock($product_id); // get product stock details by product id
        $existing_stock   = $productstockdata->stock_quantity;
        $new_stock        = $existing_stock + $quantity; 
       
        $this->db->where('product_id', $product_id);
        $this->db->update('sma_productionunit_products', array('stock_quantity' => $new_stock)); // update stock quantity if batch is created

        if ($this->db->insert('product_batches', $data)) {
            return true ;
        }
        return false;
    }

      public function addProductBatchesinProductionDashboard($data){

        $product_id = $data['product_id'];
        $quantity   = $data['quantity'];
         
        $productstockdata = $this->getProductStockinProductionDashboard($product_id); // get product stock details by product id 
        $existing_stock   = $productstockdata;
        $new_stock        = (int) $existing_stock + $quantity; 
       
         // update stock quantity if batch is created
        $this->db->query("UPDATE sma_productionunit_products SET stock_quantity = $new_stock WHERE product_id = $product_id");
        if ($this->db->insert('product_batches', $data)) {
            return true ;
        }
        return false;
    }

    // get last batch number for create next batch number(automatically)
    public function getLastBatchNumber($product_id) {
        
        $this->db->select('batch_no');
        $this->db->from('product_batches'); 
        $this->db->where('product_id', $product_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }

    public function resetProductStock($product_id){

        $productionunit_stock = [
            'stock_quantity'      => 0
            // 'open_order_quantity' => 0
        ];
        if ($this->db->update('productionunit_products', $productionunit_stock, array('product_id' => $product_id))) {
            return true ;
        }
        return false;
    }
    public function getOrderRefrenceNoById($order_id){
        $this->db->select('*');
        $this->db->from('procurement_orders');
        $this->db->where('id', $order_id);
        $this->db->order_by('id', 'DESC'); 
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }

     public function getWarehousesWithProductsData(){        
        $query = $this->db->query("SELECT id, name FROM sma_warehouses")->result_array();
        $products_query = $this->db->query("SELECT id, name FROM sma_products")->result_array();        
        
        $productsDetailsQuery = $this->db->query("
                        SELECT 
                            p.id AS product_id,
                            p.name,
                            pu.location_id,
                            pu.stock_quantity,
                            IFNULL(po.total_order_quantity, 0) AS total_order_quantity
                        FROM 
                            sma_products p
                        LEFT JOIN 
                            sma_productionunit_products pu ON p.id = pu.product_id
                        LEFT JOIN 
                            (SELECT product_id, SUM(order_quantity) AS total_order_quantity
                            FROM sma_procurement_order_items
                            GROUP BY product_id) po 
                            ON p.id = po.product_id
                ")->result_array();            
                
        $completeData =  array('warehouses' => $query, 'productsDetails' => $productsDetailsQuery);                
        return $completeData;
    }
    
    #=========================================== End Production Manager Dashboard Screen  ============================================
    #========================================================Ordering History  ============================================
    
    public function get_attachment($OrderRefrenceNo) {
        $this->db->select('attachment');
        $this->db->from('orderdispatchdetails');
        $this->db->where('procurement_order_ref_no', $OrderRefrenceNo);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               
                $data = $row->attachment;
            }
            return $data;
        }
        return FALSE;
    } 

    public function get_products($searchTerm) {

        $this->db->like('products.name', $searchTerm,'both');
        $this->db->or_like('products.code', $searchTerm, 'both');
        $this->db->select('products.name as name,products.id, product_variants.name as varient_name')
                ->join('product_variants', 'product_variants.product_id = products.id', 'left');

        $query = $this->db->get('products');
        $results = array();
        foreach ($query->result() as $row) {
            $results[] = $row;
        }

        return $results;
    }

    public function get_product_variants($product_id) {
        $this->db->where('product_id', $product_id);
        $query = $this->db->get('product_variants');
        
        return $query->result();
    }
    public function getProductIdByName($name) {

        $this->db->where('name', $name);
        $query = $this->db->get('products');
        $result = $query->row();
        return $result;

    }
    public function add_product_to_productionunit($data) {
        // Perform a batch insert
        return $this->db->insert_batch('productionunit_products', $data);
    }  

    public function CourierDetails($data, $procurement_order_id) {

        // return $this->db->insert('sma_orderdispatchdetails', $data);

        $result = $this->db->insert('sma_orderdispatchdetails', $data);
        if ($result) {
            $this->db->where('id', $procurement_order_id);
            $this->db->update('sma_procurement_orders', ['status' => 'Dispatched']);
            
            $this->db->where('procurement_orders_id', $procurement_order_id);
            $this->db->update('sma_procurement_order_items', ['item_status' => 'Dispatched']); 
    
            return $this->db->affected_rows();
        }
        return false; 
    }
    public function getProcurementOrderData($procurement_order_ref_no) {
        $q = $this->db->get_where('procurement_orders', array('procurement_order_ref_no' => $procurement_order_ref_no), 1);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getExistingProducts() {
        return $this->db->get('sma_productionunit_products')->result();
    }
        
    ///////////////////////////// GET ALL KOT /////////////////////////////////////////////////////
    public function all_kot($warehouse_id)
    {
        $query = "SELECT
            p.name AS Product,
            COALESCE(poi.total_order_quantity, 0) AS `Order Quantity`,
            COALESCE(pup.stock_quantity, 0) AS `Stock Quantity`,
            CASE
                WHEN COALESCE(pup.stock_quantity, 0) >= COALESCE(poi.total_order_quantity, 0) THEN 0
                WHEN COALESCE(pup.stock_quantity, 0) = 0 THEN COALESCE(poi.total_order_quantity, 0)
                WHEN COALESCE(pup.stock_quantity, 0) < COALESCE(poi.total_order_quantity, 0) THEN COALESCE(poi.total_order_quantity, 0) - COALESCE(pup.stock_quantity, 0)
                ELSE 0
            END AS `Built Quantity`,
            u.code AS Unit,
            COALESCE(GROUP_CONCAT(DISTINCT po_locations.location_name ORDER BY po_locations.location_name SEPARATOR ', '), '') AS `Outlets Requesting`,
            '' AS `Packing Instructions`,
            '' AS `Special Instruction`
        FROM
            sma_products p
        LEFT JOIN
            sma_productionunit_products pup
            ON pup.product_id = p.id AND pup.location_id IN ({$warehouse_id})
        LEFT JOIN (
            SELECT
                product_id,
                SUM(order_quantity) AS total_order_quantity
            FROM
                sma_procurement_order_items
            WHERE
                item_status IN ('Committed', 'Locked')
            GROUP BY
                product_id
        ) AS poi ON poi.product_id = p.id
        LEFT JOIN
            sma_units u ON u.id = p.unit
        LEFT JOIN (
            SELECT
                sps.product_id,
                spo.location_name
            FROM
                sma_procurement_orders spo
            JOIN
                sma_procurement_order_items sps ON spo.id = sps.procurement_orders_id
            WHERE
                spo.status = 'Locked'
                AND spo.location_id IN ({$warehouse_id})
            GROUP BY
                sps.product_id, spo.location_name
        ) AS po_locations ON po_locations.product_id = p.id
        WHERE
            pup.product_id IS NOT NULL
        GROUP BY
            p.id, p.name, p.code, pup.stock_quantity, poi.total_order_quantity, u.code
        ORDER BY
            COALESCE(poi.total_order_quantity, 0) DESC, p.name ASC;";
        $result = $this->db->query($query);
        return $result->result_array();
    }

    public function kot_by_order($orderId, $toggle)
    {
        if($toggle)
        {
            $query = "SELECT 
            p.name AS Product,
            poi.order_quantity AS `Order Quantity`,
            CASE
                WHEN pup.stock_quantity > poi.order_quantity THEN 0
                WHEN pup.stock_quantity = 0 THEN poi.order_quantity
                WHEN pup.stock_quantity < poi.order_quantity THEN poi.order_quantity - pup.stock_quantity
                ELSE 0
            END AS `Built Quantity`,
            pup.stock_quantity AS `Stock Quantity`,
            u.code AS Unit,
            po.location_name AS `Outlets Requesting`,
            po.procurement_order_ref_no AS `procurement_order_ref_no`,
            po.note AS `Packing Instructions`,
            po.note AS `Special Instruction`
            FROM sma_procurement_orders po
            JOIN sma_procurement_order_items poi ON po.id = poi.procurement_orders_id
            JOIN sma_products p ON p.id = poi.product_id
            JOIN sma_units u ON u.id = p.unit
            JOIN sma_productionunit_products pup 
            ON pup.product_id = poi.product_id
            WHERE po.id = ?
            ORDER BY p.name;";
            $result = $this->db->query($query, [$orderId]);
        }
        else{
            $warehouse_id = $this->session->userdata('warehouse_id');
                $query = "SELECT 
                    p.name AS Product,
                    poi.order_quantity AS `Order Quantity`,
                    CASE
                        WHEN pup.stock_quantity > poi.order_quantity THEN 0
                        WHEN pup.stock_quantity = 0 THEN poi.order_quantity
                        WHEN pup.stock_quantity < poi.order_quantity THEN poi.order_quantity - pup.stock_quantity
                        ELSE 0
                    END AS `Built Quantity`,
                    pup.stock_quantity AS `Stock Quantity`,
                    u.code AS Unit,
                    po.location_name AS `Outlets Requesting`,
                    po.procurement_order_ref_no AS `procurement_order_ref_no`,
                    po.note AS `Packing Instructions`,
                    po.note AS `Special Instruction`
                FROM sma_procurement_orders po
                JOIN sma_procurement_order_items poi ON po.id = poi.procurement_orders_id
                JOIN sma_products p ON p.id = poi.product_id
                JOIN sma_units u ON u.id = p.unit
                JOIN sma_productionunit_products pup ON pup.product_id = poi.product_id AND pup.location_id = ?
                WHERE po.id = ?
                ORDER BY p.name";
            
                $result = $this->db->query($query, [$warehouse_id, $orderId]);
        }
        return $result->result_array();
    }

    public function get_warehouse_name($warehouse_id)
    {
        $this->db->select('name');
        $this->db->from('sma_warehouses');
        $this->db->where('id', $warehouse_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->name;
        }
        return 'Unknown';
    }
    ///////////////////////////// update transfer after received order from outlet/////////////////////////////////////////
    public function get_transfer($procurement_order_ref_no) {
        $query = $this->db->get_where('sma_transfers', array('procurement_order_ref_no' => $procurement_order_ref_no));
        return $query->row(); 
    }
    public function get_transfer_items($transfer_id) {
        $q = $this->db->get_where('sma_transfer_items', array('transfer_id' => $transfer_id));
        if ($q->num_rows() > 0) {
            return $q->result(); 
        }
        return false; 
    }
    public function updateTransfer($id, $data , $items) {
        //  error_reporting(E_ALL);
        // ini_set('display_errors', 1);
        $status = $data['status'];
        if ($this->db->update('transfers', $data, ['id' => $id])) {            
            $this->db->delete('transfer_items', ['transfer_id' => $id]);
             
            foreach ($items as $item) {
                $item = (array) $item;
                $item['transfer_id'] = $id;
                               
                if ($status !== 'completed') {
                    $titem = $item;
                    $titem['item_status'] = $status;
                    unset($titem['hsn_code']);
                    $this->db->insert('transfer_items', $titem);
                }
                
                if($status == 'sent'){
                    $qty = $item['unit_quantity'];
                                           
                    $pclause = ['product_id' => $item['product_id'], 'warehouse_id' => $data['from_warehouse_id'], 'option_id' => $item['option_id'], 'quantity_balance >' => 0 ];
                    $piw1 = $this->getPurchasedItems($pclause);
                    if($piw1) { 
                        foreach ($piw1 as $key => $pi) {
                            
                            if($pi->quantity_balance < $qty) {
                                $quantity_balance = 0;
                                $qty = $qty - $pi->quantity_balance;
                            } else {
                                $quantity_balance = $pi->quantity_balance - $qty;
                                $qty = 0;
                            }   

                            if($this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id])){
                                
                                $tclause = ['transfer_id'=> $item['transfer_id'], 'product_id' => $item['product_id'], 'warehouse_id' => $data['to_warehouse_id'], 'option_id' => $item['option_id'], 'status'=>'!received' ];
                                $tiw2   = $this->getPurchasedItems($tclause);
                                
                                if($tiw2){ 
                                    $trpi = (array)$tiw2[0];                                   
                                    
                                } else {                    
                                   // $trpi = (array)$pi;                               
                                                                   
                                    $trpi['transfer_id']      = $item['transfer_id'];
                                    $trpi['batch_number']     = !empty($item['batch_number']) ? $item['batch_number'] : NULL;
                                    $trpi['product_id']       = $pi->product_id;
                                    $trpi['product_code']     = $pi->product_code;
                                    $trpi['product_name']     = $pi->product_name;
                                    $trpi['option_id']        = $pi->option_id;
                                    $trpi['quantity']         = $item['quantity'];
                                    $trpi['unit_quantity']    = $item['unit_quantity'];
                                    $trpi['quantity_balance']     = 0;
                                    $trpi['quantity_received']    = 0;                                    
                                    $trpi['status']           = 'pending';
                                    $trpi['warehouse_id']     = $data['to_warehouse_id'];
                                    $trpi['date']             = date('Y-m-d');
                                    $trpi['unit_cost']        = $item['unit_cost'];
                                    $trpi['real_unit_cost']   = $item['real_unit_cost'];
                                    $trpi['net_unit_cost']    = $item['net_unit_cost'];
                                    $trpi['tax_rate_id']      = $item['tax_rate_id'];
                                    $trpi['tax']              = $item['tax'];
                                    $trpi['item_tax']         = $item['item_tax'];
                                    $trpi['subtotal']         = $item['subtotal'];
                                    $trpi['expiry']           = $item['expiry'];
                                    $trpi['hsn_code']         = $item['hsn_code'];
                                    
                                    if($item['item_tax']) {
                                        $gst_rate = substr($item['tax'], 0, 4 );
                                        $gst = (float)$item['item_tax'] / 2;
                                        
                                        $trpi['gst_rate'] = ((float)$gst_rate / 2);
                                        $trpi['cgst'] = $gst;
                                        $trpi['sgst'] = $gst;
                                        $trpi['igst'] = 0;
                                        
                                        //Set IGST Conditions
                                        if($data['from_warehouse_state_code'] != '' && $data['to_warehouse_state_code']!=''){                                        
                                            if($data['from_warehouse_state_code'] != $data['to_warehouse_state_code']){

                                                $trpi['gst_rate'] = $gst_rate;
                                                $trpi['cgst'] = 0;
                                                $trpi['sgst'] = 0;
                                                $trpi['igst'] = $item['item_tax'];
                                            }                                        
                                        } 
                                    }
                                    
                                    $this->db->insert('purchase_items' , $trpi);
                                }
                            } 
                            if($qty == 0) { break; }    
                             
                        }//end foreach
                        
                    }                  
                
                   $this->site->syncProductQty($item['product_id'], $data['from_warehouse_id']);
                   if($item['option_id']){
                        $this->site->syncVariantQty($item['option_id'], $data['from_warehouse_id'], $item['product_id']);
                   }
                }//End status == sent                
                elseif($status == 'completed'){
                    
                    $clause2 = ['transfer_id' => $id ,'product_id' => $item['product_id'], 'warehouse_id' => $data['to_warehouse_id'], 'option_id' => $item['option_id'], 'batch_number' => $item['batch_number'], 'status' => '!received' ];
                    $piw2 = $this->getPurchasedItems($clause2);
                    
                    if($piw2) {
                        
                        $quantity_balance   = $piw2->quantity_balance + $item['unit_quantity'];
                        $quantity           = $item['request_quantity'];
                        $quantity_received  = $piw2->quantity_received + $item['unit_quantity'];
                        $status = $quantity == $quantity_received ? 'received' : 'partial';                        
                        $update = [
                                'quantity'          => $quantity,
                                'quantity_balance'  => $quantity_balance,
                                'quantity_received' => $quantity_received, 
                                'status'            => $status, 
                            ];
                        
                        $this->db->update('purchase_items', $update, ['id' => $piw2->id]);
                    } else {
                        $trdata['transfer_id']      = $item['transfer_id'];
                        $trdata['product_id']       = $item['product_id'];
                        $trdata['product_code']     = $item['product_code'];
                        $trdata['product_name']     = $item['product_name'];
                        $trdata['option_id']        = ($item['option_id'] ? $item['option_id'] : 0);
                        $trdata['batch_number']     = (!empty($item['batch_number']) ? $item['batch_number'] : NULL);
                        $trdata['warehouse_id']     = $data['to_warehouse_id'];
                        $trdata['net_unit_cost']    = $item['net_unit_cost'];
                        $trdata['unit_cost']        = $item['unit_cost'];
                        $trdata['real_unit_cost']   = $item['real_unit_cost'];
                        $trdata['product_unit_id']  = $item['product_unit_id'];
                        $trdata['product_unit_code']= $item['product_unit_code'];                        
                        $trdata['item_tax']         = $item['item_tax'];
                        $trdata['tax_rate_id']      = $item['tax_rate_id'];
                        $trdata['tax']              = $item['tax'];
                        $trdata['subtotal']         = $item['subtotal'];
                        $trdata['unit_quantity']    = $item['unit_quantity'];                        
                        $trdata['quantity']         = $item['quantity'];                         
                        $trdata['quantity_balance'] = $item['quantity'];
                        $trdata['quantity_received']= $item['quantity'];
                        $trdata['status']           = 'received';
                        $trdata['date']             = date('Y-m-d');
                        $trdata['hsn_code']         =  $item['hsn_code'];
                        
                        if($item['item_tax']) {
                            $gst_rate = substr($item['tax'], 0, 4 );
                            $gst = (float)$item['item_tax'] / 2;

                            $trdata['gst_rate'] = ((float)$gst_rate / 2);
                            $trdata['cgst'] = $gst;
                            $trdata['sgst'] = $gst;
                            $trdata['igst'] = 0;
                            //Set IGST Conditions
                            if($data['from_warehouse_state_code']!='' && $data['to_warehouse_state_code']!=''){                                        
                                if($data['from_warehouse_state_code'] != $data['to_warehouse_state_code']){

                                    $trdata['gst_rate'] = $gst_rate;
                                    $trdata['cgst'] = 0;
                                    $trdata['sgst'] = 0;
                                    $trdata['igst'] = $item['item_tax'];
                                }                                        
                            }
                        }
                        
                        $this->db->insert('purchase_items', $trdata);                        
                    }
                    
                    $this->site->syncProductQty($item['product_id'], $data['to_warehouse_id']);
                    if($item['option_id']){
                        $this->site->syncVariantQty($item['option_id'], $data['to_warehouse_id'], $item['product_id']);
                    }
                } //End Status == complited                  
            }
           
            $this->db->update('transfers', ['status' => $data['status']], array('id' => $id));
            return true;
        }
        return false;
    }
    public function getPurchasedItems($where_clause) {

        $product_storage_type = $where_clause['product_id'] ? $this->site->getProductStorageType($where_clause['product_id']) : 'packed';

        $orderby = ($this->Settings->accounting_method == 1) ? 'desc' : 'asc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $this->db->order_by('quantity_balance', 'DESC');

        if ($where_clause['option_id'] && $product_storage_type == 'packed') {
            $this->db->where('option_id', $where_clause['option_id']);
        }
        unset($where_clause['option_id']);

        if ($this->Settings->product_batch_setting > 0 && $where_clause['batch_number']) {
            $this->db->where('batch_number', $where_clause['batch_number']);
        }
        unset($where_clause['batch_number']);

        if ($where_clause['status']) {
            if($where_clause['status'] == '!received'){
                $this->db->where('status !=', 'received');
            } else {
                $this->db->where('status', $where_clause['status']);
            }
            unset($where_clause['status']);
        } else {
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        }
        $this->db->where($where_clause);

        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
           foreach (($q->result()) as $row) {
                
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    ///////////////////////////// update transfer after received order from outlet END/////////////////////////////////////////
    
    // while mapping products : add data in  sma_warehouses_products table
    public function add_product_to_warehouse($war_data) {
        return $this->db->insert_batch('sma_warehouses_products', $war_data);
    }
    // update quantity againt warehouse in warehouse product table
    public function updateWarehouseProductQty($data){

        $product_id     = $data['product_id'];
        $quantity       = $data['quantity'];
        $warehouse_id   = $data['location_id'];
        
        $productstockdata = $this->site->getWarehouseProductQuantity($warehouse_id, $product_id); // get product stock details by product id
        $existing_stock   = $productstockdata->quantity;
        $new_stock        = $existing_stock + $quantity; 
       
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $this->db->update('sma_warehouses_products', array('quantity' => $new_stock)); 
        return true;
    }

}
?>