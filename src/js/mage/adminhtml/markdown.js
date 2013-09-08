/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
;
(function () {
    var dialogWindow,
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

        _renderMarkdownJs = function (mdDetector) {
            mdDetector = unescape(mdDetector);
            showPreview(marked($(htmlId).value.replace(mdDetector, '')));
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

        renderMarkdown = function (Idhtml, mdDetector, renderUrl) {
            htmlId = Idhtml;
            if (renderUrl && typeof renderUrl === 'string') {
                _renderMarkdownAjax(renderUrl);
            } else {
                _renderMarkdownJs(mdDetector);
            }
            return;

        },

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

        _livePreview = function ($markdownLivePreview) {
            var editorId = $markdownLivePreview.readAttribute('data-elementid'),
                $editorId = $(editorId),
                _mdHandling = new _mdHandler();

            _mdHandling.setMdDetector($markdownLivePreview);

            var _originalHeight = $markdownLivePreview.getStyle('height'), _clicked = false;
            $markdownLivePreview.observe('click', function (e) {
                var css = {height: ''};
                if (_clicked) {
                    css['height'] = _originalHeight;
                    _clicked = false;
                } else {
                    _clicked = true;
                }
                $markdownLivePreview.setStyle(css);
            });

            $editorId.observe('keyup', function (e) {
                _mdHandling.text = e.target.value;
                $markdownLivePreview.innerHTML = _mdHandling.hasMarkdown()
                    ? _mdHandling.getRenderedMarkdown()
                    : 'Offline ...';
            });
        },

        _mdHandler = function () {
            this.text = '';
            this._mdDetector = '';
        };

    _mdHandler.prototype = {
        setMdDetector: function ($markdownLivePreview) {
            this._mdDetector = unescape($markdownLivePreview.readAttribute('data-mddetector') || '~~~@#$#@!');
            return this;
        },
        getRenderedMarkdown: function () {
            return  TEXT_PREFIX + marked(this.text.replace(this._mdDetector, '')) + TEXT_SUFFIX;
        },
        hasMarkdown: function () {
            return this.text.indexOf(this._mdDetector) !== -1;
        }
    }

    this.renderMarkdown = renderMarkdown;
    this.mdExternalUrl = mdExternalUrl;
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
