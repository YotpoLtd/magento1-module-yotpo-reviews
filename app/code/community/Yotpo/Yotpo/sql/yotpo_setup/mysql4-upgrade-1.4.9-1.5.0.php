<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS `{$installer->getTable('yotpo/richsnippet')}`;
    CREATE TABLE `{$installer->getTable('yotpo/richsnippet')}` (
      `rich_snippet_id` int(11) NOT NULL auto_increment,
      `product_id` int(11) NOT NULL,
      `store_id` int(11) NOT NULL,
      `html_code` text,
      `expiration_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
      PRIMARY KEY  (`rich_snippet_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE UNIQUE INDEX yotpo_rich_snippets_product_id_store_id_i ON `{$installer->getTable('yotpo/richsnippet')}` (`product_id`, `store_id`);
");

$installer->endSetup();
