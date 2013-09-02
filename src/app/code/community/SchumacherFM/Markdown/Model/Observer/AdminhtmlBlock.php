<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Helper
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Observer_AdminhtmlBlock
{
    /**
     * adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function alterTextareaBlockTemplate(Varien_Event_Observer $observer)
    {
        if (Mage::helper('markdown')->isDisabled()) {
            return null;
        }

        /** @var $block Mage_Adminhtml_Block_Template */
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element) {
            /** @var $block Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element */

            /** @var Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg $element */
            $element = $block->getElement();
            if ($this->_isElementAllowed($element)) {
                $this->_getMarkdownButtons($element);
            }
        }
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _isElementAllowed(Varien_Data_Form_Element_Abstract $element)
    {
        $isTextarea    = $element instanceof Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg;
        $isDescription = stristr($element->getName(), 'description') !== FALSE && stristr($element->getName(), 'meta') === FALSE;
        return $isDescription && $isTextarea;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _getMarkdownButtons(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getData('after_element_html');

        $html .= Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('MD enable'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => 'toggleMarkdown(\'' .
                rawurlencode(Mage::helper('markdown')->getDetectionTag())
                . '\',\'' . $element->getHtmlId() . '\');'
            ))->toHtml().' ';

        $html .= Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('catalog')->__('Preview Markdown'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => 'renderMarkdown(\'' . $element->getHtmlId() . '\')'
            ))->toHtml();

        $element->setData('after_element_html', $html);
    }

}