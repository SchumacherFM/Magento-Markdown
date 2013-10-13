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
     * adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return null
     */
    public function alterTextareaBlockTemplate(Varien_Event_Observer $observer)
    {
        if (Mage::helper('markdown')->isDisabled()) {
            return NULL;
        }

        /** @var $block Mage_Adminhtml_Block_Template */
        $block = $observer->getEvent()->getBlock();

        $isWidgetElement  = $block instanceof Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element;
        $isCatalogElement = $block instanceof Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element;

        /**
         * main reason for this layout handle thing is to avoid loading of lot of unused JS/CSS ...
         */
        $isLayoutHandleAllowed = Mage::getSingleton('markdown/observer_adminhtml_layoutUpdate')->isAllowed();

        if ($isWidgetElement || $isCatalogElement) {
            /** @var Varien_Data_Form_Element_Abstract $element */
            $element = $block->getElement();

            $_isElementEditor               = $this->_isElementEditor($element);
            $_isCatalogElementAllowed       = $this->_isCatalogElementAllowed($element);
            $_isEmailTemplateElementAllowed = $this->_isEmailTemplateElementAllowed($element);

            if ($_isElementEditor || $_isCatalogElementAllowed || $_isEmailTemplateElementAllowed) {
                $method = $isLayoutHandleAllowed ? '_integrate' : '_addMarkdownHint';
                $this->$method($element);
            }
        }
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return $this
     */
    protected function _addMarkdownHint(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setData('after_element_html', '<small>' .
            Mage::helper('markdown')->__('Markdown feature may be available here!')
            . '</small>' . $element->getData('after_element_html'));

        /* not sure if useful ...
        $params = array(
            'layoutHandle' => '@todo',
            'returnUrl'    => Mage::app()->getRequest()->getRequestUri(),
        );
        $url    = Mage::helper('markdown')->getAdminEnableUrl($params);
        $element->setData('after_element_html', '<small><a href="' . $url . '">' .
            Mage::helper('markdown')->__('Click to add Markdown feature!')
            . '</a></small>');
        */
        return $this;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return $this
     */
    protected function _integrate(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqueEntityId = $this->_getUniqueEntityId($element);
        $idPrefix       = $element->getForm()->getHtmlIdPrefix();
        $element->setId(str_replace($idPrefix, '', $element->getHtmlId()) . $uniqueEntityId);

        // adds to every Element the MD buttons at the bottom of the textarea
        return $this->_getMarkdownButtons($element)->_addEpicEditorHtml($element)->_mergeAfterElementHtml($element);
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return $this
     */
    protected function _mergeAfterElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_afterElementHtml[90] = $element->getData('after_element_html');

        $config        = array();
        $config['dt']  = Mage::helper('markdown')->getDetectionTag(TRUE);
        $config['fuu'] = Mage::helper('markdown')->getAdminFileUploadUrl(); // file upload url

        /**
         * when rendering via marked.js include that place holder ... if rendere via PHP replace {{media url...}}
         * with the real image.
         */
        $config['phi'] = Mage::getBaseUrl('media');

        if ($this->_isMarkdownExtra($element)) {
            $config['eru'] = Mage::helper('markdown')->getAdminRenderUrl(array('markdownExtra' => 1)); // extra renderer url
        }

        $config['eeloc'] = Mage::helper('markdown')->isEpicEditorLoadOnClick();

        if (Mage::helper('markdown')->isReMarkedEnabled() === TRUE) {
            $config['rmc'] = Mage::helper('markdown')->getReMarkedConfig();
        }

        if ($this->_configInserted === FALSE) {
            $this->_afterElementHtml[1000] = '<div id="markdownGlobalConfig" data-config=\'' .
                Zend_Json_Encoder::encode($config)
                . '\' style="display:none;"></div>';
            $this->_configInserted         = TRUE;
        }

        ksort($this->_afterElementHtml);
        $element->setData('after_element_html', implode(' ', $this->_afterElementHtml));
        $this->_afterElementHtml = array();
        $element->addClass('initFileReader');
        return $this;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return $this
     */
    protected function _addEpicEditorHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (!Mage::helper('markdown')->isEpicEditorEnabled()) {
            return $this;
        }

        $id = $element->getHtmlId();

        $element->addClass('initEpicEditor');
        $this->_afterElementHtml[100] = '<div id="epiceditor_EE_' . $id . '"' . $this->_getEpicEditorHtmlConfig($element) . '></div>';
        return $this;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getEpicEditorHtmlConfig(Varien_Data_Form_Element_Abstract $element)
    {
        $config     = Mage::helper('markdown')->getEpicEditorConfig();
        $dataConfig = '';
        if ($config) {
            $dataConfig = ' data-config=\'' . $config . '\'';
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
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _isEmailTemplateElementAllowed(Varien_Data_Form_Element_Abstract $element)
    {
        $trueOne = $element instanceof Varien_Data_Form_Element_Textarea;
        $trueTwo = stristr($element->getHtmlId(), 'template_text') !== FALSE;
        return $trueOne && $trueTwo;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _isCatalogElementAllowed(Varien_Data_Form_Element_Abstract $element)
    {
        $isTextArea    = $element instanceof Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg;
        $isDescription = stristr($element->getName(), 'description') !== FALSE && stristr($element->getName(), 'meta') === FALSE;
        return $isDescription && $isTextArea;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _isElementEditor(Varien_Data_Form_Element_Abstract $element)
    {
        return $element instanceof Varien_Data_Form_Element_Editor;
    }

    /**
     * checks if md extra is enabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _isMarkdownExtra(Varien_Data_Form_Element_Abstract $element)
    {
        $_isEmailTemplateElementAllowed = $this->_isEmailTemplateElementAllowed($element);

        return Mage::helper('markdown')->isMarkdownExtra() ||
        (Mage::helper('markdown')->isMarkdownExtra('email') && $_isEmailTemplateElementAllowed);
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _getMarkdownButtons(Varien_Data_Form_Element_Abstract $element)
    {
        $htmlId = $element->getHtmlId();

        if (Mage::helper('markdown')->getDetectionTag() !== '') {
            $this->_afterElementHtml[200] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('[M↓] enable'),
                    'type'    => 'button',
                    'onclick' => 'toggleMarkdown(\'' . $htmlId . '\');'
                ))->toHtml();
        }

        $this->_afterElementHtml[210] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] Source'),
                'type'    => 'button',
                'title'   => Mage::helper('markdown')->__('View generated HTML source code'),
                'onclick' => 'toggleMarkdownSource(this,\'' . $htmlId . '\');'
            ))->toHtml();

        $this->_afterElementHtml[300] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] Syntax'),
                'type'    => 'button',
                'onclick' => 'mdExternalUrl(\'' . Mage::helper('markdown')->getCheatSheetUrl() . '\');'
            ))->toHtml();

        if ($this->_isMarkdownExtra($element)) {
            $this->_afterElementHtml[400] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('[M↓] Extra Syntax'),
                    'type'    => 'button',
                    'onclick' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_EXTRA_SYNTAX . '\');'
                ))->toHtml();
        }

        if (Mage::helper('markdown')->isEpicEditorEnabled()) {
            $this->_afterElementHtml[500] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('EpicEditor'),
                    'type'    => 'button',
                    'onclick' => 'toggleEpicEditor(this,\'' . $htmlId . '\');'
                ))->toHtml();
        }

        if (Mage::helper('markdown')->isReMarkedEnabled() === TRUE) {
            $this->_afterElementHtml[600] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('HTML2[M↓]'),
                    'type'    => 'button',
                    'onclick' => 'htmlToMarkDown(this,\'' . $htmlId . '\');'
                ))->toHtml();
        }
        return $this;
    }
}