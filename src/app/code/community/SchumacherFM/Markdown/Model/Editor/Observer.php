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
        $config   = $observer->getEvent()->getConfig();
        $settings = Mage::getModel('markdown/editor_config')->getWysiwygPluginSettings($config);
        $config->addData($settings);
        return $this;
    }
}
