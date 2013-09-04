/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
;
(function () {
    var FORM_ID = 'edit_form',
        dialogWindow,
        dialogWindowId = 'markdown-preview',
        TEXT_PREFIX = '<div class="markdown">',
        TEXT_SUFFIX = '</div>',

        htmlId = '',

        showPreview = function (responseText) {

            dialogWindow = Dialog.info(TEXT_PREFIX + responseText + TEXT_SUFFIX, {
                draggable: true,
                resizable: true,
                closable: true,
                className: "magento",
                windowClassName: "popup-window",
                title: 'Markdown Preview',
                width: 800,
                height: 480,
                zIndex: 1000,
                recenterAuto: false,
                hideEffect: Element.hide,
                showEffect: Element.show,
                id: dialogWindowId,
                onClose: closeDialogWindow.bind(this)
            });
        },

        closeDialogWindow = function (window) {
            if (!window) {
                window = dialogWindow;
            }
            if (window) {
                window.close();
            }
        },

        _renderMarkdownJs = function () {
            showPreview(marked($(htmlId).value));
        },

        _renderMarkdownAjax = function (url) {
            new Ajax.Request(url, {
                method: 'post',
                parameters: {"content": $(htmlId).value},
                onComplete: function (data) {
                    showPreview((data && data.responseText) ? data.responseText : 'Ajax Error');
                }
            });

        },

        renderMarkdown = function (Idhtml, renderUrl) {
            htmlId = Idhtml;
            if (renderUrl && typeof renderUrl === 'string') {
                _renderMarkdownAjax(renderUrl);
            } else {
                _renderMarkdownJs();
            }
            return;

        },

        markdownSyntax = function (url, Idhtml) {
            htmlId = Idhtml;
            window.open(url);
        },

        toggleMarkdown = function (detectionTag, Idhtml) {
            detectionTag = unescape(detectionTag);

            if ($(Idhtml).value.indexOf(detectionTag) === -1) {
                $(Idhtml).value = detectionTag + "\n" + $(Idhtml).value;
            }
            alert('Markdown enabled with tag: "' + detectionTag + '"');
        };

    this.renderMarkdown = renderMarkdown;
    this.markdownSyntax = markdownSyntax;
    this.toggleMarkdown = toggleMarkdown;

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
