<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pos extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->load->model('pos_model');
        $this->load->model('sales_model');
        $this->load->helper('text');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->data['pos_settings']->pos_theme = json_decode($this->data['pos_settings']->pos_theme);
        $this->session->set_userdata('last_activity', now());
        $this->lang->load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }

    public function sales($warehouse_id = NULL){
        $this->sma->checkPermissions('index');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($this->data['error'])){
			$error_url = "http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
			$logger = array($this->data['error'] , $error_url);
			$this->pos_error_log($logger);
		}
		if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('pos_sales')));
        $meta = array('page_title' => lang('pos_sales'), 'bc' => $bc);
        $this->page_construct('pos/sales', $meta, $this->data);
    }

    public function getSales($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
           $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $detail_link = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link2 = anchor('sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link3 = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li>' . $detail_link2 . '</li>
        <li>' . $detail_link3 . '</li>
         <li>' . $duplicate_link . '</li>
        <li>' . $payments_link . '</li>
        <li>' . $add_payment_link . '</li>
        <li>' . $add_delivery_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $email_link . '</li>
        <li>' . $return_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, date, reference_no, biller, customer, (grand_total+rounding), paid, (grand_total-paid) as balance, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, date, reference_no, biller, customer, (grand_total+rounding), paid, (grand_total+rounding-paid) as balance, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->group_by('sales.id');
        }
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id, cemail")->unset_column('cemail');
        echo $this->datatables->generate();
    }

    /* ---------------------------------------------------------------------------------------------------- */

    public function index($sid = NULL)
    { 
        $this->sma->checkPermissions();
 

        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            redirect('pos/open_register');
        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend = $this->input->post('suspend') ? TRUE : FALSE;
        $count = $this->input->post('count') ? $this->input->post('count') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
        $Settings =   $this->Settings;//$this->site->get_setting();
        if(isset($Settings->pos_type) && $Settings->pos_type=='pharma'){
          $this->form_validation->set_rules('patient_name',  'Patient Name', 'trim');
          $this->form_validation->set_rules('doctor_name', 'Doctor Name' , 'trim');
        }

        if ($this->form_validation->run() == TRUE) {

            $date = date('Y-m-d H:i:s');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('pos_note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $reference = $this->site->getReference('pos');

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r]; 
                $hsn_code = $_POST['hsn_code'][$r];
                $hsn_code = ($hsn_code=='null')?'':$hsn_code;
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    // $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== FALSE) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float)($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);
                    $mrp =isset( $product_details->mrp) && !empty($product_details->mrp)?$product_details->mrp:$item_net_price;  
                    $products[] = array(
                        'product_id'      => $item_id,
                        'product_code'    => $item_code,
                        'product_name'    => $item_name,
                        'product_type'    => $item_type,
                        'option_id'       => $item_option,
                        'net_unit_price'  => $item_net_price,
                        'unit_price'      => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'        => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit ? $unit->code : NULL,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id'    => $warehouse_id,
                        'item_tax'        => $pr_item_tax,
                        'tax_rate_id'     => $pr_tax,
                        'tax'             => $tax,
                        'discount'        => $item_discount,
                        'item_discount'   => $pr_item_discount,
                        'subtotal'        => $this->sma->formatDecimal($subtotal),
                        'serial_no'       => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'mrp'             => $mrp,
                        'hsn_code'         => $hsn_code,
                    );

                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== FALSE) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float)($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = NULL;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = NULL;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4); 
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $rounding = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = $this->sma->formatMoney($round_total - $grand_total);
            }
            $data = array('date'              => $date,
                          'reference_no'      => $reference,
                          'customer_id'       => $customer_id,
                          'customer'          => $customer,
                          'biller_id'         => $biller_id,
                          'biller'            => $biller,
                          'warehouse_id'      => $warehouse_id,
                          'note'              => $note,
                          'staff_note'        => $staff_note,
                          'total'             => $total,
                          'product_discount'  => $product_discount,
                          'order_discount_id' => $order_discount_id,
                          'order_discount'    => $order_discount,
                          'total_discount'    => $total_discount,
                          'product_tax'       => $product_tax,
                          'order_tax_id'      => $order_tax_id,
                          'order_tax'         => $order_tax,
                          'total_tax'         => $total_tax,
                          'shipping'          => $this->sma->formatDecimal($shipping),
                          'grand_total'       => $grand_total,
                          'total_items'       => $total_items,
                          'sale_status'       => $sale_status,
                          'payment_status'    => $payment_status,
                          'payment_term'      => $payment_term,
                          'rounding'          => $rounding,
                          'pos'               => 1,
                          'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                          'created_by'        => $this->session->userdata('user_id'),
            );

            if (!$suspend) { 
                $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) {
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->sma->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } 
                        if ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance = $gc->balance - $amount_paying;
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['paying_gift_card_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                                'gc_balance'  => $gc_balance,
                                );

                        } else {
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['cc_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                                'transaction_id' => $_POST['cc_transac_no'][$r],
                                );

                        }
 
                    }
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }

            // $this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($suspend) {
              
                $data['suspend_note'] = $this->input->post('suspend_note');
                
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    redirect("pos");
                    exit;
                }
            } else {
            if(isset($Settings->pos_type) && $Settings->pos_type=='pharma'){
                     $patient_name = $this->input->post('patient_name');
                     if($patient_name):
                         $data['cf1'] = $patient_name;
                     endif;
                     
                     $doctor_name = $this->input->post('doctor_name');
                     if($doctor_name):
                         $data['cf2'] = $doctor_name;
                     endif;
                  }
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    if(isset($sale['redirect_pay_url']) && !empty($sale['redirect_pay_url'])){ 
                        header("Location:  ".$sale['redirect_pay_url']);
                        exit;
                    }
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    } 
                    
                    /*------ For checking Print/notPrint Button updated by SW 21/01/2017 ---------------*/
                    $print = isset($_POST['submit_type'])?$_POST['submit_type']:'print';
                    $_SESSION['print_type'] = $print; 
                    /*------ End For checking Print/notPrint Button updated by SW 21/01/2017 ---------------*/
                    
                    $this->session->set_flashdata('message', $msg);

                    //redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id']);
                    if($_POST['submit_type'] == 'notprint'){
                        $response['status'] = 'success';
                        $response['sale'] =$sale;
                        $response['message'] =$msg;
                        echo json_encode($response);
                        exit;
                    }else{
                        redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id']);
                        exit;
                    }



                }
            }
        } else {
            $this->data['suspend_sale'] = NULL;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                    krsort($inv_items);
                    $c = rand(100000, 9999999);
                    foreach ($inv_items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->tax_method = 0;
                            $row->quantity = 0;
                        } else {
                            $category = $this->site->getCategoryByID($row->category_id);
                            $row->category_name = $category->name;
                            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        }
                        $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $row->quantity += $pi->quantity_balance;
                            }
                        }
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->type = $item->product_type;            
                        $row->quantity += $item->quantity;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price = $item->real_unit_price;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->serial = $item->serial_no;
                        $row->option = $item->option_id;
                        $options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

                        if ($options) {
                            $option_quantity = 0;
                            foreach ($options as $option) {
                                $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                                if ($pis) {
                                    foreach ($pis as $pi) {
                                        $option_quantity += $pi->quantity_balance;
                                    }
                                }
                                if ($option->quantity > $option_quantity) {
                                    $option->quantity = $option_quantity;
                                }
                            }
                        }

                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;
                        
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                                'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }

                    $this->data['items'] = json_encode($pr);
                    $this->data['sid'] = $sid;
                    $this->data['suspend_sale'] = $suspended_sale;
                    $this->data['message'] = lang('suspended_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                   
                } else {
                    //$this->session->set_flashdata('error', lang("bill_x_found"));
                    redirect("pos");
                }
            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                
                
                
                $this->data['reference_note'] = NULL;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');

            $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['user'] = $this->site->getUser();
            $this->data["tcp"] = $this->pos_model->products_count($this->pos_settings->default_category);
            
             $featuerd_products_count = $this->pos_model->featuerd_products_count();
         
            if((int)$featuerd_products_count > 0 &&   $this->pos_settings->pos_screen_products==1): 
                $this->data['products'] = $this->featuerdProducts();
                $this->data['featuerd_products'] = 1;
            else :
                $this->data['products'] = $this->ajaxproducts($this->pos_settings->default_category);
            endif;
            
            
             
            
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
           
              
        if($this->pos_settings->paynear==1):
            $ci = get_instance();
            $ci->config->load('payment_gateways', TRUE);
            $payment_config         = $ci->config->item('payment_gateways');
            $paynear_credential     = $payment_config['paynear'];
            $this->pos_settings->paynear_app = isset($paynear_credential['PAYNEAR_APP_SECRET_KEY']) && !empty($paynear_credential['PAYNEAR_APP_SECRET_KEY']) ? $paynear_credential['PAYNEAR_APP_SECRET_KEY'] : '';
            $this->pos_settings->paynear_web    = isset($paynear_credential['PAYNEAR_SECRET_KEY']) && !empty($paynear_credential['PAYNEAR_SECRET_KEY']) ? $paynear_credential['PAYNEAR_SECRET_KEY'] : '';
        endif; 
        $this->data['pos_settings'] = $this->pos_settings;
 

            $this->data['opend_bill_count_custom'] = $this->pos_model->bills_count(); //updated by SW 0n25-01-2015 
            $this->load->view($this->theme . 'pos/add', $this->data);
        }
    }

    public function view_bill()
    {
        $this->sma->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function registers()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('open_registers')));
        $meta = array('page_title' => lang('open_registers'), 'bc' => $bc);
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    public function open_register()
    {
        $this->sma->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang("cash_in_hand"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'date' => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
                );
        }
        if ($this->form_validation->run() == TRUE && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang("welcome_to_pos"));
            redirect("pos");
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('open_register')));
            $meta = array('page_title' => lang('open_register'), 'bc' => $bc);
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function close_register($user_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang("total_cash"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang("total_cheques"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang("total_cc_slips"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $rid = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = array(
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
                );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang("register_closed"));
            redirect("welcome");
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $register_open_time = $user_register ? $user_register->date : NULL;
                $this->data['cash_in_hand'] = $user_register ? $user_register->cash_in_hand : NULL;
                $this->data['register_open_time'] = $user_register ? $register_open_time : NULL;
            } else {
                $register_open_time = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = NULL;
                $this->data['register_open_time'] = NULL;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['cashrefunds'] = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users'] = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

     public function getProductDataByCode($code = NULL, $warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        if (!$code) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = false;
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology,  $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            $row->serial = '';
            $options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
            }
            $row->option = $option;
            $row->quantity = 0;
            $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo NULL; die();
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $option_quantity += $pi->quantity_balance;
                        }
                    }
                    if ($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }
            if ($row->promotion) {
                $row->price = $row->promo_price;
            } elseif ($customer->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            } elseif ($warehouse->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            }
            $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
            $row->real_unit_price = $row->price;
            $row->base_quantity = 1;
            $row->base_unit = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
            $combo_items = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);

            $this->sma->send_json($pr);
        } else {
            echo NULL;
        }
    }

    public function ajaxproducts($category_id = NULL, $brand_id = NULL)
    {
        $this->sma->checkPermissions('index');
        $warehouse_id = NULL;
        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            //$user = $this->site->getUser();
            //$warehouse_id = $user->warehouse_id;
        }
        
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = NULL;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library("pagination");

        $config = array();
        $config["base_url"] = base_url() . "pos/ajaxproducts";
        $config["total_rows"] = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id,$warehouse_id);
        $config["per_page"] = $this->pos_settings->pro_limit;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $subcategory_id, $brand_id,$warehouse_id);
        $pro = 1;
        $prcount = $config["total_rows"];
         $i=1;
      
        $prods = '<div>';
        if (!empty($products)) {
           // foreach (limit( $products,21) as $product) {
           $i= 0;
            foreach ($products as $product) {
            
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }

                $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\"><img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded' /><span>" . character_limiter($product->name, 20) . "</span></button>";

                $pro++;
               if (++$i == 21) break;
             
             
             //$i==21;
           //break 21; 

            }
            
        }
        $prods .= "</div>";
        
        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function ajaxcategorydata($category_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->site->getSubCategories($category_id);
        $scats = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(array('products' => $products, 'subcategories' => $scats, 'tcp' => $tcp));
    }

    public function ajaxbranddata($brand_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }

        $products = $this->ajaxproducts(FALSE, $brand_id);

        if (!($tcp = $this->pos_model->products_count(FALSE, FALSE, $brand_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(array('products' => $products, 'tcp' => $tcp));
    }

    /* ------------------------------------------------------------------------------------ */

    public function view($sale_id = NULL, $modal = NULL)
    { 
    
        $this->sma->checkPermissions('index');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $_PID = $this->Settings->default_printer;
    	$this->data['default_printer'] =  $this->site->defaultPrinterOption($_PID);
    	
        $this->load->helper('text');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        
        if($this->data['default_printer']->tax_classification_view):
         $inv->rows_tax = $this->sales_model->getAllTaxItems($sale_id,$inv->return_id) ;
        endif; 
        
        
        $isGstSale = $this->site->isGstSale($sale_id);
        $inv->GstSale = !empty($isGstSale)?1:0;
        //var_dump( $inv->GstSale);
        
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
	$print = array();
	$print['print_option'] =  $this->site->defaultPrinterOption($_PID);
        $print['rows'] = $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $print['biller'] = $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $print['customer'] = $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $print['payments'] = $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $print['pos'] = $this->data['pos'] = $this->pos_model->getSetting();
        unset($print['pos']->pos_theme);
        $print['barcode'] = $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $print['return_sale'] = $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $print['return_rows'] = $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $print['return_payments'] = $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $print['inv'] = $this->data['inv'] = $inv;
        $print['sid'] = $this->data['sid'] = $sale_id;
        $print['modal'] = $this->data['modal'] = $modal;
        $print['page_title'] = $this->data['page_title'] = $this->lang->line("invoice"); 
        $print['taxItems'] = $this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($inv->id,$inv->return_id) ;
         
        $Settings =  $this->Settings;//$this->site->get_setting();
        if($inv->sale_status=='completed' && $inv->payment_status=='paid' ):
            $syncID =  $this->pos_model->syncOrderReward($inv->id);
            if($syncID):
               $ci = get_instance();                
                $order_pt = floor(($this->data['inv']->grand_total/$Settings->each_spent)*$Settings->ca_point);
                $data =array();
                $data['customer_id'] =  $this->data['customer']->phone ; 
                $data['merchant_id'] =  $ci->config->item('merchant_phone');  
                $data['points']      =  $order_pt ; 
                $data['order_id']    =  $sale_id ; 
                $data['remark']      =  'Order ID '.$sale_id.' point achived'. $order_pt  ;
                $url = 'http://simplypos.co.in/api/v1/customer/merchant/transaction/reward';
                 $res = $this->post_to_url($url, $data) ;
            endif;  
        endif;
        $print['pos_type'] = $Settings->pos_type;
        $this->data['sms_limit']     =  $this->sma->BalanceSMS();
      	
        if(isset($Settings->pos_type) && $Settings->pos_type=='pharma'):
            $print['patient_name'] =  $inv->cf1;
            $print['doctor_name'] =  $inv->cf2;
      	endif;
       
        $this->load->view($this->theme . 'pos/view', $this->data);
        
        $print['brcode'] = $this->sma->save_barcode($inv->reference_no, 'code128', 66, false);
        $print['qrcode'] = $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2);
        $arr = explode("'",$print['brcode']);
        $print['brcode'] = $arr[1];
        $qrr = explode("'",$print['qrcode']);
        $print['qrcode'] = $qrr[1];
        //echo $print['rows'][0]->net_unit_price;
        foreach($print['rows'] as $key => $row){
            foreach($row as $key2 => $value){
                if($key2 == 'quantity'){
                        $print['rows'][$key]->quantity = round($value, 2);
                }
                if($key2 == 'unit_quantity'){
                        $print['rows'][$key]->quantity = round($value, 2);
                }
            }
        }/*
        foreach($print['payments'] as $key => $row){
                foreach($row as $key2 => $value){
                        $print['payments'][$key]->$key2 = round($value);
                }
        }
        foreach($print['inv'] as $key => $row){
                $print['inv']->$key = round($row, 2);
        }*/

        if($sale_id != $_SESSION['print'] && $_SESSION['print_type']==NULL){
            $row_taxes_print = $inv->rows_tax ;
            unset( $inv->rows_tax );
            $row_taxes_print_arr = array() ;    
            foreach ($row_taxes_print as $_key => $_data) {
                foreach ( $_data as $_key1 => $value1) {
                    $row_taxes_print_arr[] =  $value1;
                }
            } 
             $inv->rows_tax = $row_taxes_print_arr;
                ?>
                <script>
                        window.MyHandler.setPrintRequest('<?php echo json_encode($print); ?>');
                </script>
                <?php 
                 
                unset($print);
        }
        $_SESSION['print'] = $sale_id;
    }

    public function register_details()
    {
        $this->sma->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getTodayCCSales();
        $this->data['cashsales'] = $this->pos_model->getTodayCashSales();
        $this->data['chsales'] = $this->pos_model->getTodayChSales();
        $this->data['pppsales'] = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales'] = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales'] = $this->pos_model->getTodaySales();
        $this->data['refunds'] = $this->pos_model->getTodayRefunds();
        $this->data['expenses'] = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', TRUE);
        if ($pin == $this->pos_pin) {
            $this->sma->send_json(array('res' => 1));
        }
        $this->sma->send_json(array('res' => 0));
    }

    public function barcode($text = NULL, $bcs = 'code128', $height = 50)
    {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : NULL,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'instamojo'                 => $this->input->post('instamojo'),
                'ccavenue'                  => $this->input->post('ccavenue'),
                'paytm'                     => $this->input->post('paytm'),
                'paynear'                   => $this->input->post('paynear'),
                'payumoney'                   => $this->input->post('payumoney'),
                'default_eshop_warehouse'   => $this->input->post('default_eshop_warehouse'),
                'default_eshop_pay'         => $this->input->post('default_eshop_pay'),
                'eshop_cod'                 => $this->input->post('eshop_cod'),
                'eshop_order_tax'           => $this->input->post('eshop_order_tax'),
                'pos_screen_products'       => $this->input->post('pos_screen_products'),
                'pos_theme'                 => json_encode($this->input->post('pos_theme')),
            );
            $payment_config = array(
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
                'instamojo_api_key'    => $this->input->post('instamojo_api_key'),
                'instamojo_auth_token'    => $this->input->post('instamojo_auth_token'),
                'ccavenue_merchant_id'    => $this->input->post('ccavenue_merchant_id'),
                'ccavenue_access_code'    => $this->input->post('ccavenue_access_code'),
                'ccavenue_working_key'    => $this->input->post('ccavenue_working_key'),
                'PAYTM_ENVIRONMENT' => $this->input->post('PAYTM_ENVIRONMENT'), 
		'PAYTM_MERCHANT_KEY' => $this->input->post('PAYTM_MERCHANT_KEY'), 
		'PAYTM_MERCHANT_MID' => $this->input->post('PAYTM_MERCHANT_MID'), 
		'PAYTM_MERCHANT_WEBSITE' => $this->input->post('PAYTM_MERCHANT_WEBSITE'), 
		'PAYNEAR_APP_SECRET_KEY'  => $this->input->post('PAYNEAR_APP_SECRET_KEY'), 
		'PAYNEAR_SECRET_KEY'  => $this->input->post('PAYNEAR_SECRET_KEY'), 
		'PAYNEAR_MERCHANT_ID' => $this->input->post('PAYNEAR_MERCHANT_ID'), 
		'PAYNEAR_APP_MERCHANT_ID' => $this->input->post('PAYNEAR_APP_MERCHANT_ID'), 
                
                'PAYUMONEY_MID'  => $this->input->post('PAYUMONEY_MID'),  
                'PAYUMONEY_KEY'  => $this->input->post('PAYUMONEY_KEY'),  
                'PAYUMONEY_SALT'  => $this->input->post('PAYUMONEY_SALT'),  
                'PAYUMONEY_AUTH_HEADER'  => $this->input->post('PAYUMONEY_AUTH_HEADER'),  
                
                
		'PAYNEAR_SANDBOX'     =>  0, 
                
            );
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("pos/settings");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->updateSetting($data)) {
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                redirect("pos/settings?pos_setting_change=1");
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                redirect("pos/settings");
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos'] = $this->pos_model->getSetting();
            $this->data['pos']->pos_theme = json_decode($this->data['pos']->pos_theme);
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize = $this->config->item('authorize');
            $this->data['api_login_id'] = $authorize['api_login_id'];
            $this->data['api_transaction_key'] = $authorize['api_transaction_key'];
            $this->data['APIUsername'] = $this->config->item('APIUsername');
            $this->data['APIPassword'] = $this->config->item('APIPassword');
            $this->data['APISignature'] = $this->config->item('APISignature');
            $this->data['paypal_balance'] = NULL; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance'] = NULL; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $instamojo = $this->config->item('instamojo'); 
            $this->data['instamojo_auth_token'] = $instamojo['AUTH_TOKEN'];
            $this->data['instamojo_api_key'] = $instamojo['API_KEY'];  
            
            $ccavenue = $this->config->item('ccavenue'); 
            $this->data['ccavenue_merchant_id'] = $ccavenue['MERCHANT_ID'];
            $this->data['ccavenue_access_code'] = $ccavenue['ACCESS_CODE'];      
            $this->data['ccavenue_working_key'] = $ccavenue['API_KEY'];     
            
             $paytm = $this->config->item('paytm'); 
            $this->data['PAYTM_ENVIRONMENT'] = $paytm['PAYTM_ENVIRONMENT'];
            $this->data['PAYTM_MERCHANT_KEY'] = $paytm['PAYTM_MERCHANT_KEY'];      
            $this->data['PAYTM_MERCHANT_MID'] = $paytm['PAYTM_MERCHANT_MID'];     
            $this->data['PAYTM_MERCHANT_WEBSITE'] = $paytm['PAYTM_MERCHANT_WEBSITE'];   
            
            $paynear = $this->config->item('paynear');  
            $this->data['PAYNEAR_APP_SECRET_KEY']   = $paynear['PAYNEAR_APP_SECRET_KEY'];
            $this->data['PAYNEAR_SECRET_KEY']   = $paynear['PAYNEAR_SECRET_KEY'];
            $this->data['PAYNEAR_MERCHANT_ID']  = $paynear['PAYNEAR_MERCHANT_ID'];
            $this->data['PAYNEAR_APP_MERCHANT_ID']  = $paynear['PAYNEAR_APP_MERCHANT_ID'];
            $this->data['PAYNEAR_SANDBOX']      = $paynear['PAYNEAR_SANDBOX'];
            
            $payumoney = $this->config->item('payumoney');  
            $this->data['PAYUMONEY_MID']            = $payumoney['PAYUMONEY_MID'];
            $this->data['PAYUMONEY_KEY']            = $payumoney['PAYUMONEY_KEY'];
            $this->data['PAYUMONEY_SALT']           = $payumoney['PAYUMONEY_SALT'];
            $this->data['PAYUMONEY_AUTH_HEADER']    = $payumoney['PAYUMONEY_AUTH_HEADER'];
                    
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('pos_settings')));
            $meta = array('page_title' => lang('pos_settings'), 'bc' => $bc);
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = array(
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
            'instamojo_api_key'    => $config['instamojo_api_key'],
            'instamojo_auth_token'    => $config['instamojo_auth_token'],
            'ccavenue_merchant_id'   => $config['ccavenue_merchant_id'],
            'ccavenue_access_code'   => $config['ccavenue_access_code'],
            'ccavenue_working_key'   => $config['ccavenue_working_key'],
            'PAYTM_ENVIRONMENT'   => $config['PAYTM_ENVIRONMENT'],
            'PAYTM_MERCHANT_KEY'   => $config['PAYTM_MERCHANT_KEY'],
            'PAYTM_MERCHANT_MID'   => $config['PAYTM_MERCHANT_MID'],
            'PAYTM_MERCHANT_WEBSITE'   => $config['PAYTM_MERCHANT_WEBSITE'],
            'PAYNEAR_SECRET_KEY'=>$config['PAYNEAR_SECRET_KEY'],
            'PAYNEAR_MERCHANT_ID'=>$config['PAYNEAR_MERCHANT_ID'],
            'PAYNEAR_SANDBOX'=>$config['PAYNEAR_SANDBOX'],	
            'PAYNEAR_APP_SECRET_KEY'=>$config['PAYNEAR_APP_SECRET_KEY'],    
            'PAYNEAR_APP_MERCHANT_ID'=>$config['PAYNEAR_APP_MERCHANT_ID'],
            
            'PAYUMONEY_MID'=>$config['PAYUMONEY_MID'],
            'PAYUMONEY_KEY'=>$config['PAYUMONEY_KEY'],
            'PAYUMONEY_SALT'=>$config['PAYUMONEY_SALT'],
            'PAYUMONEY_AUTH_HEADER'=>$config['PAYUMONEY_AUTH_HEADER'], 
        );
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url'] = site_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;

        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $bills = $this->pos_model->fetch_bills($config['per_page'], $per_page,1);
        if (!empty($bills)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>'.lang('date').': ' . $bill->date . '<br>'.lang('items').': ' . $bill->count . '<br>'.lang('total').': ' . $this->sma->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_opeded_bill') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, TRUE);

    }

    public function delete($id = NULL){
        $this->sma->checkPermissions('index');
        if ($this->pos_model->deleteBill($id)) {
            echo lang("suspended_sale_deleted");
        }
    }

    public function email_receipt($sale_id = NULL){
        $this->sma->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        } 
        $_PID = $this->Settings->default_printer;
    	$this->data['default_printer'] =  $this->site->defaultPrinterOption($_PID);
        
        if ( ! $sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if($this->data['default_printer']->tax_classification_view):
         $inv->rows_tax = $this->sales_model->getAllTaxItems($sale_id,$inv->return_id) ;
        endif; 
        $isGstSale = $this->site->isGstSale($sale_id);
        $inv->GstSale = !empty($isGstSale)?1:0;
        
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);

        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['page_title'] = $this->lang->line("invoice");

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->sma->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }
        $this->data['customer']->email =  $to;
        $this->data['taxItems'] = $this->sales_model->getAllTaxItemsGroup($inv->id,$inv->return_id) ;
        $Settings =   $this->Settings;//$this->site->get_setting();
        
        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, TRUE);
            
        if ($this->sma->send_email($to, 'Receipt from ' . $this->data['biller']->company, $receipt)) {
            $this->sma->send_json(array('msg' => $this->lang->line("email_sent")));
        } else {
            $this->sma->send_json(array('msg' => $this->lang->line("email_failed")));
        }
    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        } else {
            die('Failed to update last activity.');
        }
    }

    public function add_payment($id = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == TRUE) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'received',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == TRUE && $msg = $this->pos_model->addPayment($payment, $customer_id)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>'.$e;
                            }
                        } else {
                            $error .= '<br>'.$m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang("payment_added"));
                }
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            redirect("pos/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale = $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }

    public function updates()
    {
        /*
         if (DEMO) {
         
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('envato_username', lang("envato_username"), 'required');
        if ($this->form_validation->run() == TRUE) {
            $this->db->update('pos_settings', array('purchase_code' => $this->input->post('purchase_code', TRUE), 'envato_username' => $this->input->post('envato_username', TRUE)), array('pos_id' => 1));
            redirect('pos/updates');
        } else {
            $fields = array('version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol . 'tecdiary.com/api/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->page_construct('pos/updates', $meta, $this->data);
        }*/
    }

    public function install_update($file, $m_version, $version)
    {
        /*if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->sma->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                redirect("pos/updates");
            }
        }
        $this->db->update('pos_settings', array('version' => $version, 'update' => 0), array('pos_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        redirect("pos/updates");
         * 
         */
    }
    function getProductByID($id = NULL) {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $row = $this->site->getProductByID($id);
        echo json_encode($row);
    }

    public function instamojo_notify(){
        $payment_request_id = $this->input->get('payment_request_id');
        $payment_id         = $this->input->get('payment_id');
        $this->load->library('instamojo'); 
        if(!empty($payment_request_id) && !empty($payment_id)):
          
            $Transaction = $this->pos_model->getInstamojoTransaction(array('request_id'=>$payment_request_id)); 
            
            if($Transaction->request_id==$payment_request_id){
                $sid = $Transaction->order_id;
                $this->pos_model->updateInstamojoTransaction($payment_request_id,array('payment_id'=>$payment_id));
                $ci = get_instance();
                $ci->config->load('payment_gateways', TRUE);
                $payment_config = $ci->config->item('payment_gateways');
                
                $instamojo_credential = $payment_config['instamojo'];
                $api = new Instamojo($instamojo_credential['API_KEY'], $instamojo_credential['AUTH_TOKEN'], $instamojo_credential['API_URL']);
                $paymentDetail = $api->paymentDetail($payment_id) ;
                if(is_array($paymentDetail)):
                    $pay_res = serialize($paymentDetail);
                    $this->pos_model->updateInstamojoTransaction($payment_request_id,array('success_response'=>$pay_res));
                    if(isset($paymentDetail["status"]) && in_array($paymentDetail["status"],array('Credit','credit','Completed'))):
                      $res = $this->pos_model->instomojoAfterSale($paymentDetail,$sid);
                      if($res):
                          $this->session->set_flashdata('message', lang('payment_done'));
                          redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                      else:    
                          
                      endif;  
                      
                    endif;
                endif;
            }
        endif;
    }  
    
    public function ccavenue_init(){
        $this->load->library('ccavenue'); 
        $sale_id =  $this->input->get('sid');
        if((int)$sale_id > 0):
        $_req =   $this->pos_model->getCcavenueTransaction(array('order_id'=>$sale_id));  
        if($_req->id):
            $this->session->set_flashdata('error', "CCavenue".lang('payment_process_already_initiated'));
            redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale_id);
        endif;
        $sale = $this->site->getSaleByID($sale_id) ;
        if($sale->id == $sale_id):
        $customer = $this->site->getCompanyByID($sale->customer_id); 
        $ci = get_instance();
        $ci->config->load('payment_gateways', TRUE);
        $payment_config = $ci->config->item('payment_gateways');
        $ccavenue_credential = $payment_config['ccavenue'];
        $merchant_id    = isset($ccavenue_credential['MERCHANT_ID']) && !empty($ccavenue_credential['MERCHANT_ID'])? $ccavenue_credential['MERCHANT_ID']:'';
        $access_code    = isset($ccavenue_credential['ACCESS_CODE']) && !empty($ccavenue_credential['ACCESS_CODE'])? $ccavenue_credential['ACCESS_CODE']:'';
        $working_key    = isset($ccavenue_credential['API_KEY']) && !empty($ccavenue_credential['API_KEY'])? $ccavenue_credential['API_KEY']:'';
        $API_URL        = isset($ccavenue_credential['API_URL']) && !empty($ccavenue_credential['API_URL'])? $ccavenue_credential['API_URL']:'';
        $arr['tid'] = time();
        $arr['merchant_id']     = $merchant_id;
        $arr['order_id']        = $sale_id;
        $arr['amount']          =  $sale->grand_total ;
        $arr['currency']        =   $this->Settings->default_currency;
        $arr['redirect_url']    = base_url('pos/ccavenue_notify');
        $arr['cancel_url']      = base_url('pos/ccavenue_cancel');
        $arr['billing_name']    = $customer->name;
        $arr['billing_tel']     = $customer->phone;
        $arr['billing_email']   = $customer->email;
        $arr['billing_city']    = $customer->city;
        $arr['billing_state']   = $customer->state;
        $arr['billing_zip']     = $customer->postal_code;
        $arr['merchant_param1'] = $sale->reference_no;
        try{
            $api            = new Ccavenue($merchant_id, $access_code, $working_key);
            $encrypted_data = $api->getPostData($arr); 
            $this->data['merchant_id'] = $merchant_id;
            $this->data['ccavenue_access_code'] = $access_code;      
            $this->data['ccavenue_working_key'] = $working_key;      
            $this->data['url'] = $API_URL;      
            $this->data['encrypted_data'] = $encrypted_data;      
            
            $this->pos_model->addCcavenueTransaction(array('sale_id'=>$sale_id,'req_data'=>$arr));
            $this->page_construct('pos/ccavenue', NULL, $this->data);
        }
        catch (Exception $e) {
            echo   $e->getMessage();
        }   
         endif;
        endif;
    }
    
    public function ccavenue_notify(){
        
        $this->load->library('ccavenue'); 
        $ci = get_instance();
        $ci->config->load('payment_gateways', TRUE);
        $payment_config = $ci->config->item('payment_gateways');
        $ccavenue_credential = $payment_config['ccavenue'];
        $merchant_id    = isset($ccavenue_credential['MERCHANT_ID']) && !empty($ccavenue_credential['MERCHANT_ID'])? $ccavenue_credential['MERCHANT_ID']:'';
        $access_code    = isset($ccavenue_credential['ACCESS_CODE']) && !empty($ccavenue_credential['ACCESS_CODE'])? $ccavenue_credential['ACCESS_CODE']:'';
        $working_key    = isset($ccavenue_credential['API_KEY']) && !empty($ccavenue_credential['API_KEY'])? $ccavenue_credential['API_KEY']:'';
        $API_URL        = isset($ccavenue_credential['API_URL']) && !empty($ccavenue_credential['API_URL'])? $ccavenue_credential['API_URL']:'';
       try{
            $api            = new Ccavenue($merchant_id, $access_code, $working_key);
            $_data1 = isset($_POST["encResp"])?$_POST["encResp"]:''; 
            $decrypted_data = $api->getResultData($_data1);  
            if(is_array($decrypted_data) ):
                $id = isset($decrypted_data["order_id"])?$decrypted_data["order_id"]:null;
                if((int)$id > 0):
                    $_req =   $this->pos_model->getCcavenueTransaction(array('order_id'=>$id));  
                    if(isset($_req->order_id)){
                       $this->pos_model->updateCcavenueTransaction($id, array('response_data'=>serialize($decrypted_data),'update_time'=>date('Y-m-d H:i:s')));
                    }
                endif;
                $o_status = isset($decrypted_data["order_status"])?$decrypted_data["order_status"]:null;
                switch ($o_status) {
                    case 'Success':
                        $msg = 'success';
                        $sid = $id;
                        $tracking_id = isset($decrypted_data["tracking_id"])?$decrypted_data["tracking_id"]:null;
                        $res = $this->pos_model->CcavenueAfterSale($decrypted_data,$sid);
                            if($res):
                              $this->session->set_flashdata('message', lang('payment_done'));
                              redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                            endif; 
                        break;
                    case 'Failure':
                        $msg = 'The transaction has been declined.';
                        $this->session->set_flashdata('message', $msg);
                        redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                        break;
                    default:
                        break;
                }               
            endif;
            redirect($this->pos_settings->after_sale_page ? "pos" : "pos/");         
        }
        catch (Exception $e) {
            $this->session->set_flashdata('message', $e->getMessage());
            redirect($this->pos_settings->after_sale_page ? "pos" : "pos/");
        }   
                    
         
        
    }
    
    public function ccavenue_cancel(){
        $this->session->set_flashdata('error', lang('payment_not_done'));
        redirect($this->pos_settings->after_sale_page ? "pos" : "pos" );
    }
    
    public function post_to_url($url, $data) {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }
        rtrim($fields, '&');
        $post = curl_init();
        curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($post, CURLOPT_POST, count($data));
        curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($post, CURLOPT_TIMEOUT, 60); 
        $result = curl_exec($post);
        curl_close($post);
        return $result;
    }
    
    public function paytm_init() {
        $this->load->library('paytm');
        $sale_id = $this->input->get('sid');
        if ((int) $sale_id > 0):
            $_req = $this->pos_model->getPaytmTransaction(array('order_id' => $sale_id));
            if ($_req->id):
                $this->session->set_flashdata('error', "Paytm".lang('payment_process_already_initiated'));
                redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale_id);
            endif;
            $sale = $this->site->getSaleByID($sale_id);
            if ($sale->id == $sale_id):
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $ci = get_instance();
                $ci->config->load('payment_gateways', TRUE);
                $payment_config = $ci->config->item('payment_gateways');
                $paytm_credential = $payment_config['paytm'];

                $PAYTM_MERCHANT_KEY = isset($paytm_credential['PAYTM_MERCHANT_KEY']) && !empty($paytm_credential['PAYTM_MERCHANT_KEY']) ? $paytm_credential['PAYTM_MERCHANT_KEY'] : '';

                $PAYTM_MERCHANT_MID = isset($paytm_credential['PAYTM_MERCHANT_MID']) && !empty($paytm_credential['PAYTM_MERCHANT_MID']) ? $paytm_credential['PAYTM_MERCHANT_MID'] : '';

                $API_URL = isset($paytm_credential['PAYTM_TXN_URL']) && !empty($paytm_credential['PAYTM_TXN_URL']) ? $paytm_credential['PAYTM_TXN_URL'] : '';


                $PAYTM_MERCHANT_WEBSITE = isset($paytm_credential['PAYTM_MERCHANT_WEBSITE']) && !empty($paytm_credential['PAYTM_MERCHANT_WEBSITE']) ? $paytm_credential['PAYTM_MERCHANT_WEBSITE'] : '';

                $arr['tid'] = time();

                $paramList["MID"] = $PAYTM_MERCHANT_MID;
                $paramList["ORDER_ID"] = $sale->id;
                $paramList["CUST_ID"] = $customer->id;
                $paramList["INDUSTRY_TYPE_ID"] = 'Retail';
                $paramList["CHANNEL_ID"] = 'WEB';
                $paramList["TXN_AMOUNT"] = $this->sma->formatDecimal($sale->grand_total);
                $paramList["WEBSITE"] = $PAYTM_MERCHANT_WEBSITE;
		$paramList["MSISDN"] = $customer->phone; //Mobile number of customer
		$paramList["EMAIL"] = $customer->email; //Email ID of customer
		$paramList["VERIFIED_BY"] = "EMAIL"; //
		$paramList["IS_USER_VERIFIED"] = "YES"; //
		$paramList['CALLBACK_URL']    = base_url('pos/paytm_notify');

                try {
                    $api = new Paytm($paytm_credential);
                    $checkSum = $api->getChecksumFromArray($paramList, $PAYTM_MERCHANT_KEY);

                    $this->data['merchant_id'] = $merchant_id;
                    $this->data['paytm_access_code'] = $access_code;
                    $this->data['paramList'] = $paramList;
                    $this->data['PAYTM_TXN_URL'] = $API_URL;
                    $this->data['CHECKSUMHASH'] = $checkSum;

                    $this->pos_model->addpaytmTransaction(array('sale_id' => $sale_id, 'req_data' => $paramList));
                    $this->page_construct('pos/paytm', NULL, $this->data);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            endif;
        endif;
    }

     public function paytm_notify() {

        $this->load->library('paytm');
        $ci = get_instance();
        $ci->config->load('payment_gateways', TRUE);
        $payment_config = $ci->config->item('payment_gateways');
        $paytm_credential = $payment_config['paytm'];
        $PAYTM_MERCHANT_KEY = isset($paytm_credential['PAYTM_MERCHANT_KEY']) && !empty($paytm_credential['PAYTM_MERCHANT_KEY']) ? $paytm_credential['PAYTM_MERCHANT_KEY'] : '';

        $PAYTM_MERCHANT_MID = isset($paytm_credential['PAYTM_MERCHANT_MID']) && !empty($paytm_credential['PAYTM_MERCHANT_MID']) ? $paytm_credential['PAYTM_MERCHANT_MID'] : '';
        $API_URL = isset($paytm_credential['API_URL']) && !empty($paytm_credential['API_URL']) ? $paytm_credential['API_URL'] : '';
        
         $MID = $this->input->post('MID') ? $this->input->post('MID') : NULL;
         $ORDERID = $this->input->post('ORDERID') ? $this->input->post('ORDERID') : NULL;
         if($ORDERID):
                $this->pos_model->updatePaytmTransaction($ORDERID, array('response_data'=>serialize($_POST),'update_time'=>date('Y-m-d H:i:s')));
          endif;
         
         $STATUS = $this->input->post('STATUS') ? $this->input->post('STATUS') : NULL;
         $RESPMSG = $this->input->post('RESPMSG') ? $this->input->post('RESPMSG') : NULL;
         if($STATUS!='TXN_SUCCESS'){
            $this->session->set_flashdata('error', $RESPMSG);
            if((int)$ORDERID > 0):
                 redirect("pos/view/".$ORDERID);
            else:    
                redirect("pos/");
            endif;          
         }
        try {
              $api = new Paytm($paytm_credential);
              $requestParamList = array("MID" => $PAYTM_MERCHANT_MID, "ORDERID" => $ORDERID);
                    
            $responseParamList = $api->getTxnStatus($requestParamList);
            
            $_ORDERID = $responseParamList['ORDERID'] ? $responseParamList['ORDERID'] : NULL; 
            $_STATUS = $responseParamList['STATUS'] ? $responseParamList['STATUS'] : NULL; 
            $_RESPMSG = $responseParamList['RESPMSG'] ? $responseParamList['RESPMSG'] : NULL; 
            $_TXNID = $responseParamList['TXNID'] ? $responseParamList['TXNID'] : NULL; 
            if($_ORDERID==$ORDERID && $_STATUS=='TXN_SUCCESS'):
                
                $msg = 'success';
                        $sid = $ORDERID;
                        $tracking_id = $_TXNID;
                        $res = $this->pos_model->PaytmAfterSale($responseParamList,$sid);
                        if($res):
                          $this->session->set_flashdata('message', lang('payment_done'));
                          redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                        endif; 
                
                $this->session->set_flashdata('message', $_RESPMSG);
                redirect("pos/view/".$ORDERID);
            else:
                $this->session->set_flashdata('error', $_RESPMSG);
                redirect("pos/view/".$ORDERID);
            endif;
                    
        } catch (Exception $e) {
            $this->session->set_flashdata('message', $e->getMessage());
            redirect($this->pos_settings->after_sale_page ? "pos" : "pos/");
        }
    } 
     
     public function paynear_init() {
        $this->load->library('paynearepay');
       
        $sale_id = $this->input->get('sid');
        $mobile_app = $this->input->get('mobile_app');
        
        $_mobile_app = md5('MPA'.$sale_id);
        $valid_app_call ='';
        if(!empty($mobile_app) && $_mobile_app== $mobile_app){
        	$valid_app_call='1';
        }
        
        if ((int) $sale_id > 0):
            $_req = $this->pos_model->getPaynearTransaction(array('order_id' => $sale_id));
            if ($_req->id):
                $this->session->set_flashdata('error', "Paynear".lang('payment_process_already_initiated'));
                redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale_id);
            endif;
            $sale = $this->site->getSaleByID($sale_id);
            if ($sale->id == $sale_id):
                $customer   = $this->site->getCompanyByID($sale->customer_id);
                $ci                     = get_instance();
                $ci->config->load('payment_gateways', TRUE);
                $payment_config         = $ci->config->item('payment_gateways');
                $paynear_credential     = $payment_config['paynear'];

                $PAYNEAR_SECRET_KEY     = isset($paynear_credential['PAYNEAR_SECRET_KEY']) && !empty($paynear_credential['PAYNEAR_SECRET_KEY']) ? $paynear_credential['PAYNEAR_SECRET_KEY'] : '';
                $PAYNEAR_MERCHANT_ID    = isset($paynear_credential['PAYNEAR_MERCHANT_ID']) && !empty($paynear_credential['PAYNEAR_MERCHANT_ID']) ? $paynear_credential['PAYNEAR_MERCHANT_ID'] : '';
                $testMode               = isset($paynear_credential['PAYNEAR_SANDBOX']) && !empty($paynear_credential['PAYNEAR_SANDBOX']) ? true : false;
               
                 $PAYNEAR_APP_MERCHANT_ID    = isset($paynear_credential['PAYNEAR_APP_MERCHANT_ID']) && !empty($paynear_credential['PAYNEAR_APP_MERCHANT_ID']) ? $paynear_credential['PAYNEAR_APP_MERCHANT_ID'] : '';
                $arr['tid']                         = time();
                
                $paramList["referenceNo"]           = $sale->id;
                $paramList["outletId"]              = '0';
                $paramList["apiVersion"]            = '2.0.1';
                $paramList["currencyCode"]          = 'INR';
                $paramList["locale"]                = 'EN-US';
		$paramList["description"]           = $sale->reference_no;  
                $paramList["amount"]                = $this->sma->formatDecimal($sale->grand_total);
		$paramList["channel"]               = '3';  
		$paramList["responseURL"]           = base_url('pos/paynear_notify'); //Email ID of customer
                
		$paramList["billingContactName"]    = str_replace("-",'',$customer->name); //
		$paramList["billingAddress"]        = $customer->address; //
		$paramList["billingCity"]           = $customer->city; //
		$paramList["billingState"]          = $customer->state; //
		$paramList["billingPostalCode"]     = $customer->postal_code; //
		$paramList["billingCountry"]        = 'IND'; //
		$paramList["billingEmail"]          = $customer->email; //
		$paramList["billingPhone"]          = $customer->phone; //
		$paramList["shippingContactName"]    = '';
		$paramList["shippingAddress"]        = '';
		$paramList["shippingCity"]           = '';
		$paramList["shippingState"]          = '';
		$paramList["shippingPostalCode"]      = ''; //
		$paramList["shippingCountry"]        = '';
		$paramList["shippingEmail"]          = '';
		$paramList["shippingPhone"]          = '';
		
		if($valid_app_call=='1'):
		 $this->load->library('apicrypter');
		 $config = $ci->config;
		 $ApiCrypter = new ApiCrypter();
		   $PAYNEAR_APP_SECRET_KEY    = isset($paynear_credential['PAYNEAR_APP_SECRET_KEY']) && !empty($paynear_credential['PAYNEAR_APP_SECRET_KEY']) ? $paynear_credential['PAYNEAR_APP_SECRET_KEY'] : '';
		   
		   if($PAYNEAR_APP_SECRET_KEY):
		   	$app_paynear_key = $ApiCrypter->encrypt($PAYNEAR_APP_SECRET_KEY);
		   endif;
		   
      		 $MERCHANT_PHONE = isset($config->config['merchant_phone']) ? $config->config['merchant_phone'] : '';
        	 $APIKEY         = "435DSFSDFDSF743500909809DFSFJKJ234324534";
        	 
		$paramList["api_url"]            = base_url('paynear/v2');   
		$paramList["secret_token"]        = md5($MERCHANT_PHONE.$APIKEY. $sale->id.time());  
		$paramList["PAYNEAR_MERCHANT_ID"] = $PAYNEAR_MERCHANT_ID;  
		$mobile_app_paynear_type = $this->input->get('paynear_type');
		   ?>
		   <script>
			 window.MyHandler.setTransactindata('<?php echo $PAYNEAR_APP_MERCHANT_ID ; ?>' ,'<?php echo $paramList["referenceNo"]; ?>' ,'<?php echo $paramList["amount"] ; ?>','<?php echo $paramList["api_url"] ; ?>','<?php echo $paramList["secret_token"] ; ?>','<?php echo  $this->Settings->default_currency;?>','<?php echo $mobile_app_paynear_type ;?>','<?php echo $app_paynear_key?>');
			</script>
		  <?php		
		     $this->pos_model->addpaynearTransaction(array('sale_id' => $sale_id, 'req_data' => $paramList,'secret_token'=>$paramList["secret_token"]));	
		     
 			exit;
		endif; 
		
                $api = new PaynearEpay($PAYNEAR_MERCHANT_ID, $PAYNEAR_SECRET_KEY, $testMode);
                try {
                $paramList1 = $paramList;
               $paramList1['currency']=  $this->Settings->default_currency;
                    $this->pos_model->addpaynearTransaction(array('sale_id' => $sale_id, 'req_data' => $paramList1));
                    $api->initiatePayment($paramList);
                } catch (Exception $e) {
                    
                    $this->session->set_flashdata('error', $e->getMessage());
                    redirect("pos");
                }
            endif;
        endif;
    }

     public function paynear_notify() {

        $this->load->library('paynearepay');
        $ci                     = get_instance();
        $ci->config->load('payment_gateways', TRUE);
        $payment_config         = $ci->config->item('payment_gateways');
        $paynear_credential     = $payment_config['paynear'];

        $PAYNEAR_SECRET_KEY     = isset($paynear_credential['PAYNEAR_SECRET_KEY']) && !empty($paynear_credential['PAYNEAR_SECRET_KEY']) ? $paynear_credential['PAYNEAR_SECRET_KEY'] : '';
        $PAYNEAR_MERCHANT_ID    = isset($paynear_credential['PAYNEAR_MERCHANT_ID']) && !empty($paynear_credential['PAYNEAR_MERCHANT_ID']) ? $paynear_credential['PAYNEAR_MERCHANT_ID'] : '';
        $testMode               = isset($paynear_credential['PAYNEAR_SANDBOX']) &&  $paynear_credential['PAYNEAR_SANDBOX']==1   ? true : false;
        $api = new PaynearEpay($PAYNEAR_MERCHANT_ID, $PAYNEAR_SECRET_KEY, $testMode);
        try{
            $result = $api->getPaymentResponse($_POST);
            
            $ORDERID = $this->input->post('orderRefNo') ? $this->input->post('orderRefNo') : NULL;
               $_req=  $this->pos_model->getPaynearTransaction(array('order_id'=>$ORDERID ));
	        if(!isset($_req->order_id)){
		        $this->session->set_flashdata('error', "Paynear details not  found agaienst  Sale id  ".$ORDERID); 
             		redirect("pos");        
		}
	
            if($ORDERID):
                $this->pos_model->updatePaynearTransaction($ORDERID, array('response_data'=>serialize($result),'update_time'=>date('Y-m-d H:i:s')));
            endif;
            
            $params['orderRefNo']    = $result['orderRefNo'];
            $params['paymentId']     = $result['paymentId'];
            $params['transactionId'] = $result['transactionId'];
            $params['amount']        = $result['amount'];
            if($result['responseCode']=='000' && $result['responseMessage']=='Success'):
                $_result = $api->getTransactionStatus($params);
                if($_result['responseCode']=='000' && $_result['responseMessage']=='Success'):
                    $sid = $ORDERID; 
                    $res = $this->pos_model->PaynearAfterSale($_result,$sid);
                    if($res):
                      $this->session->set_flashdata('message', lang('payment_done'));
                      redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                    endif; 
                endif;
            endif;
            $this->session->set_flashdata('error', $result['responseMessage']); 
             redirect("pos");           
         } catch(Exception $e){
            $this->session->set_flashdata('error', $e->getMessage());
            redirect("pos");
         }
          $this->session->set_flashdata('error', 'Something went wrong ,please try again ');
          redirect("pos");           
    }
    
     public function payumoney_init() {
        $this->load->library('payumoney');
        $sale_id = $this->input->get('sid');
        if ((int) $sale_id > 0):
            $_req = $this->pos_model->getPayumoneyTransaction(array('order_id' => $sale_id));
            if ($_req->id):
                $this->session->set_flashdata('error', "Payumoney ".lang('payment_process_already_initiated'));
                redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale_id);
            endif;
            $sale = $this->site->getSaleByID($sale_id);
            if ($sale->id == $sale_id):
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $ci = get_instance();
                $ci->config->load('payment_gateways', TRUE);
                $payment_config = $ci->config->item('payment_gateways');
              
                $payumoney_credential = $payment_config['payumoney'];
 
                $PAYUMONEY_MID = isset($payumoney_credential['PAYUMONEY_MID']) && !empty($payumoney_credential['PAYUMONEY_MID']) ? $payumoney_credential['PAYUMONEY_MID'] : '';
                $PAYUMONEY_KEY = isset($payumoney_credential['PAYUMONEY_KEY']) && !empty($payumoney_credential['PAYUMONEY_KEY']) ? $payumoney_credential['PAYUMONEY_KEY'] : '';
                $PAYUMONEY_SALT = isset($payumoney_credential['PAYUMONEY_SALT']) && !empty($payumoney_credential['PAYUMONEY_SALT']) ? $payumoney_credential['PAYUMONEY_SALT'] : '';
                $PAYUMONEY_AUTH_HEADER = isset($payumoney_credential['PAYUMONEY_AUTH_HEADER']) && !empty($payumoney_credential['PAYUMONEY_AUTH_HEADER']) ? $payumoney_credential['PAYUMONEY_AUTH_HEADER'] : '';
                $posted = array();

                $posted['key'] = $PAYUMONEY_KEY;
                $posted['txnid'] = $sale->id;
                $posted['amount'] = $this->sma->formatDecimal($sale->grand_total);
                $posted['firstname'] = str_replace(array(' ','-' ),'' ,$customer->name);
                $posted['email'] = $customer->email;
                $posted['phone'] = $customer->phone;
                $posted['lastname'] = '';
                $posted['address1'] = $customer->address;
                $posted['address2'] = '';
                $posted['city'] = $customer->city;
                $posted['state'] = $customer->state;
                $posted['country'] = 'IND';
                $posted['zipcode'] = $customer->postal_code;
                $posted['productinfo'] = 'POS ORDER ' . $sale->reference_no;
                $posted['udf1'] = $sale_id;
                $posted['udf2'] = '';
                $posted['udf3'] = '';
                $posted['udf4'] = '';
                $posted['udf5'] = '';
                $posted['pg'] = '';
                $posted['furl'] = base_url('pos/payumoney_cancel');
                $posted['surl'] = base_url('pos/payumoney_notify');
                $posted['service_provider'] = 'payu_paisa';



                try {
                    $api = new Payumoney($PAYUMONEY_MID, $PAYUMONEY_KEY, $PAYUMONEY_SALT, $PAYUMONEY_AUTH_HEADER);
                    $encrypted_data = $api->calculate_hash_before_transaction($posted);
                    $posted['hash'] = $encrypted_data;

                    $this->pos_model->addPayumoneyTransaction(array('sale_id' => $sale_id, 'req_data' => $posted));
                    $this->data['posted'] = $posted;
                    $this->data['apiAction'] = $api->getApiUrl();
                    
                    $this->page_construct('pos/payumoney', NULL, $this->data);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            endif;
        endif;
    }

    
    public function payumoney_notify() {
        $this->load->library('payumoney');
        $ci = get_instance();
        $ci->config->load('payment_gateways', TRUE);
        $payment_config = $ci->config->item('payment_gateways');
        $payumoney_credential = $payment_config['payumoney'];

        $PAYUMONEY_MID = isset($payumoney_credential['PAYUMONEY_MID']) && !empty($payumoney_credential['PAYUMONEY_MID']) ? $payumoney_credential['PAYUMONEY_MID'] : '';
        $PAYUMONEY_KEY = isset($payumoney_credential['PAYUMONEY_KEY']) && !empty($payumoney_credential['PAYUMONEY_KEY']) ? $payumoney_credential['PAYUMONEY_KEY'] : '';
        $PAYUMONEY_SALT = isset($payumoney_credential['PAYUMONEY_SALT']) && !empty($payumoney_credential['PAYUMONEY_SALT']) ? $payumoney_credential['PAYUMONEY_SALT'] : '';
        $PAYUMONEY_AUTH_HEADER = isset($payumoney_credential['PAYUMONEY_AUTH_HEADER']) && !empty($payumoney_credential['PAYUMONEY_AUTH_HEADER']) ? $payumoney_credential['PAYUMONEY_AUTH_HEADER'] : '';
        try {
            $posted = $_POST;
            $api = new Payumoney($PAYUMONEY_MID, $PAYUMONEY_KEY, $PAYUMONEY_SALT, $PAYUMONEY_AUTH_HEADER);
            $res = $api->check_hash_after_transaction($PAYUMONEY_SALT, $posted);
            $sid =$ORDERID = $this->input->post('udf1') ? $this->input->post('udf1') : NULL;
            $_req = $this->pos_model->getPayumoneyTransaction(array('order_id' => $ORDERID));
            if (isset($_req->order_id) && $ORDERID == $_req->order_id) {
                $this->pos_model->updatePayumoneyTransaction($ORDERID, array('response_data' => serialize($posted), 'update_time' => date('Y-m-d H:i:s')));
            }

            if ($res === true) {

                $validateOrder = $api->validateOrder($ORDERID);
                
                if (empty($validateOrder)) {
                    $this->session->set_flashdata('error', "Order is not validated  successfully");
                    redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                }
                $jsonObj = json_decode($validateOrder);
               
                if (isset($jsonObj->result[0]->paymentId) && $posted['payuMoneyId'] == $jsonObj->result[0]->paymentId && $posted['status'] == 'success'):
                    $res1 = $this->pos_model->PayumoneyAfterSale($posted, $ORDERID);
                    /*    */
                    if ($res1):
                        $this->session->set_flashdata('message', lang('payment_done'));
                        redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
                    endif;
		echo  "IN";
                endif;
               
            } 
            $this->session->set_flashdata('error', lang('payment_not_done'));
            redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sid);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $this->session->set_flashdata('error', lang('payment_not_done'));
        redirect($this->pos_settings->after_sale_page ? "pos" : "pos" );
    }
    
    public function payumoney_cancel(){        
        $this->load->library('payumoney');
        $ci                     = get_instance();
        $ci->config->load('payment_gateways', TRUE);
        $payment_config         = $ci->config->item('payment_gateways'); 
        $payumoney_credential = $payment_config['payumoney'];
 
        $PAYUMONEY_MID = isset($payumoney_credential['PAYUMONEY_MID']) && !empty($payumoney_credential['PAYUMONEY_MID']) ? $payumoney_credential['PAYUMONEY_MID'] : '';
        $PAYUMONEY_KEY = isset($payumoney_credential['PAYUMONEY_KEY']) && !empty($payumoney_credential['PAYUMONEY_KEY']) ? $payumoney_credential['PAYUMONEY_KEY'] : '';
        $PAYUMONEY_SALT = isset($payumoney_credential['PAYUMONEY_SALT']) && !empty($payumoney_credential['PAYUMONEY_SALT']) ? $payumoney_credential['PAYUMONEY_SALT'] : '';
        $PAYUMONEY_AUTH_HEADER = isset($payumoney_credential['PAYUMONEY_AUTH_HEADER']) && !empty($payumoney_credential['PAYUMONEY_AUTH_HEADER']) ?   $payumoney_credential['PAYUMONEY_AUTH_HEADER'] : '';
         try {
          $posted = $_POST;
            $api = new Payumoney($PAYUMONEY_MID, $PAYUMONEY_KEY, $PAYUMONEY_SALT, $PAYUMONEY_AUTH_HEADER);
            $res = $api->check_hash_after_transaction( $PAYUMONEY_SALT,$posted);
           
            $ORDERID = $this->input->post('udf1') ? $this->input->post('udf1') : NULL;
            $_req =   $this->pos_model->getPayumoneyTransaction(array('order_id'=>$ORDERID));  
            
	      if(isset($_req->order_id) && $ORDERID== $_req->order_id ){
	               $this->pos_model->updatePayumoneyTransaction($ORDERID, array('response_data'=>serialize($posted),'update_time'=>date('Y-m-d H:i:s'))); 
	         }
            
           
            $this->session->set_flashdata('error', lang('payment_not_done').$_POST['unmappedstatus']);
           redirect($this->pos_settings->after_sale_page ? "pos" : "pos" );
           
            
        } catch (Exception $e) {
          
              $this->session->set_flashdata('error', lang('payment_not_done').$e->getMessage());
        }
        
         $this->session->set_flashdata('error', lang('payment_not_done'));
         redirect($this->pos_settings->after_sale_page ? "pos" : "pos" );
    } 
     public function featuerdProducts()
    {
        $this->sma->checkPermissions('index');
                    
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library("pagination");

        $config = array();
        $config["base_url"]         = base_url() . "pos/featuerdProducts";
        $config["total_rows"]       = $this->pos_model->featuerd_products_count();
        $config["per_page"]         = $this->pos_settings->pro_limit;
        $config['prev_link']        = FALSE;
        $config['next_link']        = FALSE;
        $config['display_pages']    = FALSE;
        $config['first_link']       = FALSE;
        $config['last_link']        = FALSE;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_featuerd_products($config["per_page"], $page);
        $pro = 1;
        $prcount = $config["total_rows"];
         $i=1;
      
        $prods = '<div>';
        if (!empty($products)) { 
           $i= 0;
            foreach ($products as $product) {
            
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }

                $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\"><img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded' /><span>" . character_limiter($product->name, 20) . "</span></button>";

                $pro++;
               if (++$i == 21) break;
             
             
             //$i==21;
           //break 21; 

            }
            
        }
        $prods .= "</div>";
        
        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }
}