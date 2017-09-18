<?php
class Yotpo_Yotpo_Adminhtml_YotpoController extends Mage_Adminhtml_Controller_Action {
    //max amount of orders to export
    const MAX_ORDERS_TO_EXPORT = 5000;
    const MAX_BULK_SIZE = 200;
    public function massMailAfterPurchaseAction() 
    {
        try {
            ini_set('max_execution_time', 300);
            $result = null;
            $storeCode = Mage::app()->getRequest()->getParam('store');
            
            $currentStore = null;
            foreach (Mage::app()->getStores() as $store) {
                if ($store->getCode() == $storeCode) {
                    $currentStore = $store;
                    break;
                }
            }
            $storeId = $currentStore->getId();
            if (Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey', $currentStore) == null or
                    Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_secret', $currentStore) == null
            ) {
                Mage::app()->getResponse()->setBody('Please make sure you insert your APP KEY and SECRET and save configuration before trying to export past orders');
                return;
            }
            $token = Mage::helper('yotpo/apiClient')->oauthAuthentication($storeId);
            if ($token == null) {
                Mage::app()->getResponse()->setBody("Please make sure the APP KEY and SECRET you've entered are correct");
                return;
            }
            $today = time();
            $last = $today - (60 * 60 * 24 * 90); //90 days ago
            $from = date("Y-m-d", $last);
            $offset = 0;
            $salesModel = Mage::getModel("sales/order");
            $orderStatuses = Mage::getStoreConfig('yotpo/yotpo_general_group/custom_order_status', $currentStore);
            if ($orderStatuses == null) {
                $orderStatuses = array('complete');
            } else {
                $orderStatuses = array_map('strtolower', explode(' ', $orderStatuses));
            }
            $salesCollection = $salesModel->getCollection()
                    ->addFieldToFilter('status', $orderStatuses)
                    ->addFieldToFilter('store_id', $storeId)
                    ->addAttributeToFilter('created_at', array('gteq' => $from))
                    ->addAttributeToSort('created_at', 'DESC')
                    ->setPageSize(self::MAX_BULK_SIZE);

            $pages = $salesCollection->getLastPageNumber();
            do {
                try {
                    $offset++;
                    $salesCollection->setCurPage($offset)->load();
                    $orders = array();
                    foreach ($salesCollection as $order) {
                        $$orderData = array();
                        if (!$order->getCustomerIsGuest()) {
                            $$orderData["user_reference"] = $order->getCustomerId();
                        }
                        $$orderData['order_id'] = $order->getIncrementId();
                        $$orderData["email"] = $order->getCustomerEmail();
                        $$orderData["customer_name"] = $order->getCustomerName();
                        $$orderData["order_date"] = $order->getCreatedAtDate()->toString('yyyy-MM-dd HH:mm:ss');
                        $$orderData['currency_iso'] = $order->getOrderCurrency()->getCode();
                        $$orderData['products'] = Mage::helper('yotpo/apiClient')->prepareProductsData($order);
                        $orders[] = $$orderData;
                    }
                    if (count($orders) > 0) {
                        Mage::helper('yotpo/apiClient')->massCreatePurchases($orders, $token, $storeId);
                    }
                } catch (Exception $e) {
                    Mage::log('Failed to export past orders. Error: ' . $e);
                }

                $salesCollection->clear();
            } while ($offset <= (self::MAX_ORDERS_TO_EXPORT / self::MAX_BULK_SIZE) && $offset < $pages);
        } catch (Exception $e) {
            Mage::log('Failed to export past orders. Error: ' . $e);
        }

        Mage::app()->getResponse()->setBody(1);
    }

    protected function _isAllowed() 
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/product');
    }

}
