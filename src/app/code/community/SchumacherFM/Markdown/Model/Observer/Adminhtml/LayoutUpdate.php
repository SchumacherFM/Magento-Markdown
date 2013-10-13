<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Observer_Adminhtml_LayoutUpdate
{

    protected $_isAllowedFlag = FALSE;

    /**
     * adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function injectMarkdownFiles(Varien_Event_Observer $observer)
    {
        if (Mage::helper('markdown')->isDisabled()) {
            return NULL;
        }

        /** @var Mage_Core_Model_Layout $layout */
        $layout = $observer->getEvent()->getLayout();

        /** @var Mage_Core_Model_Layout_Update $update */
        $update = $layout->getUpdate();

        if ($this->_isAllowed($update)) {
            $update->addHandle('MARKDOWN_HEADER');
        }

    }

    /**
     * @param Mage_Core_Model_Layout_Update $update
     *
     * @return bool
     */
    protected function _isAllowed(Mage_Core_Model_Layout_Update $update)
    {

        $handles        = $update->getHandles();
        $allowedHandles = Mage::helper('markdown')->getAllowedLayoutHandles();

        foreach ($handles as $handle) {
            if (isset($allowedHandles[$handle])) {
                $this->_isAllowedFlag = TRUE;
                break;
            }
        }
        return $this->_isAllowedFlag;
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->_isAllowedFlag;
    }

}