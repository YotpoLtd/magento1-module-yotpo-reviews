<?php


class Yotpo_Yotpo_Adminhtml_YotpoController extends Mage_Adminhtml_Controller_Action
{
   
    //max amount of orders to export
    const MAX_ORDERS_TO_EXPORT = 5000;
    const MAX_BULK_SIZE        = 200;

    public function massMailAfterPurchaseAction() {

        try {
            ini_set('max_execution_time', 300);
       
            $result = null;

            $store_code = Mage::app()->getRequest()->getParam('store');

            $current_store = null;

            foreach (Mage::app()->getStores() as $store) {
                if ($store->getCode() == $store_code) {
                    global $current_store;
                    $current_store = $store;
                    break;
                }
            }

            $store_id = $current_store->getId();

            if (Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey', $current_store) == null or
                Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_secret', $current_store) == null
                )
            {
               Mage::app()->getResponse()->setBody('Please make sure you insert your APP KEY and SECRET and save configuration before trying to export past orders');
                return;   
            }

            $token = Mage::helper('yotpo/apiClient')->oauthAuthentication($store_id);
            if ($token == null) 
            {                
                Mage::app()->getResponse()->setBody("Please make sure the APP KEY and SECRET you've entered are correct");
                return;
            }


            $today = time();
            $last = $today - (60*60*24*90); //90 days ago
            $from = date("Y-m-d", $last);
            $offset = 0;
            $salesModel=Mage::getModel("sales/order");
            $orderStatuses = Mage::getStoreConfig('yotpo/yotpo_general_group/custom_order_status', $current_store);
            if ($orderStatuses == null) {
                $orderStatuses = array('complete');
            } else {
                $orderStatuses = array_map('strtolower', explode(' ', $orderStatuses));
            }
            $salesCollection = $salesModel->getCollection()
                    ->addFieldToFilter('status', $orderStatuses)
                    ->addFieldToFilter('store_id', $store_id)
                    ->addAttributeToFilter('created_at', array('gteq' =>$from))
                    ->addAttributeToSort('created_at', 'DESC')
                    ->setPageSize(self::MAX_BULK_SIZE);

            $pages = $salesCollection->getLastPageNumber();

            do {

                try {

                    $offset++;
                    $salesCollection->setCurPage($offset)->load();
                 
                    $orders = array();

                    foreach($salesCollection as $order)
                    {

                        $order_data = array();
                        $order_data['order_id'] = $order->getIncrementId();
                        $order_data["email"] = $order->getCustomerEmail();
                        $order_data["customer_name"] = $order->getCustomerName();
                        $order_data["order_date"] = $order->getCreatedAtDate()->toString('yyyy-MM-dd HH:mm:ss');
                        $order_data['currency_iso'] = $order->getOrderCurrency()->getCode();
                        $order_data['products'] = Mage::helper('yotpo/apiClient')->prepareProductsData($order);

                        $orders[] = $order_data;
                    }

                    if (count($orders) > 0) 
                    {
                        Mage::helper('yotpo/apiClient')->massCreatePurchases($orders, $token, $store_id);
                    }
                    
                } catch (Exception $e) {
                    Mage::log('Failed to export past orders. Error: '.$e);    
                }

                $salesCollection->clear();

            } while ($offset <= (self::MAX_ORDERS_TO_EXPORT / self::MAX_BULK_SIZE) && $offset < $pages);

        } catch(Exception $e) {
            Mage::log('Failed to export past orders. Error: '.$e);
        }

        Mage::app()->getResponse()->setBody(1);
    } 
}