<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 *
 * avoiding to load files on every page in the adminhtml area.
 */
class SchumacherFM_Markdown_Model_Observer_Adminhtml_EpicEditor
{
    /**
     *
     * @var array
     */
    protected $_epicEditorFiles = array(
        'js' => array(
            'markdown/adminhtml/epiceditor.js',
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

        if ($this->_isAllowedBlock($block) === FALSE) {
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
    protected function _isAllowedBlock(Mage_Core_Block_Abstract $block)
    {
        $isPage                = $block instanceof Mage_Adminhtml_Block_Page;
        $isLayoutHandleAllowed = Mage::getSingleton('markdown/observer_adminhtml_layoutUpdate')->isAllowed();
        return $isPage && $isLayoutHandleAllowed;
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