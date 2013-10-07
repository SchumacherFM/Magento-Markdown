<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 *
 * avoiding to load files on every page in the adminhtml area.
 */
class SchumacherFM_Markdown_Model_Observer_AdminhtmlEpicEditor
{
    /**
     * using the body css class ... for better performance ... ?
     * add additional css body classes here where the editor should be loaded
     *
     * @var array
     */
    protected $_allowedEpicEditorPages = array(
        'adminhtml-cms-page-edit',
        'adminhtml-catalog-product-edit',
        'adminhtml-cms-block-edit',
        'adminhtml-catalog-category-edit',
        'adminhtml-system-email-template-edit',
    );

    /**
     *
     * @var array
     */
    protected $_epicEditorFiles = array(
        'js' => array(
            'markdown/adminhtml/epiceditor.js',
            'markdown/adminhtml/highlight.pack.js',
        ),
    );

    /**
     * adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function injectEpicEditor(Varien_Event_Observer $observer)
    {
        if (Mage::helper('markdown')->isDisabled() || !Mage::helper('markdown')->isEpicEditorEnabled()) {
            return NULL;
        }

        /** @var $block Mage_Adminhtml_Block_Page */
        $block = $observer->getEvent()->getBlock();

        if (!$this->_isAllowedPageBlock($block)) {
            return NULL;
        }
        /** @var Mage_Adminhtml_Block_Page_Head $headBlock */
        $headBlock = $block->getLayout()->getBlock('head');

        if (isset($this->_epicEditorFiles['js'])) {
            foreach ($this->_epicEditorFiles['js'] as $js) {
                $headBlock->addJs($js);
            }
        }
        if (isset($this->_epicEditorFiles['css'])) {
            foreach ($this->_epicEditorFiles['css'] as $css) {
                $headBlock->addCss($css);
            }
        }
        return NULL;
    }

    /**
     * dispatches also an event for modifying the css classes
     *
     * @return array
     */
    protected function _getAllowedEpicEditorPages()
    {
        Mage::dispatchEvent('markdown_adminhtml_epiceditor_inject', array('observer' => $this));
        return $this->_allowedEpicEditorPages;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     *
     * @return bool
     */
    protected function _isAllowedPageBlock(Mage_Core_Block_Abstract $block)
    {
        $class = $block->getBodyClass();
        if (empty($class)) {
            return FALSE;
        }

        $hasClass                = FALSE;
        $_allowedEpicEditorPages = $this->_getAllowedEpicEditorPages();
        foreach ($_allowedEpicEditorPages as $pageBodyClass) {
            if (strpos($class, $pageBodyClass) !== FALSE) {
                $hasClass = TRUE;
                break;
            }
        }

        $isPage = $block instanceof Mage_Adminhtml_Block_Page;

        return $isPage && $hasClass;
    }

    /**
     * @param array $allowedEpicEditorPages
     */
    public function setAllowedEpicEditorPages($allowedEpicEditorPages)
    {
        $this->_allowedEpicEditorPages = $allowedEpicEditorPages;
    }

    /**
     * @return array
     */
    public function getAllowedEpicEditorPages()
    {
        return $this->_allowedEpicEditorPages;
    }

    /**
     * @param array $epicEditorFiles
     */
    public function setEpicEditorFiles($epicEditorFiles)
    {
        $this->_epicEditorFiles = $epicEditorFiles;
    }

    /**
     * @return array
     */
    public function getEpicEditorFiles()
    {
        return $this->_epicEditorFiles;
    }
}