<?php

class Yotpo_Yotpo_Helper_RichSnippets extends Mage_Core_Helper_Abstract
{
    private $_config;

    public function __construct ()
    {
        $this->_config = Mage::getStoreConfig('yotpo');
    }

    public function getRichSnippet()
    {
        
        try {

            $productId = Mage::registry('product')->getId();
            $snippet = Mage::getModel('yotpo/richsnippet')->getSnippetByProductIdAndStoreId($productId, Mage::app()->getStore()->getId());
            
            if (($snippet == null) || (!$snippet->isValid())) {
                //no snippet for product or snippet isn't valid anymore. get valid snippet code from yotpo api
                
                $res = Mage::helper('yotpo/apiClient')->createApiGet("products/".($this->getAppKey())."/richsnippet/".$productId, 2);
                
                if ($res["code"] != 200) {
                    //product not found or feature disabled.
                    return "";
                }

                $body = $res["body"];
                $averageScore = $body->response->rich_snippet->reviews_average;
                $reviewsCount = $body->response->rich_snippet->reviews_count;
                $ttl = $body->response->rich_snippet->ttl;

                if ($snippet == null) {
                    $snippet = Mage::getModel('yotpo/richsnippet');
                    $snippet->setProductId($productId);
                    $snippet->setStoreId(Mage::app()->getStore()->getid());
                }

                $snippet->setAverageScore($averageScore);
                $snippet->setReviewsCount($reviewsCount);
                $snippet->setExpirationTime(date('Y-m-d H:i:s', time() + $ttl));
                $snippet->save();
                
                return array( "average_score" => $averageScore, "reviews_count" => $reviewsCount);
            }
            return array( "average_score" => $snippet->getAverageScore(), "reviews_count" => $snippet->getReviewsCount());

        } catch(Excpetion $e) {
            Mage::log($e);
        }
        return array();
    }

    private function getAppKey()
    {
        return trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey',Mage::app()->getStore()));
    }
}