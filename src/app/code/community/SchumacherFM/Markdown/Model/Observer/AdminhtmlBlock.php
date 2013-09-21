<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Observer_AdminhtmlBlock
{

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

        if ($isWidgetElement || $isCatalogElement) {
            /** @var Varien_Data_Form_Element_Abstract $element */
            $element = $block->getElement();

            if ($this->_isEmailTemplateElementAllowed($element)) {
                $element->setData('after_element_html', ' ');
                $this->_getMarkdownButtons($element, 'template_text');
            }

            if ($this->_isElementEditor($element) || $this->_isCatalogElementAllowed($element)) {
                $this->_integrate($element);
            }
        }
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _integrate(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqueEntityId = $this->_getUniqueEntityId($element);
        $idPrefix       = $element->getForm()->getHtmlIdPrefix();
        $element->setId(str_replace($idPrefix, '', $element->getHtmlId()) . $uniqueEntityId);

        if ($this->_isCatalogElementAllowed($element)) {
            $this->_getMarkdownButtons($element);
        }

        $this->_addEpicEditorHtml($element);
        $this->_mergeAfterElementHtml($element);
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _mergeAfterElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_afterElementHtml[90] = $element->getData('after_element_html');
        ksort($this->_afterElementHtml);
        $element->setData('after_element_html', implode(' ', $this->_afterElementHtml));
        $this->_afterElementHtml = array();
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _addEpicEditorHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $element->setClass('initEpicEditor');
        $this->_afterElementHtml[100] = '<div id="epiceditor_EE_' . $id . '"' . $this->_getEpicEditorHtmlConfig() . '></div>';
    }

    /**
     *
     * @return string
     */
    protected function _getEpicEditorHtmlConfig()
    {
        $config     = Mage::helper('markdown')->getEpicEditorConfig();
        $dataConfig = '';
        if ($config) {
            $dataConfig = ' data-config="' . $config . '"';
        }
        $tag = Mage::helper('markdown')->getDetectionTag(TRUE);
        $dataConfig .= ' data-detectiontag="' . $tag . '"';
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
        $trueOne = $element instanceof Varien_Data_Form_Element_Note;
        $trueTwo = stristr($element->getHtmlId(), 'insert_variable') !== FALSE;
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
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string|null                       $htmlId
     */
    protected function _getMarkdownButtons(Varien_Data_Form_Element_Abstract $element, $htmlId = NULL)
    {

        $htmlId = empty($htmlId) ? $element->getHtmlId() : $htmlId;

        $this->_afterElementHtml[200] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] enable'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => 'toggleMarkdown(\'' . Mage::helper('markdown')->getDetectionTag(TRUE) . '\',\'' . $htmlId . '\');'
            ))->toHtml();

        $this->_afterElementHtml[300] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] Syntax'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => 'mdExternalUrl(\'' . Mage::helper('markdown')->getCheatSheetUrl() . '\');'
            ))->toHtml();

        if (Mage::helper('markdown')->isMarkdownExtra()) {
            $this->_afterElementHtml[400] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('[M↓] Extra Syntax'),
                    'type'    => 'button',
                    'class'   => 'btn-wysiwyg',
                    'onclick' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_EXTRA_SYNTAX . '\');'
                ))->toHtml();
        }

        if (Mage::helper('markdown')->isEpicEditorEnabled()) {
            $this->_afterElementHtml[500] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('EpicEditor on/off'),
                    'type'    => 'button',
                    'class'   => 'btn-wysiwyg',
                    'onclick' => 'toggleEpicEditor(\'' . $htmlId . '\');'
                ))->toHtml();
        }
    }
}