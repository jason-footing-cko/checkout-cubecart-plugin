<?php

class methods_creditcardpci extends methods_Abstract
{

    protected function _createCharge($order_info)
    {
        $config = parent::_createCharge($order_info);

        $config['postedParam']['card'] = array_merge($config['postedParam']['card'], array(
            'name'        => trim($_POST['firstName']) . ' ' . trim($_POST['lastName']),
            'number'      => trim($_POST['cardNumber']),
            'expiryMonth' => str_pad($_POST['expirationMonth'], 2, '0', STR_PAD_LEFT),
            'expiryYear'  => $_POST['expirationYear'],
            'cvv'         => trim($_POST['cvc2']),
            )
        );

        return $this->_getCharge($config);
    }

}
