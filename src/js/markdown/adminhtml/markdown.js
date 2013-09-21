/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
/*global $,marked,varienGlobalEvents*/
;
(function () {
    'use strict';
    var
        detectionTag = '',
        epicEditorInstances = {},
        htmlId = '',
        EPIC_EDITOR_PREFIX = 'epiceditor_EE_';

    function mdExternalUrl(url, Idhtml) {
        htmlId = Idhtml;
        window.open(url);
    }

    function toggleMarkdown(detectionTag, textareaId) {
        detectionTag = unescape(detectionTag);

        if ($(textareaId).value.indexOf(detectionTag) === -1) {

            var instance = epicEditorInstances[textareaId] || false;
            if (instance && instance.is('loaded')) {
                instance.getElement('editor').body.innerHTML = detectionTag + "<br>\n" + instance.getElement('editor').body.innerHTML;
            } else {
                $(textareaId).value = detectionTag + "\n" + $(textareaId).value;
            }
        }
        alert('Markdown enabled with tag: "' + detectionTag + '"');
    }

    function _epicParser(content) {
        if (detectionTag && detectionTag !== '') {
            content = content.replace(detectionTag, '');
        }
        return marked(content);
    }

    function _getDefaultEpicEditorOptions() {
        return {
            container: null,
            textarea: null,
            basePath: '/skin/adminhtml/default/default/epiceditor/',
            clientSideStorage: true,
            parser: _epicParser,
            localStorageName: 'epiceditor',
            useNativeFullscreen: true,
            file: {
                name: 'epiceditor',
                defaultContent: '',
                autoSave: 100
            },
            theme: {
                base: 'themes/base/epiceditor.css',
                preview: 'themes/preview/github.css',
                editor: 'themes/editor/epic-light.css'
            },
            button: {
                preview: true,
                fullscreen: true,
                bar: "show"
            },
            focusOnLoad: false,
            shortcut: {
                modifier: 18,
                fullscreen: 70,
                preview: 80
            },
            string: {
                togglePreview: 'Toggle Preview Mode',
                toggleEdit: 'Toggle Edit Mode',
                toggleFullscreen: 'Enter Fullscreen'
            },
            autogrow: {
                minHeight: 400,
                maxHeight: 700,
                scroll: true
            }
        };
    }

    function _createEpicEditorInstances(event, element) {

        var
            epicHtmlId = EPIC_EDITOR_PREFIX + (element.id || ''),
            $epicHtmlId = $(epicHtmlId),
            textAreaId = element.id || '',
            editorOptions = _getDefaultEpicEditorOptions(),
            instanceId = textAreaId,
            epicEditorInstance = {},
            userConfig = {};

        if (!epicEditorInstances[instanceId]) {
            userConfig = unescape($epicHtmlId.readAttribute('data-config') || '{}').evalJSON(true);
            detectionTag = unescape($epicHtmlId.readAttribute('data-detectiontag') || '');
            Object.extend(editorOptions, userConfig);
            editorOptions.container = epicHtmlId;
            editorOptions.textarea = textAreaId;
            editorOptions.localStorageName = textAreaId;

            element.hide();
            epicEditorInstance = new window.EpicEditor(editorOptions);
            epicEditorInstance
                .on('load', function () {
                    $epicHtmlId.setStyle({
                        display: 'block',
                        height: parseInt(editorOptions.autogrow.maxHeight || 700, 10) + 'px'
                    });
                    epicEditorInstance.reflow();
                })
                .on('unload', function () {
                    $epicHtmlId.setStyle({
                        display: 'none'
                    });
                });
            epicEditorInstances[instanceId] = epicEditorInstance.load();
        }
    }

    function toggleEpicEditor(textAreaId) {

        var
            instanceId = textAreaId,
            instance = epicEditorInstances[instanceId] || false,
            epicHtmlId = EPIC_EDITOR_PREFIX + textAreaId;

        if (false === instance) {
            _createEpicEditorInstances(null, $(textAreaId));
            return;
        }

        if (instance.is('loaded')) {
            instance.unload();
            $(textAreaId).show();
        } else {
            $(textAreaId).hide();
            instance.load();
        }
        return;
    }


    function mdLoadEpicEditor() {

        if (!window.EpicEditor) {
            return false;
        }

        var parentElementIds = ['product_edit_form', 'edit_form', 'category-edit-container'];
        if (varienGlobalEvents) {
            varienGlobalEvents.fireEvent('mdLoadEpicEditorForms', parentElementIds);
        }
        //  loading multiple instances on one page
        // only works with event delegation due category edit page ...
        // fire event for customization varienGlobalEvents.attachEventHandler('showTab', function (e) {...}
        parentElementIds.forEach(function (elementId) {
            var $elementId = $(elementId);
            if ($elementId) {
                $elementId.on('click', 'textarea.initEpicEditor', _createEpicEditorInstances);
            }
        });
    }

    this.mdExternalUrl = mdExternalUrl;
    this.toggleMarkdown = toggleMarkdown;
    this.toggleEpicEditor = toggleEpicEditor;

    document.observe('dom:loaded', mdLoadEpicEditor);

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
