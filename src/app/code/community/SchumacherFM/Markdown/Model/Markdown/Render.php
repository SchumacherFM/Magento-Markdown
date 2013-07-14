<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Markdown_Render
{
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
        $renderer = Mage::getModel('markdown/michelf_markdown');
        return $renderer->defaultTransform($text);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function renderMarkdown($text)
    {
        return $this->_renderMarkdown($text);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function renderPageObserver(Varien_Event_Observer $observer)
    {
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
     * checks if text contains no html ... if so considered as markdown ... not a nice way...
     *
     * @param string $text
     *
     * @return bool
     */
    protected function _isMarkdown($text)
    {
        $flag = !empty($text);
        return $flag === TRUE && !preg_match('~<(div|span|h1|h2|h3|p|hr|em|strong|a|img|ul|ol|li|table|input|form)~i', $text);
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