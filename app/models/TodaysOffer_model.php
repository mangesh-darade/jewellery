<?php class TodaysOffer_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->created_by      = $this->session->userdata('user_id');
       
    }

    ////////////////////////////////   getProductByCategories //////////////////////////////////////////
    public function getProductByCategories($categoryID, $subcategory_id = null) {
        $data = array();
        $query = "
            SELECT 
                products.*,
                 categories.name AS categoryName
            FROM 
                sma_products AS products
                 JOIN 
                sma_categories AS categories ON categories.id = products.category_id
            WHERE 
                products.category_id = " . (int)$categoryID;

        // Only add subcategory condition if it's provided
        if (!is_null($subcategory_id)) {
            $query .= " AND products.subcategory_id = " . (int)$subcategory_id;
        }

        $query .= " ORDER BY products.name ASC";

        $q = $this->db->query($query);

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return FALSE;
    }

    public function getProcurmentOrders($status, $order_dispatch = null, $specific_date = null) {
        $this->db->select('sma_today_special_items.product_id, sma_today_special_items.id,sma_today_special_items.price, sma_today_special_items.date,sma_products.name as product_name,sma_categories.name as category_name');
        $this->db->from('sma_today_special_items');
        $this->db->join('sma_products', 'sma_today_special_items.product_id = sma_products.id', 'left');
        $this->db->join('sma_categories', 'sma_categories.id = sma_products.category_id', 'left');

        // Get today's date
        $today = date('Y-m-d');

        // Apply filter based on status
        if ($status === 'current_order') {
            $this->db->where('DATE(sma_today_special_items.date)', $today);

        } elseif ($status === 'previous_order') {
            $this->db->where('DATE(sma_today_special_items.date) <=', $today);
            $this->db->group_by('sma_today_special_items.product_id');

        } elseif ($status === 'partially') {
             $this->db->order_by('sma_today_special_items.date', 'DESC');
        }

        // Optional order_dispatch filter
        if (!empty($order_dispatch)) {
            $this->db->where('sma_today_special_items.order_dispatch', $order_dispatch);
        }

        $this->db->order_by('sma_today_special_items.id', 'DESC');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
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

    public function PlaceProcurementOrder($Items){
                 
            if ($this->db->insert_batch('sma_today_special_items', $Items)) {
                return true ;
            }
      
        return false;
    }
    public function getUnitById($id) {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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
















}
?>