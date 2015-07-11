<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Controller
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Adminhtml_MarkdownController extends Mage_Adminhtml_Controller_Action
{
    public function addHandleAction() {
        $handle = $this->getRequest()->getParam('handle');
        if (! $handle) {
            $this->_redirectReferer();
        }

        $customHandles = trim(Mage::getStoreConfig('markdown/markdown/custom_layout_handles'));
        if (!empty($customHandles)) {
            $customHandles = preg_split('~\s+~', $customHandles, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $customHandles = array();
        }

        if (array_search($handle, $customHandles) !== false) {
            Mage::getSingleton('adminhtml/session')
                ->addError($this->__('Could not enable markdown for this page.'));
            $this->_redirectReferer();
        }

        $customHandles[] = $handle;

        Mage::getConfig()->saveConfig(
            'markdown/markdown/custom_layout_handles',
            implode("\n", $customHandles));
        Mage::getConfig()->cleanCache();

        Mage::getSingleton('adminhtml/session')
            ->addSuccess($this->__('Enabled markdown for this page.'));

        $this->_redirectReferer();
    }
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

        if (! (isset($file['extra']['nameNoExtension']) && isset($file['extra']['extension'])) || empty($binaryData)) {
            $return['msg'] = 'Either fileName or binaryData or file is empty ...';
            return $this->_setReturn($return, TRUE);
        }
        $fileName = $file['extra']['nameNoExtension'].'.'.$file['extra']['extension'];

        if (strpos(strtolower($fileName), 'clipboard') !== FALSE) {
            $fileName = 'clipboard_' . date('Ymd-His') . '_' . str_replace('clipboard', '', strtolower($fileName));
        }
        $fileName   = preg_replace('~[^\w\.]+~i', '_', $fileName);

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

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('markdown_editor');
    }
}
