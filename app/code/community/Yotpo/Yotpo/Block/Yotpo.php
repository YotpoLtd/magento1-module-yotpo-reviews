<?php

class Yotpo_Yotpo_Block_Yotpo extends Mage_Core_Block_Template
{
    public function __construct()
    {
    	parent::__construct();
    }

    public function setProduct($product)
    {
    	$this->setData('product', $product);
    	$_product = $this->getProduct();
    	echo $_product->getName();
    }


    public function getProduct()
	{
        if (!$this->hasData('product'))
        {
            $this->setData('product', Mage::registry('product'));
        }

        return $this->getData('product');
    }

    public function getProductId()
    {
     	$_product = $this->getProduct();
     	$productId = $_product->getId();
    	return $productId;
    }

    public function getAppKey()
    {
        return trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey',Mage::app()->getStore()));
    }

    public function getProductName()
    {
    	$_product = $this->getProduct();
    	$productName = $_product->getName();

    	return htmlspecialchars($productName);
    }

    public function getProductImageUrl()
    {
        $image_url = Mage::getModel('catalog/product_media_config')->getMediaUrl($this->getProduct()->getSmallImage());
        return $image_url;
    }

    public function getProductBreadcrumbs()
    {
    	return "";
    }

     public function getProductModel()
    {
    	$_product = $this->getProduct();
    	$productModel = $_product->getData('sku');
    	return htmlspecialchars($productModel);
    }

    public function getProductDescription()
    {
    	$_product = $this->getProduct();
    	$productDescription = Mage::helper('core')->htmlEscape(strip_tags($_product->getShortDescription()));
    	return $productDescription;
    }

    public function getProductUrl()
    {
        $productUrl = Mage::app()->getStore()->getCurrentUrl();
    	return $productUrl;
    }

    public function getProductRating()
    {

    }

}