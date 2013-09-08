<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Markdown_Observer extends SchumacherFM_Markdown_Model_Markdown_Abstract
{
    /**
     * @var null
     */
    protected $_currentObserverMethod = null;

    /**
     * @var array
     */
    protected $_mdExtraUsage = array(
        // observer method => config value
        'renderEmailTemplate' => 'email',
    );

    /**
     * @return null|bool null = use global
     */
    protected function _isObserverMdExtraUsage()
    {
        $isset = isset($this->_mdExtraUsage[$this->_currentObserverMethod]);
        if (!$isset) {
            return null;
        }

        return Mage::helper('markdown')->isMarkdownExtra($this->_mdExtraUsage[$this->_currentObserverMethod]);
    }

    /**
     * @return bool
     */
    protected function _getIsExtraRenderer()
    {
        $globalExtra           = parent::_getIsExtraRenderer();
        $_observerMdExtraUsage = $this->_isObserverMdExtraUsage();
        if ($_observerMdExtraUsage === null) {
            return $globalExtra;
        }
        return $_observerMdExtraUsage;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function renderEmailTemplate(Varien_Event_Observer $observer)
    {
        $this->_currentObserverMethod = __FUNCTION__;
        if ($this->_isDisabled) {
            return null;
        }

        $object = $observer->getEvent()->getObject();
        if (!$object instanceof Mage_Core_Model_Email_Template) {
            return null;
        }

        $template = $object->getData('template_text');

        if ($this->isMarkdown($template)) {
            $object->setData('template_text', $this->_renderMarkdown($template));
            $css = Mage::helper('markdown')->getTransactionalEmailCSS();
            $object->setData('template_styles', $css);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function renderPage(Varien_Event_Observer $observer)
    {
        $this->_currentObserverMethod = __FUNCTION__;
        if ($this->_isDisabled) {
            return null;
        }

        /** @var Mage_Cms_Model_Page $page */
        $page = $observer->getEvent()->getPage();
        if (!$page instanceof Mage_Cms_Model_Page) {
            return null;
        }
        $content = $this->_renderMarkdown($page->getContent());
        $page->setContent($content);
    }

    /**
     * renders every block as markdown except those having the html tags of method _isMarkdown in it
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function renderBlock(Varien_Event_Observer $observer)
    {
        $this->_currentObserverMethod = __FUNCTION__;
        if ($this->_isDisabled) {
            return null;
        }

        /** @var Mage_Cms_Block_Block $page */
        $block = $observer->getEvent()->getBlock();

        if (!$this->_isAllowedBlock($block)) {
            return null;
        }

        /** @var Varien_Object $transport */
        $transport = $observer->getEvent()->getTransport();

        /**
         * you can set on any block the property ->setData('is_markdown',true)
         * then the block will get rendered as markdown even if it contains html
         */
        $isMarkdown = (boolean)$block->getIsMarkdown();
        $this->setOptions(array(
            'force'          => $isMarkdown,
            'protectMagento' => TRUE,
        ));
        $html = $transport->getHtml();
        $transport->setHtml($this->_renderMarkdown($html));

    }

    /**
     * @param Varien_Object $block
     *
     * @return bool
     */
    protected function _isAllowedBlock(Varien_Object $block)
    {
        return $block instanceof Mage_Cms_Block_Block || $block instanceof Mage_Cms_Block_Widget_Block;
    }

}