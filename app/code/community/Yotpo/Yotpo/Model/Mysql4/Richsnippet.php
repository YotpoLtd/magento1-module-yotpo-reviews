<?php

class Yotpo_Yotpo_Model_Mysql4_Richsnippet extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct()
    {
        $this->_init('yotpo/richsnippet', 'rich_snippet_id');
    }   
}