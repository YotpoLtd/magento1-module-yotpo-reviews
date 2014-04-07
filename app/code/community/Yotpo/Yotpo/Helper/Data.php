<?php

class Yotpo_Yotpo_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_config;

    public function __construct ()
    {
        $this->_config = Mage::getStoreConfig('yotpo');
    }

    public function showWidget($thisObj, $product = null, $print=true)
    {
        $res = $this->renderYotpoProductBlock($thisObj, 'yotpo-reviews', $product, $print);
        if ($print == false) {
            return $res;
        }
    }

    public function showBottomline($thisObj, $product = null, $print=true)
    {

        $res = $this->renderYotpoProductBlock($thisObj, 'yotpo-bottomline', $product);
        if ($print == false){
            return $res;
        }
    }

    public function showQABottomline($thisObj, $product = null, $print=true)
    {

        $res = $this->renderYotpoProductBlock($thisObj, 'yotpo-qa-bottomline', $product);
        if ($print == false){
            return $res;
        }
    }

    private function renderYotpoProductBlock($thisObj, $blockName, $product = null, $print=true)
    {
        $block = $thisObj->getLayout()->getBlock('content')->getChild('yotpo');
        if ($block == null) {
            Mage::log("can't find yotpo block");
            return;
        }

        $block = $block->getChild($blockName);
        if ($block == null) {
            Mage::log("can't find yotpo child named: ".$blockName);
            return;
        }

        if ($product != null)
        {
            $block->setAttribute('product', $product);
        }
        if ($block != null)
        {

            if ($print == true) {
                echo $block->toHtml();
            } else {
                return $block->toHtml();
            }

        }

    }

}