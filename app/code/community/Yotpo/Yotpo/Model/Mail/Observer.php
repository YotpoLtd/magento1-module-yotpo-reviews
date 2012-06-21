<?php

class Yotpo_Yotpo_Model_Mail_Observer
{
	
	const YOTPO_OAUTH_TOKEN_URL = "https://oauth.yotpo.com/oauth/token";
	const YOTPO_API_URL = "https://api.yotpo.com/apps";
	
	public function __construct()
	{
	}
	
	/**
	* send an api call to yotpo noitifying about new purchase
	* @param Varien_Event_Observer $observer
	* @return Yotpo_Yotpo_Model_Mail_Observer
	*/
	public function mail_after_purchase($observer)
	{
		
	
		$OAuthStorePath = Mage::getModuleDir('', 'Yotpo_Yotpo') . DS . 'lib' . DS .'oauth-php/library/OAuthStore.php';
		$OAuthRequesterPath = Mage::getModuleDir('', 'Yotpo_Yotpo') . DS . 'lib' . DS .'oauth-php/library/OAuthRequester.php';
		require_once ($OAuthStorePath);
		require_once ($OAuthRequesterPath);
		
		
		$app_key = Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey',Mage::app()->getStore());
		$secret = Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_secret', Mage::app()->getStore());
		$disable_feature = Mage::getStoreConfig('yotpo/yotpo_mail_after_purchase_group/disable_feature', Mage::app()->getStore());
		
			Mage::log('app_key: '.$app_key);
			Mage::log('secret: '.$secret);
		
		//check if both app_key and secret exist
		if(($app_key == null) or ($secret == null))
		{
			return $this;
		}
		
		//check if feature is disabled by store owner
		if ($disable_feature == TRUE) 
		{
			return $this;
		}
		
		$event = $observer->getEvent();
		$order = $event->getOrder();
		
		$data = array();
		
		$data["email"] = $order->getCustomerEmail();
		$data["customer_name"] = $order->getCustomerName();
		$data["order_id"] = $order->getIncrementId();
		$data['platform'] = 'magento';
		
		$products = $order->getAllItems();
		
		$products_arr = array();
		
		foreach ($products as $product) {
			
			$product_data = array();
			$product_data['name'] = $product->getName();
			$product_data['url'] = Mage::getModel('catalog/product')->load($product->getProductId())->getProductUrl();	
		
			$products_arr[$product->getProductId()] = $product_data;
			
		}

		$data['products'] = $products_arr;

		$yotpo_options = array( 'consumer_key' => $app_key, 'consumer_secret' => $secret, 'client_id' => $app_key, 'client_secret' => $secret, 'grant_type' => 'client_credentials' );
		OAuthStore::instance("2Leg", $yotpo_options );

		try
		{
        	
        	$request = new OAuthRequester(self::YOTPO_OAUTH_TOKEN_URL, "POST", $yotpo_options);        	
        	$result = $request->doRequest(0);
			
			$tokenParams = json_decode($result['body'], true);
        	$data['utoken'] = $tokenParams['access_token'];
        	
			$config = array('timeout' => 30);
			$http = new Varien_Http_Adapter_Curl();
			$feed_url = self::YOTPO_API_URL.DS.$app_key."/purchases/";
			$http->setConfig($config);
			$http->write(Zend_Http_Client::POST, $feed_url, '1.1', array(), http_build_query($data));
			$data = $http->read();

		}
		catch(OAuthException2 $e)
		{
		
			Mage::log('error: ' .$e);
		}

		return $this;	
	}
	
}