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
     *
     * @return null
     */
    public function alterTextareaBlockTemplate(Varien_Event_Observer $observer)
    {
        if (Mage::helper('markdown')->isDisabled()) {
            return null;
        }

        /** @var $block Mage_Adminhtml_Block_Template */
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element) {
            /** @var Varien_Data_Form_Element_Abstract $element */
            $element = $block->getElement();

            if ($this->_isEmailTemplateElementAllowed($element)) {
                $element->setData('after_element_html', ' ');
                $this->_getMarkdownButtons($element, 'template_text');
            }

            if ($this->_isElementEditor($element)) {
                $this->_addLivePreviewToEditor($element);
            }
        }

        if ($block instanceof Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element) {
            /** @var Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg $element */
            $element = $block->getElement();
            if ($this->_isCatalogElementAllowed($element)) {
                $this->_getMarkdownButtons($element);
            }
        }
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
        $isTextarea    = $element instanceof Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg;
        $isDescription = stristr($element->getName(), 'description') !== FALSE && stristr($element->getName(), 'meta') === FALSE;
        return $isDescription && $isTextarea;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string|null                       $htmlId
     */
    protected function _getMarkdownButtons(Varien_Data_Form_Element_Abstract $element, $htmlId = null)
    {
        $html   = array($element->getData('after_element_html'));
        $htmlId = empty($htmlId) ? $element->getHtmlId() : $htmlId;

        $html[] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] enable'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => 'toggleMarkdown(\'' . Mage::helper('markdown')->getDetectionTag(TRUE) . '\',\'' . $htmlId . '\');'
            ))->toHtml();

        $html[] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] Preview'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => Mage::helper('markdown')->getRenderMarkdownJs($htmlId),
            ))->toHtml();

        $html[] = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button', '', array(
                'label'   => Mage::helper('markdown')->__('[M↓] Syntax'),
                'type'    => 'button',
                'class'   => 'btn-wysiwyg',
                'onclick' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_SYNTAX . '\');'
            ))->toHtml();

        if (Mage::helper('markdown')->isMarkdownExtra()) {

            $html[] = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('markdown')->__('[M↓] Extra Syntax'),
                    'type'    => 'button',
                    'class'   => 'btn-wysiwyg',
                    'onclick' => 'mdExternalUrl(\'' . SchumacherFM_Markdown_Helper_Data::URL_MD_EXTRA_SYNTAX . '\');'
                ))->toHtml();
        }

        $element->setData('after_element_html', implode(' ', $html));
    }

    /**
     * Live preview only available for non markdown extra mode.
     * otherwise the ajax request and php markdown rendering would kill the users patience
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _isElementEditor(Varien_Data_Form_Element_Abstract $element)
    {
        return !Mage::helper('markdown')->isMarkdownExtra() && $element instanceof Varien_Data_Form_Element_Editor;
    }

    /**
     * @param Varien_Data_Form_Element_Editor $element
     *
     * @return $this
     */
    protected function _addLivePreviewToEditor(Varien_Data_Form_Element_Editor $element)
    {
        $previewHtml = '<div id="markdown_live_preview"
        style="overflow:scroll; height:25em;"
        data-mddetector="' . Mage::helper('markdown')->getDetectionTag(TRUE) . '"
        data-elementid="' . $element->getHtmlId() . '" class="buttons-set"><div class="markdown">' .
            Mage::helper('markdown')->__('[M↓] Live Preview enabled ...')
            . '</div></div>';
        $element->setData('after_element_html', $previewHtml);
        return $this;
    }

}