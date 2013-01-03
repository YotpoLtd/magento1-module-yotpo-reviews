<?php

class Yotpo_Yotpo_Model_Export_Observer
{
	public function addMassAction($observer) {
   		$block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && strstr( $block->getRequest()->getControllerName(), 'review'))
        {
        	$block->addItem('exportreviews', array(
                'label' => Mage::helper('review')->__('Export Reviews'),
                'url' => Mage::app()->getStore()->getUrl('*/review_export/csvexport'),
            ));
        }
   }
}