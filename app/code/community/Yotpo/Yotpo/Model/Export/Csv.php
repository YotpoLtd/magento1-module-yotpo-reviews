<?php

class Yotpo_Yotpo_Model_Export_Csv extends Mage_Core_Model_Abstract
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    /**
     * export given reviews to csv file in var/export.
     */
    public function exportReviews($reviewsToExport)
    {
        try 
        {
            $fileName = 'review_export_'.date("Ymd_His").'.csv';
            $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');                
            $this->writeHeadRow($fp);

            # Load all reviews with thier votes
            $allReviews = Mage::getModel('review/review')
                            ->getResourceCollection()
                            ->addStoreFilter(Mage::app()->getStore()->getId()) 
                            ->addRateVotes();

            foreach ($allReviews as $fullReview) 
            {   
                # check if we want to export the current review
                foreach ($reviewsToExport as $reviewId) 
                {
                    if ($fullReview->getId() == $reviewId) 
                    {
                        $this->writeReview($fullReview, $fp);
                        break;
                    }
                }
            }

            fclose($fp);

            return $fileName;
        } 
        catch (Exception $e) 
        {
            Mage::log($e->getMessage());  
        }
    }

    /**
	 * Writes the head row with the column names in the csv file.
	 */
    protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
	 * Writes the row(s) for the given review in the csv file.
	 * A row is added to the csv file for each reviewed item.
	 */
    protected function writeReview($review, $fp)
    {
        $productId = $review->getData("entity_pk_value");
        $record = array_merge($this->getReviewItemValues($review), $this->getProductItemValues($productId));
        fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
    }

    protected function getHeadRowValues()
    {
        return array(
            'review_title',
            'review_content',
            'display_name',
            'review_status',         #STATUS_APPROVED = 1;STATUS_PENDING = 2;STATUS_NOT_APPROVED = 3;
            'review_score',
            'review_date',
            'sku',
            'product_title',
            'product_description',
            'product_url',
            'product_image_url',
            'appkey'
        );
    }

    protected function getProductItemValues($product_id)
    {
        $obj = Mage::getModel('catalog/product');
        $product = $obj->load($product_id);

        $Image = "";
        try
        {
            $Image = $product->getImageUrl();
        }
        catch (Exception $e){}

        return array(
            $product_id,
            $product->getName(),
            $product->getShortDescription(),
            $product->getProductUrl(),
            $Image,
            Mage::getModel('Yotpo_Yotpo_Block_Yotpo')->getAppKey()
        );
    }

    protected function getReviewItemValues($review)
    {
        return array(
            $review->getTitle(),
            $review->getDetail(),
            $review->getNickname(),
            $review->getStatusId(),
            $this->calculateReviewScore($review),
            $review->getData("created_at")
        );
    }

    protected function calculateReviewScore($review)
    {
        $avg = 0;
        $ratings = array();
        foreach($review->getRatingVotes() as $vote) 
        {
            $ratings[] = $vote->getValue();                    
        }

        if (count($ratings) > 0) 
        {
            $avg = array_sum($ratings)/count($ratings);
            return $avg;
        }
    }
}
?>