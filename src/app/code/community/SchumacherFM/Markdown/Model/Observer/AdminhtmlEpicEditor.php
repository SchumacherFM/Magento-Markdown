<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 *
 * avoiding to load codemirror files on every page in the adminhtml area.
 */
class SchumacherFM_Markdown_Model_Observer_AdminhtmlEpicEditor
{
    /**
     * using the body css class ... for better performance
     *
     * @var array
     */
    protected $_allowedEpicEditorPages = array(
        'adminhtml-cms-page-edit',
        'adminhtml-catalog-product-edit',
        'adminhtml-cms-block-edit',
        'adminhtml-catalog-category-edit',
    );

    /**
     *
     * @var array
     */
    protected $_epicEditorFiles = array(
        'js' => array(
            'markdown/adminhtml/epiceditor.min.js',
//            'markdown/adminhtml/epiceditor.js',
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
     * @param Mage_Core_Block_Abstract $block
     *
     * @return bool
     */
    protected function _isAllowedPageBlock(Mage_Core_Block_Abstract $block)
    {
        $class    = $block->getBodyClass();
        $hasClass = FALSE;
        foreach ($this->_allowedEpicEditorPages as $pageBodyClass) {
            if (strpos($class, $pageBodyClass) !== FALSE) {
                $hasClass = TRUE;
                break;
            }
        }

        $isPage = $block instanceof Mage_Adminhtml_Block_Page;

        return $isPage && $hasClass;
    }

    /**
     * @see https://twitter.com/iamdevloper/status/378464078895017984
     * fired: category_prepare_ajax_response
     *
     * @param Varien_Event_Observer $observer
     */
    public function injectEpicJsCatalogCategoryEdit(Varien_Event_Observer $observer)
    {
        $content = $observer->getEvent()->getResponse()->getContent();

        $js = '<script type="text/javascript">mdLoadEpicEditor(true);</script>';

        $content = str_replace(SchumacherFM_Markdown_Model_Observer_AdminhtmlBlock::CATALOG_CATEGORY_EDIT_JS_REPLACER, $js, $content);
        $observer->getEvent()->getResponse()->setContent($content);
        $content = NULL;
    }
}