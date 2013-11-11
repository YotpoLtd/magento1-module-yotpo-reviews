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

    public function getSnippetByProductIdAndStoreId($product_id, $store_id)
    {
        Mage::log('ingetSnippetByProductIdAndStoreId');
        $col = $this->getCollection()->addFieldToFilter('store_id', $store_id);
        Mage::log('Returned collection with '.$col->getSize().' objects');
        if ($col->getSize() == 0) {
            return null;
        }
        $snippet = $col->getItemByColumnValue('product_id', $product_id);
        return $snippet;
    }
}