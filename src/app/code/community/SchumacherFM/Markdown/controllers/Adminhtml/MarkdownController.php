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

        $md = Mage::helper('markdown')->renderTemplateMediaUrl($md);

        return $this->_setReturn($md);
    }

    /**
     * @param string $string
     * @param bool   $jsonEncode
     *
     * @return $this
     */
    protected function _setReturn($string = '', $jsonEncode = FALSE)
    {
        if (TRUE === $jsonEncode) {
            $this->getResponse()->setHeader('Content-type', 'application/json', TRUE);
        }
        $this->getResponse()->setBody($jsonEncode ? Zend_Json_Encoder::encode($string) : $string);
        return $this;
    }

    /**
     * @todo better subdirectories
     *       saves a file in the dir: media/wysiwyg/markdown/....
     *
     * @return $this
     */
    public function fileUploadAction()
    {

        $return     = array(
            'err'     => TRUE,
            'msg'     => 'An error occurred.',
            'fileUrl' => ''
        );
        $binaryData = base64_decode($this->getRequest()->getParam('binaryData', ''));
        $file       = json_decode($this->getRequest()->getParam('file', '[]'), TRUE);
        $fileName   = preg_replace('~[^\w\.]+~i', '', isset($file['name']) ? $file['name'] : '');

        if (strpos(strtolower($fileName), 'clipboard') !== FALSE) {
            $fileName = 'clipboard_' . date('Ymd-His') . '_' . str_replace('clipboard', '', strtolower($fileName));
        }

        if (empty($fileName) || empty($binaryData) || empty($file)) {
            $return['msg'] = 'Either fileName or binaryData or file is empty ...';
            return $this->_setReturn($return, TRUE);
        }

        $savePath = $this->_getStorageRoot() . $this->_getStorageSubDirectory();
        $io       = new Varien_Io_File();
        if ($io->checkAndCreateFolder($savePath)) {
            $result = (int)file_put_contents($savePath . $fileName, $binaryData); // io->write will not work :-(
            if ($result > 10) {
                $return['err']     = FALSE;
                $return['msg']     = '';
                $return['fileUrl'] = Mage::helper('markdown')->getTemplateMediaUrl($this->_getStorageSubDirectory() . $fileName);
            }
        }

        $this->_setReturn($return, TRUE);
    }

    /**
     * @return mixed|string
     */
    protected function _getStorageSubDirectory()
    {
        $userDir = Mage::getStoreConfig('markdown/file_reader/upload_dir');
        if (empty($userDir)) {
            $userDir = Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY . DS . 'markdown' . DS;
        }
        return $userDir;
    }

    /**
     * Images Storage root directory
     *
     * @return string
     */
    protected function _getStorageRoot()
    {
        return Mage::getConfig()->getOptions()->getMediaDir() . DS;
    }
}