/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
;
(function () {
    var FORM_ID = 'edit_form';
    var dialogWindow;
    var dialogWindowId = 'markdown-preview';
    var TEXT_PREFIX = '<div class="markdown">';
    var TEXT_SUFFIX = '</div>';

    var htmlId = '';

    var showPreview = function (responseText) {

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
    }

    var closeDialogWindow = function (window) {
        if (!window) {
            window = dialogWindow;
        }
        if (window) {
            window.close();
        }
    }

    var _renderJs = function () {
        showPreview(marked($(htmlId).value));
    }

    var renderMarkdown = function (Idhtml) {
        htmlId = Idhtml;
        _renderJs();
        return;

    }

    var markdownSyntax = function (url, Idhtml) {
        htmlId = Idhtml;
        window.open(url);
    }

    var toggleMarkdown = function (detectionTag, Idhtml) {
        detectionTag = unescape(detectionTag);

        if ($(Idhtml).value.indexOf(detectionTag) === -1) {
            $(Idhtml).value = detectionTag + "\n" + $(Idhtml).value;
        }
        alert('Markdown enabled with tag: "' + detectionTag+'"');
    }

    this.renderMarkdown = renderMarkdown;
    this.markdownSyntax = markdownSyntax;
    this.toggleMarkdown = toggleMarkdown;

}).call(function () {
        return this || (typeof window !== 'undefined' ? window : global);
    }());
