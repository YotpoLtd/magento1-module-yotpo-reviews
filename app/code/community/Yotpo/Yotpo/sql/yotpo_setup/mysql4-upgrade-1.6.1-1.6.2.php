<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS `yotpo_rich_snippets`;
    CREATE TABLE `yotpo_rich_snippets` (
      `rich_snippet_id` int(11) NOT NULL auto_increment,
      `product_id` int(11) NOT NULL,
      `store_id` int(11) NOT NULL,
      `average_score` float(11) NOT NULL,
      `reviews_count` int(11) NOT NULL,
      `expiration_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
      PRIMARY KEY  (`rich_snippet_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE UNIQUE INDEX yotpo_rich_snippets_product_id_store_id_i ON `yotpo_rich_snippets` (`product_id`, `store_id`);
");

$installer->endSetup();
