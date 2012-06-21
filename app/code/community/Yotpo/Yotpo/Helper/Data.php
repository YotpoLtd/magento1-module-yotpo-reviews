<?php

class Yotpo_Yotpo_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	private $_config;
    
    public function __construct ()
    {
        $this->_config = Mage::getStoreConfig('yotpo');
    }

	public function showWidget($thisObj)
	{
		echo $thisObj->getChildHtml('yotpo-reviews');
	}
	
	public function showBottomline($thisObj, $product = null)
	{
		
		$block = $thisObj->getLayout()->getBlock('content')->getChild('yotpo')->getChild('yotpo-bottomline');
							
		if ($product != null) 
		{
			$block->setAttribute('product', $product);
		}
							
		echo $block->toHtml();
	}
	
}
