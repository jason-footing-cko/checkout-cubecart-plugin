<?php

class methods_creditcard extends methods_Abstract
{

    protected function _createCharge($order_info)
    {
        global $config;

        $gateway = $GLOBALS['cart']->basket['gateway'];
        $module_config = $config->get($gateway);
        $scretKey = $module_config['secretkey'];
        $configs['authorization'] = $scretKey;
        $configs['timeout'] = $_REQUEST['timeout'];
        $configs['paymentToken'] = $_REQUEST['cko-cc-paymenToken'];
        
        $Api = CheckoutApi_Api::getApi(array('mode' => $_REQUEST['mode']));

        return $Api->verifyChargePaymentToken($configs);
    }

    protected function _captureConfig()
    {
        global $config;

        $gateway = $GLOBALS['cart']->basket['gateway'];
        $module_config = $config->get($gateway);

        $to_return['postedParam'] = array(
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => $module_config['autocaptime']
        );
        return $to_return;
    }

    protected function _authorizeConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );

        return $to_return;
    }

    public function generatePaymentToken()
    {

        global $config;

        $order = Order::getInstance();
        $cart_order_id = $order->_email_details['order_summary']['cart_order_id'] ? $order->_email_details['order_summary']['cart_order_id'] : $_REQUEST['cart_order_id'];

        $order_summary = $order->getSummary($cart_order_id);
        $cart = unserialize($order_summary['basket']);
        $gateway = $GLOBALS['cart']->basket['gateway'];
        $module_config = $config->get($gateway);
        $default_currency = $GLOBALS['config']->get('config', 'default_currency');
        $billingInfo = $cart['billing_address'];
        $shippingInfo = $cart['delivery_address'];
        $amountCents = (int) ($cart['total'] * 100);

        $configs = array();
        $configs['authorization'] = $module_config['secretkey'];
        $configs['mode'] = $module_config['mode'];
        $configs['timeout'] = $module_config['timeout'];
        
        if ($module_config['payment_type'] == 'AUTH_CAPTURE') {
            $configs = array_merge_recursive($configs, $this->_captureConfig());
        } else {

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
            'currency'        => $default_currency,
            'description'     => "Order number::$cart_order_id",
            'shippingDetails' => $shippingAddressConfig,
            'products'        => $products,
            'card'            => array(
                'billingDetails' => $billingAddressConfig
            )
        ));

        $Api = CheckoutApi_Api::getApi(array('mode' => $module_config['mode']));
        $paymentTokenCharge = $Api->getPaymentToken($configs);

        $paymentTokenArray = array(
            'message' => '',
            'success' => '',
            'eventId' => '',
            'token'   => '',
        );

        if ($paymentTokenCharge->isValid()) {
            $paymentTokenArray['token'] = $paymentTokenCharge->getId();
            $paymentTokenArray['success'] = true;
        } else {


            $paymentTokenArray['message'] = $paymentTokenCharge->getExceptionState()->getErrorMessage();
            $paymentTokenArray['success'] = false;
            $paymentTokenArray['eventId'] = $paymentTokenCharge->getEventId();
        }
        
        return $paymentTokenArray;
    }

}
