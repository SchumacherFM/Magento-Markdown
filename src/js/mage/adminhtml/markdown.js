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

        toggleMarkdown = function (detectionTag, Idhtml) {
            detectionTag = unescape(detectionTag);

            if ($(Idhtml).value.indexOf(detectionTag) === -1) {
                $(Idhtml).value = detectionTag + "\n" + $(Idhtml).value;
            }
            alert('Markdown enabled with tag: "' + detectionTag + '"');
        },
        toggleEpicEditor = function (textareaId) {
            if (!epicEditorInstances[textareaId]) {
                return false;
            }

            var instance = epicEditorInstances[textareaId];
            if (instance.is('loaded')) {
                // some ridiculous copying due to strange code in EpicEditor when unload is called :-(
                // https://github.com/OscarGodson/EpicEditor/issues/289
                var currentText = $(textareaId).value;
                instance.unload();
                $(textareaId).value = currentText;
                $(textareaId).removeClassName('no-display');
            } else {
                $(textareaId).addClassName('no-display');
                instance.load();
            }

        },
        _loadEpicEditor = function () {

            if (!window.EpicEditor) {
                return false;
            }
            var editorOptions = {
                container: null,
                textarea: null,
                basePath: '/skin/adminhtml/default/default/epiceditor/',
                clientSideStorage: true,
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
                autogrow: true
            };

            // going into the callback hell ... for loading multiple instances on one page
            ['product_edit_form', 'category_edit_form', 'edit_form'].forEach(function (formId) {
                var $form = $(formId);
                if ($form) {
                    $form.select('.initEpicEditor').forEach(function (divEpic) {
                        var
                            epicHtmlId = divEpic.id,
                            userConfig = unescape(divEpic.readAttribute('data-config') || '{}').evalJSON(true),
                            htmlIdSplit = epicHtmlId.split('_EE_'),
                            textAreaId = htmlIdSplit[1] || '';

                        Object.extend(editorOptions, userConfig);

                        editorOptions.container = epicHtmlId;
                        editorOptions.textarea = textAreaId;

                        epicEditorInstances[textAreaId] = new window.EpicEditor(editorOptions).load();

                    });

                }
            });


        };

    this.mdExternalUrl = mdExternalUrl;
    this.toggleMarkdown = toggleMarkdown;
    this.toggleEpicEditor = toggleEpicEditor;

    document.observe('dom:loaded', function () {
        _loadEpicEditor();
    });

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
