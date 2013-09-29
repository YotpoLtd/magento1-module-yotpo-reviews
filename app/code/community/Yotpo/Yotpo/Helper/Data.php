<?php

class Yotpo_Yotpo_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	private $_config;
    
    public function __construct ()
    {
        $this->_config = Mage::getStoreConfig('yotpo');
    }

	public function showWidget($thisObj, $product = null)
	{
		$this->renderYotpoProductBlock($thisObj, 'yotpo-reviews', $product);	
	}
	
	public function showBottomline($thisObj, $product = null)
	{

		$this->renderYotpoProductBlock($thisObj, 'yotpo-bottomline', $product);
	}

	private function renderYotpoProductBlock($thisObj, $blockName, $product = null) 
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
			echo $block->toHtml();	
		}
		
	}
	
}
