<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Todays_special extends MY_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation'); 
        $this->load->model('Production_Unit_Model_New');
        $this->load->model('todaysoffer_model');
        $this->load->model('Products_model');
        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->database(); // Database library
        $this->load->library('datatables');
        $this->load->library('upload');
        $this->digital_upload_path = 'files/'.$this->Customer_assets;
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/production_unit';
        $this->thumbs_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
        $this->data['Settings'] = $this->Settings;

    }

    function index($warehouse_id = NULL) {
         $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        if ($this->Owner || $this->Admin) {
            $location_data = $this->site->getAllWarehouses(); 
        }else {
            $location_data = $this->site->getWarehouseByIDs($location_id); //get
        }
        $locationName  = '';
        foreach ($location_data as $location) {
           $locationName = $location->name;
        }
            $this->data['error']       = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories']  = $this->todaysoffer_model->getProductCategoriesList();     
            $this->data['outletName']  = $location_data;

            $bc   = array(array('link' => base_url(), 'page' => lang('Todays_Special')), array('link' => '#'));
            $meta = array('page_title' => lang('Todays_Special'), 'bc' => $bc);

            $this->page_construct('products/map_todays_offer', $meta, $this->data);   
    }
        
    public function GetProductsbyCategoriesID() {   
        $this->load->model('todaysoffer_model');
        $categoriesId    = $this->input->get('categoriesId');
        $subcategoryId   = $this->input->get('subcategoryId');
        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        $location        =  $this->todaysoffer_model->getWarehouseByID($location_id);
        $productsByCategories = '';
        
        if ($categoriesId || $subcategoryId) {
            $productsByCategories  = $this->todaysoffer_model->getProductByCategories($categoriesId,$subcategoryId);    
        }
      
        if ($productsByCategories) {
            $i=0;
            foreach ($productsByCategories as $row) {
                
                $unitData = $this->todaysoffer_model->getUnitById($row->unit);
                $row->unit_name         = $unitData->name;
                $row->unit_price        = $row->price;  
                $row->org_price         = $row->price;
                $row->base_unit_price   = $row->price;
                $row->unit_weight       = $row->weight;
                $row->quantity          = 1;
                $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);

            }
            $pr[] = ['productsByCat' => $productsByCategories,  'row' => $row, 'tax_rate' => $tax_rate];
        }else {
            $pr[] = ['productsByCat' => $productsByCategories];

        }
        echo json_encode($pr);
        return;
    }
    public function PlaceProcurementOrder(){

        $user_id       = $this->session->userdata('user_id');
        $user_data     = $this->site->getUser($user_id); //get user information
        $location_id   = $user_data->warehouse_id;
        if ($orders = $this->input->post('orders')) {
            // Debugging: Output orders received
           
            if (!empty($orders)) {
                $Items = array();
                $i = 0;
                $rows = "";
                $note = isset($orders[0]['note']) ? $orders[0]['note'] : '';
                $outletNames = isset($orders[0]['outletNames']) ? $orders[0]['outletNames'] : $locationName;
                
                $requested_delivery_date = isset($orders[0]['requested_delivery_date']) ? $orders[0]['requested_delivery_date'] : '';
                $cgst = $sgst = $igst = 0;
                
                // Determine if it's interstate for GST calculation
                $interStateTax = !empty($location_data->state_code) ? true : false;
        
                foreach ($orders as $order) {
                    // Fetch product details
                    $rows = $this->todaysoffer_model->getProductDetailsByName($order['product']);
                    $net_price = str_replace(['Rs.', ','], '', $order['net_price']);
                    // Prepare item data
                    $Items[$i] = array(
                        'product_id'           => $rows->id,
                        'category_id'         => $order['category'],
                        'title'         => $order['category'],
                        'price'       => $order['0'],
                        'date'       => $order['requested_delivery_date'],
                        
                    );
                    $i++;
                }
       
                // Insert data into procurement orders table
                if ($this->todaysoffer_model->PlaceProcurementOrder($Items)) {
                    $this->load->library('session');
                    $affected_rows = $this->db->affected_rows();
                    $this->session->set_userdata('affected_rows_count', $affected_rows);
                    $response = array('status' => 'success', 'message' => 'Order Placed Successfully.');
                } else {
                    $response = array('status' => 'error', 'message' => 'Failed to insert data.');
                }
                echo json_encode($response);
                return;
            } else {
                // If no items found in orders
                $response = array('status' => 'error', 'message' => 'Item Data Not Found.');
                echo json_encode($response);
            }
        } else {
            // If no orders received
            $this->session->set_flashdata('error', validation_errors());
            redirect("production_Unit/procurementOrders");
        }
    
    }
    public function getProcurementOrderList() {     

        // Example PHP controller endpoint
        $status          = $this->input->get('orderStatus');
        $order_id        = $this->input->get('order_id');

        if ($status) {
            $order_dispatch = ($this->Settings->set_order_dispatch == '1') ? '1' : '0';
            $getOrderData  = $this->todaysoffer_model->getProcurmentOrders($status, $order_dispatch); 
            $pr[] = ['getOrderData' => $getOrderData];

        }else {
            $pr = "Please Select Status";
        }
        echo json_encode($pr);
        return;
    }




















  
    

}
?>