<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Observer
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Options_Styles_HighLightStyles extends SchumacherFM_Markdown_Model_Options_Styles_AbstractStyles
{
    public function __construct()
    {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::app()->getLayout();

        $this->_path = array(
            Mage::getBaseDir('skin'), $layout->getArea(), 'default', 'default', 'markdown', 'highlight', 'styles'
        );
    }
}