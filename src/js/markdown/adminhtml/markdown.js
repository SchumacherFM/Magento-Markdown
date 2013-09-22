/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
/*global $,marked,varienGlobalEvents,Ajax*/
;
(function () {
    'use strict';
    var
        detectionTag = null,
        epicEditorInstances = {},
        htmlId = '',
        mdExtraRenderUrl = null,
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

    /**
     *
     * @param string content
     * @returns {promise.Promise}
     * @private
     */
    function _mdExtraRender(content) {

        var
            p = new promise.Promise(),
            ajaxRequest = new Ajax.Request(mdExtraRenderUrl, {
                onSuccess: function (response) {
                    p.done(null, response.responseText);
                },
                method: 'post',
                parameters: {
                    'content': content
                }
            });

        return p;
    }

    /**
     * so rendering via markdown extra works only if there is one textarea field on the pages
     * which creates one instance ... this limitation is due to the promise ... maybe there are better ways
     * fallback is marked()
     * @private
     */
    function _getEpicEditorActiveInstance() {

        if (Object.keys(epicEditorInstances).length !== 1) {
            return false;
        }

        var keys = Object.keys(epicEditorInstances),
            oneKey = keys[0];

        return epicEditorInstances[oneKey];
    }

    function _epicParser(content) {
        var currentActiveInstance = _getEpicEditorActiveInstance(),
            pContent;

        if (currentActiveInstance && content.length > 10 && mdExtraRenderUrl !== '' && mdExtraRenderUrl.indexOf('http') !== -1) {
            pContent = _mdExtraRender(content);
            pContent.then(function (error, html) {
                currentActiveInstance.getElement('previewer').body.innerHTML = html;
            });
            return '<h3>Promise will be resolved shortly ...</h3>';
        }

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
            if (null === detectionTag) {
                detectionTag = unescape($epicHtmlId.readAttribute('data-detectiontag') || '');
            }
            if (null === mdExtraRenderUrl) {
                mdExtraRenderUrl = $epicHtmlId.readAttribute('data-mdextrarenderer') || '';
            }
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
            instance = epicEditorInstances[instanceId] || false;

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

        if ('undefined' === typeof window.EpicEditor) {
            return; // console.log('EpicEditor not loaded');
        }

        var parentElementIds = ['product_edit_form', 'edit_form', 'category-edit-container', 'email_template_edit_form'];
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

/*
 *  Copyright 2012-2013 (c) Pierre Duquesne <stackp@online.fr>
 *  Licensed under the New BSD License.
 *  https://github.com/stackp/promisejs
 *  https://raw.github.com/stackp/promisejs/master/promise.js
 *  modified by @SchumacherFM
 */

(function (exports) {
    'use strict';

    function Promise() {
        this._callbacks = [];
    }

    Promise.prototype.then = function (func, context) {
        var p;
        if (this._isdone) {
            p = func.apply(context, this.result);
        } else {
            p = new Promise();
            this._callbacks.push(function () {
                var res = func.apply(context, arguments);
                if (res && typeof res.then === 'function')
                    res.then(p.done, p);
            });
        }
        return p;
    };

    Promise.prototype.done = function () {
        this.result = arguments;
        this._isdone = true;
        for (var i = 0; i < this._callbacks.length; i++) {
            this._callbacks[i].apply(null, arguments);
        }
        this._callbacks = [];
    };

    function join(promises) {
        var p = new Promise();
        var results = [];

        if (!promises || !promises.length) {
            p.done(results);
            return p;
        }

        var numdone = 0;
        var total = promises.length;

        function notifier(i) {
            return function () {
                numdone += 1;
                results[i] = Array.prototype.slice.call(arguments);
                if (numdone === total) {
                    p.done(results);
                }
            };
        }

        for (var i = 0; i < total; i++) {
            promises[i].then(notifier(i));
        }

        return p;
    }

    function chain(funcs, args) {
        var p = new Promise();
        if (funcs.length === 0) {
            p.done.apply(p, args);
        } else {
            funcs[0].apply(null, args).then(function () {
                funcs.splice(0, 1);
                chain(funcs, arguments).then(function () {
                    p.done.apply(p, arguments);
                });
            });
        }
        return p;
    }


    var promise = {
        Promise: Promise,
        join: join,
        chain: chain,

        /* Error codes */
        ENOXHR: 1,
        ETIMEOUT: 2

    };

    if (typeof define === 'function' && define.amd) {
        /* AMD support */
        define(function () {
            return promise;
        });
    } else {
        exports.promise = promise;
    }

})(this);