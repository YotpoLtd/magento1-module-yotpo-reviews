<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$prefix = (string) Mage::getConfig()->getTablePrefix();

$base_table = "yotpo_rich_snippets";
$table_with_quotes = "`" . $base_table .  "`";

if ($prefix != "") {
    $base_table = $prefix . $base_table;
    $table_with_quotes =  "`". $base_table . "`";
}

$sql =  "DROP TABLE IF EXISTS " . $table_with_quotes . ";
               CREATE TABLE " . $table_with_quotes . " (
          `rich_snippet_id` int(11) NOT NULL auto_increment,
          `product_id` int(11) NOT NULL,
          `store_id` int(11) NOT NULL,
          `average_score` float(11) NOT NULL,
          `reviews_count` int(11) NOT NULL,
          `expiration_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
          PRIMARY KEY  (`rich_snippet_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        CREATE UNIQUE INDEX " . $base_table . "_product_id_store_id_i ON " . $table_with_quotes . " (`product_id`, `store_id`);
";
$installer->run($sql);


//add notice to notification inbox if no app key or secret is inserted yet

$shouldShowNotification = false;

foreach (Mage::app()->getStores() as $store) {
    if (!Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey', $store)) {
        $shouldShowNotification = true;
        break;
    }
}

#handle single store magento site
if (!Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey', Mage::app()->getStore())) {
    $shouldShowNotification = true;
}

if ($shouldShowNotification) {
    Mage::helper('yotpo/Utils')->createAdminNotification
    (
        "Please visit the Yotpo extension page in your system configuration store settings page and finish the installation.",
        "In order to start generating reviews with Yotpo, you'll need to finish the installation process",
        "http://support.yotpo.com/entries/24858236-Configuring-Yotpo-after-installation?utm_source=customers_magento_admin&utm_medium=pop_up&utm_campaign=magento_not_installed_pop_up"
    );
}

$installer->endSetup();
