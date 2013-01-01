<?php

class Yotpo_Yotpo_Model_Export_Csv extends Mage_Core_Model_Abstract
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    /**
     * export given reviews to csv file in var/export.
     *
     * @param $reviews List of reviews to export.
     * @return String The name of the written csv file in var/export
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
	 *
	 * @param $fp The file handle of the csv file
	 */
    protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
	 * Writes the row(s) for the given review in the csv file.
	 * A row is added to the csv file for each reviewed item.
	 *
	 * @param Mage_Sales_Model_Review $review The review to write csv of
	 * @param $fp The file handle of the csv file
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
            'Review Title',
            'Review Detail',
            'Nickname',
            'Review Status',         #STATUS_APPROVED = 1;STATUS_PENDING = 2;STATUS_NOT_APPROVED = 3;
            'Rating',
            'Product Sku',
            'Product Name',
            'Product Description',
            'Product Url',
            'Product Image Url'
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
            $product->getSku(),
            $product->getName(),
            $product->getDescription(),
            $product->getProductUrl(),
            $Image
        );
    }

    protected function getReviewItemValues($review)
    {
        return array(
            $review->getTitle(),
            $review->getDetail(),
            $review->getNickname(),
            $review->getStatusId(),
            $this->calculateReviewScore($review)
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