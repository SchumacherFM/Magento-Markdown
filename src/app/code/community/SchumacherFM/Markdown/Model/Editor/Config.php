<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Editor_Config
{
    /**
     * Prepare variable wysiwyg config
     *
     * @param Varien_Object $config
     *
     * @return array
     */
    public function getWysiwygPluginSettings($config)
    {
        $variableConfig            = array();
        $onclickParts              = array(
            'search'  => array('html_id'),
            'subject' => 'renderMarkdown(\'' . $this->getVariablesWysiwygActionUrl() . '\', \'{{html_id}}\');'
        );
        $variableWysiwygPlugin     = array(
            array(
                'name'    => 'markdown',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('adminhtml')->__('Preview Markdown'),
                    'url'     => $this->getVariablesWysiwygActionUrl(),
                    'onclick' => $onclickParts,
                    'class'   => 'plugin'
                )
            )
        );
        $configPlugins             = $config->getData('plugins');
        $variableConfig['plugins'] = array_merge($configPlugins, $variableWysiwygPlugin);
        return $variableConfig;
    }

    /**
     * Return url of action to get variables
     *
     * @return string
     */
    public function getVariablesWysiwygActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/markdown/ajaxRender');
    }
}
