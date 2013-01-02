<?php

class Yotpo_Yotpo_Review_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Exports reviews by id in post param "reviews" to csv and download file when finished.
     */
    public function csvexportAction()
    {
    	$reviews = $this->getRequest()->getPost('reviews', array());
		$file = Mage::getModel('Yotpo_Yotpo_Model_Export_Csv')->exportReviews($reviews);
        $this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
}
?>