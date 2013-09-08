<?php

/**
 * Markdown renderer Interface
 *
 * @category    SchumacherFM_Markdown
 * @package     Model
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
interface SchumacherFM_Markdown_Model_Markdown_Interface
{
    /**
     * transform markdown into html
     * @param string $text
     *
     * @return string
     */
    public static function defaultTransform($text);

}
