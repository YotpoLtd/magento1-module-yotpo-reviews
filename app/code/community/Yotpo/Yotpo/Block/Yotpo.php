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

        $product = $this->getData('product');
        $configurable_product_model = Mage::getModel('catalog/product_type_configurable');
        $parentIds= $configurable_product_model->getParentIdsByChild($product->getId());
            if (count($parentIds) > 0) {
                $product = Mage::getModel('catalog/product')->load($parentIds[0]);
            }
        return $product;
    }
    
    public function getProductId() 
    {   		
     	$_product = $this->getProduct();
     	$productId = $_product->getId();
    	return $productId;
    }

    
    public function getAppKey() 
    {
  
   	 	return Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey',Mage::app()->getStore());
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
    	$productDescription = Mage::helper('core')->htmlEscape(strip_tags($_product->getShortDescription()));
    	return $productDescription;
    }

    public function isRichSnippetEnabled() {
        return !Mage::getStoreConfig('yotpo/yotpo_rich_snippets_group/rich_snippets_disabled',Mage::app()->getStore());
    }

    public function getRichSnippet() 
    {

        if ($this->isRichSnippetEnabled()) {
            
            try {

                $productId = $this->getProductId();

                $snippet = Mage::getModel('yotpo/richsnippet')->getSnippetByProductId($productId);

                if (($snippet == null) || (!$snippet->isValid())) {
                    //no snippet for product or snippet isn't valid anymore. get valid snippet code from yotpo api
                 
                    $res = Mage::helper('yotpo/apiClient')->createApiGet("products/".($this->getAppKey())."/".$productId."/richsnippet", 2);
                
                    if ($res["code"] != 200) {
                        //product not found or feature disabled.
                        return "";
                    }

                    $body = $res["body"];
                    $htmlCode = $body->response->rich_snippet->html_code;
                    if (empty($htmlCode)) {
                        //feature is not enabled by user
                        return "";
                    }
                    $ttl = $body->response->rich_snippet->ttl;

                    if ($snippet == null) {
                        $snippet = Mage::getModel('yotpo/richsnippet');
                        $snippet->setProductId($productId);
                    }

                    $snippet->setHtmlCode($htmlCode);
                    $snippet->setExpirationTime(date('Y-m-d H:i:s', time()));
                    $snippet->save();
                }
                return $snippet->getHtmlCode();

            } catch(Excpetion $e) {
                Mage::log($e);
            }       
        }
        return "";
    }

     
    public function getProductUrl()
    {
    	$_product = $this->getProduct();
    	$productUrl = $_product->getProductUrl();   	
    	return $productUrl; 	
    }

    public function getProductRating()
    {
        
    }

}