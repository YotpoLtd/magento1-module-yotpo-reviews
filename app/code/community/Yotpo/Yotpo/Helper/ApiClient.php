<?php

class Yotpo_Yotpo_Helper_ApiClient extends Mage_Core_Helper_Abstract
{
	
	const YOTPO_OAUTH_TOKEN_URL   = "https://api.yotpo.com/oauth/token";
	const YOTPO_SECURED_API_URL   = "https://api.yotpo.com";
	const YOTPO_UNSECURED_API_URL = "http://api.yotpo.com";
	const DEFAULT_TIMEOUT = 30;

	
	protected $disable_feature = null;

    protected $app_keys = array();
    protected $secrets = array();

	public function __construct()
	{
        foreach (Mage::app()->getStores() as $store) {
            $store_id = $store->getId();
            $this->app_keys[$store_id] = trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey', $store));
            $this->secrets[$store_id] = trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_secret', $store));
        }
	}

	public function oauthAuthentication($store_id)
	{

        $store_app_key = $this->app_keys[$store_id];
        $store_secret = $this->secrets[$store_id];

		if ($store_app_key == null or $store_secret == null)
		{
			Mage::log('Missing app key or secret');
			return null;
		}
		
		$yotpo_options = array('client_id' => $store_app_key, 'client_secret' => $store_secret, 'grant_type' => 'client_credentials');
		
		try 
		{
			$result = $this->createApiPost('oauth/token', $yotpo_options);
			return $result['body']->access_token;
		} 
		catch(Exception $e) 
		{
			Mage::log('error: ' .$e);
			return null;
		}
	}

	public function isEnabled($store_id)
	{
		//check if both app_key and secret exist
		if(($this->app_keys[$store_id] == null) or ($this->secrets[$store_id] == null))
		{
			return false;
		}
		return true;
	}

	public function prepareProductsData($order) 
	{
        Mage::app()->setCurrentStore($order->getStoreId());
        $products = $order->getAllVisibleItems(); //filter out simple products
        $products_arr = array();

        foreach ($products as $product) {

            //use configurable product instead of simple if still needed
            $full_product = Mage::getModel('catalog/product')->load($product->getProductId());

            $configurable_product_model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $configurable_product_model->getParentIdsByChild($full_product->getId());
            if (count($parentIds) > 0) {
                $full_product = Mage::getModel('catalog/product')->load($parentIds[0]);
            }

            $specs_data = array();
            $product_data = array();

            $product_data['name'] = $full_product->getName();
            $product_data['url'] = '';
            $product_data['image'] = '';
            try {
                $product_data['url'] = $full_product->getUrlInStore(array('_store' => $order->getStoreId()));
                $product_data['image'] = $full_product->getImageUrl();

                if ($full_product->getUpc()) {
                    $specs_data['upc'] = $full_product->getUpc();
                }

                if ($full_product->getIsbn()) {
                    $specs_data['isbn'] = $full_product->getIsbn();
                }

                if ($full_product->getBrand()) {
                    $specs_data['brand'] = $full_product->getBrand();
                }

                if ($full_product->getMpn()) {
                    $specs_data['mpn'] = $full_product->getMpn();
                }
                
                if ($full_product->getSku()) {
                    $specs_data['external_sku'] = $full_product->getSku();
                }

                if (!empty($specs_data)) {
                    $product_data['specs'] = $specs_data;
                }
            } catch (Exception $e) {
                Mage::log('error: ' . $e);
            }

            $product_data['description'] = Mage::helper('core')->htmlEscape(strip_tags($full_product->getDescription()));
            $product_data['price'] = $product->getPrice();

            $products_arr[$full_product->getId()] = $product_data;
        }

        return $products_arr;
    }

	public function createApiPost($path, $data, $timeout=self::DEFAULT_TIMEOUT) {
		try 
		{

			$config = array('timeout' => $timeout);
			$http = new Varien_Http_Adapter_Curl();
			$feed_url = self::YOTPO_SECURED_API_URL."/".$path;
			$http->setConfig($config);
			$http->write(Zend_Http_Client::POST, $feed_url, '1.1', array('Content-Type: application/json'), json_encode($data));
			$resData = $http->read();
			return array("code" => Zend_Http_Response::extractCode($resData), "body" => json_decode(Zend_Http_Response::extractBody($resData)));


		}
		catch(Exception $e)
		{
			Mage::log('error: ' .$e);
		}	
	}

	public function createApiGet($path, $timeout=self::DEFAULT_TIMEOUT) {
		try 
		{
			$config = array('timeout' => $timeout);
			$http = new Varien_Http_Adapter_Curl();
			$feed_url = self::YOTPO_UNSECURED_API_URL."/".$path;
			$http->setConfig($config);
			$http->write(Zend_Http_Client::GET, $feed_url, '1.1', array('Content-Type: application/json'));
			$resData = $http->read();
			return array("code" => Zend_Http_Response::extractCode($resData), "body" => json_decode(Zend_Http_Response::extractBody($resData)));

		} catch (Exception $e) 
		{
			Mage::log('error: '.$e);
		}
	}

    public function getAppKey($store_id)
    {
        return $this->app_keys[$store_id];
    }

	public function createPurchases($order, $store_id)
	{
       	$this->createApiPost("apps/".$this->app_keys[$store_id]."/purchases", $order);
	}

	public function massCreatePurchases($orders, $token, $store_id)
	{

		$data = array();
		$data['utoken'] = $token;
		$data['platform'] = 'magento';
		$data['orders'] = $orders;
		$this->createApiPost("apps/".$this->app_keys[$store_id]."/purchases/mass_create", $data);

	}
	
}