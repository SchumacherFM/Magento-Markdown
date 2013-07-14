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
     * @param string $text
     *
     * @return string
     */
    protected function _renderMarkdown($text)
    {
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
    public function renderContentObserver(Varien_Event_Observer $observer)
    {
        /** @var Mage_Cms_Model_Page $page */
        $page = $observer->getEvent()->getPage();

        if ($page instanceof Mage_Cms_Model_Page) {
            $content = $this->_renderMarkdown($page->getContent());
            $page->setContent($content);
        }

        return $this;
    }

}