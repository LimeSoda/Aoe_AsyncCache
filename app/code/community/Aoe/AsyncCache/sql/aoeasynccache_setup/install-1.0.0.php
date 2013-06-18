<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* Create table asynccache */
$table = $installer->getConnection()->newTable($installer->getTable('aoeasynccache/asynccache'));

$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'primary'  => true,
    'unsigned' => true,
    'nullable' => false,
));
$table->addColumn('tstamp', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable' => false,
));
$table->addColumn('mode', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
    'nullable' => false,
    'default'  => ''
));
$table->addColumn('tags', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
    'nullable' => false,
    'default'  => ''
));
$table->addColumn('trace', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
    'nullable' => false,
));
$table->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
    'nullable' => false,
    'default'  => ''
));
$table->addColumn('processed', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    'nullable' => true,
));

$installer->getConnection()->createTable($table);

// add unique index
$installer->getConnection()->addIndex(
    $installer->getTable('aoeasynccache/asynccache'),
    $installer->getIdxName('aoeasynccache/asynccache', array('mode', 'tags', 'status')),
    array('mode', 'tags', 'status'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
