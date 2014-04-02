<?php

/**
 * @category    SchumacherFM_Markdown
 * @package     Helper
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Helper_Data extends Mage_Core_Helper_Abstract
{
    const URL_MD_EXTRA_SYNTAX = 'http://michelf.ca/projects/php-markdown/extra/';

    /**
     * easy access method for rendering markdown in phtml files
     * usage:
     * echo Mage::helper('markdown')->render($_product->getDescription())
     *
     * @param string $text
     * @param array  $options
     *
     * @return string
     */
    public function render($text, array $options = NULL)
    {
        return Mage::getSingleton('markdown/markdown_render')
            ->setOptions($options)
            ->renderMarkdown($text);
    }

    /**
     * @return string
     */
    public function getCheatSheetUrl()
    {
        return Mage::getStoreConfig('markdown/markdown/cheatsheet');
    }

    /**
     * not DRY ;-)
     *
     * @param bool $fullPath
     *
     * @return string
     */
    public function getHighLightStyleCss($fullPath = FALSE)
    {
        $styleFile = Mage::getStoreConfig('markdown/markdown/highlight_style');
        $return    = 'markdown' . DS . 'highlight' . DS . 'styles' . DS . $styleFile;
        if (TRUE === $fullPath) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . $return;
        }
        return $return;
    }

    /**
     * not DRY ;-)
     *
     * @param bool $fullPath
     *
     * @return string
     */
    public function getMarkdownStyleCss($fullPath = FALSE)
    {
        $styleFile = Mage::getStoreConfig('markdown/markdown/preview_style');
        $return    = 'markdown' . DS . 'styles' . DS . $styleFile;
        if (TRUE === $fullPath) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . $return;
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getPreviewIframeCSS()
    {
        return trim(Mage::getStoreConfig('markdown/markdown/preview_iframe_css'));
    }

    /**
     * @return string
     */
    public function getTextareaStyle()
    {
        return trim(Mage::getStoreConfig('markdown/markdown/textarea_style'));
    }

    /**
     * @return string
     */
    public function getMdExtraDocUrl()
    {
        return self::URL_MD_EXTRA_SYNTAX;
    }

    /**
     * @param bool $encoded
     *
     * @return mixed|string
     */
    public function getDetectionTag($encoded = FALSE)
    {
        $tag = trim(Mage::getStoreConfig('markdown/markdown/detection_tag'));
        if (empty($tag) === TRUE) {
            return '';
        }
        return $encoded ? rawurlencode($tag) : $tag;
    }

    /**
     * @todo if backend check for current selected store view / website
     *
     * check if MD is enabled ... per store view
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !Mage::getStoreConfigFlag('markdown/markdown/enable');
    }

    /**
     * Check if Markdown is enabled for emails
     *
     * @return bool
     */
    public function isEmailDisabled()
    {
        return !Mage::getStoreConfigFlag('markdown/markdown/enable_email');
    }

    /**
     * @param string $type enum email|page|block ... last two not supported, maybe later.
     *
     * @return bool
     */
    public function isMarkdownExtra($type = NULL)
    {
        return Mage::getStoreConfigFlag('markdown/markdown_extra/enable' . (!empty($type) ? '_' . $type : ''));
    }

    /**
     * @return bool
     */
    public function isHiddenInsertImageButton()
    {
        return Mage::getStoreConfigFlag('markdown/markdown/hide_insert_image_button');
    }

    /**
     * loads CSS files and minifies it
     *
     * @todo if backend check for current selected store view / website
     *       check if md extra is enabled ... per store view
     *
     * @return string
     */
    public function getTransactionalEmailCSS()
    {
        $file = Mage::getStoreConfig('markdown/markdown_extra/te_md_css');
        if (empty($file)) {
            return '';
        }
        $content = trim(implode('', @file(Mage::getBaseDir() . DS . $file)));
        if (empty($content)) {
            Mage::log('Markdown CSS file [' . $file . '] is empty!');
        }
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content); // comments
        $content = preg_replace('~\s+~', ' ', $content); // all whitespaces
        $content = preg_replace('~\s*(:|\{|\}|,|;)\s*~', '\\1', $content); // all other whitespaces
        return $content;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getAdminRenderUrl(array $params = NULL)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/markdown/render', $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getAdminFileUploadUrl(array $params = NULL)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/markdown/fileUpload', $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getAdminEnableUrl(array $params = NULL)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/markdown/enable', $params);
    }

    /**
     * @return bool
     */
    public function isEpicEditorEnabled()
    {
        return (boolean)Mage::getStoreConfig('markdown/epiceditor/enable');
    }

    /**
     * @return bool
     */
    public function isEpicEditorLoadOnClick()
    {
        return (boolean)Mage::getStoreConfig('markdown/epiceditor/load_on_click_textarea');
    }

    /**
     * if json is invalid returns false
     *
     * @return string|boolean
     */
    public function getEpicEditorConfig()
    {
        $config             = $this->_getJsonConfig('epiceditor');
        $config             = FALSE !== $config ? json_decode($config, TRUE) : array();
        $config['basePath'] = Mage::getBaseUrl('skin') . 'adminhtml/default/default/epiceditor/';
        return json_encode($config);
    }

    /**
     * if json is invalid returns false
     *
     * @param string $type
     *
     * @return bool|string
     */
    protected function _getJsonConfig($type)
    {
        $config = trim(Mage::getStoreConfig('markdown/' . $type . '/config'));
        if (empty($config)) {
            return FALSE;
        }
        $decoded = json_decode($config);
        return $decoded instanceof stdClass ? rawurlencode($config) : FALSE;
    }

    /**
     * @return bool
     */
    public function isReMarkedEnabled()
    {
        return (boolean)Mage::getStoreConfig('markdown/remarked/enable');
    }

    /**
     * if json is invalid returns false
     *
     * @return string|boolean
     */
    public function getReMarkedConfig()
    {
        return $this->_getJsonConfig('remarked');
    }

    /**
     * @param $imageUrl
     *
     * @return string
     */
    public function getTemplateMediaUrl($imageUrl)
    {
        return sprintf('{{media url="%s"}}', $imageUrl);
    }

    /**
     * @param $content
     *
     * @return mixed
     */
    public function renderTemplateMediaUrl($content)
    {
        return preg_replace('~\{\{media\s+url="([^"]+)"\s*\}\}~i', Mage::getBaseUrl('media') . '\\1', $content);
    }

    /**
     * @return array
     */
    public function getAllowedLayoutHandles()
    {
        $handles = array(
            'editor'                               => 1,
            'adminhtml_cms_block_edit'             => 1,
            'adminhtml_cms_page_index'             => 1,
            'adminhtml_cms_page_edit'              => 1,
            'adminhtml_catalog_product_edit'       => 1,
            'adminhtml_catalog_category_edit'      => 1,
            'mpblog_admin_adminhtml_post_edit'     => 1,
        );

        if (!$this->isEmailDisabled()) {
            $handles['adminhtml_system_email_template_edit'] = 1;
        }

        $customHandles = trim((string)Mage::getStoreConfig('markdown/markdown/custom_layout_handles'));
        if (!empty($customHandles)) {
            $customHandles = preg_split('~\s+~', $customHandles, -1, PREG_SPLIT_NO_EMPTY);
            $customHandles = array_flip($customHandles);
            $handles       = array_merge($handles, $customHandles);
        }
        return $handles;
    }

    /**
     * @return array
     */
    public function getStoreCodes()
    {
        $stores    = array();
        $appStores = Mage::app()->getStores(TRUE, FALSE);
        foreach ($appStores as $store) {
            /** @var $store Mage_Core_Model_Store */
            $stores[] = $store->getCode();
        }
        return $stores;
    }
}