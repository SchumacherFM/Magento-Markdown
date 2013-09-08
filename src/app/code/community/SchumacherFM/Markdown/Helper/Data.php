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
            ->renderMarkdown($text);
    }

    /**
     * @param bool $encoded
     *
     * @return mixed|string
     */
    public function getDetectionTag($encoded = FALSE)
    {
        $tag = Mage::getStoreConfig('schumacherfm/markdown/detection_tag');
        return $encoded ? rawurlencode($tag) : $tag;
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
     * @param string $type enum email|page|block ... last two not supported, maybe later.
     *
     * @return bool
     */
    public function isMarkdownExtra($type = null)
    {
        return (boolean)Mage::getStoreConfig('schumacherfm/markdown/md_extra' . (!empty($type) ? '_' . $type : ''));
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
        $file = Mage::getStoreConfig('schumacherfm/markdown/te_md_css');
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

    /**
     * @return string
     */
    public function getAdminRenderUrl()
    {
        return Mage::helper("adminhtml")->getUrl('*/markdown/render');
    }

    /**
     * @param string $htmlId
     *
     * @return string
     */
    public function getRenderMarkdownJs($htmlId)
    {
        $args = array('\'' . $htmlId . '\'', '\'' . Mage::helper('markdown')->getDetectionTag(TRUE) . '\'');
        if ($this->isMarkdownExtra()) {
            $args[] = '\'' . $this->getAdminRenderUrl() . '\'';
        }
        return 'renderMarkdown(' . implode(',', $args) . ');';
    }
}