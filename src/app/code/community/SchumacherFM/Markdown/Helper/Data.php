<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Helper
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Helper_Data extends Mage_Core_Helper_Abstract
{
    const URL_MD_SYNTAX       = 'http://daringfireball.net/projects/markdown/syntax';
    const URL_MD_EXTRA_SYNTAX = 'http://michelf.ca/projects/php-markdown/extra/';

    /**
     * easy access method for rendering markdown in phtml files
     * usage:
     * echo Mage::helper('markdown')->render($_product->getDescription())
     *
     * @param string $text
     * @param array  $options
     *
     * @return string
     */
    public function render($text, array $options = null)
    {
        return Mage::getSingleton('markdown/markdown_render')
            ->setOptions($options)
            ->renderMarkdown($text, TRUE);
    }

    /**
     * @todo if backend check for current selected store view / website
     *
     * @return bool
     */
    public function getDetectionTag()
    {
        return Mage::getStoreConfig('schumacherfm/markdown/detection_tag');
    }

    /**
     * @todo if backend check for current selected store view / website
     *
     * check if MD is enabled ... per store view
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !(boolean)Mage::getStoreConfig('schumacherfm/markdown/enable');
    }

    /**
     * @todo if backend check for current selected store view / website
     *       check if md extra is enabled ... per store view
     *
     * @return bool
     */
    public function isMarkdownExtra()
    {
        return (boolean)Mage::getStoreConfig('schumacherfm/markdown/md_extra');
    }

    /**
     * loads CSS files and minifies it
     *
     * @todo if backend check for current selected store view / website
     *       check if md extra is enabled ... per store view
     *
     * @return string
     */
    public function getTransactionalEmailCSS()
    {
        $file = Mage::getStoreConfig('schumacherfm/markdown/te_markdown_css');
        if (empty($file)) {
            return '';
        }
        $content = trim(implode('', @file(Mage::getBaseDir() . DS . $file)));
        if (empty($content)) {
            Mage::log('Markdown CSS file [' . $file . '] is empty!');
        }
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content); // comments
        $content = preg_replace('~\s+~', ' ', $content); // all whitespaces
        $content = preg_replace('~\s*(:|\{|\}|,|;)\s*~', '\\1', $content); // all other whitespaces
        return $content;
    }
}