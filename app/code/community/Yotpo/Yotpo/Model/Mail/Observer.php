<?php

class Yotpo_Yotpo_Model_Mail_Observer
{
	
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
		try {

			if (!Mage::helper('yotpo/apiClient')->isEnabled()) 
			{
				return $this;
			}
		
			$event = $observer->getEvent();
			$order = $event->getOrder();

			if ($order->getStatus() != 'complete') {
				return $this;

			}
			$data = array();
			
			$data["email"] = $order->getCustomerEmail();
			$data["customer_name"] = $order->getCustomerName();
			$data["order_id"] = $order->getIncrementId();
			$data['platform'] = 'magento';
			$data['currency_iso'] = $order->getOrderCurrency()->getCode();
			$data['products'] = Mage::helper('yotpo/apiClient')->prepareProductsData($order);

			$data['utoken'] = Mage::helper('yotpo/apiClient')->oauthAuthentication();
			if ($data['utoken'] == null) {
				//failed to get access token to api
				Mage::log('access token recieved from yotpo api is null');
				return $this;
			}

			Mage::helper('yotpo/apiClient')->createPurchases($data);
			return $this;	

		} catch(Exception $e) {
			Mage::log('Failed to send mail after purchase. Error: '.$e);
		}
	}
	
}