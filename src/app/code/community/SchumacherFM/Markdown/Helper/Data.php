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
     * @param array $options
     *
     * @return string
     */
    public function render($text,array $options = null)
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
}