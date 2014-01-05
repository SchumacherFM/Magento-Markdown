<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Observer_Adminhtml_Block
{

    /**
     * @var bool
     */
    protected $_configInserted = FALSE;

    /**
     * @var array
     */
    protected $_afterElementHtml = array();

    /**
     * @var SchumacherFM_Markdown_Helper_Data
     */
    protected $_helper = NULL;

    /**
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_currentElement = NULL;

    /**
     * contains the base live preview URL
     *
     * @var array
     */
    protected $_livePreviewUrl = NULL;

    /**
     * adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function alterTextareaBlockTemplate(Varien_Event_Observer $observer)
    {
        $this->_helper = Mage::helper('markdown');
        if ($this->_helper->isDisabled()) {
            return NULL;
        }

        /** @var $block Mage_Adminhtml_Block_Template */
        $block            = $observer->getEvent()->getBlock();
        $isWidgetElement  = $block instanceof Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element;
        $isCatalogElement = $block instanceof Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element;

        /**
         * main reason for this layout handle thing is to avoid loading of lot of unused JS/CSS ...
         */
        $isLayoutHandleAllowed = Mage::getSingleton('markdown/observer_adminhtml_layoutUpdate')->isAllowed();

        if ($isWidgetElement || $isCatalogElement) {
            /** @var Varien_Data_Form_Element_Abstract _currentElement */
            $this->_currentElement = $block->getElement();

            $_isElementEditor               = $this->_isElementEditor();
            $_isCatalogElementAllowed       = $this->_isCatalogElementAllowed();
            $_isEmailTemplateElementAllowed = $this->_isEmailTemplateElementAllowed();

            if ($_isElementEditor || $_isCatalogElementAllowed || $_isEmailTemplateElementAllowed) {
                $this->_tryToGetPreviewUrl($block);
                $method = $isLayoutHandleAllowed ? '_integrate' : '_addMarkdownHint';
                $this->$method();
            }
        }
    }

    /**
     * @todo this needs to be extended for each block on which markdown can occur.
     *
     * @param Mage_Core_Block_Abstract $block
     *
     * @return null
     */
    protected function _tryToGetPreviewUrl(Mage_Core_Block_Abstract $block)
    {
        if (NULL !== $this->_livePreviewUrl) {
            return NULL;
        }

        // cms page edit
        if (TRUE === $this->_isElementEditor()) {

            /* @var $model Mage_Cms_Model_Page */
            $model = Mage::registry('cms_page');
            if (empty($model)) {
                return NULL;
            }
            $coreUrl = Mage::getModel('core/url');

            $identifier = $model->getIdentifier();
            if (!empty($identifier)) {
                $this->_livePreviewUrl = $coreUrl->getUrl(
                    $identifier, array(
                        '_current' => FALSE
                    )
                );
            }
        }

        // catalog
        if (TRUE === $this->_isCatalogElementAllowed()) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::registry('current_product');
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::registry('current_category');

            if ($product) {
                $this->_livePreviewUrl = $product->getUrlInStore();
            }
            if ($category) {
                $this->_livePreviewUrl = $category->getCategoryIdUrl();
            }
            $this->_livePreviewUrl = preg_replace('~\?___store=[^\&]+~i', '', $this->_livePreviewUrl);
        }
        return NULL;
    }

    /**
     * @return $this
     */
    protected function _addMarkdownHint()
    {
        $this->_currentElement->setData('after_element_html', '<small>' .
            $this->___('Markdown feature may be available here!')
            . '</small>' . $this->_currentElement->getData('after_element_html'));
        return $this;
    }

    /**
     * @return $this
     */
    protected function _integrate()
    {
        $uniqueEntityId = $this->_getUniqueEntityId($this->_currentElement);
        $idPrefix       = $this->_currentElement->getForm()->getHtmlIdPrefix();
        $this->_currentElement->setId(str_replace($idPrefix, '', $this->_currentElement->getHtmlId()) . $uniqueEntityId);

        // adds to every Element the MD buttons at the bottom of the textarea
        return $this
            ->_getMarkdownButtons()
            ->_addEpicEditorHtml()
            ->_mergeAfterElementHtml();
    }

    /**
     * @return $this
     */
    protected function _mergeAfterElementHtml()
    {
        $this->_afterElementHtml[90] = $this->_currentElement->getData('after_element_html');

        $this->_addMarkDownConfig();

        Mage::dispatchEvent('markdown_merge_after_element_html', array(
            'instance' => $this,
        ));

        ksort($this->_afterElementHtml);
        $this->_currentElement->setData('after_element_html', $this->_generateTabs());
        $this->_afterElementHtml = array();
        $this->_currentElement->addClass('initMarkdown ' . $this->_helper->getTextareaStyle());
        return $this;
    }

    /**
     * singleton to add markdown config
     * @return bool
     */
    protected function _addMarkDownConfig()
    {
        if ($this->_configInserted === TRUE) {
            return $this->_configInserted;
        }

        $config        = array();
        $config['dt']  = $this->_helper->getDetectionTag(TRUE);
        $config['fuu'] = $this->_helper->getAdminFileUploadUrl(); // file upload url

        /**
         * when rendering via marked.js include that place holder ... if rendere via PHP replace {{media url...}}
         * with the real image.
         */
        $config['phi'] = Mage::getBaseUrl('media');

        if ($this->_isMarkdownExtra()) {
            $config['eru'] = $this->_helper->getAdminRenderUrl(array('markdownExtra' => 1)); // extra renderer url
        }

        $config['stores']  = $this->_helper->getStoreCodes();
        $config['eeloc']   = $this->_helper->isEpicEditorLoadOnClick();
        $config['hideIIB'] = $this->_helper->isHiddenInsertImageButton();
        $config['mdCss']   = $this->_helper->getMarkdownStyleCss(TRUE);
        $config['hlCss']   = $this->_helper->getHighLightStyleCss(TRUE);
        $config['lpUrl']   = $this->_livePreviewUrl;
        $config['feaBUrl'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'markdown/adminhtml/feature/'; // feature base url (raptor)

        if ($this->_helper->isReMarkedEnabled() === TRUE) {
            $config['rmc'] = $this->_helper->getReMarkedConfig();
        }

        $this->_afterElementHtml[1000] = '<div id="markdownGlobalConfig" data-config=\'' .
            Zend_Json_Encoder::encode($config)
            . '\' style="display:none;"></div>';
        $this->_configInserted         = TRUE;
        return $this->_configInserted;
    }

    /**
     * @return string
     */
    protected function _generateTabs()
    {
        $block = Mage::getSingleton('core/layout')->createBlock('markdown/adminhtml_form_renderer_fieldset_element_textarea');
        $block
            ->setElementId($this->_currentElement->getHtmlId())
            ->setAfterElementHtml(implode(' ', $this->_afterElementHtml));
        return $block->toHtml();
    }

    /**
     * @return $this
     */
    protected function _addEpicEditorHtml()
    {
        if (!$this->_helper->isEpicEditorEnabled()) {
            return $this;
        }

        $id = $this->_currentElement->getHtmlId();

        $this->_currentElement->addClass('initEpicEditor');
        $this->_afterElementHtml[100] = ' <div id="epiceditor_EE_' . $id . '"' . $this->_getEpicEditorHtmlConfig() . '></div>';
        return $this;
    }

    /**
     * @return string
     */
    protected function _getEpicEditorHtmlConfig()
    {
        $config     = $this->_helper->getEpicEditorConfig();
        $dataConfig = '';
        if ($config) {
            $dataConfig = ' data-config = \'' . $config . '\'';
        }
        return $dataConfig;
    }

    /**
     * this is mainly a work around for the category section because fields will
     * be there loaded via ajax with the same id each time ... and that confuses me and
     * Epic Editor 8-)
     *
     * @param Varien_Data_Form_Element_Abstract $parentElement
     *
     * @return string
     */
    protected function _getUniqueEntityId(Varien_Data_Form_Element_Abstract $parentElement)
    {
        /** @var Varien_Data_Form_Element_Collection $elements */
        $elements = $parentElement->getForm()->getElements();

        $idString = '';
        foreach ($elements as $fieldSet) {
            /** @var Varien_Data_Form_Element_Fieldset $fieldSet */
            $sortedElements = $fieldSet->getSortedElements();
            foreach ($sortedElements as $sortedElement) {
                /** @var $sortedElement Varien_Data_Form_Element_Abstract */
                if (stristr($sortedElement->getName(), 'id') !== FALSE) {
                    $idString .= $sortedElement->getValue();
                }
            }
        }

        // prevent trouble with strange values due to localStorage ...
        $secretKey = Mage::getModel('adminhtml/url')->getSecretKey();
        $path      = Mage::app()->getRequest()->getRequestUri();
        $idString .= '_' . md5(str_replace($secretKey, '', $path));

        // we could also use here md5 but it want to see the values.
        return preg_replace('~[^a-z0-9_\-]+~i', '', $idString);
    }

    /**
     * @return bool
     */
    protected function _isEmailTemplateElementAllowed()
    {
        $trueOne = $this->_currentElement instanceof Varien_Data_Form_Element_Textarea;
        $trueTwo = stristr($this->_currentElement->getHtmlId(), 'template_text') !== FALSE;
        return $trueOne && $trueTwo;
    }

    /**
     * @return bool
     */
    protected function _isCatalogElementAllowed()
    {
        $isTextArea    = $this->_currentElement instanceof Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg;
        $isDescription = stristr($this->_currentElement->getName(), 'description') !== FALSE && stristr($this->_currentElement->getName(), 'meta') === FALSE;
        return $isDescription && $isTextArea;
    }

    /**
     * @return bool
     */
    protected function _isElementEditor()
    {
        return $this->_currentElement instanceof Varien_Data_Form_Element_Editor;
    }

    /**
     * @return bool
     */
    protected function _isMarkdownExtra()
    {
        $_isEmailTemplateElementAllowed = $this->_isEmailTemplateElementAllowed();

        return $this->_helper->isMarkdownExtra() ||
        ($this->_helper->isMarkdownExtra('email') && $_isEmailTemplateElementAllowed);
    }

    /**
     * @return $this
     */
    protected function _getMarkdownButtons()
    {
        $htmlId = $this->_currentElement->getHtmlId();

        if ($this->_helper->getDetectionTag() !== '') {
            $this->_afterElementHtml[200] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => $this->___('Markdown enable'),
                    'type'    => 'button',
                    'class'   => 'mdButton',
                    'onclick' => 'toggleMarkdown(\'' . $htmlId . '\');'
                ))->toHtml();
        }

        if ($this->_helper->isEpicEditorEnabled()) {
            $this->_afterElementHtml[500] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => $this->___('EpicEditor'),
                    'class'   => 'mdButton',
                    'type'    => 'button',
                    'onclick' => 'toggleEpicEditor(this,\'' . $htmlId . '\');'
                ))->toHtml();
        }

        if ($this->_helper->isReMarkedEnabled() === TRUE) {
            $this->_afterElementHtml[600] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => $this->___('Convert HTML to Markdown'),
                    'class'   => 'mdButton',
                    'type'    => 'button',
                    'onclick' => 'htmlToMarkDown(this,\'' . $htmlId . '\');'
                ))->toHtml();
        }
        return $this;
    }

    /**
     * @param $translation
     *
     * @return string
     */
    protected function ___($translation)
    {
        return $this->_helper->__($translation);
    }
}