<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

/* @var $configurationModel Mage_Core_Model_Config */
$configurationModel = Mage::getModel('core/config');
$configurationModel->saveConfig('cms/wysiwyg/enabled', 'disabled');
$installer->endSetup();