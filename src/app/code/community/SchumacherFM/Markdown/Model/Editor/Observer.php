<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Editor_Observer
{
    /**
     * Add markdown wysiwyg plugin config
     *
     * @param Varien_Event_Observer $observer
     *
     * @return SchumacherFM_Markdown_Model_Editor_Observer
     */
    public function prepareWysiwygPluginConfig(Varien_Event_Observer $observer)
    {
        if (Mage::helper('markdown')->isDisabled()) {
            return null;
        }

        $config   = $observer->getEvent()->getConfig();
        $settings = Mage::getModel('markdown/editor_config')->getWysiwygPluginSettings($config);
        $config->addData($settings);
        return $this;
    }

    /**
     * is Markdown is enabled then disable completely the wysiwyg editor
     *
     * @param Varien_Event_Observer $observer
     */
    public function enableDisableWysiwyg(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Config_Data $data */
        $data = $observer->getEvent()->getDataObject()->getData();

        if (isset($data['groups']['markdown']) && isset($data['groups']['markdown']['fields']['enable'])) {
            // @todo watch for store code ...
            $isEnabled          = (boolean)$data['groups']['markdown']['fields']['enable']['value'];
            $configurationModel = Mage::getModel('core/config');
            $configurationModel->saveConfig('cms/wysiwyg/enabled', $isEnabled ? 'disabled' : 'enabled');
        }
    }
}
