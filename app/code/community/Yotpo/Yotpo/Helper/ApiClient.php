<?php
class Yotpo_Yotpo_Helper_ApiClient extends Mage_Core_Helper_Abstract
{

    const YOTPO_OAUTH_TOKEN_URL = "https://api.yotpo.com/oauth/token";
    const YOTPO_SECURED_API_URL = "https://api.yotpo.com";
    const YOTPO_UNSECURED_API_URL = "http://api.yotpo.com";
    const DEFAULT_TIMEOUT = 30;

    protected $disable_feature = null;
    protected $appKeys = array();
    protected $secrets = array();

    public function __construct()
    {
        foreach (Mage::app()->getStores() as $store) {
            $storeId = $store->getId();
            $this->appKeys[$storeId] = trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey', $store));
            $this->secrets[$storeId] = trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_secret', $store));
        }
    }

    public function oauthAuthentication($storeId)
    {

        $storeAppKey = $this->appKeys[$storeId];
        $storeSecret = $this->secrets[$storeId];

        if ($storeAppKey == null or $storeSecret == null) {
            Mage::log('Missing app key or secret');
            return null;
        }

        $yotpoOptions = array('client_id' => $storeAppKey, 'client_secret' => $storeSecret, 'grant_type' => 'client_credentials');

        try {
            $result = $this->createApiPost('oauth/token', $yotpoOptions);
            return $result['body']->access_token;
        } catch (Exception $e) {
            Mage::log('error: ' . $e);
            return null;
        }
    }

    public function isEnabled($storeId)
    {
        //check if both app_key and secret exist
        if (($this->appKeys[$storeId] == null) or ( $this->secrets[$storeId] == null)) {
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

            $configurableProductModel = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $configurableProductModel->getParentIdsByChild($full_product->getId());
            if (count($parentIds) > 0) {
                $full_product = Mage::getModel('catalog/product')->load($parentIds[0]);
            }

            $productData = array();

            $productData['name'] = $full_product->getName();
            $productData['sku'] = $full_product->getSku();
            $productData['url'] = '';
            $productData['image'] = '';
            try {
                $productData['url'] = $full_product->getUrlInStore(array('_store' => $order->getStoreId()));
                $productData['image'] = $full_product->getImageUrl();
                if($full_product->getUpc()){
                $productData['upc'] = $full_product->getUpc();
                }
                if($full_product->getIsbn()){
                $productData['isbn'] = $full_product->getIsbn();
                }
                if($full_product->getBrand()){
                $productData['brand'] = $full_product->getBrand();
                }
                if($full_product->getMpn()){
                $productData['mpn'] = $full_product->getMpn();
                }
            } catch (Exception $e) {
                $productData['name'] = '';
            }

            $productData['description'] = Mage::helper('core')->htmlEscape(strip_tags($full_product->getDescription()));
            $productData['price'] = $product->getPrice();

            $products_arr[$full_product->getId()] = $productData;
        }

        return $products_arr;
    }

    public function createApiPost($path, $data, $timeout = self::DEFAULT_TIMEOUT)
    {
        try {

            $config = array('timeout' => $timeout);
            $http = new Varien_Http_Adapter_Curl();
            $feed_url = self::YOTPO_SECURED_API_URL . "/" . $path;
            $http->setConfig($config);
            $http->write(Zend_Http_Client::POST, $feed_url, '1.1', array('Content-Type: application/json'), json_encode($data));
            $resData = $http->read();
            return array("code" => Zend_Http_Response::extractCode($resData), "body" => json_decode(Zend_Http_Response::extractBody($resData)));
        } catch (Exception $e) {
            Mage::log('error: ' . $e);
        }
    }

    public function createApiGet($path, $timeout = self::DEFAULT_TIMEOUT)
    {
        try {
            $config = array('timeout' => $timeout);
            $http = new Varien_Http_Adapter_Curl();
            $feed_url = self::YOTPO_UNSECURED_API_URL . "/" . $path;
            $http->setConfig($config);
            $http->write(Zend_Http_Client::GET, $feed_url, '1.1', array('Content-Type: application/json'));
            $resData = $http->read();
            return array("code" => Zend_Http_Response::extractCode($resData), "body" => json_decode(Zend_Http_Response::extractBody($resData)));
        } catch (Exception $e) {
            Mage::log('error: ' . $e);
        }
    }

    public function getAppKey($storeId)
    {
        return $this->appKeys[$storeId];
    }

    public function createPurchases($order, $storeId)
    {
        $this->createApiPost("apps/" . $this->appKeys[$storeId] . "/purchases", $order);
    }

    public function massCreatePurchases($orders, $token, $storeId)
    {

        $data = array();
        $data['utoken'] = $token;
        $data['platform'] = 'magento';
        $data['orders'] = $orders;
        $this->createApiPost("apps/" . $this->appKeys[$storeId] . "/purchases/mass_create", $data);
    }
}
