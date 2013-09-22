<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Controller
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Adminhtml_MarkdownController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @return void
     */
    public function renderAction()
    {
        $content       = $this->getRequest()->getParam('content', NULL);
        $markdownExtra = ((int)$this->getRequest()->getParam('markdownExtra', 0)) === 1;

        if (!$this->getRequest()->isPost() || empty($content)) {
            return $this->_setReturn('Incorrect Request');
        }

        $md = Mage::helper('markdown')->render($content, array(
            'extra' => $markdownExtra
        ));
        return $this->_setReturn($md);
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function _setReturn($string = '')
    {
        $this->getResponse()->setBody($string);
        return $this;
    }
}