<?php

class Yotpo_Yotpo_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('yotpo/system/config/button.phtml');
    }
 
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxExportUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_yotpo/massMailAfterPurchase');
    }
 
    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'yotpo_button',
            'label'     => $this->helper('adminhtml')->__('Generate reviews for my past orders'),
            'onclick'   => 'javascript:exportOrders(); return false;'
        ));
 
        return $button->toHtml();
    }
}


?>