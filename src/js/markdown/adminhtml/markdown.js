/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
;
(function () {
    var
        epicEditorInstances = {},
        htmlId = '',

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
        toggleEpicEditor = function (textareaId) {
            if (!epicEditorInstances[textareaId]) {
                return false;
            }

            var instance = epicEditorInstances[textareaId];
            if (instance.is('loaded')) {
                instance.unload();
                $(textareaId).removeClassName('no-display');
            } else {
                $(textareaId).addClassName('no-display');
                instance.load();
            }

        },
        _epicParser = function (content) {
            console.log(content);
            return 'Hallo';
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
        _loadEpicEditor = function () {

            if (!window.EpicEditor) {
                return false;
            }

            // going into the callback hell ... for loading multiple instances on one page
            ['product_edit_form', 'category_edit_form', 'edit_form'].forEach(function (formId) {
                var $form = $(formId);
                if ($form) {
                    $form.select('.initEpicEditor').forEach(_createEpicEditorInstances);
                }
            });
        },
        _createEpicEditorInstances = function (divEpic) {
            var
                epicHtmlId = divEpic.id,
                htmlIdSplit = epicHtmlId.split('_EE_'),
                textAreaId = htmlIdSplit[1] || '',
                editorOptions = _getDefaultEpicEditorOptions();

            if (!epicEditorInstances[textAreaId]) {
                var userConfig = unescape(divEpic.readAttribute('data-config') || '{}').evalJSON(true);
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

    this.mdExternalUrl = mdExternalUrl;
    this.toggleMarkdown = toggleMarkdown;
    this.toggleEpicEditor = toggleEpicEditor;

    document.observe('dom:loaded', function () {

        // editor can't load properly due to the late initialized tabs ... therefore thanks there are events!
        if (_documentHasTabs()) {

            var allowedTabs = {
                'page_tabs_content_section': true,  // cms page
                'product_info_tabs_group_34': true,  // product edit
                'category_info_tabs_group_4': true   // category edit
            };

            varienGlobalEvents.attachEventHandler('showTab', function (e) {
                console.log(e.tab.id);
                if (allowedTabs[e.tab.id]) {
                    _loadEpicEditor();
                }
            });

        } else {
            _loadEpicEditor();
        }

    });

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
