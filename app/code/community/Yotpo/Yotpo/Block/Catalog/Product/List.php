<?php

class Yotpo_Yotpo_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List {

	const CATEGORY_BOTTOMLINE = 'yotpo/yotpo_general_group/enable_bottomline_category_page';
	const CATEGORY_DEFAULT_GRID = 'catalog/frontend/grid_per_page';
    const CATEGORY_DEFAULT_LIST = 'catalog/frontend/list_per_page';
    protected function _getProductCollection() {
        $page = Mage::app()->getRequest()->getParam('p');
        $limit = $this->getLimit();
        $_productCollection = parent::_getProductCollection();
        $_productCollection->setCurPage($page)
                           ->setPageSize($limit);
        $enableBottomlineCategoryPage = Mage::getStoreConfig(self::CATEGORY_BOTTOMLINE);
        if ($enableBottomlineCategoryPage) {

            foreach ($_productCollection as $_product) {
                $_product->setRatingSummary(true);
            }
        }
        return $_productCollection;
    }

    public function getReviewsSummaryHtml(Mage_Catalog_Model_Product $product, $templateType = false, $displayIfNoReviews = false) {

        $enableBottomlineCategoryPage = Mage::getStoreConfig(self::CATEGORY_BOTTOMLINE);
        if ($enableBottomlineCategoryPage) {
			
			return '<div class="yotpo bottomLine"
						data-product-id="'.$product->getId().'"
						data-url="'.$product->getUrl().'">
					</div>';
        } else {
            return parent::getReviewsSummaryHtml($product, $templateType, $displayIfNoReviews);
        }
    }
	
	private function getLimit() {
        $limit = Mage::app()->getRequest()->getParam('limit');
        if (empty($limit)) {
            $mode = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar')->getCurrentMode();
            if ($mode != 'grid') {
                $limit = Mage::getStoreConfig(self::CATEGORY_DEFAULT_LIST);
            } else {
                $limit = Mage::getStoreConfig(self::CATEGORY_DEFAULT_GRID);
            }
        }

        return $limit;
    }

}
