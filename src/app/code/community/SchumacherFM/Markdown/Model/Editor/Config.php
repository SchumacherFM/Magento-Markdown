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
        $variableConfig        = array();
        $onclickPreview        = array(
            'search'  => array('html_id'),
            'subject' => Mage::helper('markdown')->getRenderMarkdownJs('{{html_id}}'),
        );
        $onclickSyntax         = array(
            'search'  => array('html_id'),
            'subject' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_SYNTAX . '\',\'{{html_id}}\');'
        );
        $variableWysiwygPlugin = array(
            array(
                'name'    => 'markdownToggle',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] enable'),
                    'url'     => '',
                    'onclick' => array(
                        'search'  => array('html_id'),
                        'subject' => 'toggleMarkdown(\'' . Mage::helper('markdown')->getDetectionTag(TRUE) . '\',\'{{html_id}}\');'
                    ),
                    'class'   => 'plugin'
                )
            ),
            array(
                'name'    => 'markdown',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] Preview'),
                    'url'     => '',
                    'onclick' => $onclickPreview,
                    'class'   => 'plugin'
                )
            ),
            array(
                'name'    => 'mdExternalUrl',
                'src'     => '',
                'options' => array(
                    'title'   => Mage::helper('markdown')->__('[M↓] Syntax'),
                    'url'     => '',
                    'onclick' => $onclickSyntax,
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
                        'subject' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_EXTRA_SYNTAX . '\',\'{{html_id}}\');'
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
