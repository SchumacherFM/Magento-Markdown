/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
;
(function () {
    var
        detectionTag = '',
        epicEditorInstances = {},
        htmlId = '',
        mdLoadEpicEditorForce = false,

        mdExternalUrl = function (url, Idhtml) {
            htmlId = Idhtml;
            window.open(url);
        },

        toggleMarkdown = function (detectionTag, textareaId) {
            detectionTag = unescape(detectionTag);

            if ($(textareaId).value.indexOf(detectionTag) === -1) {

                if (epicEditorInstances[textareaId]) {
                    var instance = epicEditorInstances[textareaId];
                    instance.getElement('editor').body.innerHTML = detectionTag + "<br>\n" + instance.getElement('editor').body.innerHTML;
                } else {
                    $(textareaId).value = detectionTag + "\n" + $(textareaId).value;
                }
            }
            alert('Markdown enabled with tag: "' + detectionTag + '"');
        },
        toggleEpicEditor = function (textAreaId) {
            if (!epicEditorInstances[textAreaId]) {
                return;
            }

            var instance = epicEditorInstances[textAreaId];
            if (instance.is('loaded')) {

                console.log('wrapper: ', instance.getElement('wrapper') || false);

                instance.unload();
                $(textAreaId).removeClassName('no-display');
            } else {
                $(textAreaId).addClassName('no-display');
                instance.load();
            }
            return;
        },
        _epicParser = function (content) {
            if (detectionTag !== '' && !detectionTag) {
                content = content.replace(detectionTag, '');
            }
            return marked(content);
        },
        _getDefaultEpicEditorOptions = function () {
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
        },
        mdLoadEpicEditor = function (forceLoading) {

            if (!window.EpicEditor) {
                return false;
            }
            mdLoadEpicEditorForce = forceLoading || false;

            // going into the callback hell ... for loading multiple instances on one page
            ['product_edit_form' /* , 'category_edit_form'*/, 'edit_form'].forEach(function (formId) {
                var $form = $(formId);

                if ($form) {
                    $form.select('.initEpicEditor').forEach(_createEpicEditorInstances);
                }
            });

            if ($('category_info_tabs_group_4_content')) {
//                console.log($('category_info_tabs_group_4_content').select('.initEpicEditor'));
                _createEpicEditorInstances($('category_info_tabs_group_4_content').select('.initEpicEditor')[0]);
            }

        },
        _createEpicEditorInstances = function (divEpic) {
            console.log('divEpic', divEpic.id);
            var
                epicHtmlId = divEpic.id,
                htmlIdSplit = epicHtmlId.split('_EE_'),
                textAreaId = htmlIdSplit[1] || '',
                editorOptions = _getDefaultEpicEditorOptions();

            if (!epicEditorInstances[textAreaId] || true === mdLoadEpicEditorForce) {
                var userConfig = unescape(divEpic.readAttribute('data-config') || '{}').evalJSON(true);
                detectionTag = unescape(divEpic.readAttribute('data-detectiontag') || '');
                Object.extend(editorOptions, userConfig);
                editorOptions.container = epicHtmlId;
                editorOptions.textarea = textAreaId;
                var epicEditorInstance = new window.EpicEditor(editorOptions).load();
                epicEditorInstances[textAreaId] = epicEditorInstance;
            }
        },
        _documentHasTabs = function () {
            var isVertical = $$('ul.tabs').length === 1; // isProductOrCms
            var isHorizontal = $$('ul.tabs-horiz').length === 1; // isCategory
            return isVertical || isHorizontal;
        };

    // polluting env :-) @todo fix that
    this.mdExternalUrl = mdExternalUrl;
    this.toggleMarkdown = toggleMarkdown;
    this.toggleEpicEditor = toggleEpicEditor;
    this.mdLoadEpicEditor = mdLoadEpicEditor;

    document.observe('dom:loaded', function () {

        if ($(document.body).hasClassName('adminhtml-catalog-category-edit') || !window.EpicEditor) {
            console.log('EpicEditor loading disabled ...');
            return null;
        }

        // editor can't load properly due to the late initialized tabs ... therefore thanks there are events!
        if (_documentHasTabs()) {

            var allowedTabs = {
                'page_tabs_content_section': true,  // cms page
                'product_info_tabs_group_34': true  // product edit

                // https://twitter.com/iamdevloper/status/378464078895017984
                // cannot do that in category edit due to extjs tree and ajax loading :-(
                // we need the initialization directly in the form ...
                // 'category_info_tabs_group_4': true   // category edit
            };

            varienGlobalEvents.attachEventHandler('showTab', function (e) {
                if (allowedTabs[e.tab.id]) {
                    mdLoadEpicEditor();
                }
            });

        } else {
            mdLoadEpicEditor();
        }

    });

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
