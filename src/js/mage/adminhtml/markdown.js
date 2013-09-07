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
        },
        _scrollPreviewBox = function () {
            this.selectionStart = 0;
            this.textLength = 0;
            this.text = '';
        },
        _livePreview = function ($markdownLivePreview) {
            var editorId = $markdownLivePreview.readAttribute('data-elementid'),
                $editorId = $(editorId),
                mddetector = unescape($markdownLivePreview.readAttribute('data-mddetector')),
                scrollObject = new _scrollPreviewBox();

            $editorId.observe('keyup', function (e) {
//                console.log(e.target.selectionStart, e.target.selectionEnd, e.target.textLength);
                var mdText = e.target.value;
                if (mdText.indexOf(mddetector) !== -1) {
                    mdText = mdText.replace(mddetector, '');
                    $markdownLivePreview.innerHTML = marked(mdText);
                    if (e.target.selectionStart === e.target.selectionEnd) {
                        scrollObject.selectionStart = e.target.selectionStart;
                        scrollObject.textLength = e.target.textLength;
                        scrollObject.text = mdText;
                        scrollObject.scroll();
                    }
                } else {
                    $markdownLivePreview.innerHTML = 'Offline ...';
                }
            });
        };

    _scrollPreviewBox.prototype = {
        _getTotalTextHeight: function () {
            return this.text.split("\n").length;
        },
        _getCurrentTextHeight: function () {

        },
        scroll: function () {
            console.log(this.selectionStart, this.textLength, this._getTotalTextHeight());
        }
    }

    this.renderMarkdown = renderMarkdown;
    this.markdownSyntax = markdownSyntax;
    this.toggleMarkdown = toggleMarkdown;

    document.observe('dom:loaded', function () {
        var markdownLivePreview = $('markdown_live_preview');
        if (markdownLivePreview) {
            _livePreview(markdownLivePreview);
        }
    });

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
