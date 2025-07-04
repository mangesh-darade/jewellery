<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sma {

    private $_merchant_phone;   // Merchant Phone
    private $apiKey;            // Common API KEY
    private $pos_api_url;       // Common API ENDPOINT
    private $configData;
    private $currencyLable;

    public function __construct() {
        $ci = get_instance();
        $config = $this->configData = $ci->config;
        $mobile =  $this->merchant_mobile();
        // $this->_merchant_phone = isset($config->config['merchant_phone']) ? $config->config['merchant_phone'] : $mobile;
        $this->_merchant_phone =   $mobile;
        $this->apiKey = isset($config->config['apiKey']) ? $config->config['apiKey'] : '';
        $this->pos_api_endpoint = isset($config->config['pos_api_endpoint']) ? $config->config['pos_api_endpoint'] : 'https://elintpos.in/Posadmin_15.01/api';
        $this->pos_api_url = rtrim($this->pos_api_endpoint, '/');
        $this->load->helper('text');
        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->model('transfers_model');
    }
    
    public function merchant_mobile() {

        $q = $this->db->get_where('users', array('id' =>'1'), 1);
        if ($q->num_rows() > 0) {
            $data =  $q->row();
            return  $data->phone ;
        }
        return FALSE;

    }
    public function setSettings($Settings) {

        $this->currencyLable = ($Settings->default_currency == 'USD') ? 'Dollar' : 'Rupees';
    }

    public function __get($var) {
        return get_instance()->$var;
    }

    private function _rglobRead($source, &$array = array()) {

        if (!$source || trim($source) == "") {
            $source = ".";
        }
        foreach ((array) glob($source . "/*/") as $key => $value) {
            $this->_rglobRead(str_replace("//", "/", $value), $array);
        }
        $hidden_files = glob($source . ".*") and $htaccess = preg_grep('/\.htaccess$/', $hidden_files);
        $files = array_merge(glob($source . "*.*"), $htaccess);
        foreach ($files as $key => $value) {
            $array[] = str_replace("//", "/", $value);
        }
    }

    private function _zip($array, $part, $destination, $output_name = 'sma') {
        $zip = new ZipArchive;
        @mkdir($destination, 0777, true);

        if ($zip->open(str_replace("//", "/", "{$destination}/{$output_name}" . ($part ? '_p' . $part : '') . ".zip"), ZipArchive::CREATE)) {
            foreach ((array) $array as $key => $value) {
                $zip->addFile($value, str_replace(array("../", "./"), null, $value));
            }
            $zip->close();
        }
    }

    public function formatMoney($number) {
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
                    $this->formatSAC($this->formatDecimal($number)) .
                    ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
        $decimals = $this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
                number_format($number, $decimals, $ds, $ts) .
                ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
    }

    public function formatQuantity($number, $decimals = null) {
        if (!$decimals) {
            $decimals = $this->Settings->qty_decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatNumber($number, $decimals = null) {
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatDecimal($number, $decimals = null) {
        if (!is_numeric($number)) {
            return null;
        }
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        return number_format($number, $decimals, '.', '');
    }

    public function clear_tags($str) {
        // return htmlentities(
        //         strip_tags($str, '<span><div><a><br><p><b><i><u><img><blockquote><small><ul><ol><li><hr><big><pre><code><strong><em><table><tr><td><th><tbody><thead><tfoot><h3><h4><h5><h6>'
        //         ), ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8'
        // );
        return strip_tags($str);
    }

    public function decode_html($str) {
        return html_entity_decode($str, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8');
    }

    public function roundMoney($num, $nearest = 0.05) {
        return round($num * (1 / $nearest)) * $nearest;
    }

    public function roundNumber($number, $toref = null) {
        switch ($toref) {
            case 1:
                $rn = round($number * 20) / 20;
                break;
            case 2:
                $rn = round($number * 2) / 2;
                break;
            case 3:
                $rn = round($number);
                break;
            case 4:
                $rn = ceil($number);
                break;
            default:
                $rn = $number;
        }
        return $rn;
    }

    public function unset_data($ud) {
        if ($this->session->userdata($ud)) {
            $this->session->unset_userdata($ud);
            return true;
        }
        return false;
    }

    public function hrsd($sdate) {
        if ($sdate) {
            if ($sdate == '0000-00-00') {
                return false;
            }
            return date($this->dateFormats['php_sdate'], strtotime($sdate));
        } else {
            return '0000-00-00';
        }
    }

    public function hrld($ldate) {
        if ($ldate) {
            return date($this->dateFormats['php_ldate'], strtotime($ldate));
        } else {
            return '0000-00-00 00:00:00';
        }
    }

    public function fsd($inv_date) {
        if ($inv_date) {
            $jsd = $this->dateFormats['js_sdate'];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . "-" . substr($inv_date, 3, 2) . "-" . substr($inv_date, 0, 2);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . "-" . substr($inv_date, 0, 2) . "-" . substr($inv_date, 3, 2);
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000-00-00';
        }
    }

    public function fld($ldate) {
        if ($ldate) {
            $date = explode(' ', $ldate);
            $jsd = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time = $date[1];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . "-" . substr($inv_date, 3, 2) . "-" . substr($inv_date, 0, 2) . " " . $time;
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . "-" . substr($inv_date, 0, 2) . "-" . substr($inv_date, 3, 2) . " " . $time;
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000-00-00 00:00:00';
        }
    }

    public function send_email($to, $subject, $message, $from = null, $from_name = null, $attachment = null, $cc = null, $bcc = null) {

        $this->load->library('email');
        $config['useragent'] = "Stock Manager Advance";
        $config['protocol'] = $this->Settings->protocol;
        $config['mailtype'] = "html";
        $config['crlf'] = "\r\n";
        $config['newline'] = "\r\n";
       if ($this->Settings->protocol == 'sendmail') {

            $config['mailpath'] = $this->Settings->mailpath;
        } elseif ($this->Settings->protocol == 'smtp') {
            $this->load->library('encrypt');
            $config['smtp_host'] = $this->Settings->smtp_host;
            $config['smtp_user'] = $this->Settings->smtp_user;
            $config['smtp_pass'] = $this->encrypt->decode($this->Settings->smtp_pass);
            $config['smtp_port'] = $this->Settings->smtp_port;
            /*if (!empty($this->Settings->smtp_crypto)) {
                $config['smtp_crypto'] = $this->Settings->smtp_crypto;
            }*/

        //  $config['protocol'] = 'sendmail';

       /* $config['protocol'] = 'sendmail';
       $config['smtp_host'] = 'ssl://smtp.googlemail.com';
       $config['smtp_user'] = 'Exp@gmail.com';
       $config['smtp_pass'] = 'gmailpass';
       $config['smtp_port'] = 25;*/
      }



       

      
          $this->email->initialize($config);
       $this->email->set_newline("\r\n");

        if ($from && $from_name) {
            $this->email->from($from, $from_name);
        } elseif ($from) {
            $this->email->from($from, $this->Settings->site_name);
        } else {
            $this->email->from($this->Settings->default_email, $this->Settings->site_name);
        }

        
        $this->email->to($to);
        if ($cc) {
            $this->email->cc($cc);
        }
        if ($bcc) {
            $this->email->bcc($bcc);
        }
        $this->email->subject($subject);
  
        $this->email->message($message);
         if ($attachment) {
            if (is_array($attachment)) {
                foreach ($attachment as $file) {
                    $this->email->attach($file);
                }
            } else {
                $this->email->attach($attachment);
            }
        }

        if ($this->email->send()) {
             //echo $this->email->print_debugger(); die();
            return true;
        } else {
             //echo $this->email->print_debugger(); die();
            return false;
        }
    }

    public function checkPermissions($action = null, $js = null, $module = null) {
        if (!$this->actionPermissions($action, $module)) {
            $this->session->set_flashdata('error', lang("access_denied"));
            if ($js) {
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
            } else {
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }
        }
    }

    public function actionPermissions($action = null, $module = null) {
        if ($this->Owner || $this->Admin) {
            if ($this->Admin && stripos($action, 'delete') !== false) {
                return false;
            }
            return true;
        } elseif ($this->Customer || $this->Supplier) {
            return false;
        } else {
            if (!$module) {
                $module = $this->m;
            }
            if (!$action) {
                $action = $this->v;
            }
            //$gp = $this->site->checkPermissions();
            if ($this->GP[$module . '-' . $action] == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function save_barcode($text = null, $bcs = 'code128', $height = 56, $stext = 1, $sq = null) {
        $drawText = ($stext != 1) ? false : true;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $text, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1);
        if ($this->Settings->barcode_img) {
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::draw($bcs, 'image', $barcodeOptions, $rendererOptions);
            ob_start();
            imagepng($imageResource);
            $imagedata = ob_get_contents();
            ob_end_clean();
            return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='bcimg' />";
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            // $imageResource = Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            // return $imageResource;
            ob_start();
            Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            $imagedata = ob_get_contents();
            ob_end_clean();
            return "<img src='data:image/svg+xml;base64," . base64_encode($imagedata) . "' alt='{$text}' class='bcimg' />";
        }
        return FALSE;
    }

    public function qrcode($type = 'text', $text = 'https://elintpos.in', $size = 2, $level = 'H', $sq = null) {
        $file_name = 'assets/mdata/'.$this->Customer_assets.'/uploads/qrcode' . $this->session->userdata('user_id') . ($sq ? $sq : '') . ($this->Settings->barcode_img ? '.png' : '.svg');
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('phpqrcode');
        $config = array('data' => $text, 'size' => $size, 'level' => $level, 'savename' => $file_name);
        if (!$this->Settings->barcode_img) {
            $config['svg'] = 1;
        }
        $this->phpqrcode->generate($config);
        if ($this->Settings->barcode_img) {
            $imagedata = file_get_contents($file_name);
            return "<img src='data:image/png;base64," . base64_encode($imagedata) . "' alt='{$text}' class='qrimg' style='float:right;' />";
        }
        $imagedata = file_get_contents($file_name);
        return "<img src='data:image/svg+xml;base64," . base64_encode($imagedata) . "' alt='{$text}' class='qrimg' style='float:right;' />";
    }

    public function generate_pdf($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P') {
        if (!$output_type) {
            $output_type = 'D';
        }
        if (!$margin_bottom) {
            $margin_bottom = 10;
        }
        if (!$margin_top) {
            $margin_top = 20;
        }
        $this->load->library('pdf');
        $pdf = new mPDF('utf-8', 'A4-' . $orientation, '13', '', 10, 10, $margin_top, $margin_bottom, 9, 9);
        $pdf->debug = false;
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        // if you need to add protection to pdf files, please uncomment the line below or modify as you need.
        // $pdf->SetProtection(array('print')); // You pass 2nd arg for user password (open) and 3rd for owner password (edit)
        // $pdf->SetProtection(array('print', 'copy')); // Comment above line and uncomment this to allow copying of content
        $pdf->SetTitle($this->Settings->site_name);
        $pdf->SetAuthor($this->Settings->site_name);
        $pdf->SetCreator($this->Settings->site_name);
        $pdf->SetDisplayMode('fullpage');
        $stylesheet = file_get_contents('assets/bs/bootstrap.min.css');
        $pdf->WriteHTML($stylesheet, 1);
        // $pdf->SetFooter($this->Settings->site_name.'||{PAGENO}/{nbpg}', '', TRUE); // For simple text footer

        if (is_array($content)) {
            $pdf->SetHeader($this->Settings->site_name . '||{PAGENO}/{nbpg}', '', TRUE); // For simple text header
            $as = sizeof($content);
            $r = 1;
            foreach ($content as $page) {
                $pdf->WriteHTML($page['content']);
                if (!empty($page['footer'])) {
                    $pdf->SetHTMLFooter('<p class="text-center">' . $page['footer'] . '</p>', '', true);
                }
                if ($as != $r) {
                    $pdf->AddPage();
                }
                $r++;
            }
        } else {

            $pdf->WriteHTML($content);
            if ($header != '') {
                $pdf->SetHTMLHeader('<p class="text-center">' . $header . '</p>', '', true);
            }
            if ($footer != '') {
                $pdf->SetHTMLFooter('<p class="text-center">' . $footer . '</p>', '', true);
            }
        }

        if ($output_type == 'S') {
            $file_content = $pdf->Output('', 'S');
            write_file('assets/mdata/'.$this->Customer_assets.'/uploads/' . $name, $file_content);
            return 'assets/mdata/'.$this->Customer_assets.'/uploads/' . $name;
        } else {
            $pdf->Output($name, $output_type);
        }
    }

    public function print_arrays() {
        $args = func_get_args();
        echo "<pre>";
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo "</pre>";
        die();
    }

    public function logged_in() {
        return (bool) $this->session->userdata('identity');
    }

    public function in_group($check_group, $id = false) {
        if (!$this->logged_in()) {
            return false;
        }
        $id || $id = $this->session->userdata('user_id');
        $group = $this->site->getUserGroup($id);
        if ($group->name === $check_group) {
            return true;
        }
        return false;
    }

    public function log_payment($msg, $val = null) {
        $this->load->library('logs');
        return (bool) $this->logs->write('payments', $msg, $val);
    }

    public function update_award_points($total, $customer, $user, $scope = null) {
        if (!empty($this->Settings->each_spent) && $total >= $this->Settings->each_spent) {
            $company = $this->site->getCompanyByID($customer);
            $points = floor(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
            $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
            $this->db->update('companies', array('award_points' => $total_points), array('id' => $customer));
        }
        if (!empty($this->Settings->each_sale) && !$this->Customer && $total >= $this->Settings->each_sale) {
            $staff = $this->site->getUser($user);
            $points = floor(($total / $this->Settings->each_sale) * $this->Settings->sa_point);
            $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
            $this->db->update('users', array('award_points' => $total_points), array('id' => $user));
        }
        return true;
    }

    public function zip($source = null, $destination = "./", $output_name = 'sma', $limit = 5000) {
        if (!$destination || trim($destination) == "") {
            $destination = "./";
        }

        $this->_rglobRead($source, $input);
        $maxinput = count($input);
        $splitinto = (($maxinput / $limit) > round($maxinput / $limit, 0)) ? round($maxinput / $limit, 0) + 1 : round($maxinput / $limit, 0);

        for ($i = 0; $i < $splitinto; $i++) {
            $this->_zip(array_slice($input, ($i * $limit), $limit, true), $i, $destination, $output_name);
        }

        unset($input);
        return;
    }

    public function unzip($source, $destination = './') {
        // @chmod($destination, 0777);
        $zip = new ZipArchive;
        if ($zip->open(str_replace("//", "/", $source)) === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
        // @chmod($destination,0755);

        return true;
    }

    public function view_rights($check_id, $js = null) {
        if (!$this->Owner && !$this->Admin) {
            if ($check_id != $this->session->userdata('user_id')) {
                $this->session->set_flashdata('warning', $this->data['access_denied']);
                if ($js) {
                    die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome') . "'; }, 10);</script>");
                } else {
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
                }
            }
        }
        return true;
    }

    public function makecomma($input) {
        if (strlen($input) <= 2) {
            return $input;
        }
        $length = substr($input, 0, strlen($input) - 2);
        $formatted_input = $this->makecomma($length) . "," . substr($input, -2);
        return $formatted_input;
    }

    public function formatSAC($num) {
        $pos = strpos((string) $num, ".");
        if ($pos === false) {
            $decimalpart = "00";
        } else {
            $decimalpart = substr($num, $pos + 1, 2);
            $num = substr($num, 0, $pos);
        }

        if (strlen($num) > 3 & strlen($num) <= 12) {
            $last3digits = substr($num, -3);
            $numexceptlastdigits = substr($num, 0, -3);
            $formatted = $this->makecomma($numexceptlastdigits);
            $stringtoreturn = $formatted . "," . $last3digits . "." . $decimalpart;
        } elseif (strlen($num) <= 3) {
            $stringtoreturn = $num . "." . $decimalpart;
        } elseif (strlen($num) > 12) {
            $stringtoreturn = number_format($num, 2);
        }

        if (substr($stringtoreturn, 0, 2) == "-,") {
            $stringtoreturn = "-" . substr($stringtoreturn, 2);
        }

        return $stringtoreturn;
    }

    public function md($page = FALSE) {
        if ($page == 'login') {
            $page = base_url('login');
        }
        die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($page ? $page : (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome')) . "'; }, 10);</script>");
    }

    public function analyze_term($term) {
        $spos = strpos($term, $this->Settings->barcode_separator);
        if ($spos !== false) {
            $st = explode($this->Settings->barcode_separator, $term);
            $sr = trim($st[0]);
            $option_id = isset($st[1]) ? trim($st[1]) : false;
        } else {
            $sr = $term;
            $option_id = false;
        }
        return array('term' => $sr, 'option_id' => $option_id);
    }

    public function paid_opts($paid_by = null, $purchase = false) {
        $opts = '
        <option value="cash"' . ($paid_by && $paid_by == 'cash' ? ' selected="selected"' : '') . '>' . lang("cash") . '</option>
        <option value="gift_card"' . ($paid_by && $paid_by == 'gift_card' ? ' selected="selected"' : '') . '>' . lang("gift_card") . '</option>
        <option value="credit_note"' . ($paid_by && $paid_by == 'credit_note' ? ' selected="selected"' : '') . '>' . lang("Credit_Note") . '</option>
        <option value="CC"' . ($paid_by && $paid_by == 'CC' ? ' selected="selected"' : '') . '>' . lang("CC") . '</option>
        <option value="DC"' . ($paid_by && $paid_by == 'DC' ? ' selected="selected"' : '') . '>' . lang("DC") . '</option>
        <option value="Cheque"' . ($paid_by && $paid_by == 'Cheque' ? ' selected="selected"' : '') . '>' . lang("cheque") . '</option>
        <option value="other"' . ($paid_by && $paid_by == 'other' ? ' selected="selected"' : '') . '>' . lang("other") . '</option> 
        <option value="NEFT"' . ($paid_by && $paid_by == 'NEFT' ? ' selected="selected"' : '') . '>' . lang("NEFT") . '</option>
        <option value="PAYTM"' . ($paid_by && $paid_by == 'PAYTM' ? ' selected="selected"' : '') . '>' . lang("PAYTM") . '</option>
        <option value="Googlepay"' . ($paid_by && $paid_by == 'Googlepay' ? ' selected="selected"' : '') . '>' . lang("Google pay") . '</option>
        <option value="complimentry"' . ($paid_by && $paid_by == 'complimentry' ? ' selected="selected"' : '') . '>' . lang("complimentry") . '</option>
        <option value="swiggy"' . ($paid_by && $paid_by == 'swiggy' ? ' selected="selected"' : '') . '>' . lang("swiggy") . '</option>
        <option value="zomato"' . ($paid_by && $paid_by == 'zomato' ? ' selected="selected"' : '') . '>' . lang("zomato") . '</option>
        <option value="ubereats"' . ($paid_by && $paid_by == 'ubereats' ? ' selected="selected"' : '') . '>' . lang("ubereats") . '</option>
        <option value="magicpin"' . ($paid_by && $paid_by == 'magicpin' ? ' selected="selected"' : '') . '>' . lang("magicpin") . '</option>
        <option value="UPI_QRCODE"' . ($paid_by && $paid_by == 'UPI_QRCODE' ? ' selected="selected"' : '') . '>' . lang("UPI & QR Code") . '</option>
        
        ';



        if (!$purchase) {
            $opts .= '<option value="deposit"' . ($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '') . '>' . lang("deposit") . '</option>';
        }
        return $opts;
    }

    public function send_json($data) {
        header('Content-Type: application/json');
        die(json_encode($data));
        exit;
    }

    public function pos_error_log(array $logger) {
        return TRUE;
        
//        $date = new DateTime();
//        $error_time = $date->format('U = Y-m-d H:i:s');
//        $data = array(
//            "action" => "setLog",
//            "error_url" => $logger[1],
//            "pos_url" => base_url(),
//            "error_message" => $logger[0]
//        );
//        $surl = 'https://simplypos.in/posadmin/pos_error_log.php';
        // $this->post_to_url($surl, $data);
    }

    public function post_to_url($url, $data) {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }

        $fields = rtrim($fields, '&');
        $post = curl_init();
        curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($post, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($post, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($post, CURLOPT_ENCODING, "");
        curl_setopt($post, CURLOPT_POST, count($data));
        curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($post);
        curl_close($post);
        return $result;
    }

    public function UPBillTable($printer, $inv, $return_sale, $rows, $return_rows, $class = null, $print = NULL) {

        $itemTaxes = isset($inv->rows_tax) ? $inv->rows_tax : array();
        $column_id_str = isset($printer->column_id_str) && !empty($printer->column_id_str) ? $printer->column_id_str : '';
        $column_name_str = isset($printer->column_name_str) && !empty($printer->column_name_str) ? $printer->column_name_str : '';
        $data = isset($printer->data) && !empty($printer->data) ? $printer->data : '';
        $optionDetails = isset($printer->optionDetails) && !empty($printer->optionDetails) ? $printer->optionDetails : '';


        if (empty($column_id_str) || empty($column_name_str) || empty($data)):
            return false;
        endif;

        $crop_product_name = $printer->crop_product_name;
        $show_sr_no = $printer->show_sr_no;
        $column_id_arr = explode(',', $column_id_str);
        $column_name_arr = explode(',', $column_name_str);
        $data_arr = explode(',', $data);

        $table_header = '';
        if (count($column_id_arr) != count($column_name_arr) || count($column_id_arr) != count($data_arr)):
            return false;
        endif;
        $column_cnt = count($column_id_arr);
        $column_cnt = ($show_sr_no == 1) ? $column_cnt + 1 : $column_cnt;
        $total_column_offset = $column_cnt - 1;

        /* ------------------------------------------------HEADER--------------------------------------  */
        $sr_th = ($show_sr_no == 1) ? '<th class="">Sr.No</th>' : '';
        foreach ($column_name_arr as $column_key => $column_name):
            if (!empty($column_name)):
                $table_header = $table_header . '<th class="' . $data_arr[$column_key] . '">' . $column_name . '</th>';
            endif;
        endforeach;
        $tableHeader = '<thead><tr>' . $sr_th . $table_header . '</tr></thead>';

        $mrp_total = $qty_total = $discount_total = $tax_total = $net_total = $unit_total = 0;
        $item_arr = $item_return_arr = '';
        /* ------------------------------------------------HEADER End--------------------------------------  */

        /* ------------------------------------------------Table Body --------------------------------------  */
        $table_body = '';
        $sr = $r = 0;
        $taxAttr = $taxAttrName = array();

        foreach ($rows as $row) {

            $sr++;
            $table_body = $table_body . '<tr>';
            $sr_td = ($show_sr_no == 1) ? '<td class="">' . $sr . '</td>' : '';
            $table_body = $table_body . $sr_td;
            $i = 0;
            foreach ($data_arr as $data):

                $id = $column_id_arr[$i];
                $obj = $optionDetails[$id];

                if ($i == 0):
                    $append_product_name = '';

                    if ($printer->append_product_code_in_name && !empty($row->product_code)):
                        $append_product_name .= '<br/>Code: ' . $row->product_code;
                    endif;

                    $prod_name = ($crop_product_name == 1) ? character_limiter($row->$data, 20) : $row->$data;
                    $res = strtolower($prod_name . $append_product_name);

                    //Show/Hide Combo Product Items
                    if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                        if ($printer->show_combo_products_list) {
                            $res .= " + (";
                            foreach ($row->combo_items as $comk => $comv) {
                                $res .= $comv->name . " Qty." . (int) $comv->qty . "  , ";
                            }
                            $res = trim($res, ", ");
                            $res .= ")";
                        }//end if.
                    }

                    //Show/Hide Invoice Product Image
                    if ($printer->show_product_image == 1) {
                        $itemImage = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/' . $row->image;
                        $image_size = ($printer->product_image_size != '') ? $printer->product_image_size : 'width:40px;height:40px;';
                        if (file_exists($itemImage)) {
                            $imgTag = '<img src="' . base_url($itemImage) . '" style="' . $image_size . 'margin-right:5px;" alt="' . $row->image . '" align="left" /> ';
                        } else {
                            $imgTag = '<img src="' . base_url('assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/no_image.png') . '" style="' . $image_size . 'margin-right:5px;" align="left" alt="no_image" /> ';
                        }
                    } else {
                        $imgTag = '';
                    }
                    $table_body = $table_body . '<td style="text-transform: capitalize;">' . $imgTag . $res . '</td>';
                elseif ($data == 'unit_price'):
                    $table_body = $table_body . '<td >' . $this->custom_format($row->unit_price, $obj->format) . '</td>';
                elseif ($data == 'real_unit_price'):
                    $table_body = $table_body . '<td >' . $this->custom_format($row->real_unit_price, $obj->format) . '</td>';


                elseif (!empty($obj->formula) && strpos($data, '|')):
                    $_data_arr = explode('|', $data);
                    $f_arr = explode('|', $obj->formula);
                    $f1_arr = explode('|', $obj->format);
                    $k = 0;
                    $res = '';
                    foreach ($_data_arr as $_key => $_data) {

                        $unit_total = ($_data == 'unit_price' && !empty($row->$_data)) ? $unit_total + $row->$_data : $unit_total;
                        $res = $res . $this->custom_format($row->$_data, $f1_arr[$_key]) . ' ' . $f_arr[$k];
                    }
                    $res = substr($res, 0, -1);
                    $table_body = $table_body . '<td>' . $res . '</td>';
                else :

                    $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'net_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';
                    $mrp_total = ($data == 'mrp' && !empty($row->$data)) ? $mrp_total + $row->$data : $mrp_total;
                    $net_total = ($data == 'real_unit_price' && !empty($row->$data)) ? $net_total + $row->$data : $net_total;
                    $unit_total = ($data == 'unit_price' && !empty($row->$data)) ? $unit_total + $row->$data : $unit_total;
                    $tax_total = ($data == 'item_tax' && !empty($row->$data)) ? $tax_total + $row->$data : $tax_total;
                    $discount_total = ($data == 'item_discount' && !empty($row->$data)) ? $discount_total + $row->$data : $discount_total;
                    $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->$data : $qty_total;

                    if (!empty($obj->format)):
                        if ($data == 'real_unit_price') {
                            $res = $this->custom_format(($row->real_unit_price * $qty_total), $obj->format);
                        } else {
                            $res = $this->custom_format($row->$data, $obj->format);
                        }
                    //$res = $this->custom_format($row->$data,$obj->format);
                    else:
                        $res = $row->$data;
                    endif;

                    switch ($data) {
                        case 'subtotal':
                        case 'unit_price':
                        case 'net_unit_price':
                            if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                                $total_combo_item_price = 0;
                                foreach ($row->combo_items as $comk => $comv) {
                                    $total_combo_item_price += ($comv->qty * $comv->unit_price);
                                }
                                //  $res = "<del>" .$this->custom_format($total_combo_item_price,$obj->format)."</del><br>".$res ;
                            }

                            break;
                    }

                    $table_body = $table_body . '<td class="' . $class . '">' . $res . '</td>';
                endif;

                $i++;
            endforeach;

            $table_body = $table_body . '</tr>';
            $taxConfig = isset($itemTaxes[$row->id]) ? $itemTaxes[$row->id] : NULL;
            if (is_array($taxConfig)):

                if ($print):
                    $table_body = $table_body . $this->taxAttrTblDiv($itemTaxes, $row->id, $total_column_offset);
                else:
                    //$table_body = $table_body . $this->taxAttrTBL($itemTaxes, $row->id, $total_column_offset);
                    $table_body = $table_body . $this->taxAttrTBLInline($itemTaxes, $row->id, $total_column_offset);
                endif;
            endif;
            $r++;
        }
        if ($return_rows) {
            $table_body = $table_body . '<tr class="warning"><td colspan="' . $column_cnt . '" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
            $sr1 = 0;
            foreach ($return_rows as $row) {
                $sr1++;
                $table_body = $table_body . '<tr>';
                $sr_td = ($show_sr_no == 1) ? '<td class="">' . $sr1 . '</td>' : '';
                $table_body = $table_body . $sr_td;
                $i = 0;

                foreach ($data_arr as $data):

                    $id = $column_id_arr[$i];
                    $obj = $optionDetails[$id];

                    if ($i == 0):
                        $tax_suffix = '';
                        if ($printer->append_taxval_in_productname):
                            $row->tax = empty($row->tax) ? 0 : $row->tax;
                            $taxVal = number_format(str_replace('%', '', $row->tax), 0);
                            $tax_suffix = ' (' . $taxVal . '%) ';
                        endif;
                        $res = ($crop_product_name == 1) ? character_limiter($row->$data, 18) . $tax_suffix : $row->$data . $tax_suffix;
                        $table_body = $table_body . '<td>' . $res . '</td>';

                    elseif ($data == 'unit_price'):
                        $table_body = $table_body . '<td >' . $this->custom_format($row->real_unit_price, $obj->format) . '</td>';


                    elseif (!empty($obj->formula) && strpos($data, '|')):
                        $_data_arr = explode('|', $data);
                        $f_arr = explode('|', $obj->formula);
                        $f1_arr = explode('|', $obj->format);
                        $k = 0;
                        $res = '';
                        foreach ($_data_arr as $_key => $_data) {
                            $unit_total = ($_data == 'unit_price' && !empty($row->$_data)) ? $unit_total + $row->$_data : $unit_total;
                            $res = $res . $this->custom_format($row->$_data, $f1_arr[$_key]) . ' ' . $f_arr[$k];
                        }
                        $res = substr($res, 0, -1);
                        $table_body = $table_body . '<td>' . $res . '</td>';
                    else :
                        $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';
                        $res = $this->custom_format($row->$data, $obj->format);

                        $mrp_total = ($data == 'mrp' && !empty($row->$data)) ? $mrp_total + $row->$data : $mrp_total;
                        $net_total = ($data == 'real_unit_price' && !empty($row->$data)) ? $net_total + $row->$data : $net_total;
                        $unit_total = ($data == 'unit_price' && !empty($row->$data)) ? $unit_total + $row->$data : $unit_total;

                        $tax_total = ($data == 'item_tax' && !empty($row->$data)) ? $tax_total + $row->$data : $tax_total;
                        $discount_total = ($data == 'item_discount' && !empty($row->$data)) ? $discount_total + $row->$data : $discount_total;
                        $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->$data : $qty_total;

                        $table_body = $table_body . '<td class="' . $class . '">' . $res . '</td>';
                    endif;

                    $i++;
                endforeach;
                $table_body = $table_body . '</tr>';
                $taxConfig = isset($itemTaxes[$row->id]) ? $itemTaxes[$row->id] : NULL;
                if (is_array($taxConfig)):

                    if ($print):
                        $table_body = $table_body . $this->taxAttrTblDiv($itemTaxes, $row->id, $total_column_offset);
                    else:
                        //$table_body = $table_body . $this->taxAttrTBL($itemTaxes, $row->id, $total_column_offset);
                        $table_body = $table_body . $this->taxAttrTBLInline($itemTaxes, $row->id, $total_column_offset);
                    endif;
                endif;
                //echo $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)).' ('.$this->sma->formatMoney($row->net_unit_price).' + '.$this->sma->formatMoney($row->item_tax / $row->quantity) . ')</td><td class="no-border border-bottom text-right">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';
                $r++;
            }
        }
        $tableBody = '<tbody>' . $table_body . '</tbody>';
        /* ------------------------------------------------Table Body  End--------------------------------------  */



        /* ------------------------------------------------Footer--------------------------------------  */
        $footer_row1 = $footer_row2 = $footer_row3 = $footer_row4 = $footer_row5 = $footer_row6 = '';
        $footer_row1_cell1 = ($show_sr_no == 1) ? 2 : 1;
        $footer_row1 = $footer_row1 . '<tr> ';
        $i = 0;

        foreach ($data_arr as $data):

            $id = $column_id_arr[$i];
            $obj = $optionDetails[$id];

            if ($i == 0):
                $footer_row1 = $footer_row1 . '<th colspan="' . $footer_row1_cell1 . '" >' . lang("total") . '</th>';

            elseif (!empty($obj->formula) && strpos($data, '|')):
                $_data_arr = explode('|', $data);
                $f_arr = explode('|', $obj->formula);
                $f1_arr = explode('|', $obj->format);
                $k = 0;
                $res = '';
                foreach ($_data_arr as $_key => $_data) {

                    $res = ($_data == 'unit_price' && $unit_price != 0) ? $unit_price : $res;
                }

                $footer_row1 = $footer_row1 . '<th>' . $res . '</th>';
            else :
                $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';

                switch ($data) {
                    case 'unit_quantity':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatQuantity($qty_total) . '</th>';
                        break;

                    case 'mrp':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($mrp_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'real_unit_price':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($net_total) . '</th>';
                        //$footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'unit_price':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($unit_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'item_tax':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($tax_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'item_discount':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($discount_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'subtotal':
                        $footer_row1 = $footer_row1 . '<th class="text-left">' . $this->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)) . '</th>';
                        break;

                    default:
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '"> </th>';
                        break;
                }

            endif;

            $i++;
        endforeach;

        $footer_row1 = $footer_row1 . ' </tr>';

        //------------------------Order Tax ---------------------//

        if ($inv->order_tax != 0) {
            $order_tax_label = !empty($inv->order_tax_label) && ( $inv->order_tax_label != '-') ? $inv->order_tax_label : lang("Order Level Tax");
            $footer_row2 = '<tr><th  colspan="' . $total_column_offset . '">' . $order_tax_label . '</th><th class="text-left">' . $this->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
        }
        //------------------------Order Tax End---------------------//
        //------------------------ Order Discount  ---------------------//
        if ($inv->order_discount != 0) {
            $footer_row3 = '<tr><th  colspan="' . $total_column_offset . '">' . lang("order_discount") . '</th><th class="text-left">' . $this->formatMoney($inv->order_discount) . '</th></tr>';
        }
        //------------------------Order Discount End---------------------//
        //------------------------Return Surcharge ---------------------//
        if (!empty($return_sale) && $return_sale->surcharge != 0) {
            $footer_row4 = '<tr><th  colspan="' . $total_column_offset . '">' . lang("order_discount") . '</th><th class="text-left">' . $this->formatMoney($return_sale->surcharge) . '</th></tr>';
        }
        //------------------------Return Surcharge End ---------------------//
        //------------------------Shipping Charges---------------------//
        $footer_row5_shipping = '';
        if ($inv->shipping) {
            $footer_row5_shipping = '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("Shipping") . '</th>'
                    . '<th class="left">' . $this->formatMoney($inv->shipping) . '</th>'
                    . '</tr>';
        }

        //------------------------Grand Total ---------------------//
        if ($inv->rounding): // check Rounding  issue 
            $footer_row5 = '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("rounding") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney($inv->rounding) . '</th>'
                    . '</tr>';

            $GTotal = $this->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding) + $return_sale->grand_total) : ($inv->grand_total + $inv->rounding));
            $GTotalW = $this->convert_number_to_words($return_sale ? (($inv->grand_total + $inv->rounding) + $return_sale->grand_total) : ($inv->grand_total + $inv->rounding));
            $GTotalW = !empty($GTotalW) ? '<span style="text-transform: uppercase;font-size: smaller;float: right;padding-right: 25px;"> ( ' . $GTotalW . ' ' . $this->currencyLable . ' Only ) </span>' : '';
            $footer_row5 = $footer_row5_shipping . $footer_row5 . '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("grand_total") . $GTotalW . '</th>'
                    . '<th class="left">' . $GTotal . '</th>'
                    . '</tr>';
        else:
            $GTotal = $this->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total);
            $GTotalW = $this->convert_number_to_words($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total);
            $GTotalW = !empty($GTotalW) ? '<span style="text-transform: uppercase;font-size: smaller;float: right;padding-right: 25px;"> ( ' . $GTotalW . ' ' . $this->currencyLable . ' Only ) </span>' : '';

            $footer_row5 = $footer_row5_shipping . '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("grand_total") . $GTotalW . '</th>'
                    . '<th class="left">' . $GTotal . '</th>'
                    . '</tr>';
        endif;
        //------------------------Grand Total End---------------------//
        //------------------------Partial Paid---------------------//
        if ($inv->paid < $inv->grand_total) :

            $footer_row6 = ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("paid_amount") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid) . '</th>'
                    . '</tr>';

            $footer_row6 = $footer_row6 . ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("Due_Amount") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding) + $return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)) . '</th>'
                    . '</tr>';
        else: 
           $footer_row6 = ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("paid_amount") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid) . '</th>'
                    . '</tr>';
        endif;
        //------------------------Partial Paid End ---------------------//
        $footer_row7 = '';
        if (count($taxAttr) > 0) {
            foreach ($taxAttr as $_code => $_value) :
                $footer_row7 = $footer_row7 . ' <tr><th colspan="' . $total_column_offset . '" >' . $_code . '</th>'
                        . '<th class="text-right">' . $this->formatMoney($_value) . '</th></tr>';
            endforeach;
        }

        $tableFooter = '<tfoot>' . $footer_row1 . $footer_row2 . $footer_row3 . $footer_row4 . $footer_row7 . $footer_row5 . $footer_row6 . '</tfoot>';
        /* ------------------------------------------------Footer End--------------------------------------  */


        /* ------------------------------------------------Table  --------------------------------------  */
        if (!empty($class)):
            $table = '<table class="table table-bordered table-hover table-striped">' . $tableHeader . $tableBody . $tableFooter . '</table>';
        else:
            $table = '<table class="table table-striped table-condensed">' . $tableHeader . $tableBody . $tableFooter . '</table>';
        endif;

        /* ------------------------------------------------Table Body  End--------------------------------------  */

        return $table;
    }

    public function posBillTable($printer, $inv, $return_sale, $rows, $return_rows, $class = null, $print = NULL) {


        $itemTaxes = isset($inv->rows_tax) ? $inv->rows_tax : array();
        $column_id_str = isset($printer->column_id_str) && !empty($printer->column_id_str) ? $printer->column_id_str : '';
        $column_name_str = isset($printer->column_name_str) && !empty($printer->column_name_str) ? $printer->column_name_str : '';
        $data = isset($printer->data) && !empty($printer->data) ? $printer->data : '';
        $optionDetails = isset($printer->optionDetails) && !empty($printer->optionDetails) ? $printer->optionDetails : '';


        if (empty($column_id_str) || empty($column_name_str) || empty($data)):
            return false;
        endif;

        $crop_product_name = $printer->crop_product_name;
        $show_sr_no = $printer->show_sr_no;
        $column_id_arr = explode(',', $column_id_str);
        $column_name_arr = explode(',', $column_name_str);
        $data_arr = explode(',', $data);

        $table_header = '';
        if (count($column_id_arr) != count($column_name_arr) || count($column_id_arr) != count($data_arr)):
            return false;
        endif;
        $column_cnt = count($column_id_arr);
        $column_cnt = ($show_sr_no == 1) ? $column_cnt + 1 : $column_cnt;
        $total_column_offset = $column_cnt - 1;

        /* ------------------------------------------------HEADER--------------------------------------  */
        $sr_th = ($show_sr_no == 1) ? '<th class="">Sr.No</th>' : '';
        $ColColumn = 0;
        foreach ($column_name_arr as $column_key => $column_name):
            if (!empty($column_name)):
                $table_header = $table_header . '<th class="' . $data_arr[$column_key] . '">' . $column_name . '</th>';
                $ColColumn++;
            endif;
        endforeach;
        $tableHeader = '<thead><tr>' . $sr_th . $table_header . '</tr></thead>';

        $mrp_total = $qty_total = $weight_total = $discount_total = $tax_total = $net_total = $unit_total = $total_net_price = $totalprice = $totalmrp = 0;
        $item_arr = $item_return_arr = '';
        /* ------------------------------------------------HEADER End--------------------------------------  */

        /* ------------------------------------------------Table Body --------------------------------------  */
        $table_body = '';
        $sr = $r = 0;
        $taxAttr = $taxAttrName = array();
        $OldCat = '';
        foreach ($rows as $row) {
            if ($this->pos_settings->display_category == 1) {
                if ($OldCat != $row->category_id) {
                    $table_body = $table_body . '<tr><td colspan="' . $ColColumn . '" style="font-weight:bold;">' . $row->category_name . '</td></tr>';
                }
            }
            $VariantPrice = 0;
            if ($row->option_id != 0) {
                $VariantPrice = (isset($row->variant_price) && $row->variant_price) ? $row->variant_price : 0;
            }
            $sr++;
            $table_body = $table_body . '<tr>';
            $sr_td = ($show_sr_no == 1) ? '<td class="">' . $sr . '</td>' : '';
            $table_body = $table_body . $sr_td;
            $i = 0;
            foreach ($data_arr as $data):

                $id = $column_id_arr[$i];
                $obj = $optionDetails[$id];

                if ($i == 0):
                    $append_product_name = '';
                    if ($row->option_id != 0) {
                        //$append_product_name .= '('.$row->variant.')';
                    }
                    if ($printer->append_taxval_in_productname):
                        $row->tax = empty($row->tax) ? 0 : $row->tax;
                        $taxVal = number_format(str_replace('%', '', $row->tax), 0);
                        $append_product_name .= '<br/>Tax: ' . $taxVal . '%';
                    endif;

                    if ($printer->append_product_code_in_name && !empty($row->product_code)):
                        $append_product_name .= '<br/>Code: ' . $row->product_code;
                    endif;

                    if ($printer->append_hsn_code_in_name && !empty($row->hsn_code)):
                        $append_product_name .= '<br/>HSN: ' . $row->hsn_code;
                    endif;

                    if ($printer->append_note_in_name && !empty($row->note)):
                        $append_product_name .= '<br/>Note: ' . $row->note;
                    endif;

                    //$prod_name = ($crop_product_name == 1) ? character_limiter($row->$data, 20) : $row->$data;
                    $product_name_Truncate = explode("(", $row->$data);

                    $prod_name = (($crop_product_name == 1) ? character_limiter($product_name_Truncate[0], 20) : $product_name_Truncate[0]);

                    $prod_name .= (isset($product_name_Truncate[1]) ? ' (' . $product_name_Truncate[1] : '');

                    $res = strtolower($prod_name . $append_product_name);

                    //Show/Hide Combo Product Items
                    if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                        if ($printer->show_combo_products_list) {
                            $res .= " + (";
                            foreach ($row->combo_items as $comk => $comv) {
                                $res .= $comv->name . " Qty." . (int) $comv->qty . "  , ";
                            }
                            $res = trim($res, ", ");
                            $res .= ")";
                        }//end if.
                    }

                    //Show/Hide Invoice Product Image
                    if ($printer->show_product_image == 1) {
                        $itemImage = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/' . $row->image;
                        $image_size = ($printer->product_image_size != '') ? $printer->product_image_size : 'width:40px;height:40px;';
                        if (file_exists($itemImage)) {
                            $imgTag = '<img src="' . base_url($itemImage) . '" style="' . $image_size . 'margin-right:5px;" alt="' . $row->image . '" align="left" /> ';
                        } else {
                            $imgTag = '<img src="' . base_url('assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/no_image.png') . '" style="' . $image_size . 'margin-right:5px;" align="left" alt="no_image" /> ';
                        }
                    } else {
                        $imgTag = '';
                    }
                    $table_body = $table_body . '<td style="text-transform: capitalize;">' . $imgTag . $res . '</td>';
                elseif ($data == 'unit_price'):
                    $table_body = $table_body . '<td >' . $this->custom_format($row->unit_price, $obj->format) . '</td>';
                elseif ($data == 'real_unit_price'):
                    $table_body = $table_body . '<td >' . $this->custom_format(($row->real_unit_price + $VariantPrice), $obj->format) . '</td>';


                elseif (!empty($obj->formula) && strpos($data, '|')):
                    $_data_arr = explode('|', $data);
                    $f_arr = explode('|', $obj->formula);
                    $f1_arr = explode('|', $obj->format);
                    $k = 0;
                    $res = '';
                    foreach ($_data_arr as $_key => $_data) {

                        $unit_total = ($_data == 'unit_price' && !empty($row->$_data)) ? $unit_total + $row->$_data : $unit_total;
                        $res = $res . $this->custom_format($row->$_data, $f1_arr[$_key]) . ' ' . $f_arr[$k];
                    }
                    $res = substr($res, 0, -1);
                    $table_body = $table_body . '<td>' . $res . '</td>';
                else :

                    $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'net_price', 'invoice_net_unit_price', 'invoice_total_net_unit_price', 'net_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';
                    $mrp_total = ($data == 'mrp' && !empty($row->$data)) ? $mrp_total + $row->$data : $mrp_total;
                    $net_total = ($data == 'real_unit_price' && !empty($row->$data)) ? $net_total + $row->$data : $net_total;
                    $unit_total = ($data == 'unit_price' && !empty($row->$data)) ? $unit_total + $row->$data : $unit_total;
                    $tax_total = ($data == 'item_tax' && !empty($row->$data)) ? $tax_total + $row->$data : $tax_total;
                    $discount_total = ($data == 'item_discount' && !empty($row->$data)) ? $discount_total + $row->$data : $discount_total;
                    $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->$data : $qty_total;
                    $weight_total = ($data == 'item_weight' && !empty($row->$data)) ? $weight_total + $row->$data : $weight_total;
                    $total_net_price = ($data == 'invoice_total_net_unit_price' && !empty($row->$data)) ? $total_net_price + $row->$data : $total_net_price;
                    $totalprice = ($data == 'invoice_net_unit_price' && !empty($row->$data)) ? $totalprice + $row->$data : $totalprice;
                    $totalmrp = ($data == 'net_price' && !empty($row->$data)) ? $totalmrp + $row->$data : $totalmrp;

                    if (!empty($obj->format)):
                        if ($data == 'real_unit_price') {
                            $res = $this->custom_format(($row->real_unit_price * $qty_total), $obj->format);
                        } else {
                            $res = $this->custom_format($row->$data, $obj->format);
                        }
                    //$res = $this->custom_format($row->$data,$obj->format);
                    else:
                        $res = $row->$data;
                    endif;

                    switch ($data) {
                        case 'subtotal':
                        case 'unit_price':
                        case 'net_unit_price':
                            if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                                $total_combo_item_price = 0;
                                foreach ($row->combo_items as $comk => $comv) {
                                    $total_combo_item_price += ($comv->qty * $comv->unit_price);
                                }
                                //  $res = "<del>" .$this->custom_format($total_combo_item_price,$obj->format)."</del><br>".$res ;
                            }

                            break;
                    }

                    $table_body = $table_body . '<td class="' . $class . '">' . $res . '</td>';
                endif;

                $i++;
            endforeach;

            $table_body = $table_body . '</tr>';
            $taxConfig = isset($itemTaxes[$row->id]) ? $itemTaxes[$row->id] : NULL;
            if (is_array($taxConfig)):

                if ($print):

                    $table_body = $table_body . $this->taxAttrTblDiv($itemTaxes, $row->id, $total_column_offset);
                else:
                    // $table_body = $table_body . $this->taxAttrTBL($itemTaxes, $row->id, $total_column_offset);
                    $table_body = $table_body . $this->taxAttrTBLInline($itemTaxes, $row->id, $total_column_offset);
                endif;
            endif;
            $r++;
            $OldCat = $row->category_id;
        }
        if ($return_rows) {
            $table_body = $table_body . '<tr class="warning"><td colspan="' . $column_cnt . '" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
            $sr1 = 0;
            foreach ($return_rows as $row) {
                $sr1++;
                $table_body = $table_body . '<tr>';
                $sr_td = ($show_sr_no == 1) ? '<td class="">' . $sr1 . '</td>' : '';
                $table_body = $table_body . $sr_td;
                $i = 0;

                foreach ($data_arr as $data):

                    $id = $column_id_arr[$i];
                    $obj = $optionDetails[$id];

                    if ($i == 0):
                        $tax_suffix = '';
                        if ($printer->append_taxval_in_productname):
                            $row->tax = empty($row->tax) ? 0 : $row->tax;
                            $taxVal = number_format(str_replace('%', '', $row->tax), 0);
                            $tax_suffix = ' (' . $taxVal . '%) ';
                        endif;
                        $res = ($crop_product_name == 1) ? character_limiter($row->$data, 18) . $tax_suffix : $row->$data . $tax_suffix;
                        $table_body = $table_body . '<td>' . $res . '</td>';

                    elseif ($data == 'unit_price'):
                        $table_body = $table_body . '<td >' . $this->custom_format($row->real_unit_price, $obj->format) . '</td>';


                    elseif (!empty($obj->formula) && strpos($data, '|')):
                        $_data_arr = explode('|', $data);
                        $f_arr = explode('|', $obj->formula);
                        $f1_arr = explode('|', $obj->format);
                        $k = 0;
                        $res = '';
                        foreach ($_data_arr as $_key => $_data) {
                            $unit_total = ($_data == 'unit_price' && !empty($row->$_data)) ? $unit_total + $row->$_data : $unit_total;
                            $res = $res . $this->custom_format($row->$_data, $f1_arr[$_key]) . ' ' . $f_arr[$k];
                        }
                        $res = substr($res, 0, -1);
                        $table_body = $table_body . '<td>' . $res . '</td>';
                    else :
                        $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'net_price', 'invoice_net_unit_price', 'invoice_total_net_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';
                        $res = $this->custom_format($row->$data, $obj->format);

                        //$mrp_total = ($data == 'mrp' && !empty($row->$data)) ? $mrp_total + $row->$data : $mrp_total;
                        $net_total = ($data == 'real_unit_price' && !empty($row->$data)) ? $net_total + $row->$data : $net_total;
                        $unit_total = ($data == 'unit_price' && !empty($row->$data)) ? $unit_total + $row->$data : $unit_total;

                        $tax_total = ($data == 'item_tax' && !empty($row->$data)) ? $tax_total + $row->$data : $tax_total;
                        $discount_total = ($data == 'item_discount' && !empty($row->$data)) ? $discount_total + $row->$data : $discount_total;
                        $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->$data : $qty_total;
                        $weight_total = ($data == 'item_weight' && !empty($row->$data)) ? $weight_total + $row->$data : $weight_total;
                        $total_net_price = ($data == 'invoice_total_net_unit_price' && !empty($row->$data)) ? $total_net_price + $row->$data : $total_net_price;

                        // $totalprice = ($data == 'invoice_net_unit_price' && !empty($row->$data)) ? $totalprice + $row->$data : $totalprice;
                        $totalmrp = ($data == 'net_price' && !empty($row->$data)) ? $totalmrp + $row->$data : $totalmrp;

                        $table_body = $table_body . '<td class="' . $class . '">' . $res . '</td>';
                    endif;

                    $i++;
                endforeach;
                $table_body = $table_body . '</tr>';
                $taxConfig = isset($itemTaxes[$row->id]) ? $itemTaxes[$row->id] : NULL;
                if (is_array($taxConfig)):

                    if ($print):
                        $table_body = $table_body . $this->taxAttrTblDiv($itemTaxes, $row->id, $total_column_offset);
                    else:
                        //$table_body = $table_body . $this->taxAttrTBL($itemTaxes, $row->id, $total_column_offset);
                        $table_body = $table_body . $this->taxAttrTBLInline($itemTaxes, $row->id, $total_column_offset);
                    endif;
                endif;
                //echo $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)).' ('.$this->sma->formatMoney($row->net_unit_price).' + '.$this->sma->formatMoney($row->item_tax / $row->quantity) . ')</td><td class="no-border border-bottom text-right">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';
                $r++;
            }
        }
        $tableBody = '<tbody>' . $table_body . '</tbody>';
        /* ------------------------------------------------Table Body  End--------------------------------------  */



        /* ------------------------------------------------Footer--------------------------------------  */
        $footer_row1 = $footer_row2 = $footer_row3 = $footer_row4 = $footer_row5 = $footer_row6 = '';
        $footer_row1_cell1 = ($show_sr_no == 1) ? 2 : 1;
        $footer_row1 = $footer_row1 . '<tr> ';
        $i = 0;

        foreach ($data_arr as $data):

            $id = $column_id_arr[$i];
            $obj = $optionDetails[$id];

            if ($i == 0):
                $footer_row1 = $footer_row1 . '<th colspan="' . $footer_row1_cell1 . '" >' . lang("total") . '</th>';

            elseif (!empty($obj->formula) && strpos($data, '|')):
                $_data_arr = explode('|', $data);
                $f_arr = explode('|', $obj->formula);
                $f1_arr = explode('|', $obj->format);
                $k = 0;
                $res = '';
                foreach ($_data_arr as $_key => $_data) {

                    $res = ($_data == 'unit_price' && $unit_price != 0) ? $unit_price : $res;
                }

                $footer_row1 = $footer_row1 . '<th>' . $res . '</th>';
            else :
                $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'net_price', 'invoice_net_unit_price', 'invoice_total_net_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';

                switch ($data) {
                    case 'unit_quantity':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatQuantity($qty_total) . '</th>';
                        break;

                    case 'item_weight':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatQuantity($weight_total) . 'KG</th>';
                        break;

                    case 'mrp':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($mrp_total) . '</th>';
                        //$footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'real_unit_price':
                        //  $footer_row1=$footer_row1.'<th class="'. $class.'">'.$this->formatMoney($net_total).'</th>';
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '"></th>';
                        break;



                    case 'unit_price':
                        //   $footer_row1=$footer_row1.'<th class="'. $class.'">'.$this->formatMoney($unit_total).'</th>';
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '"></th>';
                        break;
                    case 'invoice_total_net_unit_price':

                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($total_net_price) . '</th>';
                        break;
                    case 'invoice_net_unit_price':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($totalprice) . '</th>';
                        break;

                    case 'net_price':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($totalmrp) . '</th>';
                        break;


                    case 'item_tax':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($tax_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'item_discount':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '">' . $this->formatMoney($discount_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'subtotal':
                        $footer_row1 = $footer_row1 . '<th class="text-left">' . $this->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)) . '</th>';
                        break;

                    default:
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '"> </th>';
                        break;
                }

            endif;

            $i++;
        endforeach;

        $footer_row1 = $footer_row1 . ' </tr>';

        //------------------------Order Tax ---------------------//

        if ($inv->order_tax != 0) {
            $order_tax_label = !empty($inv->order_tax_label) && ( $inv->order_tax_label != '-') ? $inv->order_tax_label : lang("tax");
            $footer_row2 = '<tr><th  colspan="' . $total_column_offset . '">' . $order_tax_label . '</th><th class="text-right">' . $this->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
        }
        //------------------------Order Tax End---------------------//
        //------------------------ Order Discount  ---------------------//
        if ($inv->order_discount != 0) {
            $footer_row3 = '<tr><th  colspan="' . $total_column_offset . '">' . lang("order_discount") . '</th><th class="text-left">' . $this->formatMoney($inv->order_discount) . '</th></tr>';
        }
        //------------------------Order Discount End---------------------//
        //------------------------Return Surcharge ---------------------//
        if (!empty($return_sale) && $return_sale->surcharge != 0) {
            $footer_row4 = '<tr><th  colspan="' . $total_column_offset . '">' . lang("order_discount") . '</th><th class="text-left">' . $this->formatMoney($return_sale->surcharge) . '</th></tr>';
        }
        //------------------------Return Surcharge End ---------------------//
        //------------------------Shipping Charges---------------------//
        $footer_row5_shipping = '';
        if ($inv->shipping && (isset($inv->eshop_sale) && $inv->eshop_sale)) {
            $footer_row5_shipping = '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("Shipping") . '</th>'
                    . '<th class="left">' . $this->formatMoney($inv->shipping) . '</th>'
                    . '</tr>';
        }

        //------------------------Grand Total ---------------------//
        if ($inv->rounding): // check Rounding  issue 
            $footer_row5 = '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("rounding") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney($inv->rounding) . '</th>'
                    . '</tr>';

            $GTotal = $this->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding));
            $GTotalW = $this->convert_number_to_words($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding));
            $GTotalW = !empty($GTotalW) ? '<span style="text-transform: uppercase;font-size: smaller;float: right;padding-right: 25px;"> ( ' . $GTotalW . ' ' . $this->currencyLable . ' Only ) </span>' : '';
            $footer_row5 = $footer_row5_shipping . $footer_row5 . '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("grand_total") . $GTotalW . '</th>'
                    . '<th class="left">' . $GTotal . '</th>'
                    . '</tr>';
        else:
            $GTotal = $this->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total);
            $GTotalW = $this->convert_number_to_words($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total);
            $GTotalW = !empty($GTotalW) ? '<span style="text-transform: uppercase;font-size: smaller;float: right;padding-right: 25px;"> ( ' . $GTotalW . ' ' . $this->currencyLable . ' Only ) </span>' : '';

            $footer_row5 = $footer_row5_shipping . '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("grand_total") . $GTotalW . '</th>'
                    . '<th class="left">' . $GTotal . '</th>'
                    . '</tr>';
        endif;
        //------------------------Grand Total End---------------------//
        //------------------------Partial Paid---------------------//
        if ($inv->paid < $inv->grand_total) :

            $footer_row6 = ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("paid_amount") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid) . '</th>'
                    . '</tr>';

            $footer_row6 = $footer_row6 . ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("Due_Amount") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)) . '</th>'
                    . '</tr>';


        else:
       
           $footer_row6 = ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("paid_amount") . '</th>'
                    . '<th class="text-left">' . $this->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid) . '</th>'
                    . '</tr>';          

        endif;
        //------------------------Partial Paid End ---------------------//
        $footer_row7 = '';
        if (count($taxAttr) > 0) {
            foreach ($taxAttr as $_code => $_value) :
                $footer_row7 = $footer_row7 . ' <tr><th colspan="' . $total_column_offset . '" >' . $_code . '</th>'
                        . '<th  class="text-right">' . $this->formatMoney($_value) . '</th></tr>';
            endforeach;
        }

        $tableFooter = '<tfoot>' . $footer_row1 . $footer_row2 . $footer_row3 . $footer_row4 . $footer_row7 . $footer_row5 . $footer_row6 . '</tfoot>';
        /* ------------------------------------------------Footer End--------------------------------------  */



        /* ------------------------------------------------Table  --------------------------------------  */
        if (!empty($class)):
            $table = '<table class="table table-bordered table-hover table-striped">' . $tableHeader . $tableBody . $tableFooter . '</table>';
        else:
            $table = '<table class="table table-striped table-condensed">' . $tableHeader . $tableBody . $tableFooter . '</table>';
        endif;

        /* ------------------------------------------------Table Body  End--------------------------------------  */

        return $table;
    }

    public function custom_format($val, $format) {
        switch ($format) {
            case 'formatMoney':
                return $this->formatMoney($val);

                break;

            case 'formatQuantity':
                return $this->formatQuantity($val);

                break;

            default:
                break;
        }
    }

    public function convert_number_to_words($number) {
        $number = $this->formatNumber($number);
        $number = str_replace(",", '', $number);
        if (class_exists('NumberFormatter')):
            $f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
            return $f->format($number);
        endif;
    }

    public function getSmsDltTeIdByTemplateKey($sms_template_key) {

        $q = $this->db->select('dlt_te_id, client_dlt_te_id')->where(['template_key' => $sms_template_key, 'is_active' => 1])->get('sms_configs');

        if ($q->num_rows() > 0) {
            $row = $q->result_array();
            $dlt_te_id = $row[0]['client_dlt_te_id'] != '' ? $row[0]['client_dlt_te_id'] : $row[0]['dlt_te_id'];
            return $dlt_te_id;
        }
        return FALSE;
    }

    public function SendSMS($mobile, $msg, $sms_template_key, $sms_header = null, $DLT_TE_ID = null) {

        if (!$sms_template_key && !$DLT_TE_ID) {
            return '{"status":"error", "message":"SMS Template Key is empty."}';
        } elseif ($sms_template_key && !$DLT_TE_ID) {
            $DLT_TE_ID = $this->getSmsDltTeIdByTemplateKey($sms_template_key);
            if ($DLT_TE_ID == FALSE) {
                return '{"status":"error", "message":"SMS DLT_TE_ID not found for template key ' . $sms_template_key . '"}';
            }
        }

        $smsAPI = $this->configData->config['smsAPI'];
        $datasms = "?authkey=" . $this->configData->config['authkey'];
        $datasms .= "&route=" . $this->configData->config['route'];
        $datasms .= "&response=" . $this->configData->config['response'];
        $datasms .= "&country=" . $this->configData->config['country'];
        if ($sms_header) {
            $sender = $sms_header;
        } else {
            $sender =  ((bool)$this->Settings->sms_sender) ? $this->Settings->sms_sender :  $this->configData->config['sender'];
        }

        $msg = $msg.' '.$this->Settings->sms_brand_name;
        $datasms .= "&sender=" . $sender;
        $datasms .= "&message=" . urlencode($msg);
        $datasms .= "&mobiles=" . $mobile;
        $datasms .= "&unicode=0";
        $datasms .= "&campaign=" . $sender;
        $datasms .= "&DLT_TE_ID=" . $DLT_TE_ID;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $smsAPI . $datasms,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return '{"status":"error", "message":"' . $err . '"}';
        } else {
            return $response;
        }
    }

    public function SendCrmSMS($mobile, $msg, $sms_header = null, $DLT_TE_ID = null) {

        if (!$DLT_TE_ID) {
            return '{"status":"error", "message":"SMS Template Id is empty."}';
        }

        $smsAPI = $this->configData->config['smsAPI'];
        $datasms = "?authkey=" . $this->configData->config['authkey'];
        $datasms .= "&route=" . $this->configData->config['route'];
        $datasms .= "&response=" . $this->configData->config['response'];
        $datasms .= "&country=" . $this->configData->config['country'];

        $sender = $sms_header ? $sms_header : (!empty($this->Settings->sms_promotional_header) ? $this->Settings->sms_promotional_header : $this->Settings->sms_sender );
        $msg = $msg.' '.$this->Settings->site_name; 
        $datasms .= "&sender=" . $sender;
        $datasms .= "&message=" . urlencode($msg);
        $datasms .= "&mobiles=" . $mobile;
        $datasms .= "&unicode=1";
        $datasms .= "&campaign=" . $sender;
        $datasms .= "&DLT_TE_ID=" . $DLT_TE_ID;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $smsAPI . $datasms,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return '{"status":"error", "message":"' . $err . '"}';
        } else {
            return $response;
        }
    }

    public function BalanceSMS() {
        $datasms = array(
            "mphone" => $this->_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'BalanceSms',
        );
        $surlsms = $this->pos_api_url . '/sms-request.php';
        $res = $this->post_to_url($surlsms, $datasms);
       
        $smsBalance = '';
        if (!empty($res)):
            $Obj = json_decode($res);
            if (isset($Obj->status) && $Obj->status == 'success' && $Obj->sms_count > 0):
                $smsBalance = (int) $Obj->sms_count;
            endif;
        endif;
        // $smsBalance = "101";
        return $smsBalance;
    }

    public function update_sms_count($smscount) {
        $datasms = [
            'mphone' => $this->_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'UpdateBalanceSms',
            'smscount' => ($smscount ? $smscount : 1)
        ];
        $surlsms = $this->pos_api_url . '/sms-request.php';
        $res = $this->post_to_url($surlsms, $datasms);

        return json_decode($res);
    }

    public function SmsCron($pos_sms_cron, $get = null, $pos_sms_cron_type = NULL) {
        $datasms = [
            "mphone" => $this->_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'SmsCron',
            'pos_sms_cron' => (int) $pos_sms_cron,
            'pos_sms_cron_type' => (int) $pos_sms_cron_type,
        ];

        if (!empty($get)):
            $datasms['fetch_data'] = '1';
        endif;

        $surlsms = $this->pos_api_url . '/sms-request.php';
        $res = $this->post_to_url($surlsms, $datasms);

        $return = false;
        if (!empty($res)):
            $Obj = json_decode($res);
            if (isset($Obj->status) && $Obj->status == 'success'):
                $return = $Obj;
            endif;
        endif;
        return $return;
    }

    public function SMSList($offset, $limit, $past = 3) {

        $datasms = array(
            "mphone" => $this->_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'smsLog',
            'past' => $past,
            'limit' => (int) $limit,
            'offset' => $offset
        );
        $surlsms = $this->pos_api_url . '/sms-request.php';
        $res = $this->post_to_url($surlsms, $datasms);

        return $res;
    }

    public function setSMSLog($mobile, $msg, $smsid = '') {

        $datasms = array(
            'mphone'    => $this->_merchant_phone,
            'apikey'    => $this->apiKey,
            'action'    => 'LogSMS',
            'r_phone'   => $mobile,
            'note'      => $msg,
            'sms_id'    => $smsid,
            'sms_count_used' => '1',
        );

        $surlsms = $this->pos_api_url . '/sms-request.php';
        $res = $this->post_to_url($surlsms, $datasms);

        return $res;
    }

    public function PackageInfo() {
        $ci = get_instance();
        $config = $ci->config;
        $_merchant_phone = isset($config->config['merchant_phone']) ? $config->config['merchant_phone'] : '';

        $datasms = array(
            "phone" => $_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'merchantInfo',
        );
        $surlsms = $this->pos_api_url . '/merchantInfo.php';
        $res = $this->post_to_url($surlsms, $datasms);

        $data['package_info'] = '';
        if (!empty($res)):
            $Obj = json_decode($res);

            if (isset($Obj->status) && $Obj->status == 'success' && $Obj->msg->phone == $_merchant_phone):
                return (array) $Obj->msg;

            endif;
        endif;

        return false;
    }

    public function SyncCustomerData($customer) {
        return true;
        /*$arr = array();
        isset($customer->name) && !empty($customer->name) ? $arr['name'] = $customer->name : '';
        isset($customer->email) && !empty($customer->email) ? $arr['email'] = $customer->email : '';
        isset($customer->address) && !empty($customer->address) ? $arr['address'] = $customer->address : '';
        isset($customer->gender) && !empty($customer->gender) ? $arr['gender'] = $customer->gender : '';
        isset($customer->city) && !empty($customer->city) ? $arr['city'] = $customer->city : '';
        isset($customer->dob) && !empty($customer->dob) && $customer->dob != '0000-00-00' ? $arr['dob'] = $customer->dob : '';
        isset($customer->anniversary) && !empty($customer->anniversary) && $customer->anniversary != '0000-00-00' ? $arr['anniversary'] = $customer->anniversary : '';
        isset($customer->dob_child1) && !empty($customer->dob_child1) && $customer->dob_child1 != '0000-00-00' ? $arr['older_child_dob'] = $customer->dob_child1 : '';
        isset($customer->dob_child2) && !empty($customer->dob_child2) && $customer->dob_child2 != '0000-00-00' ? $arr['younger_child_dob'] = $customer->dob_child2 : '';
        isset($customer->dob_mother) && !empty($customer->dob_mother) && $customer->dob_mother != '0000-00-00' ? $arr['mother_dob'] = $customer->dob_mother : '';
        isset($customer->dob_father) && !empty($customer->dob_father) && $customer->dob_father != '0000-00-00' ? $arr['father_dob'] = $customer->dob_father : '';
        if (count($arr) == 0):
            return false;
        endif;

        $ci = get_instance();
        $config = $ci->config;
        $_merchant_phone = isset($config->config['merchant_phone']) ? $config->config['merchant_phone'] : '';
        $arr['merchant_phone'] = $_merchant_phone;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://simplypos.co.in/api/v1/pos/merchant/update/customer/" . $customer->phone,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arr),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);

        $this->load->library('logs');
        // $this->logs->write('customer_sync', json_encode($arr), $val);
        // $this->logs->write('customer_sync', $response, $val);
        $err = curl_error($curl);
        curl_close($curl);
        if (!$err) {
            if (!empty($response)):
                $ResObj = json_decode($response);
                if (isset($ResObj->type) && $ResObj->type == 'success'):
                    $this->db->update('companies', array('is_synced' => 1), array('id' => $customer->id));
                endif;
            endif;
        }
        return false;
         
      */
    }

    function saveBillerLocation($biller) {

        $ci = get_instance();
        $config = $ci->config;
        $_merchant_phone = isset($config->config['merchant_phone']) ? $config->config['merchant_phone'] : '';

        $data = array(
            "phone" => $_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'saveBillerLocation',
        );

        $data['lat'] = isset($biller->lat) ? $biller->lat : '';
        $data['lng'] = isset($biller->lng) ? $biller->lng : '';
        if ($data['lat'] == '' || $data['lng'] == ''):
            return false;
        endif;

        $data['id'] = isset($biller->id) ? $biller->id : '';
        $data['name'] = isset($biller->name) ? $biller->name : '';
        $data['company'] = isset($biller->company) ? $biller->company : '';
        $data['address'] = isset($biller->address) ? $biller->address : '';
        $data['city'] = isset($biller->city) ? $biller->city : '';
        $data['state'] = isset($biller->state) ? $biller->state : '';
        $data['postal_code'] = isset($biller->postal_code) ? $biller->postal_code : '';
        $data['b_phone'] = isset($biller->phone) ? $biller->phone : '';
        $data['email'] = isset($biller->email) ? $biller->email : '';

        $data['logo'] = isset($biller->logo) && !empty($biller->logo) ? base_url('assets/mdata/'.$this->Customer_assets.'/uploads/logos/' . $biller->logo) : '';

        $surlsms = $this->pos_api_url . '/merchantInfo.php';
        $res = $this->post_to_url($surlsms, $data);

        $data['package_info'] = '';
        if (!empty($res)):
            $Obj = json_decode($res);

            if (isset($Obj->status) && $Obj->status == 'success' && $Obj->msg->phone == $_merchant_phone):
                return (array) $Obj->msg;

            endif;
        endif;

        return false;
    }

    function removeBillerLocation($billerID) {

        $ci = get_instance();
        $config = $ci->config;
        $_merchant_phone = isset($config->config['merchant_phone']) ? $config->config['merchant_phone'] : '';

        $data = array(
            "phone" => $_merchant_phone,
            'apikey' => $this->apiKey,
            'action' => 'DeleteBillerLocation',
        );

        $data['id'] = isset($billerID) ? $billerID : '';

        $surlsms = $this->pos_api_url . '/merchantInfo.php';
        $res = $this->post_to_url($surlsms, $data);

        $data['package_info'] = '';
        if (!empty($res)):
            $Obj = json_decode($res);

            if (isset($Obj->status) && $Obj->status == 'success' && $Obj->msg->phone == $_merchant_phone):
                return (array) $Obj->msg;

            endif;
        endif;

        return false;
    }

    function GroupGrid($group, $g_class_arr) {
        $g_cnt = 1;
        if (!is_array($group) || count($group) == 0):
            return '<li class="col-xs-12 form-group"><p><i class="fa fa-info-circle" aria-hidden="true"></i> No Contact Group available  </p></li>';
        endif;
        $table = '';
        foreach ($group as $key => $groupData) :
            $group_c = $g_class_arr[$g_cnt - 1];
            $g_cnt = ($g_cnt == 4) ? 0 : $g_cnt;

            $table = $table . '<li class="col-md-4 col-xs-6 form-group"> 
                        <a title=" ' . $groupData->group_name . '" class="' . $group_c . ' white quick-button small group_button" data-value="' . $groupData->id . '" id="group' . $groupData->id . '" href="javascript:void(this.value);">
                            <i class="fa fa-user"></i>
                            <p class="na_group"> ' . $groupData->group_name . '</p>
                        </a>
                    </li>';
            $g_cnt++;
        endforeach;
        return $table;
    }

    function TemplateList($template, $templateType = 1) {
        $g_cnt = $_g_cnt = 0;
        $table = '<ul>';
        if (!is_array($template) || count($template) == 0):
            $_g_cnt++;
            $table = $table . '<li class="col-xs-12 form-group"><p class="na_template"><i class="fa fa-info-circle" aria-hidden="true"></i> No template available </p></li>';
        else :

            foreach ($template as $key => $templateData) :

                if ($templateType != $templateData->template_type):
                    continue;
                endif;
                $g_cnt++;
                $table = $table . '<li><a data-dltteid="' . $templateData->dlt_te_id . '" data-value="' . $templateData->id . '"  data-message="' . $templateData->template_name . '" class="tempalte_type_' . $templateData->template_type . '" id="temp_' . $templateData->id . '" href="javascript:void(0);">' . $templateData->template_name . '</a></li>';
            endforeach;

        endif;
        if ($g_cnt == 0 && $_g_cnt == 0):
            $table = $table . '<li class="col-xs-12 form-group"><p class="na_template"><i class="fa fa-info-circle" aria-hidden="true"></i> No template available </p></li>';
        endif;

        $table = $table . '</ul>';
        return $table;
    }

    function contact_group_member($member, $selectedMember) {
        $tablelist = '';
        foreach ($member as $key => $memberData) :
            $chkOpt = '';
            //if(is_numeric($selectedMember)) {
            if (!empty($selectedMember)) {
                $chkOpt = in_array($memberData->id, $selectedMember) ? 'checked' : '';
            }
            if ($key == 1) {
                $wic = '<li class="col-md-3 col-sm-6   col-xs-12"><label style="color:#000;font-size: 14px;" for="mem_' . $memberData->id . '" ><input type="checkbox" class="mbselect" name="group_mem[]" value="' . $memberData->id . '"  id="mem_' . $memberData->id . '" ' . $chkOpt . '> ' . ucfirst($memberData->name) . '</label></li>';
            } else {
                $tablelist = $tablelist . '<li class="col-md-3 col-sm-6   col-xs-12"><label style="color:#000;font-size: 14px;" for="mem_' . $memberData->id . '" ><input type="checkbox" class="mbselect" name="group_mem[]" value="' . $memberData->id . '"  id="mem_' . $memberData->id . '" ' . $chkOpt . '> ' . ucfirst($memberData->name) . '</label></li>';
            }
        endforeach;
        $table = '<ul id="group_member_list">' . $wic . $tablelist . '</ul>';
        return $table;
    }

    function contact_member_count($id) {
        $cnt = $this->site->getContactGroupMemberCount($id);
        $return = ((int) $cnt > 0) ? $cnt : 0;
        return $return;
    }

    function smsCharLimit() {
        return 160;
    }

    function conatctTemplateType($type = NULL) {
        $templateType = array('1' => 'SMS', '2' => 'Email', '3' => 'Application Message',);
        switch ($type) {
            case 1:
                $templateType = array('1' => 'SMS');
                break;
            case 2:
                $templateType = array('2' => 'Email');
                break;
            case 3:
                $templateType = array('3' => 'Application Message',);
                break;
        }
        return $templateType;
    }

    function contactEventNotification($param) {
        $arr['users'] = isset($param['users']) && is_array($param['users']) ? $param['users'] : array();
        $arr['dob'] = isset($param['dob']) && is_array($param['dob']) ? $param['dob'] : array();
        $arr['anniversary'] = isset($param['anniversary']) && is_array($param['anniversary']) ? $param['anniversary'] : array();
        $arr['dob_father'] = isset($param['dob_father']) && is_array($param['dob_father']) ? $param['dob_father'] : array();
        $arr['dob_mother'] = isset($param['dob_mother']) && is_array($param['dob_mother']) ? $param['dob_mother'] : array();
        $arr['dob_child1'] = isset($param['dob_child1']) && is_array($param['dob_child1']) ? $param['dob_child1'] : array();
        $arr['dob_child2'] = isset($param['dob_child2']) && is_array($param['dob_child2']) ? $param['dob_child2'] : array();
        $tableHeader = "<thead><tr>
                         <th width='35%'>Customer</th>
                         <th>B'Day</th> 
                         <th>Anniversary</th> 
                         <th>Father's B'Day</th>
                         <th>Mother's B'Day</th>
                         <th>Older Child's B'Day</th>
                         <th>Younger Child's B'Day</th>
                     </tr></thead>";
        $tableB = '';
        $Y = '<i class="fa fa-check-square-o"></i>';
        $N = '<i class="fa fa-close"></i>';
        if (count($arr['users']) == 0):
            $tableB = "<tr>
                         <td colspan='7'>No Notification Found</td> 
                     </tr>";
        else:
            foreach ($arr['users'] as $userData) {
                $userID = isset($userData['id']) ? $userData['id'] : '0';
                $userName = isset($userData['name']) ? $userData['name'] : '-';
                $userEmail = isset($userData['email']) ? $userData['email'] : '-';
                $userPhone = isset($userData['phone']) ? $userData['phone'] : '-';
                if ($userID > 0):
                    $isBday = in_array($userID, $arr['dob']) ? $Y : $N;
                    $isAnniversary = in_array($userID, $arr['anniversary']) ? $Y : $N;
                    $isBdayF = in_array($userID, $arr['dob_father']) ? $Y : $N;
                    $isBdayM = in_array($userID, $arr['dob_mother']) ? $Y : $N;
                    $isBdayC1 = in_array($userID, $arr['dob_child1']) ? $Y : $N;
                    $isBdayC2 = in_array($userID, $arr['dob_child2']) ? $Y : $N;

                    $tableB = $tableB . "<tr>
                             <td >
                             Name : $userName
                             <br><i class='fa fa-mobile-phone'></i> : $userPhone
                             <br><i class='fa fa-envelope-o'></i>:$userEmail
                            </td> 
                             <td >$isBday</td> 
                             <td >$isAnniversary</td> 
                             <td >$isBdayF</td> 
                             <td >$isBdayM</td> 
                             <td >$isBdayC1</td> 
                             <td >$isBdayC2</td> 
                         </tr>";
                endif;
            }
        endif;

        $table = '<table class="table table-hover table-bordered">' . $tableHeader . '<tbody>' . $tableB . '</tbody></table>';
        return $table;
    }

    function tax_attr($arr = NULL) {
        $taxAttr = $this->site->getTaxAttr();
        $tableHeader = " <thead>
                        <tr>
	                    <th width='5%'>Sr.No</th>
                            <th width='15%'>Code</th>
                            <th>Name</th> 
                            <th  width='10%'> Percentage % </th> 
                        </tr>
                        </thead>";
        $tableB = '';
        $idArr = array();
        if (count($taxAttr) > 0) {
            $i = 1;
            foreach ($taxAttr as $key => $attr) {

                $selPercantage = isset($arr[$attr->id]['percentage']) ? (float) $arr[$attr->id]['percentage'] : '';
                $idArr[] = $attr->id;
                $tableB = $tableB . '<tr>'
                        . '<td class="text-center">' . $i++ . '</td>'
                        . '<td class="text-center">' . $attr->code . '</td>'
                        . '<td class="text-center">' . $attr->name . '</td>'
                        . '<td  class="tax_attr_td  text-right"><input  type="text" class="tax_attr_input col-md-10 text-right numaric_input" name="tax_attr_' . $attr->id . '"  value="' . $selPercantage . '"></td>'
                        . '</tr>';
            }
        } else {
            $tableB = $tableB = $tableB . '<tr>'
                    . '<td colspan="4"> Not found any attribute  </td>'
                    . '</tr>';
        }
        $id_str = implode(",", $idArr);
        $table = '<table class="table table-bordered table-hover table-striped">' . $tableHeader . '<tbody>' . $tableB . '</tbody></table><input type="hidden" name="tax_attr_str" value="' . $id_str . '">';
        return $table;
    }

    function taxAtrrClassification($tax_rate_id, $_net_unit_price, $_qty, $itemId = NULL, $saleID = NULL, $type = NULL) {

        $_net_unit_price = isset($_net_unit_price) ? $_net_unit_price : 0;
        $_qty = isset($_qty) ? $_qty : 0;
        $_tax = $this->site->getTaxRateByID($tax_rate_id);
        $tax_config = isset($_tax->tax_config) ? $_tax->tax_config : '';
        $tax_config = !empty($tax_config) ? unserialize($tax_config) : NULL;

        if (empty($tax_config)):
            return false;
        endif;

        if (!is_array($tax_config)):
            return false;
        endif;
        $insert_data = array();
        foreach ($tax_config as $taxID => $tax):

            if (isset($taxArr)):
                unset($taxArr);
            endif;

            $taxArr = array();
            $taxArr['item_id'] = !empty((int) $itemId) ? $itemId : 0;

            $taxArr['attr_code'] = $tax_config[$taxID]['code'];
            $taxArr['attr_name'] = $tax_config[$taxID]['name'];
            $taxArr['attr_per'] = $tax_config[$taxID]['percentage'];
            $taxArr['tax_amount'] = ($_net_unit_price * ((float) $tax_config[$taxID]['percentage'] / 100)) * ($_qty);


            switch ($type) {
                case 'p':
                    $taxArr['purchase_id'] = !empty((int) $saleID) ? $saleID : 0;
                    //$this->site->add_tax_attr_amount_purchase($taxArr);
                    break;

                case 'q':
                    $taxArr['quote_id'] = !empty((int) $saleID) ? $saleID : 0;
                    //$this->site->add_tax_attr_amount_quote($taxArr);
                    break;

                case 'o':
                    $taxArr['order_id'] = !empty((int) $saleID) ? $saleID : 0;
                    break;

                default:
                    $taxArr['sale_id'] = !empty((int) $saleID) ? $saleID : 0;
                    //$this->site->add_tax_attr_amount($taxArr);
                    break;
            }

            $insert_data[] = $taxArr;
        endforeach;

        switch ($type) {
            case 'p':
                if ($reuslt = $this->db->insert_batch('purchase_items_tax', $insert_data)):
                    return $reuslt;
                else:
                    return FALSE;
                endif;
                break;
            case 'q':
                if ($reuslt = $this->db->insert_batch('quote_items_tax', $insert_data)):
                    return $reuslt;
                else:
                    return FALSE;
                endif;
                break;
            case 'o':
                if ($reuslt = $this->db->insert_batch('orders_items_tax', $insert_data)):
                    return $reuslt;
                else:
                    return FALSE;
                endif;
                break;
            default:
                if ($reuslt = $this->db->insert_batch('sales_items_tax', $insert_data)):
                    return $reuslt;
                else:
                    return FALSE;
                endif;
                break;
        }
    }

    function taxTableAttr($taxAttr) {
        if (!is_array($taxAttr) || count($taxAttr) == 0):
            return false;
        endif;
        $tBody = '';
        foreach ($taxAttr as $key => $attr) {
            $tBody .= '<tr>'
                    . '<td colspan="4" class="text-right">' . $key . '(' . $attr['name'] . ')' . '</td>'
                    . '<td  class="text-right">' . $this->sma->formatMoney($attr['amt']) . '</td>'
                    . '</tr>';
        }
        return $tBody;
    }

    function taxOrderTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, $isBorder = NULL) {
        $sale_id = $inv->id;
        $tclass = !empty($isBorder) ? 'table-bordered' : '';
        $table = '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table ' . $tclass . ' table-condensed table-responsive" margin:0px!important;>';
        if (!empty($tax_summary)) {
            $tax_sum_colspan = is_array($taxItems) ? count($taxItems) + 3 : 4;
            $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';

            $table .= '<th  class="text-center">' . lang('CGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('SGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('IGST') . '(%)</th>';
            $table .= '<th class="text-right" style="text-align: center;">' . lang('Qty/Wt') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_excl') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';
            if (is_array($tax_summary)) {
                foreach ($tax_summary as $summary) :
                    if ($inv->igst != 0) {
                        $rate = round($summary['rate']);
                    } else {
                        $rate = round($summary['rate']) / 2;
                    }
                    $table .= '<tr><td style="text-align: center;">' . $summary['name'] . '</td>';
                    $row = $this->site->getOItemsTaxes($sale_id, $rate);
                    if (is_array($row)) {
                        foreach ($row as $item):
                            $table .= '<td class="text-center">' . (($item->CGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                            $table .= '<td class="text-center">' . (($item->SGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                            //if($item->IGST > 0){
                            $table .= '<td class="text-center">' . (($item->IGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                            // }    
                        endforeach;
                    }

                    $table .= '<td class="text-center" style="text-align: center;">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-center" style="text-align: center;">' . $this->sma->formatMoney($summary['amt']) . ' </td><td class="text-center" style="text-align: center;">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                endforeach;
            }//end if
        }
        $table .= '</tbody></table><div class="row" style="margin:2px -15px !important;">';

        if (isset($taxItems) && is_array($taxItems) && count($taxItems) && $Settings->tax_classification_view == 1):
            $colsize = 'col-lx-3 col-md-3 col-sm-3 col-xs-3'; //(count($taxItems) == 2) ? 'col-lx-4 col-md-4 col-sm-4 col-xs-4' : (($isBorder == '1') ? 'col-lx-3 col-md-3 col-sm-3 col-xs-3' : 'col-lx-4 col-md-4 col-sm-4 col-xs-4' );
            //$colsize = (count($taxItems)==2) ? 'col-lx-4 col-md-4 col-sm-4 col-xs-4' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3';
            $row_amt = $this->site->getTotalOrderItemsTaxes($sale_id);
            foreach ($row_amt as $item) {
                //$table .= '<tr><td colspan="'.$tax_sum_colspan.'" class="text-right" >' .$_tax->attr_code.' ( '.$_tax->attr_name.' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
                $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('CGST') . '&nbsp;' . (($item->CGST != 0) ? $this->sma->formatMoney($item->CGST) : 0) . '</div>';
                $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('SGST') . '&nbsp;' . (($item->SGST != 0) ? $this->sma->formatMoney($item->SGST) : 0) . '</div>';
                if ($item->IGST != 0) {
                    $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('IGST') . '&nbsp;' . (($item->IGST != 0) ? $this->sma->formatMoney($item->IGST) : 0) . '</div>';
                }
            }
        endif;

        $table .= '<div class="' . $colsize . '" style="margin-left:-15px !important;">' . lang('Total&nbsp;Tax') . '&nbsp;' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</div></div>';


        return $table;
    }

    function taxInvvoiceTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, $isBorder = NULL) {
        $sale_id = $inv->id;
        $tclass = !empty($isBorder) ? 'table-bordered' : '';
        $table = '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table ' . $tclass . ' table-condensed" margin:0px!important;>';
        if (!empty($tax_summary)) {
            $tax_sum_colspan = is_array($taxItems) ? count($taxItems) + 3 : 4;
            $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';

            $table .= '<th  class="text-center">' . lang('CGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('SGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('IGST') . '(%)</th>';
            $table .= '<th class="text-right" style="text-align: center;">' . lang('Qty/Wt') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_excl') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';
            if (is_array($tax_summary)) {
                foreach ($tax_summary as $summary) :
                    if (round($summary['rate'])) {
                        if ($inv->igst != 0) {
                            $rate = round($summary['rate']);
                        } else {
                            $rate = round($summary['rate']) / 2;
                        }
                        $table .= '<tr><td style="text-align: center;">' . $summary['name'] . '</td>';
                        $row = $this->site->getSItemsTaxes($sale_id, $rate);
                        if (is_array($row)) {
                            foreach ($row as $item):
                                $table .= '<td class="text-center">' . (($item->CGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                                $table .= '<td class="text-center">' . (($item->SGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                                //if($item->IGST > 0){
                                $table .= '<td class="text-center">' . (($item->IGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                                //}    
                            endforeach;
                        }else {
                             $table .= '<td class="text-center">0%</td><td class="text-center">0%</td><td class="text-center">0%</td>';
                        }

                        $table .= '<td class="text-center" style="text-align: center;">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-center" style="text-align: center;">' . $this->sma->formatMoney($summary['amt']) . ' </td><td class="text-center" style="text-align: center;">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                    }
                endforeach;
            }//end if
        }
        $table .= '</tbody></table><div class="row" style="margin:2px -15px !important;">';

        if (isset($taxItems) && is_array($taxItems) && count($taxItems) && $Settings->tax_classification_view == 1):
            $colsize = (count($taxItems) == 2) ? 'col-lx-3 col-md-3 col-sm-3 col-xs-3' : (($isBorder == '1') ? 'col-lx-2 col-md-2 col-sm-2 col-xs-2' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3' );
            //$colsize = (count($taxItems)==2) ? 'col-lx-4 col-md-4 col-sm-4 col-xs-4' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3';
            foreach ($taxItems as $_tax) {
                //$table .= '<tr><td colspan="'.$tax_sum_colspan.'" class="text-right" >' .$_tax->attr_code.' ( '.$_tax->attr_name.' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
                $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . $_tax->attr_code . '&nbsp;' . $this->sma->formatMoney($_tax->amt) . '</div>';
            }
        endif;

        $table .= '<div class="' . $colsize . '" style="margin-left:-15px !important;">' . lang('Total&nbsp;Tax') . '&nbsp;' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</div></div>';

        return $table;
    }

    /* function taxInvvoiceTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, $isBorder = NULL) {
      $tclass = !empty($isBorder) ? 'table-bordered' : '';
      $table = '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">' . lang('tax_summary') . '</h4>';
      $table .= '<table class="table ' . $tclass . ' table-condensed" margin:0px!important;>';
      if (!empty($tax_summary)) {
      $tax_sum_colspan = is_array($taxItems) ? count($taxItems) + 3 : 3;
      $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';
      if (is_array($taxItems)) {
      foreach ($taxItems as $_tax):
      $table .= '<th  class="text-center">' . $_tax->attr_code . '(%)</th>';
      endforeach;
      }
      $table .= '<th class="text-right" style="text-align: center;">' . lang('qty') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_excl') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_amt') . '</th></tr>'
      . '</thead>'
      . '<tbody>';
      if (is_array($tax_summary)) {
      foreach ($tax_summary as $summary) :
      $table .= '<tr><td style="text-align: center;">' . $summary['name'] . '</td>';
      if (is_array($taxItems)) {
      foreach ($taxItems as $_tax):
      $_tax = $this->site->taxAttrPercentageBySaleTaxId($_tax->attr_code, $summary['tax_rate_id'], $inv->id);
      $_tax = ($_tax === false) ? '-' : $_tax;
      $table .= '<td  class="text-center" style="text-align: center;">' . $_tax . '</td>';
      endforeach;
      }//end if.
      $table .= '<td class="text-center" style="text-align: center;">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-center" style="text-align: center;">' . $this->sma->formatMoney($summary['amt']) . ' </td><td class="text-center" style="text-align: center;">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
      endforeach;
      }//end if
      }
      $table .= '</tbody></table><div class="row" style="margin:2px -15px !important;">';

      if (isset($taxItems) && is_array($taxItems) && count($taxItems) && $Settings->tax_classification_view == 1):
      $colsize = (count($taxItems) == 2) ? 'col-lx-3 col-md-3 col-sm-3 col-xs-3' : (($isBorder == '1') ? 'col-lx-2 col-md-2 col-sm-2 col-xs-2' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3' );
      //$colsize = (count($taxItems)==2) ? 'col-lx-4 col-md-4 col-sm-4 col-xs-4' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3';
      foreach ($taxItems as $_tax) {
      //$table .= '<tr><td colspan="'.$tax_sum_colspan.'" class="text-right" >' .$_tax->attr_code.' ( '.$_tax->attr_name.' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
      $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . $_tax->attr_code . '&nbsp;' . $this->sma->formatMoney($_tax->amt) . '</div>';
      }
      endif;

      $table .= '<div class="' . $colsize . '" style="margin-left:-15px !important;">' . lang('Total&nbsp;Tax') . '&nbsp;' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</div></div>';

      return $table;
      } */

//style="text-align:right;"

    function purchaseTaxInvvoiceTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, $isBorder = NULL) {
        $tclass = !empty($isBorder) ? 'table-bordered' : '';
        $table = '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table ' . $tclass . ' table-condensed">';
        if (!empty($tax_summary)) {
            $tax_sum_colspan = is_array($taxItems) ? count($taxItems) + 3 : 3;
            $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';
            if (is_array($taxItems)):
                foreach ($taxItems as $_tax):
                    $table .= '<th  class="text-center">' . $_tax->attr_code . '</th>';
                endforeach;
            endif;
            $table .= '<th  class="text-right">' . lang('qty') . '</th><th  class="text-right">' . lang('tax_excl') . '</th><th  class="text-right">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';
            if (is_array($taxItems)):
                foreach ($tax_summary as $summary) :
                    $table .= '<tr><td>' . $summary['name'] . '</td>';
                    foreach ($taxItems as $_tax):
                        $_tax = $this->site->taxAttrPercentageByPurchaseTaxId($_tax->attr_code, $summary['tax_rate_id'], $inv->id);
                        $_tax = ($_tax === false) ? '-' : $_tax;
                        $table .= '<td  class="text-center">' . $_tax . '</td>';
                    endforeach;
                    $table .= '<td class="text-right">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                endforeach;
            endif;
        }
        $table .= '</tbody></tfoot>';
        // print_r($taxItems);
        if (isset($taxItems) && is_array($taxItems) && count($taxItems) && $Settings->tax_classification_view__purchase == 1):
            foreach ($taxItems as $_tax) {
                $table .= '<tr><td colspan="' . $tax_sum_colspan . '" class="text-right">' . $_tax->attr_code . ' ( ' . $_tax->attr_name . ' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
            }
        endif;

        $table .= '<tr>'
                . '<th colspan="' . $tax_sum_colspan . '" class="text-right">' . lang('total_tax_amount') . '</th>'
                . '<th class="text-right">' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</th>'
                . '</tr>'
                . '</table>';
        return $table;
    }

    public function quoteTaxInvvoiceTabel($tax_summary, $taxItems, $inv, $Settings, $isBorder = NULL) {

        $tclass = !empty($isBorder) ? 'table-bordered' : '';
        $table = '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table ' . $tclass . ' table-condensed">';
        if (!empty($tax_summary)) {
            $tax_sum_colspan = is_array($taxItems) ? count($taxItems) + 3 : 3;
            $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';
            if (!empty($taxItems)) {
                foreach ($taxItems as $_tax):
                    $table .= '<th  class="text-center">' . $_tax->attr_code . '</th>';
                endforeach;
            }
            $table .= '<th  class="text-right">' . lang('qty') . '</th><th  class="text-right">' . lang('tax_excl') . '</th><th  class="text-right">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';
            foreach ($tax_summary as $summary) :
                $table .= '<tr><td>' . $summary['name'] . '</td>';
                if (!empty($taxItems)) {
                    foreach ($taxItems as $_tax):
                        $attr_code = $_tax->attr_code;
                        $_tax = $this->site->taxAttrPercentageByQuoteTaxId($attr_code, $summary['tax_rate_id'], $inv->id);
                        $_tax = ($_tax === false) ? '-' : $_tax . '%';
                        //$_tax =  ($_tax===false)?'-':$_tax;
                        //$table .=  '<td  class="text-center">' .  $_tax. '</td>';
                        $_amt = $this->site->taxAttrAmtByQuoteTaxId($attr_code, $summary['tax_rate_id'], $inv->id);
                        $_amt = ($_amt === false) ? '' : '(' . $this->sma->formatMoney($_amt) . ')';
                        $table .= '<td  class="text-center">' . $_tax . ' ' . $_amt . '</td>';
                    endforeach;
                }
                $table .= '<td class="text-right">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
            endforeach;
        }
        $table .= '</tbody></tfoot>';

        if (isset($taxItems) && is_array($taxItems) && count($taxItems) && $Settings->tax_classification_view == 1):
            foreach ($taxItems as $_tax) {
                $table .= '<tr><td colspan="' . $tax_sum_colspan . '" class="text-right">' . $_tax->attr_code . ' ( ' . $_tax->attr_name . ' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
            }
        endif;

        $table .= '<tr>'
                . '<th colspan="' . $tax_sum_colspan . '" class="text-right">' . lang('total_tax_amount') . '</th>'
                . '<th class="text-right">' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</th>'
                . '</tr>'
                . '</table>';
        return $table;
    }

    public function taxAttrTBL($itemTaxes, $id, $total_column_offset) {
        $table_body = '';
        $order_tax_label = lang("tax");
        $taxConfig = isset($itemTaxes[$id]) ? $itemTaxes[$id] : NULL;
        if (is_array($taxConfig)):
            $table_body = $table_body . '<tr><th colspan="' . $total_column_offset . '"><table class="attr_table" style="width:30%;font-weight:normal;">';
            foreach ($taxConfig as $taxKey => $taxData) {
                if($taxData->attr_code == 'IGST'){
                $table_body = $table_body . '<tr>'
                        . '<th>' . $order_tax_label . '</th>'
                        . '<td>' . $taxKey . '</td>'
                        . '<td>' . (float) $taxData->attr_per . '%</td>'
                        . '<td>' . $this->formatMoney($taxData->amt) . '</td>'
                        . '</tr>';
                }else{
                 $table_body = $table_body . '<span style="font-weight: 100;"> '. $taxKey 
                              .' &nbsp; '.(float) $taxData->attr_per.'% '
                              .' &nbsp; '.$this->formatMoney($taxData->amt).'</span>';
               }
            }
            $table_body = $table_body . '</table></th></tr>';
        endif;
        return $table_body;
    }

    /* 26-11
      public function taxAttrTBLInline($itemTaxes,$id,$total_column_offset){
      $total_column_offset++;
      $table_body = '';
      $order_tax_label = lang("tax") ;
      $taxConfig = isset($itemTaxes[$id])?$itemTaxes[$id]:NULL;
      if(is_array($taxConfig)):
      $table_body = $table_body. '<tr><th colspan="'.$total_column_offset.'"><table class="attr_table" cellpadding="2" style="width:60%;font-weight:normal;"><tr><th colspan="'.$total_column_offset.'">'. $order_tax_label.':</th>' ;
      foreach ($taxConfig as $taxKey => $taxData) {
      $table_body = $table_body.  '<td>'.$taxKey.' ('.(float)$taxData->attr_per.'%) '.$this->formatMoney($taxData->amt).'</td>' ;
      }
      $table_body = $table_body.'</tr></table></th></tr>';
      endif;
      return $table_body;
      }
     */
    /* new */

    public function taxAttrTBLInline($itemTaxes, $id, $total_column_offset) {
        $total_column_offset++;
        $table_body = '';
        $order_tax_label = lang("tax");
        $taxConfig = isset($itemTaxes[$id]) ? $itemTaxes[$id] : NULL;
        if (is_array($taxConfig)):
            $total_column_offset_colspan = $total_column_offset - 3;
            $table_body = $table_body . '<tr><td colspan="' . $total_column_offset_colspan . '">' . $order_tax_label . ':</td>';

            foreach ($taxConfig as $taxKey => $taxData) {

                $table_body = $table_body . '<td>' . $taxKey . ' (' . (float) $taxData->attr_per . '%) ' . $this->formatMoney($taxData->amt) . '</td>';
            }
            $table_body = $table_body . '</tr>';
        endif;
        return $table_body;
    }

    public function taxAttrTblDiv($itemTaxes, $id, $total_column_offset) {
        $order_tax_label = lang("tax");
        $table_body = '';
        $taxConfig = isset($itemTaxes[$id]) ? $itemTaxes[$id] : NULL;
        if (is_array($taxConfig)):
            $table_body = $table_body . '<tr><td  class="text-left"  colspan="' . $total_column_offset . '">' . $order_tax_label . '<div class="attr_table" style="width: 40%;font-weight:normal;display: table;">';
            foreach ($taxConfig as $taxKey => $taxData) {
                $table_body = $table_body . '<ul style=" display: table-row;list-style-type: none;"  >'
                        . '<li   > <span style=" display: table-cell;padding: 5%;">' . $taxKey . '</span>&nbsp;&nbsp;&nbsp;<span style=" display: table-cell;padding: 5%;">' . (float) $taxData->attr_per . '%</span>&nbsp;&nbsp;&nbsp;<span  style=" display: table-cell;padding: 5%;">' . $this->formatMoney($taxData->amt) . '</span></li>'
                        . '</ul>';
            }
            $table_body = $table_body . '</div></td><td class="text-right"> </td></tr>';
        endif;
        return $table_body;
    }

    public function validPromoDate($start_date, $end_date) {
        if ($start_date == $end_date):
            return false;
        endif;

        $ci = get_instance();
        $config = $ci->config;
        $IST_OFFSET = isset($config->config['IST_OFFSET']) ? $config->config['IST_OFFSET'] : 0;

        $sDate = $this->fld($this->input->post('start_date')) . ":00";
        $eDate = $this->fld($this->input->post('end_date')) . ":00";
        if (empty($sDate) || empty($eDate)):
            return false;
        endif;

        $stime = strtotime($sDate);
        $etime = strtotime($eDate);
        $ctime = time();


        if ($etime < $ctime || $etime < $stime):
            return false;
        endif;

        return true;
    }

    public function dbSavedValue($arr, $val) {

        $arr1 = array_keys($arr);
        if (!in_array($val, $arr1)) {
            return "Note: Saved state value is $val ";
        }
        return false;
    }

    public function getStateFromStateCode($code) {
        return $this->site->getStateFromStateCode($code);
    }

    public function cron_group_member($member) {
        $tablelist = '';
        foreach ($member as $key => $memberData) :
            $chkOpt = '';
            if (is_numeric($selectedMember)) {
                $chkOpt = in_array($memberData->id, $selectedMember) ? 'checked' : '';
            }
            if ($key == 1) {
                $wic = '<li class="col-sm-3 col-xs-12"><label style="color:#000;font-size: 12px;" for="mem_' . $memberData->id . '" ><input type="checkbox" class="multi-select input-xs" name="customer_id[]" value="' . $memberData->id . '"  id="mem_' . $memberData->id . '" ' . $chkOpt . '> ' . ucfirst($memberData->name) . '</label></li>';
            } else {
                $tablelist = $tablelist . '<li class="col-sm-3 col-xs-12"><label style="color:#000;font-size: 12px;" for="mem_' . $memberData->id . '" ><input type="checkbox" class="multi-select input-xs" name="customer_id[]" value="' . $memberData->id . '"  id="mem_' . $memberData->id . '" ' . $chkOpt . '> ' . ucfirst($memberData->name) . '</label></li>';
                ;
            }
        endforeach;
        $table = '<ul id="group_member_list">' . $wic . $tablelist . '</ul>';
        return $table;
    }

    public function get_financial_year($date = '', $short_year = 0) {

        $date = $date ? $date : date('Y-m-d');
        $obj_date = date_create($date);

        $year_format = $short_year ? 'y' : 'Y';

        if (date_format($obj_date, "m") >= 4) {//On or After April (FY is current year - next year)
            $financial_year = (date_format($obj_date, $year_format)) . '-' . (date_format($obj_date, $year_format) + 1);
        } else {//On or Before March (FY is previous year - current year)
            $financial_year = (date_format($obj_date, $year_format) - 1) . '-' . date_format($obj_date, $year_format);
        }

        return $financial_year;
    }

    /**
     * Get Invoice No
     * @return int
     */
    public function getinvoiceNo($date) {

        $date = $date?$date :date('Y-m-d');
        $obj_date = date_create($date);

        $year_format = $date?date('y',strtotime($date)) :'y';

        if (date_format($obj_date, "m") >= 4) {//On or After April (FY is current year - next year)
            $financial_year = (date_format($obj_date, $year_format)) . '-' . (date_format($obj_date, $year_format) + 1);
        } else {//On or Before March (FY is previous year - current year)
            $financial_year = (date_format($obj_date, $year_format) - 1) . '-' . date_format($obj_date, $year_format);
        }
        $gettype = $this->db->select('financial_type')->where(['setting_id' => '1'])->get('sma_settings')->row();

        if ($gettype->financial_type == 'M') {

            $checkFinancial = $this->db->where(['financial_year' => $financial_year, 'financial_month' => date('m',strtotime($date))])->get('sma_financial_year')->row();
            if ($this->db->affected_rows() > 0) {
                $invoice_no = $checkFinancial->invoice_no + 1;
                $this->db->where(['id' => $checkFinancial->id])->update('sma_financial_year', ['invoice_no' => $invoice_no]);
            } else {
                $invoice_no = 1;
                $this->db->insert('sma_financial_year', ['invoice_no' => $invoice_no, 'financial_year' => $financial_year, 'financial_month' => date('m', strtotime($date))]);
            }

            if ($gettype->invoice_format == 'y-m-prepend-inv' || $gettype->invoice_format == 'y-m-inv') {
                $invoice_no = $invoice_no;
            } else {
                $invoice_no = date('m', strtotime($date)) . '/' . $invoice_no;
            }
        } else if ($gettype->financial_type == 'C') {

            $checkFinancial = $this->db->where(['financial_year' => $financial_year, 'financial_month' => NULL])->get('sma_financial_year')->row();
            if ($this->db->affected_rows() > 0) {
                $invoice_no = $checkFinancial->invoice_no + 1;
                $this->db->where(['id' => $checkFinancial->id])->update('sma_financial_year', ['invoice_no' => $invoice_no]);
            } else {
                $continue = $this->db->order_by('id', 'DESC')->get('sma_financial_year')->row();
                $invoice_no = (($continue) ? $continue->invoice_no : '0') + 1;
                $this->db->insert('sma_financial_year', ['invoice_no' => $invoice_no, 'financial_year' => $financial_year]);
            }
        } else {
            $checkFinancial = $this->db->where(['financial_year' => $financial_year, 'financial_month' => NULL])->get('sma_financial_year')->row();
            if ($this->db->affected_rows() > 0) {
                $invoice_no = $checkFinancial->invoice_no + 1;
                $this->db->where(['id' => $checkFinancial->id])->update('sma_financial_year', ['invoice_no' => $invoice_no]);
            } else {
                $invoice_no = 1;
                $this->db->insert('sma_financial_year', ['invoice_no' => $invoice_no, 'financial_year' => $financial_year]);
            }
        }

        return $invoice_no;
    }

    public function invoice_format($invoice_no, $date) {

        $invoice_no = $this->getinvoiceNo($date);

        $invoice_format = $this->Settings->invoice_format ? $this->Settings->invoice_format : 'inv';

        $prepend = $this->Settings->invoice_length ? $this->Settings->invoice_length : 4;

        switch ($invoice_format) {

            //154
            case 'inv':
                return $invoice_no;
                break;

            //19-20/154
            case 'short-fy-inv':
                $fy = $this->get_financial_year($date, $short_year = 1);
                return $fy . '/' . $invoice_no;
                break;

            //2019-2020/154
            case 'long-fy-inv':
                $fy = $this->get_financial_year($date, $short_year = 0);
                return $fy . '/' . $invoice_no;
                break;

            //2020/01/154
            case 'y-m-inv':
                $date = $date ? $date : date('Y-m-d');
                $obj_date = date_create($date);
                $ym = date_format($obj_date, "Y/m");
                return $ym . '/' . $invoice_no;
                break;

            // 000154          
            case 'prepend-inv':
                return sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            //19-20/000154
            case 'short-fy-prepend-inv':
                $fy = $this->get_financial_year($date, $short_year = 1);
                return $fy . '/' . sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            //2019-2020/000154
            case 'long-fy-prepend-inv':
                $fy = $this->get_financial_year($date, $short_year = 0);
                return $fy . '/' . sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            //2020/01/000154
            case 'y-m-prepend-inv':
                $date = $date ? $date : date('Y-m-d');
                $obj_date = date_create($date);
                $ym = date_format($obj_date, "Y/m");
                return $ym . '/' . sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            default:
                return $invoice_no;
                break;
        }//end switch
    }

    function taxArr_rate($tax_rate_id, $_net_unit_price, $_qty, $itemId = NULL, $saleID = NULL, $type = NULL) {
        $_net_unit_price = isset($_net_unit_price) ? $_net_unit_price : 0;
        $_qty = isset($_qty) ? $_qty : 0;
        $_tax = $this->site->getTaxRateByID($tax_rate_id);
        $tax_config = isset($_tax->tax_config) ? $_tax->tax_config : '';
        $tax_config = !empty($tax_config) ? unserialize($tax_config) : NULL;

        if (empty($tax_config)):
            return false;
        endif;

        if (!is_array($tax_config)):
            return false;
        endif;
        $insert_data = array();

        foreach ($tax_config as $taxID => $tax):
            $taxArr['item_id'] = !empty((int) $itemId) ? $itemId : 0;
            $taxcode = $tax_config[$taxID]['code'];
            $tax_per = $tax_config[$taxID]['percentage'];
            $taxArr['attr_code'] = $tax_config[$taxID]['code'];
            $taxArr['attr_name'] = $tax_config[$taxID]['name'];

            $taxArr['attr_per'] = $tax_config[$taxID]['percentage'];
            $taxArr['tax_amount'] = ($_net_unit_price * ((float) $tax_config[$taxID]['percentage'] / 100)) * ($_qty);
            $taxArr[$tax_per] = $tax_config[$taxID]['percentage'];
            $taxArr[$taxcode] = ($_net_unit_price * ((float) $tax_config[$taxID]['percentage'] / 100)) * ($_qty);
            $insert_data[] = $taxArr;
        endforeach;

        return $insert_data;
    }

    /* 20-02-2020 for deleted data */

    function storeDeletedData($TableName, $columnId, $DeletedId, $isParentInsert = 1) {
        if ($isParentInsert == 1) {
            $ResultArray = $this->site->getTableDatas($TableName, $columnId, $DeletedId);
            if (!empty($ResultArray)) {
                $ArrInsert = array(
                    'table_name' => 'sma_' . $TableName,
                    'deleted_id' => $DeletedId,
                    'deleted_related_data' => json_encode($ResultArray),
                );
                $this->site->insertTableData($ArrInsert);
            }
        }

        $ChildTableArr = array();
        switch ($TableName) {
            case "warehouses":
                $ChildTableArr = $this->site->getTableDatas('warehouses_products', 'warehouse_id', $DeletedId);
                $this->insertChildDataById($ChildTableArr, $TableName, $DeletedId);
                break;
            case "units":
                $ChildTableArr = $this->site->getTableDatas('units', 'base_unit', $DeletedId);
                $this->insertChildDataById($ChildTableArr, $TableName, $DeletedId);
                break;
            case "products":
                $Arr1 = array('transfer_items', 'purchase_items', 'order_items', 'sale_items', 'quote_items');
                foreach ($Arr1 as $vals1) {
                    $Res = $this->site->getTableDatas($vals1, 'product_id', $DeletedId);
                    if (!empty($Res)) {
                        return 'created';
                    }
                }
                $Arr = array('warehouses_products', 'warehouses_products_variants', 'product_variants', 'product_photos', 'product_prices');
                foreach ($Arr as $vals) {
                    $ChildTableArr = $this->site->getTableDatas($vals, 'product_id', $DeletedId);
                    $this->insertChildDataById($ChildTableArr, $vals, $DeletedId);
                }
                break;
            case "sales":
                $ResultArray = $this->site->getTableDatas($TableName, $columnId, $DeletedId);
                $ArrSales = array('data' => $ResultArray);
                if ($isParentInsert == 1) {
                    $Arr = array('sale_items', 'costing', 'sales_items_tax', 'sales', 'payments');
                    foreach ($Arr as $vals) {
                        $ChildTableArr = $this->site->getTableDatas($vals, 'sale_id', $DeletedId);
                        $ArrSales[$vals] = $ChildTableArr;
                        $this->insertChildDataById($ChildTableArr, $vals, $DeletedId);
                    }
                } else {
                    $Arr = array('sale_items', 'costing', 'sales_items_tax');
                    foreach ($Arr as $vals) {
                        $ChildTableArr = $this->site->getTableDatas($vals, 'sale_id', $DeletedId);
                        $ArrSales[$vals] = $ChildTableArr;
                        $this->insertChildDataById($ChildTableArr, $vals, $DeletedId);
                    }
                }

                break;
            case "purchases":
                $ResultArray = $this->site->getTableDatas($TableName, $columnId, $DeletedId);
                $ArrSales = array('data' => $ResultArray);
                if ($isParentInsert == 1) {
                    $Arr = array('purchase_items', 'purchase_items_tax', 'purchases', 'payments');
                    foreach ($Arr as $vals) {
                        $ChildTableArr = $this->site->getTableDatas($vals, 'purchase_id', $DeletedId);
                        $ArrSales[$vals] = $ChildTableArr;
                        $this->insertChildDataById($ChildTableArr, $vals, $DeletedId);
                    }
                } else {
                    $Arr = array('purchase_items', 'purchase_items_tax');
                    foreach ($Arr as $vals) {
                        $ChildTableArr = $this->site->getTableDatas($vals, 'purchase_id', $DeletedId);
                        $ArrSales[$vals] = $ChildTableArr;
                        $this->insertChildDataById($ChildTableArr, $vals, $DeletedId);
                    }
                }

                break;
        }
    }

    public function insertChildDataById($ChildTableArr, $TableName, $DeletedId) {
        if (!empty($ChildTableArr)) {
            foreach ($ChildTableArr as $val) {
                $ArrInsert = array(
                    'table_name' => 'sma_' . $TableName,
                    'deleted_id' => $val['id'],
                    'parent_id' => $DeletedId,
                    'deleted_related_data' => json_encode($val),
                );
                $this->site->insertTableData($ArrInsert);
            }
        }
    }

    public function deleteTableDataById($TableName, $DeletedId) {
        $this->site->deleteTableDataById('sma_' . $TableName, 'deleted_id', $DeletedId);
        switch ($TableName) {
            case "warehouses":
                $this->site->deleteTableDataById('sma_warehouses_products', 'parent_id', $DeletedId);
                break;
            case "units":
                $this->site->deleteTableDataById('sma_units', 'parent_id', $DeletedId);
                break;
            case "products":
                $Arr = array('warehouses_products', 'warehouses_products_variants', 'product_variants', 'product_photos', 'product_prices');
                foreach ($Arr as $val) {
                    $this->site->deleteTableDataById('sma_' . $val, 'parent_id', $DeletedId);
                }
                break;
        }
    }

    /* end 20-02-2020 for deleted data */

    /* NEW INVOICE CSGT AND IGST */

    function taxInvoiceTableCSI($tax_summary, $inv, $return_sale, $Settings, $isBorder = NULL) {
        $tclass = ($isBorder) ? 'table-bordered' : '';
        $sale_id = $inv->id;
        $CGST = 0;
        $SGST = 0;
        $IGST = 0;
        $table = '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table ' . $tclass . ' table-condensed table-responsive" margin:0px!important; width:80%;>';
        if (!empty($tax_summary)) {

            $tax_sum_colspan = (isset($taxItems) && is_array($taxItems)) ? count($taxItems) + 3 : 3;
            $table .= '<thead><tr><th> HSN </th><th  class="text-center">' . lang('name') . '</th>';
            $table .= '<th  class="text-center">' . lang('CGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('SGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('IGST') . '(%)</th>';

            $table .= '<th class="text-right" style="text-align: center;">' . lang('Qty/Wt') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_excl') . '</th><th class="text-right" style="text-align: center;">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';

            if (is_array($tax_summary)) {
                $rate = array_column($tax_summary, 'rate');

                array_multisort($rate, SORT_ASC, $tax_summary);
                foreach ($tax_summary as $summary) :
                    //  if(round($summary['rate'])){ 
                    $rate = round($summary['rate']) / 2;
                    $table .= '<tr><td>'.$summary['hsn_code'].' </td><td style="text-align: right;">' . $summary['name'] . '</td>';
                    $row = $this->site->getSItemsTaxes($sale_id, $rate);

                    if ($row == false) {
                        $rate = round($summary['rate']);
                        $row = $this->site->getSItemsTaxes($sale_id, $rate);
                    }

                    if (is_array($row)) {
                        foreach ($row as $item):
                            $table .= '<td class="text-right">' . (($item->CGST != 0) ? number_format($item->gst_rate,1) : 0) . '%</td>';
                            $table .= '<td class="text-right">' . (($item->SGST != 0) ? number_format($item->gst_rate,1) : 0) . '%</td>';
                            //if($item->IGST > 0){
                            $table .= '<td class="text-right">' . (($item->IGST != 0) ? number_format($item->gst_rate,1) : 0) . '%</td>';
                            //}    
                        endforeach;
                    }else {
                        $table .= '<td>0%</td><td>0%</td><td>0%</td>';
                    }

                    $table .= '<td class="text-right" style="text-align: right;">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right" style="text-align: right;">' . $this->sma->formatMoney($summary['amt']) . ' </td><td class="text-right" style="text-align: right;">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                    //  } 
                endforeach;
            }//end if
        }
        $table .= '</tbody></table><div class="row" style="margin:2px 6px !important;">';

        if (isset($inv) && $Settings->tax_classification_view == 1):
            $colsize = 'col-lx-3 col-md-3 col-sm-3 col-xs-3';
            //$row_amt = $this->site->getTotalItemsTaxes($sale_id);//

            $row_sale = $this->site->getSaleTaxes($sale_id);
            $row_return = $this->site->getReturnSaleTaxes($sale_id);
            $CGST = $row_return[0]->CGST? $row_return[0]->CGST : $row_sale[0]->CGST;
            $SGST = $row_return[0]->SGST?$row_return[0]->SGST : $row_sale[0]->SGST;
            $IGST = $row_sale[0]->IGST + $row_return[0]->IGST;

            $table .= '<div class="' . $colsize . '" style=" font-size: 10px; margin-right: 18px !important; margin-left: -13px !important;">' . lang('CGST') . '&nbsp;' . (($CGST != 0) ? $this->sma->formatMoney($CGST) : $this->sma->formatMoney(0)) . '</div>';
            $table .= '<div class="' . $colsize . '" style="font-size: 10px; margin-right: 18px !important;">' . lang('SGST') . '&nbsp;' . (($SGST != 0) ? $this->sma->formatMoney($SGST) : $this->sma->formatMoney(0)) . '</div>';
            if ($IGST != 0) {
                $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('IGST') . '&nbsp;' . (($IGST != 0) ? $this->sma->formatMoney($IGST) : $this->sma->formatMoney(0)) . '</div>';
            }
        //my$colsize = (count($taxItems) == 2) ? 'col-lx-3 col-md-3 col-sm-3 col-xs-3' : (($isBorder == '1') ? 'col-lx-2 col-md-2 col-sm-2 col-xs-2' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3' );
        /* foreach ($row_amt as $item) {

          //$table .= '<tr><td colspan="'.$tax_sum_colspan.'" class="text-right" >' .$_tax->attr_code.' ( '.$_tax->attr_name.' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
          $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('CGST') . '&nbsp; Rs.' . (($item->CGST != 0) ? number_format($item->CGST, 2) : 0) . '</div>';
          $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('SGST') . '&nbsp; Rs.' . (($item->SGST != 0) ? number_format($item->SGST, 2) : 0) . '</div>';
          if($item->IGST != 0){
          $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('IGST') . '&nbsp; Rs.' . (($item->IGST != 0) ? number_format($item->IGST, 2) : 0) . '</div>';
          }
          } */

        endif;

        $table .= '<div class="' . $colsize . '" style="font-size: 10px; margin-right: -15px !important;">' . lang('Total&nbsp;Tax') . '&nbsp;' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</div></div>';

        return $table;
    }

    function purchaseTaxInvoiceTableCSI($tax_summary, $inv, $return_sale, $Settings, $isBorder = NULL) {
        $tclass = !empty($isBorder) ? 'table-bordered' : '';
        $purchase_id = $inv->id;
        //echo $inv->igst;
        $table = '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table table-bordered table-condensed table-responsive" margin:0px!important;>';
        if (!empty($tax_summary)) {
            $tax_sum_colspan = (isset($taxItems) && is_array($taxItems)) ? count($taxItems) + 3 : 6;

            $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';
            $table .= '<th  class="text-center">' . lang('CGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('SGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('IGST') . '(%)</th>';

            $table .= '<th  class="text-right">' . lang('Qty/Wt') . '</th><th  class="text-right">' . lang('tax_excl') . '</th><th  class="text-right">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';
            if (is_array($tax_summary)):
                foreach ($tax_summary as $summary) :
                    if ($inv->igst != 0) {
                        $rate = round($summary['rate']);
                    } else {
                        $rate = round($summary['rate']) / 2;
                    }
                    $table .= '<tr><td style="text-align: center;">' . $summary['name'] . '</td>';
                    $row = $this->site->getPurchaseItemsTaxes($purchase_id, $rate);
                    if (is_array($row)) {
                        foreach ($row as $item):
                            $table .= '<td class="text-center">' . (($item->CGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                            $table .= '<td class="text-center">' . (($item->SGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                            //if($item->IGST > 0){
                            $table .= '<td class="text-center">' . (($item->IGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '%</td>';
                            //}    
                        endforeach;
                    }else {
                        $table .= '<td>0%</td><td>0%</td><td>0%</td>';
                    }

                    $table .= '<td class="text-right">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                endforeach;
            endif;
        }
        $table .= '</tbody></table><div class="row" style="margin:2px -15px !important;">';

        if (isset($inv) && $Settings->tax_classification_view__purchase == 1):
            $colsize = 'col-lx-3 col-md-3 col-sm-3 col-xs-3';
            $row_amt = $this->site->getPurchaseTotalItemsTaxes($purchase_id);
            //my$colsize = (count($taxItems) == 2) ? 'col-lx-3 col-md-3 col-sm-3 col-xs-3' : (($isBorder == '1') ? 'col-lx-2 col-md-2 col-sm-2 col-xs-2' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3' );
            foreach ($row_amt as $item) {
                //$table .= '<tr><td colspan="'.$tax_sum_colspan.'" class="text-right" >' .$_tax->attr_code.' ( '.$_tax->attr_name.' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
                $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('CGST') . '&nbsp; Rs.' . (($item->CGST != 0) ? $this->sma->formatMoney($item->CGST) : $this->sma->formatMoney(0)) . '</div>';
                $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('SGST') . '&nbsp; Rs.' . (($item->SGST != 0) ? $this->sma->formatMoney($item->SGST) : $this->sma->formatMoney(0)) . '</div>';
                if ($item->IGST != 0) {
                    $table .= '<div class="' . $colsize . '" style="margin:0px !important;">' . lang('IGST') . '&nbsp; Rs.' . (($item->IGST != 0) ? $this->sma->formatMoney($item->IGST) : $this->sma->formatMoney(0)) . '</div>';
                }
            }
        endif;

        $table .= '<div class="' . $colsize . '" style="margin-left:-15px !important;">' . lang('Total&nbsp;Tax') . '&nbsp;' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</div></div>';

        return $table;
    }

    public function quoteTaxInvoiceTableCSI($tax_summary, $inv, $Settings, $isBorder = NULL) {
        // print_r($inv);                
        $tclass = !empty($isBorder) ? 'table-bordered' : '';
        $quote_id = $inv->id;
        $table = '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
        $table .= '<table class="table table-bordered table-condensed table-responsive" margin:0px!important;>';
        if (!empty($tax_summary)) {
            $tax_sum_colspan = is_array($taxItems) ? count($taxItems) + 3 : 6;
            + $table .= '<thead><tr><th  class="text-center">' . lang('name') . '</th>';

            $table .= '<th  class="text-center">' . lang('CGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('SGST') . '(%)</th>';
            $table .= '<th  class="text-center">' . lang('IGST') . '(%)</th>';

            $table .= '<th   class="text-center">' . lang('Qty/Wt') . '</th><th  class="text-right">' . lang('tax_excl') . '</th><th  class="text-right">' . lang('tax_amt') . '</th></tr>'
                    . '</thead>'
                    . '<tbody>';
            foreach ($tax_summary as $summary) :

                /* if (!empty($taxItems)) {
                  foreach ($taxItems as $_tax):
                  $attr_code = $_tax->attr_code;
                  $_tax = $this->site->taxAttrPercentageByQuoteTaxId($attr_code, $summary['tax_rate_id'], $inv->id);
                  $_tax = ($_tax === false) ? '-' : $_tax . '%';
                  //$_tax =  ($_tax===false)?'-':$_tax;
                  //$table .=  '<td  class="text-center">' .  $_tax. '</td>';
                  $_amt = $this->site->taxAttrAmtByQuoteTaxId($attr_code, $summary['tax_rate_id'], $inv->id);
                  $_amt = ($_amt === false) ? '' : '(' . $this->sma->formatMoney($_amt) . ')';
                  $table .= '<td  class="text-center">' . $_tax . ' ' . $_amt . '</td>';
                  endforeach;
                  } */
                if ($inv->igst != 0) {
                    $rate = round($summary['rate']);
                } else {
                    $rate = round($summary['rate']) / 2;
                }
                $table .= '<tr><td style="text-align: center;">' . $summary['name'] . '</td>';
                $row = $this->site->getQuoteItemsTaxes($quote_id, $rate);

                if (is_array($row)) {
                    foreach ($row as $item):
                        $table .= '<td class="text-center">' . (($item->CGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '% <br>(' . (($item->CGST != 0) ? $this->sma->formatMoney($item->CGST) : 0) .
                                ') </td>';
                        $table .= '<td class="text-center">' . (($item->SGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '% <br>(' . (($item->SGST != 0) ? $this->sma->formatMoney($item->SGST) : 0) . ') </td>';
                        //if($item->IGST > 0){
                        $table .= '<td class="text-center">' . (($item->IGST != 0) ? $this->sma->formatDecimal($item->gst_rate) : 0) . '% <br>(' . (($item->IGST != 0) ? $this->sma->formatMoney($item->IGST) : 0) . ')  </td>';
                        //}    
                    endforeach;
                }else {
                    $table .= '<td>0%</td><td>0%</td><td>0%</td>';
                }
                $table .= '<td class="text-center">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
            endforeach;
        }
        $table .= '</tbody></tfoot>';
        /*
          if (isset($taxItems) && is_array($taxItems) && count($taxItems) && $Settings->tax_classification_view == 1):
          foreach ($taxItems as $_tax) {
          $table .= '<tr><td colspan="' . $tax_sum_colspan . '" class="text-right">' . $_tax->attr_code . ' ( ' . $_tax->attr_name . ' ) ' . '</td><th class="text-right">' . $this->sma->formatMoney($_tax->amt) . '</th></tr>';
          }
          endif;
         */
        if (isset($inv) && $Settings->tax_classification_view__purchase == 1):
            $row_amt = $this->site->getQuoteTotalItemsTaxes($quote_id);
            //my$colsize = (count($taxItems) == 2) ? 'col-lx-3 col-md-3 col-sm-3 col-xs-3' : (($isBorder == '1') ? 'col-lx-2 col-md-2 col-sm-2 col-xs-2' : 'col-lx-3 col-md-3 col-sm-3 col-xs-3' );
            foreach ($row_amt as $item) {
                $table .= '<tr><td colspan="' . $tax_sum_colspan . '" class="text-right">' . lang('CGST') . ' </td><th class="text-right">' . (($item->CGST != 0) ? $this->sma->formatMoney($item->CGST) : 0) . '</th></tr>';
                $table .= '<tr><td colspan="' . $tax_sum_colspan . '" class="text-right">' . lang('SGST') . ' </td><th class="text-right">' . (($item->SGST != 0) ? $this->sma->formatMoney($item->SGST) : 0) . '</th></tr>';
                if ($item->IGST != 0) {
                    $table .= '<tr><td colspan="' . $tax_sum_colspan . '" class="text-right">' . lang('IGST') . ' </td><th class="text-right">' . (($item->IGST != 0) ? $this->sma->formatMoney($item->IGST) : 0) . '</th></tr>';
                }
            }
        endif;
        $table .= '<tr>'
                . '<th colspan="' . $tax_sum_colspan . '" class="text-right">' . lang('total_tax_amount') . '</th>'
                . '<th class="text-right">' . $this->sma->formatMoney($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax) . '</th>'
                . '</tr>'
                . '</table>';
        return $table;
    }

    /* my 5-14-2020 */

    public function posBillTableCSI($printer, $inv, $return_sale, $rows, $return_rows, $salestax, $class = null, $print = NULL) {
        $itemTaxes = isset($inv->rows_tax) ? $inv->rows_tax : array();
        $column_id_str = isset($printer->column_id_str) && !empty($printer->column_id_str) ? $printer->column_id_str : '';
        $column_name_str = isset($printer->column_name_str) && !empty($printer->column_name_str) ? $printer->column_name_str : '';
        $data = isset($printer->data) && !empty($printer->data) ? $printer->data : '';
        $optionDetails = isset($printer->optionDetails) && !empty($printer->optionDetails) ? $printer->optionDetails : '';
        $saving = 0;
        if (empty($column_id_str) || empty($column_name_str) || empty($data)):
            return false;
        endif;

        $crop_product_name = $printer->crop_product_name;
        $show_sr_no = $printer->show_sr_no;
        $column_id_arr = explode(',', $column_id_str);
        $column_name_arr = explode(',', $column_name_str);
        $data_arr = explode(',', $data);

        $table_header = '';
        if (count($column_id_arr) != count($column_name_arr) || count($column_id_arr) != count($data_arr)):
            return false;
        endif;
        $column_cnt = count($column_id_arr);
        $column_cnt = ($show_sr_no == 1) ? $column_cnt + 1 : $column_cnt;
        $total_column_offset = $column_cnt - 1;

        /* ------------------------------------------------HEADER--------------------------------------  */
        $sr_th = ($show_sr_no == 1) ? '<th style="text-align:left;" class="">Sr.No</th>' : '';
        $ColColumn = 0;
        foreach ($column_name_arr as $column_key => $column_name):
            if (!empty($column_name)):
                $table_header = $table_header . '<th style="text-align:left;" class="' . $data_arr[$column_key] . '">' . $column_name . '</th>';
                $ColColumn++;
            endif;
        endforeach;
        $tableHeader = '<thead><tr>' . $sr_th . $table_header . '</tr></thead>';

        $mrp_total = $qty_total =$article_qty=$making_charges= $weight_total = $quantity_total = $discount_total = $tax_total = $net_total = $unit_total = $total_net_price = $totalprice = $totalmrp = 0;
        $item_arr = $item_return_arr = '';
        /* ------------------------------------------------HEADER End--------------------------------------  */

        /* ------------------------------------------------Table Body --------------------------------------  */
        $table_body = '';
        $sr = $r = 0;
        $taxAttr = $taxAttrName = array();
        $OldCat = '';
        foreach ($rows as $row) {
            if ($this->pos_settings->display_category == 1) {
                if ($OldCat != $row->category_id) {
                    $table_body = $table_body . '<tr><td colspan="' . $ColColumn . '" style="font-weight:bold;">' . $row->category_name . '</td></tr>';
                }
            }
            $VariantPrice = 0;
            if ($row->option_id != 0) {
                $VariantPrice = (isset($row->variant_price) && $row->variant_price) ? $row->variant_price : 0;
            }
            $sr++;
            $table_body = $table_body . '<tr>';
            $sr_td = ($show_sr_no == 1) ? '<td class="">' . $sr . '</td>' : '';
            $table_body = $table_body . $sr_td;
            $i = 0;
            foreach ($data_arr as $data):

                $id = $column_id_arr[$i];
                $obj = $optionDetails[$id];

                if ($i == 0):
                    $append_product_name = '';
                    if ($row->option_id != 0) {
                        $append_product_name .= '(' . $row->variant . ')';
                    }
                    if ($printer->append_taxval_in_productname):
                        $row->tax = empty($row->tax) ? 0 : $row->tax;
                        $taxVal = number_format(str_replace('%', '', $row->tax), 0);
                        $append_product_name .= '<br/>Tax: ' . $taxVal . '%';
                    endif;

                    if ($printer->append_product_code_in_name && !empty($row->product_code)):
                        $append_product_name .= '<br/>Code: ' . $row->product_code;
                    endif;

                    if ($printer->append_hsn_code_in_name && !empty($row->hsn_code)):
                        $append_product_name .= '<br/>HSN: ' . $row->hsn_code;
                    endif;

                    if ($printer->append_note_in_name && !empty($row->note)):
                        $append_product_name .= '<br/>Note: ' . $row->note;
                    endif;

                    $prod_name = ($crop_product_name == 1) ? character_limiter($row->$data, 20) : $row->$data;
                    $res = strtolower('<span ' . ($printer->product_name_bold ? 'style="font-weight:bold"' : '') . '>' . $prod_name . '</span>' . $append_product_name);

                    //Show/Hide Combo Product Items
                    if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                        if ($printer->show_combo_products_list) {
                            $res .= " + (";
                            foreach ($row->combo_items as $comk => $comv) {
                                $res .= $comv->name . " Qty." . (int) $comv->qty . "  , ";
                            }
                            $res = trim($res, ", ");
                            $res .= ")";
                        }//end if.
                    }

                    //Show/Hide Invoice Product Image
                    if ($printer->show_product_image == 1) {
                        $itemImage = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/' . $row->image;
                        $image_size = ($printer->product_image_size != '') ? $printer->product_image_size : 'width:40px;height:40px;';
                        if (file_exists($itemImage)) {
                            $imgTag = '<img src="' . base_url($itemImage) . '" style="' . $image_size . 'margin-right:5px;" alt="' . $row->image . '" align="left" /> ';
                        } else {
                            $imgTag = '<img src="' . base_url('assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/no_image.png') . '" style="' . $image_size . 'margin-right:5px;" align="left" alt="no_image" /> ';
                        }
                    } else {
                        $imgTag = '';
                    }
                    $table_body = $table_body . '<td style="text-transform: capitalize; text-align:left;">' . $imgTag . $res . '</td>';
                elseif ($data == 'unit_price'):
                    $table_body = $table_body . '<td  style="text-align:right;">' . $this->custom_format($row->unit_price, $obj->format) . '</td>';
                elseif ($data == 'real_unit_price'):
                    $table_body = $table_body . '<td >' . $this->custom_format(($row->real_unit_price + $VariantPrice), $obj->format) . '</td>';
                elseif ($data == 'Article_Qty'):
                    $table_body = $table_body . '<td style="text-transform: capitalize; text-align:center;">' . $this->custom_format(($row->article_qty), $obj->format) . '</td>';
                elseif ($data == 'Making_charges'):
                    $table_body = $table_body . '<td style="text-transform: capitalize; text-align:right;">' . $this->custom_format(($row->making_charges), $obj->format) . '</td>';
                elseif ($data == 'jewellery_price'):

                    $table_body = $table_body . '<td style="text-align:right;">' . $this->custom_format(($row->jewellery_Price), $obj->format).'</td>';

                elseif (!empty($obj->formula) && strpos($data, '|')):
                    $_data_arr = explode('|', $data);
                    $f_arr = explode('|', $obj->formula);
                    $f1_arr = explode('|', $obj->format);
                    $k = 0;
                    $res = '';
                    foreach ($_data_arr as $_key => $_data) {

                        $unit_total = ($_data == 'unit_price' && !empty($row->$_data)) ? $unit_total + $row->$_data : $unit_total;
                        $res = $res . $this->custom_format($row->$_data, $f1_arr[$_key]) . ' ' . $f_arr[$k];
                    }
                    $res = substr($res, 0, -1);
                    $table_body = $table_body . '<td>' . $res . '</td>';
                else :
                      
                    $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity','quantity', 'unit_price', 'real_unit_price', 'net_price', 'invoice_net_unit_price', 'invoice_total_net_unit_price', 'net_unit_price', 'item_tax', 'item_discount', 'saving_price', 'Article_Qty','Making_charges'))) ? 'text-left' : '';
                    $mrp_total = ($data == 'mrp' && !empty($row->$data)) ? $mrp_total + $row->$data : $mrp_total;
                    $net_total = ($data == 'real_unit_price' && !empty($row->$data)) ? $net_total + $row->$data : $net_total;
                    $unit_total = ($data == 'unit_price' && !empty($row->$data)) ? $unit_total + $row->$data : $unit_total;
                    $tax_total = ($data == 'item_tax' && !empty($row->$data)) ? $tax_total + $row->$data : $tax_total;
                    $discount_total = ($data == 'item_discount' && !empty($row->$data)) ? $discount_total + $row->$data : $discount_total;
                    // $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->$data : $qty_total;
                    $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->quantity : $qty_total;
                    $weight_total = ($data == 'item_weight' && !empty($row->$data)) ? $weight_total + $row->$data : $weight_total;
                    $total_net_price = ($data == 'invoice_total_net_unit_price' && !empty($row->$data)) ? $total_net_price + $row->$data : $total_net_price;
                    $totalprice = ($data == 'invoice_net_unit_price' && !empty($row->$data)) ? $totalprice + $row->$data : $totalprice;
                    $totalmrp = ($data == 'net_price' && !empty($row->$data)) ? $totalmrp + $row->$data : $totalmrp;                  
                 
                    if ($data == 'quantity') {
                        if (property_exists($row, 'quantity')) {
                            $quantity_total += $row->quantity;
                        }
                    }
                    // if ($data == 'Article_Qty') {
                    //     $article_qty += ($row->article_qty);
                    // }
                    // if ($data == 'Making_charges') {
                    //     $making_charges  += ($row->making_charges);
                    // }
                   if ($row->making_charges || $row->article_qty) {
                        $making_charges = ($row->making_charges);
                        $article_qty = ($row->article_qty);
                    }
                    if (!empty($obj->format)):
                        if ($data == 'real_unit_price') {
                            $res = $this->custom_format(($row->real_unit_price * $qty_total), $obj->format);
                        }
                        elseif($data == 'savings'){
                            
                            // $res = $this->custom_format(($row->mrp - $row->net_unit_price ), $obj->format);
                            // $saving = $saving + ($row->mrp - $row->net_unit_price);
                            // $res = $this->custom_format(($row->mrp - $row->real_unit_price ), $obj->format);
                            
                            if($row->item_discount !== "0.0000"){
                                $saving = $saving + ($row->item_discount);
                                $res = $this->custom_format(($row->item_discount), $obj->format);
                            }else{
                                $saving = $saving + ($row->mrp - $row->real_unit_price);
                                $res = $this->custom_format(($row->mrp - $row->real_unit_price ), $obj->format);
                            }
                           
                        }    
                        else {
                            $res = $this->custom_format($row->$data, $obj->format);
                        }
                    //$res = $this->custom_format($row->$data,$obj->format);
                    else:
                        $res = $row->$data;
                    endif;

                    switch ($data) {
                        case 'subtotal':
                        case 'unit_price':
                        case 'net_unit_price':
                            if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                                $total_combo_item_price = 0;
                                foreach ($row->combo_items as $comk => $comv) {
                                    $total_combo_item_price += ($comv->qty * $comv->unit_price);
                                }
                                //  $res = "<del>" .$this->custom_format($total_combo_item_price,$obj->format)."</del><br>".$res ;
                            }

                            break;
                    }
                    if ($data == 'unit_quantity' || $data == 'quantity') {
                        $table_body .= '<td class="' . $class . '" style="text-align: center;"><span ' . ($printer->qty_bold ? 'style="font-weight:bold;"' : '') . '>' . number_format($row->quantity, 2) . '</span></td>';
                        // $table_body = $table_body . '<td class="' . $class . '"><span ' . ($printer->qty_bold ? 'style="font-weight:bold"' : '') . '>' . $res . '</span></td>';
                    } else {
                        $table_body .= '<td class="' . $class . '" style="text-align: right;">' . $res . '</td>';
                    }
                endif;

                $i++;
            endforeach;

            $table_body = $table_body . '</tr>';
            $taxConfig = isset($itemTaxes[$row->id]) ? $itemTaxes[$row->id] : NULL;
            if (is_array($taxConfig)):

                /* if ($print):

                  $table_body = $table_body . $this->taxAttrTblDiv($itemTaxes, $row->id, $total_column_offset);
                  else:
                  // $table_body = $table_body . $this->taxAttrTBL($itemTaxes, $row->id, $total_column_offset);
                  $table_body = $table_body . $this->taxAttrTBLInline($itemTaxes, $row->id, $total_column_offset);
                  endif; */
                if ($print):
                    $table_body = $table_body . $this->taxAttrTblDiv_csi($row->gst_rate, $row->cgst, $row->sgst, $row->igst, $total_column_offset);
                else:
                    $table_body = $table_body . $this->taxAttrTBLInline_csi($row->gst_rate, $row->cgst, $row->sgst, $row->igst, $total_column_offset);
                endif;
            endif;
            $r++;
            $OldCat = $row->category_id;
        }
        if ($return_rows) {
            $table_body = $table_body . '<tr class="warning"><td colspan="' . $column_cnt . '" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
            $sr1 = 0;
            foreach ($return_rows as $row) {
                $sr1++;
                $table_body = $table_body . '<tr>';
                $sr_td = ($show_sr_no == 1) ? '<td class="">' . $sr1 . '</td>' : '';
                $table_body = $table_body . $sr_td;
                $i = 0;

                foreach ($data_arr as $data):

                    $id = $column_id_arr[$i];
                    $obj = $optionDetails[$id];

                    if ($i == 0):
                        $append_product_name = '';
                        if ($row->option_id != 0) {
                            //$append_product_name .= '('.$row->variant.')';
                        }
                        if ($printer->append_taxval_in_productname):
                            $row->tax = empty($row->tax) ? 0 : $row->tax;
                            $taxVal = number_format(str_replace('%', '', $row->tax), 0);
                            $append_product_name .= '<br/>Tax: ' . $taxVal . '%';
                        endif;

                        if ($printer->append_product_code_in_name && !empty($row->product_code)):
                            $append_product_name .= '<br/>Code: ' . $row->product_code;
                        endif;

                        if ($printer->append_hsn_code_in_name && !empty($row->hsn_code)):
                            $append_product_name .= '<br/>HSN: ' . $row->hsn_code;
                        endif;

                        if ($printer->append_note_in_name && !empty($row->note)):
                            $append_product_name .= '<br/>Note: ' . $row->note;
                        endif;

                        $prod_name = ($crop_product_name == 1) ? character_limiter($row->$data, 20) : $row->$data;
                        $res = strtolower('<span ' . ($printer->product_name_bold ? 'style="font-weight:bold"' : '') . '>' . $prod_name . '</span>' . $append_product_name);

                        //Show/Hide Combo Product Items
                        if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                            if ($printer->show_combo_products_list) {
                                $res .= " + (";
                                foreach ($row->combo_items as $comk => $comv) {
                                    $res .= $comv->name . " Qty." . (int) $comv->qty . "  , ";
                                }
                                $res = trim($res, ", ");
                                $res .= ")";
                            }//end if.
                        }

                        //Show/Hide Invoice Product Image
                        if ($printer->show_product_image == 1) {
                            $itemImage = 'assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/' . $row->image;
                            $image_size = ($printer->product_image_size != '') ? $printer->product_image_size : 'width:40px;height:40px;';
                            if (file_exists($itemImage)) {
                                $imgTag = '<img src="' . base_url($itemImage) . '" style="' . $image_size . 'margin-right:5px;" alt="' . $row->image . '" align="left" /> ';
                            } else {
                                $imgTag = '<img src="' . base_url('assets/mdata/'.$this->Customer_assets.'/uploads/thumbs/no_image.png') . '" style="' . $image_size . 'margin-right:5px;" align="left" alt="no_image" /> ';
                            }
                        } else {
                            $imgTag = '';
                        }
                        $table_body = $table_body . '<td style="text-transform: capitalize;">' . $imgTag . $res . '</td>';
                    elseif ($data == 'unit_price'):
                        $table_body = $table_body . '<td >' . $this->custom_format($row->unit_price, $obj->format) . '</td>';
                    elseif ($data == 'real_unit_price'):
                        $table_body = $table_body . '<td >' . $this->custom_format(($row->real_unit_price + $VariantPrice), $obj->format) . '</td>';


                    elseif (!empty($obj->formula) && strpos($data, '|')):
                        $_data_arr = explode('|', $data);
                        $f_arr = explode('|', $obj->formula);
                        $f1_arr = explode('|', $obj->format);
                        $k = 0;
                        $res = '';
                        foreach ($_data_arr as $_key => $_data) {

                            $unit_total = ($_data == 'unit_price' && !empty($row->$_data)) ? $unit_total + $row->$_data : $unit_total;
                            $res = $res . $this->custom_format($row->$_data, $f1_arr[$_key]) . ' ' . $f_arr[$k];
                        }
                        $res = substr($res, 0, -1);
                        $table_body = $table_body . '<td>' . $res . '</td>';
                    else :

                        $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity', 'unit_price', 'real_unit_price', 'net_price', 'invoice_net_unit_price', 'invoice_total_net_unit_price', 'net_unit_price', 'item_tax', 'item_discount', 'saving_price', 'article_qty','making_charges','jewellery_price'))) ? 'text-left' : '';
                        $mrp_total = ($data == 'mrp' && !empty($row->$data)) ? $mrp_total + $row->$data : $mrp_total;
                        $net_total = ($data == 'real_unit_price' && !empty($row->$data)) ? $net_total + $row->$data : $net_total;
                        $unit_total = ($data == 'unit_price' && !empty($row->$data)) ? $unit_total + $row->$data : $unit_total;
                        $tax_total = ($data == 'item_tax' && !empty($row->$data)) ? $tax_total + $row->$data : $tax_total;
                        $discount_total = ($data == 'item_discount' && !empty($row->$data)) ? $discount_total + $row->$data : $discount_total;
                        $qty_total = ($data == 'unit_quantity' && !empty($row->$data)) ? $qty_total + $row->$data : $qty_total;
                        $weight_total = ($data == 'item_weight' && !empty($row->$data)) ? $weight_total + $row->$data : $weight_total;
                        $total_net_price = ($data == 'invoice_total_net_unit_price' && !empty($row->$data)) ? $total_net_price + $row->$data : $total_net_price;
                        $totalprice = ($data == 'invoice_net_unit_price' && !empty($row->$data)) ? $totalprice + $row->$data : $totalprice;
                        $totalmrp = ($data == 'net_price' && !empty($row->$data)) ? $totalmrp + $row->$data : $totalmrp;

                        if (!empty($obj->format)):
                            if ($data == 'real_unit_price') {
                                $res = $this->custom_format(($row->real_unit_price * $qty_total), $obj->format);
                            } else {
                                $res = $this->custom_format($row->$data, $obj->format);
                            }
                        //$res = $this->custom_format($row->$data,$obj->format);
                        else:
                            $res = $row->$data;
                        endif;

                        switch ($data) {
                            case 'subtotal':
                            case 'unit_price':
                            case 'net_unit_price':
                                if (isset($row->combo_items[0]) && !empty($row->combo_items[0])) {
                                    $total_combo_item_price = 0;
                                    foreach ($row->combo_items as $comk => $comv) {
                                        $total_combo_item_price += ($comv->qty * $comv->unit_price);
                                    }
                                    //  $res = "<del>" .$this->custom_format($total_combo_item_price,$obj->format)."</del><br>".$res ;
                                }

                                break;
                        }

                        if ($data == 'unit_quantity' || $data == 'quantity') {
                            $table_body = $table_body . '<td class="' . $class . '"><span ' . ($printer->qty_bold ? 'style="font-weight:bold"' : '') . '>' . $res . '</span></td>';
                        } else {
                            $table_body = $table_body . '<td class="' . $class . '">' . $res . '</td>';
                        }
                    endif;

                    $i++;
                endforeach;
                $table_body = $table_body . '</tr>';
                $taxConfig = isset($itemTaxes[$row->id]) ? $itemTaxes[$row->id] : NULL;
                if (is_array($taxConfig)):

                    if ($print):
                        $table_body = $table_body . $this->taxAttrTblDiv($itemTaxes, $row->id, $total_column_offset);
                    else:
                        //$table_body = $table_body . $this->taxAttrTBL($itemTaxes, $row->id, $total_column_offset);
                        $table_body = $table_body . $this->taxAttrTBLInline($itemTaxes, $row->id, $total_column_offset);
                    endif;
                endif;
                //echo $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)).' ('.$this->sma->formatMoney($row->net_unit_price).' + '.$this->sma->formatMoney($row->item_tax / $row->quantity) . ')</td><td class="no-border border-bottom text-right">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';
                $r++;
            }
        }
        $tableBody = '<tbody>' . $table_body . '</tbody>';
        /* ------------------------------------------------Table Body  End--------------------------------------  */



        /* ------------------------------------------------Footer--------------------------------------  */
        $footer_row1 = $footer_row2 = $footer_row3 = $footer_row4 = $footer_row5 = $footer_row6 = '';
        $footer_row1_cell1 = ($show_sr_no == 1) ? 2 : 1;
        $footer_row1 = $footer_row1 . '<tr> ';
        $i = 0;

        foreach ($data_arr as $data):

            $id = $column_id_arr[$i];
            $obj = $optionDetails[$id];

            if ($i == 0):
                $footer_row1 = $footer_row1 . '<th colspan="' . $footer_row1_cell1 . '" >' . lang("total") . '</th>';

            elseif (!empty($obj->formula) && strpos($data, '|')):
                $_data_arr = explode('|', $data);
                $f_arr = explode('|', $obj->formula);
                $f1_arr = explode('|', $obj->format);
                $k = 0;
                $res = '';
                foreach ($_data_arr as $_key => $_data) {

                    $res = ($_data == 'unit_price' && $unit_price != 0) ? $unit_price : $res;
                }

                $footer_row1 = $footer_row1 . '<th>' . $res . '</th>';
            else :
                $class = ( in_array($data, array('mrp', 'subtotal', 'unit_quantity','quantity', 'unit_price', 'real_unit_price', 'net_price', 'invoice_net_unit_price', 'invoice_total_net_unit_price', 'item_tax', 'item_discount'))) ? 'text-left' : '';
              
                switch ($data) { 
                    // case 'unit_quantity':
                    //     $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: center;">' . $this->formatQuantity($qty_total) . '</th>';
                    //     break;

                    // case 'quantity':
                    //     $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: center;">' . $this->formatQuantity($quantity_total) . '</th>';
                    //     break;

                    // case 'item_weight':
                    //     $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatQuantity($weight_total) . 'KG</th>';
                    //     break;

                    case 'mrp':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($mrp_total) . '</th>';
                        //$footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'real_unit_price':
                        //  $footer_row1=$footer_row1.'<th class="'. $class.'">'.$this->formatMoney($net_total).'</th>';
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;"></th>';
                        break;



                    case 'unit_price':
                        //   $footer_row1=$footer_row1.'<th class="'. $class.'">'.$this->formatMoney($unit_total).'</th>';
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;"></th>';
                        break;
                    case 'invoice_total_net_unit_price':

                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($total_net_price) . '</th>';
                        break;
                    case 'invoice_net_unit_price':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($totalprice) . '</th>';
                        break;

                    case 'net_price':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($totalmrp) . '</th>';
                        break;


                    case 'item_tax':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($tax_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'item_discount':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($discount_total) . '</th>';
                        // $footer_row1=$footer_row1.'<th class="'. $class.'"></th>';
                        break;

                    case 'subtotal':
                        $footer_row1 = $footer_row1 . '<th class="" style="text-align: right;">' . $this->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax + $row->making_charges)) . '</th>';
                        break;
                    case 'savings':
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatMoney($saving) . '</th>';
                        break;
                    // case 'Article_Qty':
                    //     $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: center;">' . $this->formatQuantity($article_qty) . '</th>';
                    //     break;
                    // case 'Making_charges':
                    //     $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;">' . $this->formatQuantity($making_charges) . '</th>';
                    //     break;
                    default:
                        $footer_row1 = $footer_row1 . '<th class="' . $class . '" style="text-align: right;"> </th>';
                        break;
                }

            endif;

            $i++;
        endforeach;

        $footer_row1 = $footer_row1 . ' </tr>';

        //------------------------Order Tax ---------------------//

        if ($inv->order_tax != 0) {
            $order_tax_label = !empty($inv->order_tax_label) && ( $inv->order_tax_label != '-') ? $inv->order_tax_label : lang("tax");
            $footer_row2 = '<tr><th  colspan="' . $total_column_offset . '">' . $order_tax_label . '</th><th class="text-right">' . $this->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
        }
        //------------------------Order Tax End---------------------//
        //------------------------ Order Discount  ---------------------//
        if ($inv->order_discount != 0) {
            $footer_row3 = '<tr><th  colspan="' . $total_column_offset . '">' . lang("order_discount") . '</th><th class="text-left">' . $this->formatMoney($inv->order_discount) . '</th></tr>';
        }
        //------------------------Order Discount End---------------------//
        //------------------------Return Surcharge ---------------------//
        if (!empty($return_sale) && $return_sale->surcharge != 0) {
            $footer_row4 = '<tr><th  colspan="' . $total_column_offset . '">' . lang("order_discount") . '</th><th class="text-left">' . $this->formatMoney($return_sale->surcharge) . '</th></tr>';
        }
        //------------------------Return Surcharge End ---------------------//
        //------------------------Shipping Charges---------------------//
        $footer_row5_shipping = '';
        if ($inv->shipping && (isset($inv->eshop_sale) && $inv->eshop_sale)) {
            $footer_row5_shipping = '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("Shipping") . '</th>'
                    . '<th class="left">' . $this->formatMoney($inv->shipping) . '</th>'
                    . '</tr>';
        }

        //------------------------Grand Total invoice---------------------//
        if ($inv->rounding): // check Rounding  issue 
            // $footer_row5 = '<tr>'
            //         . '<th colspan="' . $total_column_offset . '" >' . lang("rounding") . '</th>'
            //         . '<th class="" style="padding:1px 1px; text-align: right;">' . $this->formatMoney($inv->rounding) . '</th>'
            //         . '</tr>';

            $GTotal = $this->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding + $row->making_charges));
            $GTotalW = $this->convert_number_to_words($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding + $row->making_charges));
            $GTotalW = !empty($GTotalW) ? '<span style="text-transform: uppercase;font-size: smaller;float: right;padding-right: 25px; "> ( ' . $GTotalW . ' ' . $this->currencyLable . ' Only ) </span>' : '';
            $footer_row5 = $footer_row5_shipping . $footer_row5 . '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("grand_total") . $GTotalW . '</th>'
                    . '<th class="" style="padding:1px 1px; text-align: right;">' . $GTotal . '</th>'
                    . '</tr>';
        else:
            $GTotal = $this->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total);
            $GTotalW = $this->convert_number_to_words($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total);
            $GTotalW = !empty($GTotalW) ? '<span style="text-transform: uppercase;font-size: smaller;float: right;padding-right: 25px;"> ( ' . $GTotalW . ' ' . $this->currencyLable . ' Only ) </span>' : '';

            $footer_row5 = $footer_row5_shipping . '<tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("grand_total") . $GTotalW . '</th>'
                    . '<th class="" style="padding:1px 1px; text-align: right;">' . $GTotal . '</th>'
                    . '</tr>';
        endif;
        //------------------------Grand Total End---------------------//
        //------------------------Partial Paid---------------------//
        if ($inv->paid < $inv->grand_total) :

            $footer_row6 = ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("paid_amount") . '</th>'
                    . '<th class="text-left" style="padding:1px 1px; text-align: right;">' . $this->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid) . '</th>'
                    . '</tr>';

            $footer_row6 = $footer_row6 . ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("Due_Amount") . '</th>'
                    . '<th class="text-left" style="padding:1px 1px; text-align: right;">' . $this->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)) . '</th>'
                    . '</tr>';


        else: 

          $footer_row6 = ' <tr>'
                    . '<th colspan="' . $total_column_offset . '" >' . lang("paid_amount") . '</th>'
                    . '<th class="text-left" style="padding:1px 1px; text-align: right;">' . $this->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid) . '</th>'
                    . '</tr>';
                    // $footer_row6 = $footer_row6 . ' <tr>'
                    // . '<th colspan="' . $total_column_offset . '" >' . lang("Balance_Amount") . '</th>'
                    // . '<th class="text-left" style="padding:1px 1px; text-align: right;">' . $this->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding) + ($return_sale->grand_total + $return_sale->rounding)) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)) . '</th>'
                    // . '</tr>';

        endif;
        //------------------------Partial Paid End ---------------------//
        $footer_row7 = '';
        if (count($taxAttr) > 0) {
            foreach ($taxAttr as $_code => $_value) :
                $footer_row7 = $footer_row7 . ' <tr><th colspan="' . $total_column_offset . '" >' . $_code . '</th>'
                        . '<th  class="text-right" style="padding:1px 1px; text-align: right;">' . $this->formatMoney($_value) . '</th></tr>';
            endforeach;
        }

        $tableFooter = '<tfoot>' . $footer_row1 . $footer_row2 . $footer_row3 . $footer_row4 . $footer_row7 . $footer_row5 . $footer_row6 . '</tfoot>';
        /* ------------------------------------------------Footer End--------------------------------------  */



        /* ------------------------------------------------Table  --------------------------------------  */
        if (!empty($class)):
            $table = '<table class="table table-bordered table-hover table-striped">' . $tableHeader . $tableBody . $tableFooter . '</table>';
        else:
            $table = '<table class="table table-striped table-condensed">' . $tableHeader . $tableBody . $tableFooter . '</table>';
        endif;

        /* ------------------------------------------------Table Body  End--------------------------------------  */

        return $table;
    }

    public function taxAttrTBLInline_csi($tax_rate, $cgst, $sgst, $igst, $total_column_offset) {
        if ($cgst != '0.0000' || $sgst != '0.0000' || $igst != '0.0000') {
            $total_column_offset++;
            $table_body = '';
            $order_tax_label = lang("tax");

            $total_column_offset_colspan = $total_column_offset; // $total_column_offset - 3;
            $table_body = $table_body . '<tr><td colspan="' . $total_column_offset_colspan . '" style="padding-left: 5%;" ><table style="width:100%"><tr><td>' . $order_tax_label . ':</td>';
            $table_body = $table_body . '<td>' . lang('CGST') . ' (' . (float) (($cgst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%) ' . (($cgst != 0) ? $this->sma->formatMoney($cgst) : 0) . '</td>';
            $table_body = $table_body . '<td>' . lang('SGST') . ' (' . (float) (($sgst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%) ' . (($sgst != 0) ? $this->sma->formatMoney($sgst) : 0) . '</td>';
            if ($igst != 0) {
                $table_body = $table_body . '<td>' . lang('IGST') . ' (' . (float) (($igst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%) ' . (($igst != 0) ? $this->sma->formatMoney($igst) : 0) . '</td>';
            }
            $table_body = $table_body . '</tr></table></td></tr>';
            return $table_body;
        }
    }

    public function taxAttrTblDiv_csi($tax_rate, $cgst, $sgst, $igst, $total_column_offset) {
        $order_tax_label = lang("tax");
        $table_body = '';
        $table_body = $table_body . '<tr><td  class="text-left"  colspan="' . $total_column_offset . '">' . $order_tax_label . '<div class="attr_table" style="width: 40%;font-weight:normal;display: table;">';

        $table_body = $table_body . '<ul style=" display: table-row;list-style-type: none;"  >'
                . '<li   > <span style=" display: table-cell;padding: 5%;">' . lang('CGST') . '</span>&nbsp;&nbsp;&nbsp;<span style=" display: table-cell;padding: 5%;">' . (float) (($cgst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%</span>&nbsp;&nbsp;&nbsp;<span  style=" display: table-cell;padding: 5%;">' . (($cgst != 0) ? $this->sma->formatMoney($cgst) : 0) . '</span></li>'
                . '</ul>';
        $table_body = $table_body . '<ul style=" display: table-row;list-style-type: none;"  >'
                . '<li   > <span style=" display: table-cell;padding: 5%;">' . lang('SGST') . '</span>&nbsp;&nbsp;&nbsp;<span style=" display: table-cell;padding: 5%;">' . (float) (($sgst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%</span>&nbsp;&nbsp;&nbsp;<span  style=" display: table-cell;padding: 5%;">' . (($sgst != 0) ? $this->sma->formatMoney($sgst) : 0) . '</span></li>'
                . '</ul>';
        if ($igst != 0) {
            $table_body = $table_body . '<ul style=" display: table-row;list-style-type: none;"  >'
                    . '<li   > <span style=" display: table-cell;padding: 5%;">' . lang('IGST') . '</span>&nbsp;&nbsp;&nbsp;<span style=" display: table-cell;padding: 5%;">' . (float) (($igst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%</span>&nbsp;&nbsp;&nbsp;<span  style=" display: table-cell;padding: 5%;">' . (($igst != 0) ? $this->sma->formatMoney($igst) : 0) . '</span></li>'
                    . '</ul>';
        }

        $table_body = $table_body . '</div></td><td class="text-right"> </td></tr>';

        return $table_body;
    }

    public function taxAttrTBL_csi($tax_rate, $cgst, $sgst, $igst, $total_column_offset) {
        //print_r($itemTaxes);
        $table_body = '';
        $order_tax_label = lang("tax");
        $table_body = $table_body . '<tr><th colspan="' . $total_column_offset . '">' . $order_tax_label . '<table class="attr_table" style="width:30%;font-weight:normal;">';

        $table_body = $table_body . '<tr><td>' . lang('CGST') . '</td><td>' . (($cgst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%</td><td>' . (($cgst != 0) ? $this->sma->formatMoney($cgst) : 0) . '</td></tr>';
        $table_body = $table_body . '<tr><td>' . lang('SGST') . '</td><td>' . (($sgst != 0) ? $this->sma->formatMoney($tax_rate) : 0) . '%</td><td>' . (($sgst != 0) ? $this->sma->formatMoney($sgst) : 0) . '</td></tr>';
        if ($igst > 0) {
            $table_body = $table_body . '<tr><td>' . lang('IGST') . '</td><td>' . (($igst != 0) ? $this->sma->formatDecimal($tax_rate) : 0) . '%</td><td>' . (($igst != 0) ? $this->sma->formatMoney($igst) : 0) . '</td></tr>';
        }
        // $table_body = $table_body . '</table></th><th class="text-right"> </th></tr>';
        $table_body = $table_body . '</table></th></tr>';
        return $table_body;
    }

    /**/

    /**
     * @userid
     * @return state code
     */
    public function getstatecode($userid = NULL) {
        $data = $this->db->select('state_code')->where(['id' => $userid])->get('sma_companies')->row();
        return $data->state_code;
    }

    public function getsystemstatecode() {
        $statecode = $this->db->select('state_code')->where(['setting_id' => '1'])->get('sma_settings')->row();
        return $statecode->state_code;
    }

    function taxArr_rate_gst($tax_rate_id, $_net_unit_price, $_qty, $itemId = NULL, $saleID = NULL, $taxType = NULL, $type = NULL) {
        $_net_unit_price = isset($_net_unit_price) ? $_net_unit_price : 0;
        $_qty = isset($_qty) ? $_qty : 0;
        $_tax = $this->site->getTaxRateByID($tax_rate_id);
        $tax_rate = $_tax->rate;
        $tax_config = isset($_tax->tax_config) ? $_tax->tax_config : '';
        $tax_config = !empty($tax_config) ? unserialize($tax_config) : NULL;

        if (empty($tax_config)):
            return false;
        endif;

        if (!is_array($tax_config)):
            return false;
        endif;
        $insert_data = array();
        switch ($taxType) {

            case 'IGST':
                if (isset($taxArr)):
                    unset($taxArr);
                endif;
                $taxArr = array();
                $taxArr['item_id'] = !empty((int) $itemId) ? $itemId : 0;
                $taxcode = "IGST";
                $tax_per = $tax_rate;
                $taxArr['attr_code'] = "IGST";
                $taxArr['attr_name'] = "Integrated Goods and Service Tax";
                $taxArr['attr_per'] = $tax_rate;
                $taxArr['tax_amount'] = ($_net_unit_price * ((float) $tax_rate / 100)) * ($_qty);
                // $total_IGST = ($_net_unit_price * ((float) $tax_rate / 100)) * ($_qty);
                $taxArr[$tax_per] = $tax_rate;
                $taxArr[$taxcode] = ($_net_unit_price * ((float) $tax_rate / 100)) * ($_qty);

                $insert_data[] = $taxArr;
                break;
            default:
                $taxvatype = array('CGST' => 'Central Goods and Service Tax', 'SGST' => 'State Goods and Service Tax');
                $GSTRate = ($tax_rate / 2);
                foreach ($taxvatype as $key => $taxval) {

                    //if (isset($taxArr)):
                    //unset($taxArr);
                    //endif;
                    // $taxArr = array();

                    $taxArr['item_id'] = !empty((int) $itemId) ? $itemId : 0;
                    $taxcode = $key;
                    $tax_per = $GSTRate;
                    $taxArr['attr_code'] = $key;
                    $taxArr['attr_name'] = $taxval;
                    $taxArr['attr_per'] = $GSTRate;
                    $taxArr['tax_amount'] = ($_net_unit_price * ((float) $GSTRate / 100)) * ($_qty);
                    $taxArr[$tax_per] = $GSTRate;
                    $taxArr[$taxcode] = ($_net_unit_price * ((float) $GSTRate / 100)) * ($_qty);
                    $insert_data[] = $taxArr;
                }
                break;
        }
        return $insert_data;
    }

    /*     * *** */
    /*     * *02-11-2020** */

    function setUserActionLog($Data) {
        if (!empty($Data)) {
            $UserData = $this->site->getUser($this->session->userdata('user_id'));
            $Datas = array(
                'action_type' => $Data['action_type'],
                'product_id' => $Data['product_id'],
                'quantity' => $Data['quantity'],
                'action_reff_id' => $Data['action_reff_id'],
                'action_affected_data' => $Data['action_affected_data'],
                'action_comment' => $Data['action_comment'],
                'user_id' => $this->session->userdata('user_id'),
                'user_name' => $UserData->first_name . ' ' . $UserData->last_name,
                'action_url' => current_url(),
            );
            $this->site->addUserActionLog($Datas);
        }
    }

    /*     * *02-11-2020** */

    public function ajax_pagignations($totalPages, $page = 1) {

        $pagignation = '';
        $pagingNumbers = 15;

        if ($totalPages > 1) {

            if ($page > 1) {
                $page_previous = '<li class="page-item"><a class="page-link" onclick="loadReport(' . ($page - 1) . ')" tabindex="-1">Previous</a></li>';
            } else {
                $page_previous = '<li class="page-item disabled"><a class="page-link">Previous</a></li>';
            }

            if ($totalPages > $page) {
                $page_next = '<li class="page-item"><a class="page-link" onclick="loadReport(' . ($page + 1) . ')">Next</a></li>';
            } else {
                $page_next = '<li class="page-item disabled"><a class="page-link">Next</a></li>';
            }

            $pagignation = '<div class="text-center"><nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-center">' . $page_previous;

            if ($page > $pagingNumbers) {
                $page_start = $page - $pagingNumbers;
            } else {
                $page_start = 1;
            }

            if ($totalPages > $pagingNumbers) {
                $maxPages = $page_start + $pagingNumbers - 1;
                $page_break = '<li class="page-item disabled"><a class="page-link" >...</a></li>';
                if ($totalPages == $page) {
                    $page_break .= '<li class="page-item active"><span class="page-link">' . $page . '<span class="sr-only">(current)</span></span></li>';
                } else {
                    $page_break .= '<li class="page-item"><a class="page-link" onclick="loadReport(' . $totalPages . ')">' . $totalPages . '</a></li>';
                }
            } else {
                $maxPages = $totalPages;
                $page_break = '';
            }

            for ($i = $page_start; $i <= $maxPages; $i++) {

                if ($i == $page) {
                    $pagignation .= '<li class="page-item active"><span class="page-link">' . $page . '<span class="sr-only">(current)</span></span></li>';
                } else {
                    $pagignation .= '<li class="page-item"><a class="page-link" onclick="loadReport(' . $i . ')">' . $i . '</a></li>';
                }
            }

            $pagignation .= $page_break . $page_next . '</ul></nav></div>';
        }//end if Pagignation 

        return $pagignation;
    }

    function get_tiny_url($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function getReturnSaleReferenceNo($latest_invoice_number){
      
        $year = date('Y');
        $month = date('m');
        $type = "POS";
        if ($latest_invoice_number) {
            $latest_number = (int)substr($latest_invoice_number, -3);
            $new_number = str_pad($latest_number + 1, 3, '0', STR_PAD_LEFT);
           
        } else {
            $new_number = '001';
        }
        $invoice_number = sprintf('%s/%s/%s/%s/%s', 'RETURN', $type, $year, $month, $new_number);
       
        return $invoice_number;
    }
    public function remove_commas($value,$formate) {
        // Remove commas
        $valueWithoutCommas = str_replace(',', '', $value);
        
        // Convert the value to a float and format it to four decimal places
        return number_format((float)$valueWithoutCommas, $formate, '.', '');
    }
    public function getExchangeSaleReferenceNo($latest_invoice_number){
      
        $year = date('Y');
        $month = date('m');
        $type = "POS";
        if ($latest_invoice_number) {
            $latest_number = (int)substr($latest_invoice_number, -3);
            $new_number = str_pad($latest_number + 1, 3, '0', STR_PAD_LEFT);
           
        } else {
            $new_number = '001';
        }
        $invoice_number = sprintf('%s/%s/%s/%s/%s', 'EXCHANGE', $type, $year, $month, $new_number);
       
        return $invoice_number;
    }
    public function generateSuspendRefNo($susdetails) {
       
        $user = $this->session->userdata('user_id');    
        $current_date = date('md'); 
       
        if ($susdetails['customer_id'] < '10') { 
            $ref_no = $current_date  . '/0' . $susdetails['customer_id'] . '/' . '0' . $user;

        } else {
            $ref_no =$current_date  . '/' . $susdetails['customer_id'] . '/' . $user;
        }
        return $ref_no;
    }
    public function getProductOptionsByGroupId($product_id, $GroupId = '') {
        if ($GroupId != '')
            $this->db->where(array('group_id' => $GroupId));
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     /////////////////////////////////////////// Multiple Biller Invoice Format //////////////////////////////////////
    public function biller_invoice_format($invoice_no, $date,$biller_invoice_type) {
        $biller_invoice_type =  is_numeric($biller_invoice_type) ? 'biller_id_invoice_no' :'biller_prefix_invoice_no';
        $invoice_no = $this->getinvoiceNobyBiller($date,$biller_invoice_type);
        $invoice_format = $this->Settings->invoice_format ? $this->Settings->invoice_format : 'inv';
        $prepend = $this->Settings->invoice_length ? $this->Settings->invoice_length : 4;

        switch ($invoice_format) {

            //154
            case 'inv':
                return $invoice_no;
                break;

            //19-20/154
            case 'short-fy-inv':
                $fy = $this->get_financial_year($date, $short_year = 1);
                return $fy . '/' . $invoice_no;
                break;

            //2019-2020/154
            case 'long-fy-inv':
                $fy = $this->get_financial_year($date, $short_year = 0);
                return $fy . '/' . $invoice_no;
                break;

            //2020/01/154
            case 'y-m-inv':
                $date = $date ? $date : date('Y-m-d');
                $obj_date = date_create($date);
                $ym = date_format($obj_date, "Y/m");
                return $ym . '/' . $invoice_no;
                break;

            // 000154          
            case 'prepend-inv':
                return sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            //19-20/000154
            case 'short-fy-prepend-inv':
                $fy = $this->get_financial_year($date, $short_year = 1);
                return $fy . '/' . sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            //2019-2020/000154
            case 'long-fy-prepend-inv':
                $fy = $this->get_financial_year($date, $short_year = 0);
                return $fy . '/' . sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            //2020/01/000154
            case 'y-m-prepend-inv':
                $date = $date ? $date : date('Y-m-d');
                $obj_date = date_create($date);
                $ym = date_format($obj_date, "Y/m");
                return $ym . '/' . sprintf("%'.0" . $prepend . "d", $invoice_no);
                break;

            default:
                return $invoice_no;
                break;
        }//end switch
    }
    public function getinvoiceNobyBiller($date,$prefix_invoice) {
        $date = $date?$date :date('Y-m-d');
        $obj_date = date_create($date);

        $year_format = $date?date('y',strtotime($date)) :'y';

        if (date_format($obj_date, "m") >= 4) {//On or After April (FY is current year - next year)
            $financial_year = (date_format($obj_date, $year_format)) . '-' . (date_format($obj_date, $year_format) + 1);
        } else {//On or Before March (FY is previous year - current year)
            $financial_year = (date_format($obj_date, $year_format) - 1) . '-' . date_format($obj_date, $year_format);
        }
        $gettype = $this->db->select('financial_type')->where(['setting_id' => '1'])->get('sma_settings')->row();

        if ($gettype->financial_type == 'M') {
            $checkFinancial = $this->db->where(['financial_year' => $financial_year, 'financial_month' => date('m',strtotime($date))])->get('sma_financial_year')->row();
            if ($this->db->affected_rows() > 0) {
                $invoice_no = $checkFinancial->$prefix_invoice + 1;
                $this->db->where(['id' => $checkFinancial->id])->update('sma_financial_year', [$prefix_invoice => $invoice_no]);
            } else {
                $invoice_no = 1;
                $this->db->insert('sma_financial_year', [$prefix_invoice => $invoice_no, 'financial_year' => $financial_year, 'financial_month' => date('m', strtotime($date))]);
            }
            if ($gettype->invoice_format == 'y-m-prepend-inv' || $gettype->invoice_format == 'y-m-inv') {
                $invoice_no = $invoice_no;
            } else {
                $invoice_no = date('m', strtotime($date)) . '/' . $invoice_no;
            }
        } else if ($gettype->financial_type == 'C') {

            $checkFinancial = $this->db->where(['financial_year' => $financial_year, 'financial_month' => NULL])->get('sma_financial_year')->row();
            if ($this->db->affected_rows() > 0) {
                $invoice_no = $checkFinancial->$prefix_invoice + 1;
                $this->db->where(['id' => $checkFinancial->id])->update('sma_financial_year', [$prefix_invoice => $invoice_no]);
            } else {
                $continue = $this->db->order_by('id', 'DESC')->get('sma_financial_year')->row();
                $invoice_no = (($continue) ? $continue->$prefix_invoice : '0') + 1;
                $this->db->insert('sma_financial_year', [$prefix_invoice => $invoice_no, 'financial_year' => $financial_year]);
            }
        } else {
            $checkFinancial = $this->db->where(['financial_year' => $financial_year, 'financial_month' => NULL])->get('sma_financial_year')->row();
            if ($this->db->affected_rows() > 0) {
                $invoice_no = $checkFinancial->$prefix_invoice + 1;
                $this->db->where(['id' => $checkFinancial->id])->update('sma_financial_year', [$prefix_invoice => $invoice_no]);
            } else {
                $invoice_no = 1;
                $this->db->insert('sma_financial_year', [$prefix_invoice => $invoice_no, 'financial_year' => $financial_year]);
            }
        }
  
        return $invoice_no;
    }
    /////////////////////////////////////////// Multiple Biller Invoice Format End //////////////////////////////////////

    ////////////////////////////////////////// Add purchase after sale //////////////////////////////////////////////////////////////////////

    // public function CreatePurchase($sale_id, $location_id, $production_unit_purchase = false) {
    //     // $this->sma->checkPermissions();
    //     if ($sale_id) {
    //         $inv = $this->sales_model->getInvoiceByID($sale_id);
    //         $inv_items = $this->sales_model->getAllInvoiceItems($sale_id);
    //         $reference =  $this->site->getReference('po');
    //         if ($this->Owner || $this->Admin || $this->GP['purchases-date']) {
    //             $date = $this->sma->fld(trim($this->input->post('date')));
    //         } else {
    //             $date = date('Y-m-d H:i:s');
    //         }
    //         $warehouse_id       = $location_id;
    //         $supplier_id       = $inv->biller_id;
    //         $status = 'received';
    //         $shipping =  0;
    //         $supplier_details = $this->site->getCompanyByID($supplier_id);
    //         //$supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
    //         $supplier = !empty($supplier_details->name) ? $supplier_details->name : $supplier_details->company;
    //         $note = $this->sma->clear_tags($this->input->post('note'));
    //         $payment_term = $this->input->post('payment_term');
    //         $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            
    //         /*Set GST Type Logic*/
    //         $supplier_state_code = $supplier_details->state_code != '' ? $supplier_details->state_code : NULL;
    //         $warehouse = $this->site->getWarehouseByID($warehouse_id);            
    //         if($warehouse->state_code != ''){
    //             // $billers_id = $this->pos_settings->default_biller;
    //             $billers_id = $warehouse->primary_biller_id;
    //             $billers_state_code = $this->sma->getstatecode($billers_id);
    //         }    
    //         $purchase_state_code = $warehouse->state_code != '' ? $warehouse->state_code : ($billers_state_code != '' ? $billers_state_code : NULL);
    //         $GSTType = 'GST';
    //         if($supplier_state_code != NULL && $purchase_state_code != NULL){
    //             $GSTType = ($supplier_state_code == $purchase_state_code) ? 'GST' : 'IGST';
    //         }
    //         $total = 0;
    //         $product_tax = 0;
    //         $order_tax = 0;
    //         $product_discount = 0;
    //         $order_discount = 0;
    //         $percentage = '%';
    //         $i = sizeof($_POST['product']);
    //         $total_cgst = $total_sgst = $total_igst = 0;
    //         foreach ($inv_items as $item) {
    //             $item_code = $item->product_code;
    //             $product = $this->site->getProductByID($item->product_id);
    //             if($item_code != '') {
    //                 $product_details = $this->purchases_model->getProductByCode($item_code);
    //                 $product_id = $product_details->id;
    //             }
    //             $item_option = 0; $item_batch_number = NULL; $batchData = NULL; 
    //             if($product->storage_type == 'packed') {
    //                 $item_option = $item->option_id; 
    //             }
    //             if($this->Settings->product_batch_setting !== 0) {
    //                 $row_batch_number = (isset($_POST['batch_number'][$r]) && $_POST['batch_number'][$r]!='') ? $_POST['batch_number'][$r] : NULL;
    //                 if ($row_batch_number) { 
    //                     $batch = explode('~', $row_batch_number);
    //                     $item_batch_number = (count($batch)==2) ? $batch[1] : $batch[0];
    //                     $batchData = $this->site->getProductBatchData($item_batch_number, $product_id, $item_option);                    
    //                 }
    //             }
    //             $hsn_code           = (isset($_POST['hsn_code'][$r]) && $_POST['hsn_code'][$r] != '') ? $hsn_code : $product_details->hsn_code;
    //             // $item_net_cost      = $item->net_unit_price;
    //             $unit_cost          = $item->net_unit_price;
    //             $real_unit_cost     = $item->real_unit_price;
    //             $item_unit_quantity = $item->quantity;
    //             $item_quantity      = $item_unit_quantity;
    //             $quantity_received = ($status == 'received') ? $item_quantity : 0;
    //             if ($product->storage_type == 'loose') {
    //                     $item_unit_quantity = $item->unit_quantity;
    //                     $item_quantity =  $item->quantity;
    //                     $quantity_received = $item->unit_quantity;
    //             }
    //             $item_tax_rate      = isset($item->tax_rate_id) ? $item->tax_rate_id : 0;
    //             $item_discount      = isset($item->discount) ? $item->discount : 0;
    //             $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
    //             if($batchData == NULL && $item_batch_number != NULL ){
    //                 $batchData = array(
    //                     'product_id'=>$product_id, 
    //                     'option_id'=>$item_option, 
    //                     'batch_no'=>$item_batch_number,
    //                     'cost' => $unit_cost,
    //                     'price' => '',
    //                     'mrp' => '',
    //                     'expiry_date' => $item_expiry ,
    //                 );
    //                 $this->site->addBatchInfo($batchData);
    //             }
    //             $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
    //             $item_unit          = $item->product_unit_id;
    //             // $item_quantity      = $_POST['product_base_quantity'][$r];
    //             $item_tax_method    =  $item->tax_method; 
    //             if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
    //                 if ($item_expiry) {
    //                     $today = date('Y-m-d');
    //                     if ($item_expiry <= $today) {
    //                         $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
    //                         redirect($_SERVER["HTTP_REFERER"]);
    //                     }
    //                 }
    //                 // $unit_cost = $real_unit_cost;
    //                 $pr_discount = 0;

    //                 if (isset($item_discount)) {
    //                     $discount = $item_discount;
    //                     $dpos = strpos($discount, $percentage);
    //                     if ($dpos !== false) {
    //                         $pds = explode("%", $discount);
    //                         $pr_discount = $this->formatDecimal(((($this->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
    //                     } else {
    //                         $pr_discount = $this->formatDecimal($discount);
    //                     }
    //                 }
    //                 $unit_cost          = $this->formatDecimal($unit_cost - $pr_discount);
    //                 $item_net_cost      = $unit_cost;
    //                 $pr_item_discount   = $this->formatDecimal($pr_discount * $item_quantity);
    //                 $product_discount   += $pr_item_discount;
    //                 $pr_tax             = 0;
    //                 $pr_item_tax        = 0;
    //                 $item_tax           = 0;
    //                 $tax                = "";
    //                 $cgst = $sgst = $igst = $gst_rate = 0;
    //                 if (isset($item_tax_rate) && $item_tax_rate != 0) {
    //                     $pr_tax = $item_tax_rate;
    //                     $tax_details = $this->site->getTaxRateByID($pr_tax);
    //                     if ($tax_details->type == 1 && $tax_details->rate != 0) {
    //                         $taxmethod = ($item_tax_method == '') ? $product_details->tax_method : $item_tax_method;
    //                         if ($product_details && $taxmethod == 1) {
    //                             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
    //                             $tax = $tax_details->rate . "%";
    //                         } else {
    //                             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
    //                             $tax = $tax_details->rate . "%";
    //                             $item_net_cost = $unit_cost - $item_tax;
    //                         }
    //                     } elseif ($tax_details->type == 2) {
    //                         if ($product_details && $taxmethod == 1) {
    //                             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
    //                             $tax = $tax_details->rate . "%";
    //                         } else {
    //                             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
    //                             $tax = $tax_details->rate . "%";
    //                             $item_net_cost = $unit_cost - $item_tax;
    //                         }
    //                         $item_tax = $this->formatDecimal($tax_details->rate);
    //                         $tax = $tax_details->rate;
    //                     }
    //                     $pr_item_tax = $this->formatDecimal($item_tax * $item_quantity, 4);
    //                 }
    //                 $product_tax    += $pr_item_tax;
    //                 $subtotal       = (($item_net_cost * $item_quantity) + $pr_item_tax);
    //                 $unit           = $this->site->getUnitByID($item_unit);
    //                 $item_option    = $item_option ? $item_option : 0;
    //                 if($pr_item_tax) {
    //                     if($GSTType == 'IGST'){
    //                         $igst = $pr_item_tax;
    //                         $gst_rate = $tax_details->rate;
    //                     } else {
    //                         $cgst = $sgst = ($pr_item_tax / 2);
    //                         $gst_rate = ($tax_details->rate / 2);
    //                     }
    //                 }
    //                 $products[] = array(
    //                     'product_id'        => $product_details->id,
    //                     'product_code'      => $item_code,
    //                     'product_name'      => $product_details->name,
    //                     'option_id'         => $item_option,
    //                     'net_unit_cost'     => $item_net_cost,
    //                     'unit_cost'         => $this->formatDecimal($item_net_cost + $item_tax),
    //                     'quantity'          => $item_quantity,
    //                     'product_unit_id'   => $item_unit,
    //                     'product_unit_code' => $unit->code,
    //                     'unit_quantity'     => $item_unit_quantity,
    //                     'quantity_balance'  => $quantity_received,
    //                     'quantity_received' => $quantity_received,
    //                     'warehouse_id'      => $warehouse_id,
    //                     'item_tax'          => $pr_item_tax,
    //                     'tax_rate_id'       => $pr_tax,
    //                     'tax'               => $tax,
    //                     'discount'          => $item_discount,
    //                     'item_discount'     => $pr_item_discount,
    //                     'subtotal'          => $this->formatDecimal($subtotal),
    //                     'expiry'            => $item_expiry,
    //                     'batch_number'      => $item_batch_number,
    //                     'real_unit_cost'    => $real_unit_cost,
    //                     'date'              => date('Y-m-d', strtotime($date)),
    //                     'status'            => $status,
    //                     'supplier_part_no'  => $supplier_part_no,
    //                     'hsn_code'          => $hsn_code,
    //                     'tax_method'        => $item_tax_method,
    //                     'gst_rate'          => $gst_rate,
    //                     'cgst'              => $cgst,
    //                     'sgst'              => $sgst,
    //                     'igst'              => $igst,
    //                     'mrp'              => $item->mrp,

    //                 );
    //                 $total += $this->formatDecimal(($item_net_cost * $item_quantity), 4);
    //                 $total_cgst += $cgst;
    //                 $total_sgst += $sgst;
    //                 $total_igst += $igst;
    //             }
    //         }
    //         if (empty($products)) {
    //             $this->form_validation->set_rules('product', lang("order_items"), 'required');
    //         } else {
    //             krsort($products);
    //         }
    //         if ($this->input->post('discount')) {
    //             $order_discount_id = $this->input->post('discount');
    //               $opos = strpos($order_discount_id, $percentage);
    //               if ($opos !== false) {
    //                 $ods = explode("%", $order_discount_id);
    //                 $order_discount = $this->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

    //               } else {
    //                 $order_discount = $this->formatDecimal($order_discount_id);
    //               }  
    //         } else {
    //             $order_discount_id = null;
    //         }
    //         //$total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
    //         $total_discount = $this->formatDecimal($product_discount);
            
    //         if ($this->Settings->tax2 != 0) {
    //             $order_tax_id = $this->input->post('order_tax');
    //             if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
    //                 if ($order_tax_details->type == 2) {
    //                     $order_tax = $this->formatDecimal($order_tax_details->rate);
    //                 }
    //                 if ($order_tax_details->type == 1) {
    //                     // $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
    //                     $order_tax = $this->formatDecimal(((($total + $product_tax ) * $order_tax_details->rate) / 100), 4);
    //                 }
    //             }
    //         } else {
    //             $order_tax_id = null;
    //         }
    //         $total_tax = $this->formatDecimal(($product_tax + $order_tax), 4);
    //         //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
    //         $grand_total = $this->formatDecimal(($total + $total_tax + $this->formatDecimal($shipping)), 4);
    //         $rounding = '';
    //         if ($this->pos_settings->rounding > 0) {
    //             $round_total = $this->roundNumber($grand_total, $this->pos_settings->rounding);
    //             $rounding = ($round_total - $grand_total);
    //         }
    //         $data = [
    //             'reference_no'      => $reference,
    //             'date'              => $date,
    //             'supplier_id'       => $supplier_id,
    //             'supplier'          => $supplier,
    //             'warehouse_id'      => $warehouse_id,
    //             'note'              => $note,
    //             'total'             => $total,
    //             'product_discount'  => $product_discount,
    //             'order_discount_id' => $order_discount_id,
    //             'order_discount'    => $order_discount,
    //             'total_discount'    => $total_discount,
    //             'product_tax'       => $product_tax,
    //             'order_tax_id'      => $order_tax_id,
    //             'order_tax'         => $order_tax,
    //             'total_tax'         => $total_tax,
    //             'shipping'          => $this->formatDecimal($shipping),
    //             'grand_total'       => $grand_total,
    //             'status'            => $status,
    //             'created_by'        => $this->session->userdata('user_id'),
    //             'payment_term'      => $payment_term,
    //             'rounding'          => $rounding,
    //             'due_date'          => $due_date,
    //             'cgst'              => $total_cgst,
    //             'sgst'              => $total_sgst,
    //             'igst'              => $total_igst,
    //         ];
    //         if ($_FILES['document']['size'] > 0) {
    //             $this->load->library('upload');
    //             $config['upload_path'] = $this->digital_upload_path;
    //             $config['allowed_types'] = $this->digital_file_types;
    //             $config['max_size'] = $this->allowed_file_size;
    //             $config['overwrite'] = false;
    //             $config['encrypt_name'] = true;
    //             $this->upload->initialize($config);
    //             if (!$this->upload->do_upload('document')) {
    //                 $error = $this->upload->display_errors();
    //                 $this->session->set_flashdata('error', $error);
    //                 redirect($_SERVER["HTTP_REFERER"]);
    //             }
    //             $photo = $this->upload->file_name;
    //             $data['attachment'] = $photo;
    //         }
    //         // $this->sma->print_arrays($data, $products);
    //     }
    //     $this->purchases_model->addPurchase($data, $products, $production_unit_purchase);
    //     return;
    // }
     public function CreatePurchase($sale_id, $location_id, $production_unit_purchase = false) {
        // $this->sma->checkPermissions();
        if ($sale_id) {
            $inv = $this->sales_model->getInvoiceByID($sale_id);
            $inv_items = $this->sales_model->getAllInvoiceItems($sale_id);
            $reference =  $this->site->getReference('po');
            if ($this->Owner || $this->Admin || $this->GP['purchases-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id       = $location_id;
            $supplier_id       = $inv->biller_id;
            $status = 'received';
            $shipping =  0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            //$supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $supplier = !empty($supplier_details->name) ? $supplier_details->name : $supplier_details->company;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            
            /*Set GST Type Logic*/
            $supplier_state_code = $supplier_details->state_code != '' ? $supplier_details->state_code : NULL;
            $warehouse = $this->site->getWarehouseByID($warehouse_id);            
            if($warehouse->state_code != ''){
                // $billers_id = $this->pos_settings->default_biller;
                $billers_id = $warehouse->primary_biller_id;
                $billers_state_code = $this->sma->getstatecode($billers_id);
            }    
            $purchase_state_code = $warehouse->state_code != '' ? $warehouse->state_code : ($billers_state_code != '' ? $billers_state_code : NULL);
            $GSTType = 'GST';
            if($supplier_state_code != NULL && $purchase_state_code != NULL){
                $GSTType = ($supplier_state_code == $purchase_state_code) ? 'GST' : 'IGST';
            }
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = sizeof($_POST['product']);
            $total_cgst = $total_sgst = $total_igst = 0;
            foreach ($inv_items as $item) {
                $item_code = $item->product_code;
                $product = $this->site->getProductByID($item->product_id);
                if($item_code != '') {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    $product_id = $product_details->id;
                }
                $item_option = 0; $item_batch_number = NULL; $batchData = NULL; 
                if($product->storage_type == 'packed') {
                    $item_option = $item->option_id; 
                }
                if($this->Settings->product_batch_setting !== 0) {
                    $row_batch_number = (isset($_POST['batch_number'][$r]) && $_POST['batch_number'][$r]!='') ? $_POST['batch_number'][$r] : NULL;
                    if ($row_batch_number) { 
                        $batch = explode('~', $row_batch_number);
                        $item_batch_number = (count($batch)==2) ? $batch[1] : $batch[0];
                        $batchData = $this->site->getProductBatchData($item_batch_number, $product_id, $item_option);                    
                    }
                }
                $hsn_code           = (isset($_POST['hsn_code'][$r]) && $_POST['hsn_code'][$r] != '') ? $hsn_code : $product_details->hsn_code;
                $item_net_cost      = $item->net_price;
                $unit_cost          = $item->unit_price;
                $real_unit_cost     = $item->real_unit_price;
                $item_unit_quantity = $item->quantity;
                $item_quantity      = $item_unit_quantity;
                $quantity_received = ($status == 'received') ? $item_quantity : 0;
                if ($product->storage_type == 'loose') {
                        $item_unit_quantity = $item->quantity;
                        $item_quantity =  $item->quantity;
                        $quantity_received = $item->unit_quantity;
                }
                $item_tax_rate      = isset($item->tax_rate_id) ? $item->tax_rate_id : 0;
                $item_discount      = isset($item->discount) ? $item->discount : 0;
                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                if($batchData == NULL && $item_batch_number != NULL ){
                    $batchData = array(
                        'product_id'=>$product_id, 
                        'option_id'=>$item_option, 
                        'batch_no'=>$item_batch_number,
                        'cost' => $unit_cost,
                        'price' => '',
                        'mrp' => '',
                        'expiry_date' => $item_expiry ,
                    );
                    $this->site->addBatchInfo($batchData);
                }
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $item->product_unit_id;
                // $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_tax_method    =  $item->tax_method; 
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->formatDecimal(((($this->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->formatDecimal($discount);
                        }
                    }
                    $unit_cost          = $this->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost      = $unit_cost;
                    $pr_item_discount   = $this->formatDecimal($pr_discount * $item_quantity);
                    $product_discount   += $pr_item_discount;
                    $pr_tax             = 0;
                    $pr_item_tax        = 0;
                    $item_tax           = 0;
                    $tax                = "";
                    $cgst = $sgst = $igst = $gst_rate = 0;
                    // if (isset($item_tax_rate) && $item_tax_rate != 0) {
                    //     $pr_tax = $item_tax_rate;
                    //     $tax_details = $this->site->getTaxRateByID($pr_tax);
                    //     if ($tax_details->type == 1 && $tax_details->rate != 0) {
                    //         $taxmethod = ($item_tax_method == '') ? $product_details->tax_method : $item_tax_method;
                    //         if ($product_details && $taxmethod == 1) {
                    //             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                    //             $tax = $tax_details->rate . "%";
                    //         } else {
                    //             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                    //             $tax = $tax_details->rate . "%";
                    //             $item_net_cost = $unit_cost - $item_tax;
                    //         }
                    //     } elseif ($tax_details->type == 2) {
                    //         if ($product_details && $taxmethod == 1) {
                    //             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                    //             $tax = $tax_details->rate . "%";
                    //         } else {
                    //             $item_tax = $this->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                    //             $tax = $tax_details->rate . "%";
                    //             $item_net_cost = $unit_cost - $item_tax;
                    //         }
                    //         $item_tax = $this->formatDecimal($tax_details->rate);
                    //         $tax = $tax_details->rate;
                    //     }
                    //     $pr_item_tax = $this->formatDecimal($item_tax * $item_quantity, 4);
                    // }
                    // $product_tax    += $pr_item_tax;
                    // $subtotal       = (($item_net_cost * $item_quantity) + $pr_item_tax);
                    $unit           = $this->site->getUnitByID($item_unit);
                    $item_option    = $item_option ? $item_option : 0;
                    // if($pr_item_tax) {
                    //     if($GSTType == 'IGST'){
                    //         $igst = $pr_item_tax;
                    //         $gst_rate = $tax_details->rate;
                    //     } else {
                    //         $cgst = $sgst = ($pr_item_tax / 2);
                    //         $gst_rate = ($tax_details->rate / 2);
                    //     }
                    // }
                    $products[] = array(
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->formatDecimal($item_net_cost),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $quantity_received,
                        'quantity_received' => $quantity_received,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $item->item_tax,
                        'tax_rate_id'       => $item->tax_rate_id,
                        'tax'               => $item->tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->formatDecimal($item->subtotal),
                        'expiry'            => $item_expiry,
                        'batch_number'      => $item_batch_number,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                        'hsn_code'          => $hsn_code,
                        'tax_method'        => $item_tax_method,
                        'gst_rate'          => $gst_rate,
                        'cgst'              => $item->cgst,
                        'sgst'              => $item->sgst,
                        'igst'              => $item->igst,
                        'mrp'              => $item->mrp,

                    );
                    $total += $this->formatDecimal(($item_net_cost * $item_quantity), 4);
                    $total_cgst += $cgst;
                    $total_sgst += $sgst;
                    $total_igst += $igst;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                  $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                  } else {
                    $order_discount = $this->formatDecimal($order_discount_id);
                  }  
            } else {
                $order_discount_id = null;
            }
            //$total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
            $total_discount = $this->formatDecimal($product_discount);
            
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        // $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        $order_tax = $this->formatDecimal(((($total + $product_tax ) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $total_tax = $this->formatDecimal(($product_tax + $order_tax), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $grand_total = $this->formatDecimal(($total + $total_tax + $this->formatDecimal($shipping)), 4);
            $rounding = '';
            if ($this->pos_settings->rounding > 0) {
                $round_total = $this->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = ($round_total - $grand_total);
            }
            $data = [
                'reference_no'      => $reference,
                'date'              => $date,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $inv->total,
                'product_discount'  => $inv->product_discount,
                'order_discount_id' => $inv->order_discount_id,
                'order_discount'    => $inv->order_discount,
                'total_discount'    => $inv->total_discount,
                'product_tax'       => $inv->product_tax,
                'order_tax_id'      => $inv->order_tax_id,
                'order_tax'         => $inv->order_tax,
                'total_tax'         => $inv->total_tax,
                'shipping'          => $this->formatDecimal($inv->shipping),
                'grand_total'       => $inv->grand_total,
                'status'            => $status,
                'created_by'        => $this->session->userdata('user_id'),
                'payment_term'      => $payment_term,
                'rounding'          => $inv->rounding,
                'due_date'          => $inv->due_date,
                'cgst'              => $inv->cgst,
                'sgst'              => $inv->sgst,
                'igst'              => $inv->igst,
            ];
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->sma->print_arrays($data, $products);
        }
        $this->purchases_model->addPurchase($data, $products, $production_unit_purchase);
        return;
    }
    // Create sales when we dispatch order from production unit
    public function CreateSales($procurement_orders, $procurement_order_items) {

        // $this->sma->checkPermissions();
        if ($procurement_orders) {

            $sale_action = $this->input->post('sale_action') ? $this->input->post('sale_action') : 'sales';
            $refKey = 'pu';
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference($refKey);
           
            if ($this->Owner || $this->Admin || $this->GP['sales-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $user_id = $this->session->userdata('user_id');
            $user = $this->site->getUser($user_id);
            $warehouse_id       = $user->warehouse_id;
            $customer_id        = $this->pos_settings->default_customer;
            $warehouse          = $this->site->getWarehouseBy_ID($warehouse_id);
            $biller_id          = $warehouse->primary_biller_id;
            $total_items        = $this->input->post('total_items');
            $sale_status        = 'completed';
            $payment_status     = $this->input->post('payment_status');
            $payment_term       = $this->input->post('payment_term');
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            //$biller             = !empty($biller_details->company !== '-' ? $biller_details->company : $biller_details->name);
            $biller             = $biller_details->name;
            $note               = $this->sma->clear_tags($this->input->post('note'));
            $staff_note         = $this->sma->clear_tags($this->input->post('staff_note'));
            $quote_id           = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            $syncQuantity       = $this->input->post('syncQuantity');
            $order_id           = $this->input->post('order_id') ? $this->input->post('order_id') : null;
            $sale_type_input    = $this->input->post('sale_type') ? $this->input->post('sale_type') : '';

            if ((!empty($customer_details->state_code) && !empty($biller_details->state_code)) && $customer_details->state_code != $biller_details->state_code) {
                $interStateTax = true;
            } else {
                $interStateTax = false;
            }
           
            $total              = 0;
            $product_tax        = 0;
            $order_tax          = 0;
            $product_discount   = 0;
            $order_discount     = 0;
            $percentage         = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            $sale_cgst = $sale_sgst = $sale_igst = 0;

            foreach ($procurement_order_items as $order_item) {
              
                if (isset($order_item)) {
                    
                    $product_details = $this->sales_model->getProductByCode($order_item->product_code);
                    $tax_method =  $product_details->tax_method;
                    $item_quantity      = $order_item->allot_quantity;
                    
                    // warehouse price group  
                    $warehouse_data          = $this->site->getWarehouseBy_ID($order_item->production_unit_id);
                    $unit_price              = ($warehouse_data->price_group_id && $pr_group_price = $this->site->getProductGroupPrice($product_details->id, $warehouse->price_group_id)) ? $pr_group_price->price : $product_details->price;
                    $real_unit_price         = $unit_price;
                    $unit = $this->site->getUnitByID($product_details->unit);
                   
                    $item_mrp       =  $this->sma->formatDecimal($product_details->mrp);
                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            //Note : unitprice is product and variant price. Real unit price is actual product price. if we taken realunitprice then grandtotal and discount calculate wrong becuase real unit price not included variant price. so now taken unit_price.(28-03-2020)
                            $pr_discount = $this->sma->formatDecimal(( ( (Float) $unit_price * (Float) $pds[0] ) / 100), 4);
                            //$pr_discount = $this->sma->formatDecimal(( ( (Float) $real_unit_price * (Float) $pds[0] ) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount, 4);
                        }
                    }
                    $unit_discount = $pr_discount;
                    $item_unit_price_less_discount = ($unit_price - $unit_discount);
                    //$item_unit_price_less_discount = $this->sma->formatDecimal($unit_price - $unit_discount); //17/05/19

                    $item_net_price = $item_unit_price_less_discount;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_quantity);
                    //Sum Of products discounts
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = '';
                   
                    $invoice_net_unit_price = 0;

                    // check product level tax and category level tax
                    $CategoryLevelTaxRate = $this->site->CalculateCategoryLevelTaxRate($product_details->id, $unit_price); // Calculate category level tax rate as per product
                    if (!empty($product_details->tax_rate)) {
                        $pr_tax = $product_details->tax_rate;
                    } else {
                        $pr_tax = $CategoryLevelTaxRate;
                    }
                    if (isset($pr_tax) && isset($order_item->tax_rate_id)) {

                        // $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        $tax = $tax_details->rate . "%";
                        if ($tax_details->rate != 0) {
                            if ($tax_details->type == 1) {
                                //Exclusive tax method calculation
                                if ($product_details && $tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / 100, 4);

                                    $net_unit_price = $item_unit_price_less_discount;
                                    $unit_price = $item_unit_price_less_discount + $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    //Inclusive tax method calculation.
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);

                                    $item_net_price = $item_unit_price_less_discount - $item_tax;

                                    $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $unit_price = $item_unit_price_less_discount;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }
                            } elseif ($tax_details->type == 2) {

                                if ($product_details && $tax_method  == 1) {
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / 100, 4);

                                    $net_unit_price = $item_unit_price_less_discount;
                                    $unit_price = $item_unit_price_less_discount + $item_tax;

                                    $invoice_unit_price = $item_unit_price_less_discount;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount + $item_tax;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($item_unit_price_less_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);

                                    $item_net_price = $item_unit_price_less_discount - $item_tax;

                                    $net_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $unit_price = $item_unit_price_less_discount;

                                    $invoice_unit_price = $item_unit_price_less_discount - $item_tax;
                                    $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                                }
                            }//end else.
                        } else {

                            $net_unit_price = $item_unit_price_less_discount;
                            $unit_price = $item_unit_price_less_discount;
                            $invoice_unit_price = $item_unit_price_less_discount;
                            $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                        }

                        $item_tax = $item_tax ? $item_tax : 0;
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_quantity, 4);

                        $unit_tax = $item_tax;
                    } else {
                        $net_unit_price = $item_unit_price_less_discount;
                        $unit_price = $item_unit_price_less_discount;

                        $invoice_unit_price = $item_unit_price_less_discount;
                        $invoice_net_unit_price = $item_unit_price_less_discount + $unit_discount;
                    }//end else

                    if ($interStateTax) {
                        $item_gst = $tax_details->rate;
                        $item_cgst = 0;
                        $item_sgst = 0;
                        $item_igst = $pr_item_tax;
                    } else {
                        $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
                        $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
                        $item_igst = 0;
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);
                    $unit = $product_details->unit;

                    $mrp                            = $item_mrp;
                    $invoice_unit_price             = $this->sma->formatDecimal($invoice_unit_price, 4);
                    $invoice_net_unit_price         = $this->sma->formatDecimal($invoice_net_unit_price, 4);
                    $invoice_total_net_unit_price   = $this->sma->formatDecimal(($invoice_net_unit_price * $item_quantity), 4);
                    $net_unit_price                 = $this->sma->formatDecimal($net_unit_price, 4);
                    $unit_price                     = $this->sma->formatDecimal($unit_price, 4);
                    $net_price                      = $this->sma->formatDecimal(($mrp * $item_quantity), 4);
                    $subtotal                       = $this->sma->formatDecimal(($unit_price * $item_quantity), 4);
                    
                    $products[] = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $product_details->code,
                        'article_code'      => $product_details->article_code,
                        'product_name'      => $product_details->name,
                        'product_type'      => $product_details->type,
                        // 'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $unit_price,
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $product_details->unit,
                        'product_unit_code' => ($unit ? $unit->code : NULL),
                        'unit_quantity'     => $item_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'item_weight'       => $item_weight,
                        'tax_rate_id'       => $pr_tax,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $subtotal,
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                        'mrp'               => $product_details->mrp,
                        'hsn_code'          => $hsn_code,
                        'delivery_status'   => 'pending',
                        'pending_quantity'  => $item_quantity,
                        'delivered_quantity'=> 0,
                        'tax_method'        => $product_details->tax_method,
                        'unit_discount'     => $unit_discount,
                        'unit_tax'          => $unit_tax,
                        'invoice_unit_price'=> $invoice_unit_price,
                        'net_price'         => $net_price,
                        'invoice_net_unit_price'        => $invoice_net_unit_price,
                        'invoice_total_net_unit_price'  => $invoice_total_net_unit_price,
                        'gst_rate'          => $item_gst,
                        'cgst'              => $item_cgst,
                        'sgst'              => $item_sgst,
                        'igst'              => $item_igst,
                        'cf1'               => $item_expiry,
                        'cf1_name'          => 'Exp. Date',
                        'batch_number'      => $batch_number,
                         
                    ];
                    $sale_cgst += $item_cgst;
                    $sale_sgst += $item_sgst;
                    $sale_igst += $item_igst;

                    // $total += $this->sma->formatDecimal(($unit_price * $item_quantity), 4);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4); //17/05/19
                                        
                }
            }
          
            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                /* $opos = strpos($order_discount_id, $percentage);
                  if ($opos !== false) {
                  $ods = explode("%", $order_discount_id);
                  $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
                  } else {
                  $order_discount = $this->sma->formatDecimal($order_discount_id);
                  } */
            } else {
                $order_discount_id = null;
            }
            // $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
            $total_discount = $this->sma->formatDecimal($product_discount);


            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    } elseif ($order_tax_details->type == 1) {
                        //$order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping)), 4);
            $rounding = '';

            if ($this->pos_settings->rounding > 0) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = ($round_total - $grand_total);
            }
            $data = ['date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => 'due',
                'payment_term' => $payment_term,
                'rounding' => $rounding,
                'due_date' => $due_date,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'cgst' => $sale_cgst,
                'sgst' => $sale_sgst,
                'igst' => $sale_igst,
                'order_no' => $procurement_orders->procurement_order_ref_no,

            ];
            $payment = array();
        }

        $sale_action = (isset($sale_action) && $sale_action != '') ? $sale_action : 'sales';
        $syncQuantity = (isset($syncQuantity) && $syncQuantity != '') ? $syncQuantity : 'sales';
        $extrasPara = array('sale_action' => $sale_action, 'syncQuantity' => $syncQuantity, 'order_id' => $order_id);
              
        $this->sales_model->addSale($data, $products, $payment, array(), $extrasPara);
        // for update counter of reference number after creating sale in sma_order_ref table
        $PURefKey = 'pu';
        if ($this->site->getReference($PURefKey) == $data['reference_no']) {
            $this->site->updateReference($PURefKey);
        }
        return;
    }
    ////////////////////////////////// transfer when we received order from outlet////////////////////////////////////////////////////////////////////
    function CreateTransfer($procurement_order_id)
    {
        if ($procurement_order_id) {

            $procurement_order          = $this->Production_Unit_Model_New->getProcurmentOrderById($procurement_order_id); 
            $procurement_order_items    = $this->Production_Unit_Model_New->getOrderItemsForSelectedOrder($procurement_order_id); 
            $user_id                    = $this->session->userdata('user_id');
            $user_data                  = $this->site->getUser($user_id); 
            $location_id                = $user_data->warehouse_id;

            $transfer_no =  $this->site->getReference('to');
            if ($this->Owner || $this->Admin ||  $this->GP['transfers-date']) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $from_warehouse = $location_id; // outlet location
            $to_warehouse = $procurement_order->location_id; // production unit location
            $note = $this->sma->clear_tags($this->input->post('note'));
            $shipping = 0;
            $status = 'sent';
            $fromWarehouseDetails = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_details = $fromWarehouseDetails[$from_warehouse];
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            $from_warehouse_state_code = $from_warehouse_details->state_code;
            
            $toWarehouseDetails = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_details = $toWarehouseDetails[$to_warehouse];
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;
            $to_warehouse_state_code = $to_warehouse_details->state_code;
           
            $total = 0;
            $product_tax = 0;
               
            foreach ($procurement_order_items as $item) {
            
                $item_code           = $item->product_code;
                $item_net_cost       = $item->net_unit_cost;
                $unit_cost           = $item->unit_cost;
                $real_unit_cost      = $item->unit_cost;
                $item_unit_quantity  = $item->allot_quantity;
                $item_tax_rate       = isset($item->tax_rate_id) ? $item->tax_rate_id : 0;
                // $product_tax_id   = isset($_POST['product_tax_id'][$r]) ? $_POST['product_tax_id'][$r] : NULL;
                // $item_expiry      = isset($_POST['expiry'][$r]) ? $this->sma->fsd($_POST['expiry'][$r]) : NULL;
                // $item_option      = $item->option_id;
                $item_unit           = $item->product_unit_id;
                $item_quantity       = $item_unit_quantity;


                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->transfers_model->getProductByCode($item_code);
                    $item_unit       = $product_details->product_unit_id;
                    $unit           = $this->site->getUnitByID($item_unit);

                    // if (!$this->Settings->overselling) {
                    // $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option);

                    // if ($warehouse_quantity->quantity < $item_quantity) {
                    //     $this->session->set_flashdata('error', lang("no_match_found") . " (" . lang('product_name') . " <strong>" . $product_details->name . "</strong> " . lang('product_code') . " <strong>" . $product_details->code . "</strong>)");
                    //     redirect("transfers/add");
                    // }
                   // }
                 
                    if ($item_tax_rate) {

                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        } elseif ($tax_details->rate == 0) {
                                   $item_tax = 0;
                                   $tax = $tax_details->rate . "%";
                                   if ($tax_details->type == 2)
                                      $tax = $tax_details->rate; 
                        }
                        
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = ($product_details && $product_details->tax_method == 1) ? $this->sma->formatDecimal($unit_cost) : $this->sma->formatDecimal($unit_cost-$item_tax, 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);


                    $products[] = [
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $to_warehouse,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        // 'mrp' => $product_details->mrp,
                        'date' => date('Y-m-d', strtotime($date))
                    ];

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);

                }
            }

            $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            $data = array('transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'from_warehouse_state_code' => $from_warehouse_state_code,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
                'to_warehouse_state_code' => $to_warehouse_state_code,
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping,
                'procurement_order_ref_no' => $procurement_order->procurement_order_ref_no

            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        }
        $this->transfers_model->addTransfer($data, $products);
        return;
    }
    // update transfer when we received order from outlet 
    public function UpdateTransfer($transfer_data, $transfer_Items, $orders) {
        // $this->sma->checkPermissions();
        if ($transfer_data) {

            $total = 0;
            $product_tax = 0;
            foreach ($transfer_Items as $transferItem) {

                $product_details    = $this->site->getProductByID($transferItem->product_id);
                $unit               = $this->site->getUnitByID($transferItem->product_unit_id);
                $shipping           = 0;
                $status             = 'completed';
                // Default quantity from item
                $item_quantity      = $transferItem->quantity;
                $quantity_received  = $item_quantity; // default to item quantity
                $quantity_balance   = $status == 'completed' ? $item_quantity : 0;
                $item_tax_rate      = $transferItem->tax_rate_id;

                // Override with received_qty from $orders if available
                foreach ($orders as $order) {
                        
                        $item_unit_quantity = $order['received_qty'];
                        
                        // if($product_details->tax_method == 0){
                        //     $unit_cost = $this->sma->formatDecimal($transferItem->net_unit_cost + $transferItem->item_tax);
                        // }else{
                        //     $unit_cost = $this->sma->formatDecimal($transferItem->net_unit_cost);
                        // } 
                        $unit_cost = $transferItem->real_unit_cost;

                        if ($item_tax_rate) {
                            
                            $pr_tax = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                }

                            } elseif ($tax_details->type == 2) {

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;

                            } elseif ($tax_details->rate == 0) {
                                    $item_tax = 0;
                                    $tax = $tax_details->rate . "%";
                                    if ($tax_details->type == 2)
                                        $tax = $tax_details->rate; 
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                            
                        } else {
                            $pr_tax = 0;
                            $pr_item_tax = 0;
                            $tax = "";
                        }

                        $item_net_cost = ($product_details && $product_details->tax_method == 1) ? $this->sma->formatDecimal($unit_cost) : $this->sma->formatDecimal(($unit_cost - $item_tax), 4);
                        $product_tax += $pr_item_tax;
                        $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    
                        $transfer_Items_data[] = array(
                            'product_id'        => $product_details->id,
                            'product_code'      => $transferItem->product_code,
                            'product_name'      => $product_details->name,
                            'net_unit_cost'     => $item_net_cost,
                            'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                            'quantity'          => $order['received_qty'], 
                            'product_unit_id'   => $product_details->unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity'     => $order['received_qty'],
                            'quantity_balance'  => $order['received_qty'],
                            'warehouse_id'      => $procurement_orders->location_id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $transferItem->tax_rate_id,
                            'tax'               => $transferItem->tax,
                            'subtotal'          => $subtotal,
                            'expiry'            => $transferItem->expiry,
                            'batch_number'      => $transferItem->batch_number,
                            'real_unit_cost'    => $transferItem->real_unit_cost,
                            'date'              => date('Y-m-d H:i:s'),
                            'status'            =>'completed',
                        );
                        $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }

            }
            $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            $transfer = [
                'transfer_no' => $transfer_data->transfer_no,
                'date' => date('Y-m-d H:i:s'),
                'from_warehouse_id' => $transfer_data->from_warehouse_id,
                'from_warehouse_code' => $transfer_data->from_warehouse_code,
                'from_warehouse_name' => $transfer_data->from_warehouse_name,
                'from_warehouse_state_code' => $transfer_data->from_warehouse_state_code,
                'to_warehouse_id' => $transfer_data->to_warehouse_id,
                'to_warehouse_code' => $transfer_data->to_warehouse_code,
                'to_warehouse_name' => $transfer_data->to_warehouse_name,
                'to_warehouse_state_code' => $transfer_data->to_warehouse_state_code,
                'note' => $transfer_data->note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'status' => 'completed',
                'procurement_order_ref_no' => $transfer_data->procurement_order_ref_no,
                'shipping' => $shipping
            ];
            $this->production_unit_model->updateTransfer($transfer_data->id,$transfer,$transfer_Items_data);
        }
        return;
    }
    ////////////////////////////////// transfer when we received order from outlet////////////////////////////////////////////////////////////////////
}
