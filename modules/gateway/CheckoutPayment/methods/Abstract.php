<?php

abstract class methods_Abstract
{

    public function process()
    {
        return $this->_placeorder();
    }

    protected function _placeorder()
    {
        $order = Order::getInstance();

        $cart_order_id = $_REQUEST['cart_order_id'];
   
        $transData['gateway'] = 'Checkout.Com';
        $transData['order_id'] = $cart_order_id;
        $transData['trans_id'] = $cart_order_id;
        $transData['amount'] = $_REQUEST['total'];
        $transData['customer_id'] = $_REQUEST['customer_id'];
        $transData['extra'] = '';

        $respondCharge = $this->_createCharge($order);
        if ($respondCharge->isValid()) {

            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {

                $status = 'Approved';
                $message = 'Your transaction has been successfully authorized with transaction id : ' . $respondCharge->getId();
                $order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
                $order->paymentStatus(Order::PAYMENT_PROCESS, $cart_order_id);

                $transData['notes'] = $message;
                $transData['status'] = $status;
                $order->logTransaction($transData);

                httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
                
            }   else {

                $status = 'Declined';
                $order->orderStatus(Order::ORDER_DECLINED, $cart_order_id);
                $order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
                $message = 'Transaction failed : Please verify your credit card details and try again! ('. $respondCharge->getResponseCode() . ')';

                $transData['notes'] = $message;
                $transData['status'] = $status;
                $GLOBALS['smarty']->assign('ErrorMessage', $message);
                $order->logTransaction($transData);
            }
            
        }   else {

            $status = 'Error';
            $message = $respondCharge->getExceptionState()->getErrorMessage();
            $GLOBALS['smarty']->assign('ErrorMessage', $message);
            $order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
            $transData['notes'] = $message;
            $transData['status'] = $status;
            $order->logTransaction($transData);
        }
    }

    protected function _createCharge($order)
    {
        global $config;

        $order = Order::getInstance();

        $cart_order_id = $_REQUEST['cart_order_id'];
        $order_summary = $order->getSummary($cart_order_id);
        $cart = unserialize($order_summary['basket']);
        $module_config = $config->get($order_summary['gateway']);

        $billingInfo = $cart['billing_address'];
        $shippingInfo = $cart['delivery_address'];

        $currency = $_REQUEST['currency_code'];
        $amountCents = (int) ($cart['total'] * 100);

        $configs = array();

        $configs['authorization'] = $module_config['secretkey'];
        $configs['mode'] = $module_config['mode'];
        $configs['timeout'] = $module_config['timeout'];

        if ($module_config['payment_type'] == 'AUTH_CAPTURE') {
            
            $configs = array_merge_recursive($configs, $this->_captureConfig($_REQUEST['autocaptime']));
            
        }   else {
            
            $configs = array_merge_recursive($configs, $this->_authorizeConfig());
            
        }

        $products = array();
        foreach ($cart['contents'] as $item) {

            $products[] = array(
                'name'     => $item['name'],
                'sku'      => $item['product_code'],
                'price'    => $item['sale_price'],
                'quantity' => $item['quantity']
            );
        }

        $billingAddressConfig = array(
            'addressLine1' => $billingInfo['line1'],
            'addressLine2' => $billingInfo['line2'],
            'postcode'     => $billingInfo['postcode'],
            'country'      => $billingInfo['country'],
            'city'         => $billingInfo['town'],
            'state'        => $billingInfo['state'],
            'phone'        => array (
                'number' => $billingInfo['phone']
                ),
        );

        $shippingAddressConfig = array(
            'addressLine1'  => $shippingInfo['line1'],
            'addressLine2'  => $shippingInfo['line2'],
            'postcode'      => $shippingInfo['postcode'],
            'country'       => $shippingInfo['country'],
            'city'          => $shippingInfo['town'],
            'state'         => $shippingInfo['state'],
            'recipientName' => $shippingInfo['first_name'] . ' ' . $shippingInfo['last_name'],
            'phone'         => array (
                'number' => $shippingInfo['phone']
                ),
        );

        $configs['postedParam'] = array_merge_recursive($configs['postedParam'], array(
            'email'           => $billingInfo['email'],
            'value'           => $amountCents,
            'trackId'         => $cart_order_id,
            'currency'        => $currency,
            'description'     => "Order number::$cart_order_id",
            'shippingDetails' => $shippingAddressConfig,
            'products'        => $products,
            'card'            => array(
                'billingDetails' => $billingAddressConfig
            )
        ));

        return $configs;
    }

    protected function _getCharge($config)
    {

        $Api = CheckoutApi_Api::getApi(array('mode' => $_REQUEST['mode']));
        return $Api->createCharge($config);
    }

    protected function _captureConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => 'y',
            'autoCapTime' => $_REQUEST['autocaptime']
        );

        return $to_return;
    }

    protected function _authorizeConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => 'n',
            'autoCapTime' => 0
        );
        return $to_return;
    }

}
