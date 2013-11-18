<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Block
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
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
}