<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Markdown_Render
{
    private $_tag = '';
    private $_isDisabled = FALSE;

    /**
     * @var SchumacherFM_Markdown_Model_Michelf_Markdown
     */
    private $_renderer = null;

    public function __construct()
    {
        /**
         * due to some weired parsings ... every text field which should contain MD must start with this tag
         */
        $this->_tag        = Mage::helper('markdown')->getDetectionTag();
        $this->_isDisabled = Mage::helper('markdown')->isDisabled();

        $isExtra         = Mage::helper('markdown')->isMarkdownExtra() ? '_extra' : '';
        $this->_renderer = Mage::getModel('markdown/michelf_markdown' . $isExtra);
    }

    /**
     * @return SchumacherFM_Markdown_Model_Michelf_Markdown
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @param string $text
     * @param bool   $force
     *
     * @return string
     */
    public function renderMarkdown($text, $force = FALSE)
    {
        return $this->_isDisabled
            ? $text
            : $this->_renderMarkdown($text, $force);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function renderPageObserver(Varien_Event_Observer $observer)
    {
        if ($this->_isDisabled) {
            return null;
        }

        /** @var Mage_Cms_Model_Page $page */
        $page = $observer->getEvent()->getPage();

        if ($page instanceof Mage_Cms_Model_Page) {
            $content = $this->_renderMarkdown($page->getContent());
            $page->setContent($content);
        }

        return $this;
    }

    /**
     * renders every block as markdown except those having the html tags of method _isMarkdown in it
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function renderBlockObserver(Varien_Event_Observer $observer)
    {
        if ($this->_isDisabled) {
            return null;
        }

        /** @var Mage_Cms_Model_Page $page */
        $block = $observer->getEvent()->getBlock();

        if ($this->_isAllowedBlock($block)) {
            /** @var Varien_Object $transport */
            $transport = $observer->getEvent()->getTransport();

            /**
             * you can set on any block the property ->setData('is_markdown',true)
             * then the block will get rendered as markdown even if it contains html
             */
            $isMarkdown = (boolean)$block->getIsMarkdown();
            $html       = $transport->getHtml();
            $transport->setHtml($this->_renderMarkdown($html, $isMarkdown));

        }
        return $this;
    }

    /**
     * @param  string $text
     * @param bool    $force
     *
     * @return string
     */
    protected function _renderMarkdown($text, $force = FALSE)
    {
        if (!$this->_isMarkdown($text) && $force === FALSE) {
            return $text;
        }
        return $this->getRenderer()->defaultTransform(str_replace($this->_tag, '', $text));
    }

    /**
     * checks if text contains no html ... if so considered as markdown ... not a nice way...
     *
     * @param string $text
     *
     * @return bool
     */
    protected function _isMarkdown(&$text)
    {
        $flag = !empty($text);
        return $flag === TRUE && strpos($text, $this->_tag) !== FALSE;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     *
     * @return bool
     */
    protected function _isAllowedBlock($block)
    {
        return $block instanceof Mage_Core_Block_Abstract;
    }

}