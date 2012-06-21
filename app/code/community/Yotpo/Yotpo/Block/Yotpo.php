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
  
    	#return $this->getData("appKey");
   	 	return Mage::getStoreConfig('yotpo/yotpo_group/yotpo_appkey',Mage::app()->getStore());
    }
    
        public function getProductName()
    {
    	$_product = $this->getProduct();
    	$productName = $_product->getName();
    	
    	return $productName;
    }
    
    public function getProductImageUrl()
	{
		$_product = $this->getProduct();
    	$productImageUrl = $_product->getImageUrl();
		return $productImageUrl;
	}
	
	 public function getDomain()
    {
    	return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
    }
    
    public function getProductBreadcrumbs()
    {
    	return "";
    }
    
     public function getProductModel() 
    {
    	$_product = $this->getProduct();
    	$productModel = $_product->getData('sku');
    	return $productModel;
    }
    
    public function getProductDescription()
    {
    	$_product = $this->getProduct();
    	$productDescription = $_product->getDescription();
    	return $productDescription;
    }

     
    public function getProductUrl()
    {
    	$_product = $this->getProduct();
    	$productUrl = $_product->getProductUrl();   	
    	return $productUrl; 	
    }
}