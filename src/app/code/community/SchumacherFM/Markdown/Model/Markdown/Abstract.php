<?php

/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
abstract class SchumacherFM_Markdown_Model_Markdown_Abstract
{
    protected $_tag = '';
    protected $_isDisabled = FALSE;
    protected $_isEmailDisabled = FALSE;

    /**
     * @var string
     */
    private $_currentRenderedText = '';

    /**
     * @var array
     */
    protected $_preserveContainer = array();

    /**
     * @var SchumacherFM_Markdown_Model_Michelf_Markdown
     */
    protected $_renderer = NULL;

    protected $_options = array(
        'force'          => FALSE,
        'extra'          => FALSE,
        'protectMagento' => TRUE,
    );

    public function __construct()
    {
        /**
         * due to some weired parsings ... every text field which should contain MD must start with this tag
         */
        $this->_tag             = Mage::helper('markdown')->getDetectionTag();
        $this->_isDisabled      = Mage::helper('markdown')->isDisabled();
        $this->_isEmailDisabled = Mage::helper('markdown')->isEmailDisabled();
    }

    /**
     * @return SchumacherFM_Markdown_Model_Michelf_Markdown
     */
    public final function getRenderer()
    {
        if ($this->_renderer !== NULL) {
            return $this->_renderer;
        }

        $_isExtra        = $this->_getIsExtraRenderer();
        $this->_renderer = Mage::getModel($this->_getRendererModelName($_isExtra));
        return $this->_renderer;
    }

    /**
     * for overloading please use this method to enable or disable the extra renderer
     *
     * @return boolean
     */
    protected function _getIsExtraRenderer()
    {
        return Mage::helper('markdown')->isMarkdownExtra() || $this->_options['extra'] === TRUE;
    }

    /**
     * for overloading and using of your own markdown renderer use this method
     *
     * @param bool $_isExtra
     *
     * @return string
     */
    protected function _getRendererModelName($_isExtra = FALSE)
    {
        return 'markdown/michelf_markdown' . ($_isExtra === TRUE ? '_extra' : '');
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = NULL)
    {
        if (FALSE === empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
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
     * @param  string $text
     *
     * @return string
     */
    protected function _renderMarkdown($text)
    {
        Varien_Profiler::start('renderMarkdown');
        $force                      = isset($this->_options['force']) && $this->_options['force'] === TRUE;
        $protectMagento             = isset($this->_options['protectMagento']) && $this->_options['protectMagento'] === TRUE;
        $this->_currentRenderedText = $text;
        $cacheKey                   = $this->getCacheKey();
        $useCache                   = Mage::app()->useCache('markdown') && FALSE !== $cacheKey;

        if (TRUE === $useCache) {
            $renderedCachedText = Mage::app()->loadCache($cacheKey);
            if (FALSE === empty($renderedCachedText)) {
                Varien_Profiler::stop('renderMarkdown');
                return $renderedCachedText;
            }
        }

        if (!$this->_isMarkdown() && $force === FALSE) {
            return $this->_currentRenderedText;
        }

        $this->_removeMarkdownTag();
        if ($protectMagento === TRUE) {
            $this->_preserveMagentoVariablesEncode();
        }

        $this->_currentRenderedText = $this->getRenderer()->defaultTransform($this->_currentRenderedText);

        if ($protectMagento === TRUE) {
            $this->_preserveMagentoVariablesDecode();
        }

        if (TRUE === $useCache) {
            Mage::app()->saveCache($this->_currentRenderedText, $cacheKey, array('markdown'), (int)Mage::getStoreConfig('markdown/markdown/cache_lifetime'));
        }

        Varien_Profiler::stop('renderMarkdown');
        return $this->_currentRenderedText;
    }

    /**
     * removes the markdown detection tag
     *
     * @return $this
     */
    protected function _removeMarkdownTag()
    {
        if (empty($this->_tag) === TRUE) {
            return $this;
        }

        $this->_currentRenderedText = trim(str_replace($this->_tag, '', $this->_currentRenderedText));
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
    private function _isMarkdown()
    {
        if (empty($this->_currentRenderedText) === TRUE) {
            return FALSE;
        }
        if (empty($this->_tag) === TRUE) {
            return TRUE;
        }
        if (strpos($this->_currentRenderedText, $this->_tag) === FALSE) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @param string reference $text
     *
     * @return bool
     */
    public function isMarkdown(&$text)
    {
        return strpos($text, $this->_tag) !== FALSE;
    }

    /**
     * @return string|boolean
     */
    public function getCacheKey()
    {
        //Checking if we are in secure area
        $store = Mage::app()->getStore();
        if ($store->isAdmin()) {
            return FALSE; // don't cache in the backend
        } else {
            $secure = $store->isFrontUrlSecure() && Mage::app()->getRequest()->isSecure();
        }
        $cacheKeyInfo = $store->getId() . DS . $secure . DS . $this->_currentRenderedText;
        return sha1($cacheKeyInfo);
    }
}