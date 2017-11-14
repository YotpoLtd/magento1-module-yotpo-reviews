<?php

class Yotpo_Yotpo_Block_Conversion extends Mage_Checkout_Block_Onepage_Success
{	
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function getOrderAmount() 
    {
        $order = $this->getOrder();
        if ($order != null) 
        {
            return $order->getSubtotal();
        }
    }

    public function getOrderCurrency() 
    {
        $order = $this->getOrder();
        if ($order != null) 
        {
            return $order->getOrderCurrency()->getCode();
        }
    }

    public function getOrder() 
    {
        $last_order_id = Mage::getSingleton('checkout/session')->getLastOrderId();
        return Mage::getModel('sales/order')->load($last_order_id);
    }

    public function getAppKey() 
    {
        return trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey',Mage::app()->getStore()));
    }
}

