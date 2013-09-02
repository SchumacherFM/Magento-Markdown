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
     * @var string
     */
    private $_currentRenderedText = '';

    private $_preserveContainer = array();

    /**
     * @var SchumacherFM_Markdown_Model_Michelf_Markdown
     */
    private $_renderer = null;

    protected $_options = array();

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
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * @param       string $text
     *
     * @return string
     */
    public function renderMarkdown($text)
    {
        return $this->_isDisabled
            ? $text
            : $this->_renderMarkdown($text);
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
            $this->setOptions(array(
                'force'          => FALSE,
                'protectMagento' => TRUE,
            ));
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
            $this->setOptions(array(
                'force'          => $isMarkdown,
                'protectMagento' => FALSE,
            ));
            $html = $transport->getHtml();
            $transport->setHtml($this->_renderMarkdown($html));

        }
        return $this;
    }

    /**
     * @param  string $text
     *
     * @return string
     */
    protected function _renderMarkdown($text)
    {
        $force                      = isset($this->_options['force']) && $this->_options['force'] === TRUE;
        $protectMagento             = isset($this->_options['protectMagento']) && $this->_options['protectMagento'] === TRUE;
        $this->_currentRenderedText = $text;
        if (!$this->_isMarkdown() && $force === FALSE) {
            return $this->_currentRenderedText;
        }

        $this->_removeMarkdownTag();
        if ($protectMagento) {
            $this->_preserveMagentoVariablesEncode();
        }
        $this->_currentRenderedText = $this->getRenderer()->defaultTransform($this->_currentRenderedText);
        if ($protectMagento) {
            $this->_preserveMagentoVariablesDecode();
        }
        return $this->_currentRenderedText;
    }

    /**
     * removes the markdown detection tag
     *
     * @return $this
     */
    protected function _removeMarkdownTag()
    {
        $this->_currentRenderedText = str_replace($this->_tag, '', $this->_currentRenderedText);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _preserveMagentoVariablesEncode()
    {
        $matches = array();
        preg_match_all('~(\{\{[a-z]+.+\}\})~ismU', $this->_currentRenderedText, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $key                            = md5($match[0]);
                $this->_preserveContainer[$key] = $match[0];
            }
            $this->_currentRenderedText = str_replace(
                $this->_preserveContainer, array_keys($this->_preserveContainer), $this->_currentRenderedText
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _preserveMagentoVariablesDecode()
    {
        if (count($this->_preserveContainer) === 0) {
            return $this;
        }
        $this->_currentRenderedText = str_replace(
            array_keys($this->_preserveContainer), $this->_preserveContainer, $this->_currentRenderedText
        );
        $this->_preserveContainer   = array();
        return $this;
    }

    /**
     * checks if text contains no html ... if so considered as markdown ... not a nice way...
     *
     * @return bool
     */
    protected function _isMarkdown()
    {
        $flag = !empty($this->_currentRenderedText);
        return $flag === TRUE && strpos($this->_currentRenderedText, $this->_tag) !== FALSE;
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