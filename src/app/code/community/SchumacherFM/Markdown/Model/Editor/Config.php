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
        $variableConfig = array();

        $variableWysiwygPlugin = array(
            array(
                'name'    => 'markdownToggle',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] enable'),
                    'url'     => '',
                    'onclick' => array(
                        'search'  => array('html_id'),
                        'subject' => 'toggleMarkdown(\'{{html_id}}\');'
                    ),
                    'class'   => 'plugin'
                )
            ),
            array(
                'name'    => 'markdownToggle',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] Source'),
                    'url'     => '',
                    'onclick' => array(
                        'search'  => array('html_id'),
                        'subject' => 'toggleMarkdownSource(\'{{html_id}}\');'
                    ),
                    'class'   => 'plugin'
                )
            ),
            array(
                'name'    => 'mdExternalUrl',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] Syntax'),
                    'url'     => '',
                    'onclick' => array(
                        'search'  => array('html_id'),
                        'subject' => 'mdExternalUrl(\'' . Mage::helper('markdown')->getCheatSheetUrl() . '\');'
                    ),
                    'class'   => 'plugin'
                )
            ),
        );

        if (Mage::helper('markdown')->isMarkdownExtra()) {
            $variableWysiwygPlugin[] = array(
                'name'    => 'markdownextrasyntax',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] Extra Syntax'),
                    'url'     => '',
                    'onclick' => array(
                        'search'  => array('html_id'),
                        'subject' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_EXTRA_SYNTAX . '\');'
                    ),
                    'class'   => 'plugin'
                )
            );
        }
        if (Mage::helper('markdown')->isEpicEditorEnabled()) {
            $variableWysiwygPlugin[] = array(
                'name'    => 'epiceditor',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('EpicEditor on/off'),
                    'url'     => '',
                    'onclick' => array(
                        'search'  => array('html_id'),
                        'subject' => 'toggleEpicEditor(\'{{html_id}}\');'
                    ),
                    'class'   => 'plugin'
                )
            );
        }

        $configPlugins             = $config->getData('plugins');
        $variableConfig['plugins'] = array_merge($configPlugins, $variableWysiwygPlugin);
        return $variableConfig;
    }
}
