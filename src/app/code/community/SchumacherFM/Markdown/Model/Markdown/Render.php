<?php
/**
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
class SchumacherFM_Markdown_Model_Markdown_Render
{

    /**
     * @param string $text
     *
     * @return string
     */
    public function renderMarkdown($text)
    {
        $renderer = Mage::getModel('markdown/michelf_markdown');
        return $renderer->defaultTransform($text);
    }

}