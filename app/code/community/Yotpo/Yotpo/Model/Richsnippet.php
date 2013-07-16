<?php

class Yotpo_Yotpo_Model_Richsnippet extends Mage_Core_Model_Abstract 
{
    protected function _construct()
    {
        $this->_init('yotpo/richsnippet');
    }  

    public function isValid() 
    {

        $expirationTime = strtotime($this->getExpirationTime());
        return ($expirationTime > time());
    } 

    public function getSnippetByProductId($product_id) 
    {
        return $this->getResourceCollection()->getItemByColumnValue('product_id', $product_id);
    }
}