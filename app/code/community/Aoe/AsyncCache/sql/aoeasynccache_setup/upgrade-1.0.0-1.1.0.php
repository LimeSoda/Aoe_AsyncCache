<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('aoeasynccache/asynccache');

$installer->getConnection()->addColumn($tableName, 'cache_type', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable' => false,
    'length' => 255,
    'default' => 'cache',
    'comment' => 'Cache Type',
));

// Replace the unique index with one accounting for cache type.
$installer->getConnection()->dropKey(
    $tableName,
    $installer->getIdxName('aoeasynccache/asynccache', array('mode', 'tags', 'status'))
);
$installer->getConnection()->addIndex(
    $installer->getTable('aoeasynccache/asynccache'),
    $installer->getIdxName('aoeasynccache/asynccache', array('cache_type', 'mode', 'tags', 'status')),
    array('cache_type', 'mode', 'tags', 'status'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
