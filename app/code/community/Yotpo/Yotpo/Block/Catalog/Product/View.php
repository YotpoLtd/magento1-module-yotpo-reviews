<?php

class Yotpo_Yotpo_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View {

    const CATEGORY_BOTTOMLINE = 'yotpo/yotpo_general_group/enable_bottomline_category_page';

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

}
