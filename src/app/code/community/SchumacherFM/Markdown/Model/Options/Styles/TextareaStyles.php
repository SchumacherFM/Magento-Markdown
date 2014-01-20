<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Options_Styles_TextareaStyles
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('markdown')->__('Default')),
            array('value' => 'lucida', 'label' => Mage::helper('markdown')->__('Lucida Sans')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'lucida' => Mage::helper('markdown')->__('Lucida Sans'),
            ''       => Mage::helper('markdown')->__('Yes'),
        );
    }
}