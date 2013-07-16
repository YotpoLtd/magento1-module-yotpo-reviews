<?php

class Yotpo_Yotpo_Helper_Utils extends Mage_Core_Helper_Abstract {
	
	/**
	 * Creates a notice in admin notification inbox
	 */
	public function createAdminNotification($title, $desc, $url, $severity=Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE) {
		$message = Mage::getModel ( 'adminnotification/inbox' );
		$message->setDateAdded ( date ( "c", time () ) );
		
		$message->setTitle($title);
		$message->setDescription($desc);
		$message->setUrl($url);
		$message->setSeverity($severity);
		$message->save();
		
		return $this;
	}
}
