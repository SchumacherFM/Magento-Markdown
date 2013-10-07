/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
/*global $,marked,varienGlobalEvents,Ajax,hljs*/
;
(function () {
    'use strict';
    var
        _detectionTag = null,
        epicEditorInstances = {},
        _mdExtraRenderUrl = null,
        EPIC_EDITOR_PREFIX = 'epiceditor_EE_',
        isViewMarkdownSourceHtml = false,
        COLOR_ON = 'green',
        COLOR_OFF = 'white',
        _toggleMarkdownSourceOriginalMarkdown = '';

    /**
     *
     * @param variable mixed
     * @returns {boolean}
     * @private
     */
    function _isObject(variable) {
        return Object.prototype.toString.call(variable) === '[object Object]';
    }

    /**
     *
     * @param variable mixed
     * @returns {boolean}
     * @private
     */
    function _isFunction(variable) {
        return Object.prototype.toString.call(variable) === '[object Function]';
    }

    /**
     *
     * @returns {boolean}
     * @private
     */
    function _isEpicEditorEnabled() {
        return window.EpicEditor !== undefined;
    }

    /**
     *
     * @returns string|boolean
     * @private
     */
    function _getMdExtraRenderUrl() {
        if (null !== _mdExtraRenderUrl) {
            return _mdExtraRenderUrl;
        }
        _mdExtraRenderUrl = $('markdownGlobalConfig').readAttribute('data-mdextrarenderer') || '';

        if (_mdExtraRenderUrl.indexOf('http') === -1) {
            _mdExtraRenderUrl = false;
        }

        return _mdExtraRenderUrl;
    }

    /**
     *
     * @returns string
     * @private
     */
    function _getDetectionTag() {
        if (null !== _detectionTag) {
            return _detectionTag;
        }
        _detectionTag = $('markdownGlobalConfig').readAttribute('data-detectiontag') || '';
        _detectionTag = decodeURIComponent(_detectionTag);
        return _detectionTag;
    }

    function mdExternalUrl(url) {
        window.open(url);
    }

    /**
     *
     * @param textareaId
     */
    function toggleMarkdown(textareaId) {


        if ($(textareaId).value.indexOf(_getDetectionTag()) === -1) {

            var instance = epicEditorInstances[textareaId] || false;
            if (instance && instance.is('loaded')) {
                instance.getElement('editor').body.innerHTML = _getDetectionTag() + "<br>\n" + instance.getElement('editor').body.innerHTML;
            } else {
                $(textareaId).value = _getDetectionTag() + "\n" + $(textareaId).value;
            }
        }
        alert('Markdown enabled with tag: "' + _getDetectionTag() + '"');
    }

    /**
     * Shows the generated source html code
     * @param object element
     * @param string textAreaId
     * @returns uninteresting
     */
    function toggleMarkdownSource(element, textAreaId) {
        var _loadEpic = false,
            _instance,
            $textAreaId = $(textAreaId);

        if (true === isViewMarkdownSourceHtml) {
            isViewMarkdownSourceHtml = false;
            element.setStyle({
                color: COLOR_OFF
            });
            // restore original markdown, if not it is lost
            if (_toggleMarkdownSourceOriginalMarkdown.length > 10) {
                $textAreaId.writeAttribute('readonly', false);
                $textAreaId.value = _toggleMarkdownSourceOriginalMarkdown;
                _toggleMarkdownSourceOriginalMarkdown = '';
            }
            return;
        }

        if ($textAreaId.value.indexOf(_getDetectionTag()) === -1) {
            alert('Markdown not found');
            return false;
        }

        _instance = epicEditorInstances[textAreaId] || false;
        _loadEpic = _isEpicEditorEnabled() && (false === _instance || (false !== _instance && _instance.is('unloaded')));

        if (true === _loadEpic) {
            toggleEpicEditor(element, textAreaId);
            _instance = epicEditorInstances[textAreaId] || false;
        }

        isViewMarkdownSourceHtml = true;
        element.setStyle({
            color: COLOR_ON
        });

        if (_instance && _isObject(_instance)) {
            _instance.preview();
        } else {
            _toggleMarkdownSourceOriginalMarkdown = $textAreaId.value;
            // no cache available oroginal MD is lost.
            $textAreaId.value = _parserDefault(_toggleMarkdownSourceOriginalMarkdown, $textAreaId);
            $textAreaId.writeAttribute('readonly', true);
        }
    }

    /**
     *
     * @param string content
     * @returns {promise.Promise}
     * @private
     */
    function _mdExtraRender(content) {

        var p = new promise.Promise();
        new Ajax.Request(_getMdExtraRenderUrl(), {
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
     * so rendering via markdown extra works only if there is one textarea field on the page
     * which creates one instance ... this limitation is due to the promise -> then() ... maybe there are better ways
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

    /**
     *
     * @param string htmlString
     * @returns string
     * @private
     */
    function _highlight(htmlString) {
        if (true === isViewMarkdownSourceHtml) {
            htmlString = '<pre class="hljs">' + hljs.highlight('xml', htmlString).value + '</pre>';
        }
        return htmlString;
    }

    /**
     *
     * default parsing without syntax highlightning
     *
     * @param string content
     * @param object $textArea
     * @returns string
     * @private
     */
    function _parserDefault(content, $textArea) {
        var pContent = {};

        if (content.length > 10 && _getMdExtraRenderUrl()) {
            pContent = _mdExtraRender(content);
            pContent.then(function (error, html) {
                $textArea.value = html;
            });
            return '<h3>Preview will be available shortly ...</h3>';
        }

        return marked(content.replace(_getDetectionTag(), ''));
    }

    /**
     *
     * @param string content
     * @param object $textArea
     * @returns string
     * @private
     */
    function _parserEpicEditor(content, $textArea) {
        var currentActiveInstance = _getEpicEditorActiveInstance(),
            pContent = {};

        if (content.length > 10 && _getMdExtraRenderUrl()) {
            pContent = _mdExtraRender(content);
            pContent.then(function (error, html) {
                if (currentActiveInstance && currentActiveInstance.is('loaded')) {
                    currentActiveInstance.getElement('previewer').body.innerHTML = _highlight(html);
                } else {
                    $textArea.value = html;
                }
            });
            return _highlight('<h3>Preview will be available shortly ...</h3>');
        }

        return _highlight(marked(content.replace(_getDetectionTag(), '')));
    }

    /**
     *
     * @returns {{container: null, textarea: null, basePath: string, clientSideStorage: boolean, parser: Function, localStorageName: string, useNativeFullscreen: boolean, file: {name: string, defaultContent: string, autoSave: number}, theme: {base: string, preview: string, editor: string}, button: {preview: boolean, fullscreen: boolean, bar: string}, focusOnLoad: boolean, shortcut: {modifier: number, fullscreen: number, preview: number}, string: {togglePreview: string, toggleEdit: string, toggleFullscreen: string}, autogrow: {minHeight: number, maxHeight: number, scroll: boolean}}}
     * @private
     */
    function _getDefaultEpicEditorOptions() {
        return {
            container: null,
            textarea: null,
            basePath: '/skin/adminhtml/default/default/epiceditor/',
            clientSideStorage: true,
            parser: _parserEpicEditor,
            localStorageName: 'epiceditor',
            useNativeFullscreen: true,
            file: {
                name: 'epiceditor',
                defaultContent: '',
                autoSave: 100
            },
            theme: {
                base: 'themes/base/epiceditor.css',
                preview: 'themes/preview/githubNxcode.css',
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

        if (element === null || element === undefined) {
            throw 'Wysiwyg only bug ...';
        }

        var
            epicHtmlId = EPIC_EDITOR_PREFIX + (element.id || ''),
            $epicHtmlId = $(epicHtmlId),
            textAreaId = element.id || '',
            editorOptions = _getDefaultEpicEditorOptions(),
            instanceId = textAreaId,
            epicEditorInstance = {},
            userConfig = {};

        if (!epicEditorInstances[instanceId]) {
            userConfig = decodeURIComponent($epicHtmlId.readAttribute('data-config') || '{}').evalJSON(true);

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

    /**
     *
     * @param element this
     * @param textAreaId string
     */
    function toggleEpicEditor(element, textAreaId) {

        var
            instanceId = textAreaId,
            instance = epicEditorInstances[instanceId] || false;

        if (false === instance) {
            _createEpicEditorInstances(null, $(textAreaId));
            element.setStyle({
                color: COLOR_ON
            });
            return;
        }

        if (instance.is('loaded')) {
            instance.unload();
            $(textAreaId).show();
            element.setStyle({
                color: COLOR_OFF
            });
        } else {
            $(textAreaId).hide();
            instance.load();
            element.setStyle({
                color: COLOR_ON
            });
        }
        return;
    }


    function mdLoadEpicEditor() {

        if (false === _isEpicEditorEnabled()) {
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
    this.toggleMarkdownSource = toggleMarkdownSource;

    document.observe('dom:loaded', mdLoadEpicEditor);

}).
    call(function () {
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
                if (res && _isFunction(res.then))
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