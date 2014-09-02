<?php
/**
 * SchumacherFM_Markdown
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    SchumacherFM
 * @package     SchumacherFM_Markdown
 * @copyright   Copyright © 2014 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Paul Hachmang – H&O <info@h-o.nl>
 *
 * 
 */
 
class SchumacherFM_Markdown_IndexController extends Mage_Core_Controller_Front_Action
{
    public function previewAction() {
        $this->loadLayout();
        $markdown = $this->getRequest()->getParam('markdown');

        /** @var Mage_Core_Block_Text $block */
        $markdownBlock = $this->getLayout()->getBlock('markdown_content');
        $markdownBlock->setText($this->_renderContent($markdown));

        $this->renderLayout();
    }

    protected function _renderContent($content) {
        /* @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        $markdown = $processor->filter($content);
        $markdown = Mage::helper('markdown')->render($markdown, array());
        return '<div class="std">'.$markdown.'</div>';
    }
}
