<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Controller
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Adminhtml_MarkdownController extends Mage_Adminhtml_Controller_Action
{
    public function ajaxRenderAction()
    {
        $content = (string)$this->getRequest()->getParam('content');
        echo Mage::getModel('markdown/markdown_render')->renderMarkdown($content);
    }

}
