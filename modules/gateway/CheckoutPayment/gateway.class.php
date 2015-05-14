<?php

/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
include 'autoload.php';

class Gateway
{

    private $_config;
    private $_module;
    private $_basket;
    private $_result_message;

    public function __construct($module = false, $basket = false)
    {
        $this->_db =& $GLOBALS['db'];
        $this->_config =& $GLOBALS['config'];
        $this->_module = $module;
        $this->_basket =& $GLOBALS['cart']->basket;
    }

    ##################################################

    public function transfer()
    {
        $transfer = array(
            'action' => currentPage(),
            'method' => 'post',
            'target' => '_self',
            'submit' => 'manual',
        );
        return $transfer;
    }

    public function getInstance()
    {
        if ($this->_module['type'] == 'pci') {

            $instance = CheckoutApi_Lib_Factory::getInstance('methods_creditcardpci');
        } else {

            $instance = CheckoutApi_Lib_Factory::getInstance('methods_creditcard');
        }

        return $instance;
    }

    ##################################################

    public function process()
    {
        $instance = $this->getInstance()->process();
        return $instance;
    }

    ##################################################

    private function formatMonth($val)
    {
        return $val . " - " . strftime("%b", mktime(0, 0, 0, $val, 1, 2009));
    }

    public function form()
    {

        if ($this->_module['type'] == 'pci') {
           ## Process transaction
            if (isset($_POST['cardNumber'])) {
                $return = $this->process();
            }
            
            $this->_result_message = $GLOBALS['smarty']->tpl_vars['ErrorMessage']->value;
            
            // Display payment result message
            if (!empty($this->_result_message)) {
                $GLOBALS['gui']->setError($this->_result_message);
            }

            //Show Expire Months
            $selectedMonth = (isset($_POST['expirationMonth'])) ? $_POST['expirationMonth'] : date('m');
            for ($i = 1; $i <= 12; ++$i) {
                $val = sprintf('%02d', $i);
                $smarty_data['card']['months'][] = array(
                    'selected' => ($val == $selectedMonth) ? 'selected="selected"' : '',
                    'value'    => $val,
                    'display'  => $this->formatMonth($val),
                );
            }

            ## Show Expire Years
            $thisYear = date("Y");
            $maxYear = $thisYear + 10;
            $selectedYear = isset($_POST['expirationYear']) ? $_POST['expirationYear'] : ($thisYear + 2);
            for ($i = $thisYear; $i <= $maxYear; ++$i) {
                $smarty_data['card']['years'][] = array(
                    'selected' => ($i == $selectedYear) ? 'selected="selected"' : '',
                    'value'    => $i,
                );
            }
            $GLOBALS['smarty']->assign('CARD', $smarty_data['card']);

            $smarty_data['customer'] = array(
                'first_name' => isset($_POST['firstName']) ? $_POST['firstName'] : $this->_basket['billing_address']['first_name'],
                'last_name'  => isset($_POST['lastName']) ? $_POST['lastName'] : $this->_basket['billing_address']['last_name'],
            );

            $GLOBALS['smarty']->assign('CUSTOMER', $smarty_data['customer']);

            ## Check for custom template for module in skin folder
            $file_name = 'creditcardpci.tpl';
            $form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
            $GLOBALS['gui']->changeTemplateDir($form_file);
            $ret = $GLOBALS['smarty']->fetch($file_name);
            $GLOBALS['gui']->changeTemplateDir();
            
        } else {

            ## Process transaction
            if (isset($_POST['cko-cc-paymenToken'])) {
                $return = $this->process();
            }
                     
            // Display payment result message
            if (!empty($this->_result_message)) {
                $GLOBALS['gui']->setError($this->_result_message);
            }

            $paymentToken = $this->getInstance()->generatePaymentToken();
            $smarty_data['checkoutapiData'] = array(
                'public_key'   => $this->_module['publickey'],
                'paymentToken' => $paymentToken['token'],
                'value'        => (int) ($this->_basket['total'] * 100),
                'currency'     => $GLOBALS['config']->get('config', 'default_currency'),
                'email'        => $this->_basket['billing_address']['email'],
                'name'         => $this->_basket['billing_address']['first_name'] . ' ' . $this->_basket['billing_address']['last_name'],
                'mode'         => $this->_module['mode']
            );

            $GLOBALS['smarty']->assign('CheckoutapiData', $smarty_data['checkoutapiData']);

            ## Check for custom template for module in skin folder
            $file_name = 'creditcardjs.tpl';
            $form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
            $GLOBALS['gui']->changeTemplateDir($form_file);
            $ret = $GLOBALS['smarty']->fetch($file_name);
            $GLOBALS['gui']->changeTemplateDir();
        }
        return $ret;
    }
    
    public function repeatVariables()
    {
        return (isset($hidden)) ? $hidden : false;
    }

    public function fixedVariables()
    {
        $default_currency = $GLOBALS['config']->get('config', 'default_currency');
        
        $hidden = array(
            'gateway'       => $this->_basket['gateway'],
            'cart_order_id' => $this->_basket['cart_order_id'],
            'currency_code' => $default_currency,
            'mode'          => $this->_module['mode'],
            'autocaptime'   => $this->_module['autocaptime'],
            'payment_type'  => $this->_module['payment_type'],
            'timeout'       => $this->_module['timeout'],
            'total'         => $this->_basket['total'],
            'customer_id'   => $this->_basket['billing_address']['customer_id'],
                
        );

        return (isset($hidden)) ? $hidden : false;
    }
    
    public function call()
    {
        $post_data = file_get_contents('php://input');

        if ($post_data) {
            $Api = CheckoutApi_Api::getApi(array('mode' => $this->_module['mode']));
            $objectCharge = $Api->chargeToObj($post_data);

            if ($objectCharge->isValid()) {

                /*
                 * Need to get track id
                 */
                $cart_order_id = $objectCharge->getTrackId();


                if ($cart_order_id) {

                    $order = Order::getInstance();
                    $order->getOrderDetails($cart_order_id);
                    $order_summary = $order->getSummary($cart_order_id);

                    if ($objectCharge->getCaptured()) {

                        $status = 'Captured';
                        $transData['notes'] = "Payment captured successfully.";
                        $order->orderStatus(Order::ORDER_COMPLETE, $cart_order_id);
                        $order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
                        
                    }   elseif ($objectCharge->getRefunded()) {

                            $status = 'Refunded';
                            $transData['notes'] = "Payment has been refunded";
                            $order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
                            $order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
                        
                    }   elseif (!$objectCharge->getAuthorised()) {

                            $status = 'Cancelled';
                            $transData['notes'] = "Payment cancelled";
                            $order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
                            $order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
                    }
                }
            }

            ## Build the transaction log data
            $transData['gateway'] = $_GET['module'];
            $transData['trans_id'] = $cart_order_id;
            $transData['amount'] = $order_summary['total'];
            $transData['status'] = $status;

            $order->logTransaction($transData);
        }
    }
}
