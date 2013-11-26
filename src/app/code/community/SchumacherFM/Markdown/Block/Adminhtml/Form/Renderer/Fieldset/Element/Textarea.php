<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Block
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 *
 * @method SchumacherFM_Markdown_Block_Adminhtml_Form_Renderer_Fieldset_Element_Textarea setElementId($id)
 * @method string getElementId()
 */
class SchumacherFM_Markdown_Block_Adminhtml_Form_Renderer_Fieldset_Element_Textarea extends Mage_Adminhtml_Block_Template
{

    /**
     * @var SchumacherFM_Markdown_Helper_Data
     */
    protected $_helper = NULL;

    /**
     * Class constructor
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('markdown/edit/form/renderer/textarea.phtml');
        $this->_helper = Mage::helper('markdown');
    }

    /**
     * @param $translation
     *
     * @return string
     */
    public function ___($translation)
    {
        return $this->_helper->__($translation);
    }

    /**
     * @return string
     */
    public function  getFileReaderInputId()
    {
        return 'man_chooser_' . $this->getElementId();
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getIframe($name, $useSandBox = TRUE)
    {
        $sandBox = $useSandBox === TRUE ? ' sandbox="allow-same-origin"' : '';
        return '<iframe class="iframePreview" ' . $sandBox . ' name="' . $name . '" src="" style="' .
        $this->_helper->getPreviewIframeCSS() . '"></iframe>';
    }
}