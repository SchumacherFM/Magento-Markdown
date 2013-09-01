<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Helper
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * easy access method for rendering markdown in phtml files
     * usage:
     * echo Mage::helper('markdown')->render($_product->getDescription())
     *
     * @param string $text
     *
     * @return string
     */
    public function render($text)
    {
        return Mage::getSingleton('markdown/markdown_render')->renderMarkdown($text, TRUE);
    }

    /**
     * check if MD is enabled ... per store view
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !(boolean)Mage::getStoreConfig('schumacherfm/markdown/enable');
    }

    /**
     * check if md extra is enabled ... per store view
     *
     * @return bool
     */
    public function isMarkdownExtra()
    {
        return (boolean)Mage::getStoreConfig('schumacherfm/markdown/md_extra');
    }
}