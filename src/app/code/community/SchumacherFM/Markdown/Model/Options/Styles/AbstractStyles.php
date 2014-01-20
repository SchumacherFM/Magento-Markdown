<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
abstract class SchumacherFM_Markdown_Model_Options_Styles_AbstractStyles
{
    protected $_path = [];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $path    = implode(DS, $this->_path) . DS . '*.css';
        $content = glob($path);

        $return = array();

        foreach ($content as $css) {
            $file     = basename($css);
            $return[] = array('value' => $file, 'label' => Mage::helper('markdown')->__($file));
        }
        return $return;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $return  = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }
        return $return;
    }
}