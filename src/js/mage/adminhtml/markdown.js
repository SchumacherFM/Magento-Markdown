/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
var renderMarkdown = function (actionUrl, htmlId) {

    var FORM_ID = 'edit_form';
    var dialogWindow;
    var dialogWindowId = 'markdown-preview';
    var TEXT_PREFIX = '<div class="markdown">';
    var TEXT_SUFFIX = '</div>';

    var _processFailure = function (e) {
        console.log('Markdown Failure:', e);
    }

    var _processResult = function (transport) {
        if (transport.responseText && transport.responseText !== '') {
            showPreview(transport.responseText);
        } else {
            console.log('Markdown Failure in rendering process!');
        }
    }

    new Ajax.Request(actionUrl, {
        method: 'post',
        parameters: $(FORM_ID).serialize(),
        onComplete: _processResult.bind(this),
        onFailure: _processFailure.bind(this)
    });

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

}
