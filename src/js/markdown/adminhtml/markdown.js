/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
/*global $,marked,varienGlobalEvents,Ajax,hljs,FileReaderJS,Event,encode_base64,reMarked*/
;
(function () {
    'use strict';
    var
        _markDownGlobalConfig = {},
        epicEditorInstances = {},
        EPIC_EDITOR_PREFIX = 'epiceditor_EE_',
        isViewMarkdownSourceHtml = false,
        _initializedFileReaderContainer = {},
        _textAreaCurrentCaretObject = {}, // set by the onClick event
        _toggleMarkdownSourceOriginalMarkdown = '';

    /**
     *
     * @param str string
     * @returns boolean|string
     * @private
     */
    function _checkHttp(str) {

        if (!str || false === str || str.indexOf('http') === -1) {
            return false;
        }
        return str;
    }

    /**
     * inits the global md config
     * @returns bool
     * @private
     */
    function _initGlobalConfig() {

        var config = JSON.parse($('markdownGlobalConfig').readAttribute('data-config') || '{}');
        if (config.dt === undefined) {
            return console.log('Markdown Global Config not found. General error!');
        }

        _markDownGlobalConfig = {
            tag: decodeURIComponent(config.dt),
            uploadUrl: _checkHttp(config.fuu || false),
            mediaBaseUrl: _checkHttp(config.phi || false),
            extraRendererUrl: _checkHttp(config.eru || false),
            eeLoadOnClick: config.eeloc || false,
            reMarkedCfg: decodeURIComponent(config.rmc || '{}').evalJSON(true)
        };
        return true;
    }

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
     * @returns {boolean}
     * @private
     */
    function _isFileReaderEnabled() {
        return window.FileReader !== undefined;
    }

    function mdExternalUrl(url) {
        window.open(url);
    }

    /**
     *
     * @param textareaId
     */
    function toggleMarkdown(textareaId) {


        if ($(textareaId).value.indexOf(_markDownGlobalConfig.tag) === -1) {

            var instance = epicEditorInstances[textareaId] || false;
            if (instance && instance.is('loaded')) {
                instance.getElement('editor').body.innerHTML = _markDownGlobalConfig.tag + "<br>\n" + instance.getElement('editor').body.innerHTML;
            } else {
                $(textareaId).value = _markDownGlobalConfig.tag + "\n" + $(textareaId).value;
            }
        }
        alert('Markdown enabled with tag: "' + _markDownGlobalConfig.tag + '"');
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
            element.removeClassName('success');

            // restore original markdown, if not it is lost
            if (_toggleMarkdownSourceOriginalMarkdown.length > 10) {
                $textAreaId.writeAttribute('readonly', false);
                $textAreaId.value = _toggleMarkdownSourceOriginalMarkdown;
                _toggleMarkdownSourceOriginalMarkdown = '';
            }
            return;
        }

        if (_markDownGlobalConfig.tag !== '' && $textAreaId.value.indexOf(_markDownGlobalConfig.tag) === -1) {
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
        element.addClassName('success');

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

        var p = new promise.Promise(),
            ar = new Ajax.Request(_markDownGlobalConfig.extraRendererUrl, {
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
     * @param content string
     * @returns string
     * @private
     */
    function _parserBefore(content) {
        var imgUrl = '',
            mediaRegex = /\{\{media\s+url="([^"]+)"\s*\}\}/i,
            matches = null;

        if (_markDownGlobalConfig.tag !== '') {
            content = content.replace(_markDownGlobalConfig.tag, '');
        }

        if (false !== _markDownGlobalConfig.mediaBaseUrl) {
            while (mediaRegex.test(content)) {
                matches = mediaRegex.exec(content);
                if (null !== matches && matches[1] !== undefined) {
                    imgUrl = _markDownGlobalConfig.mediaBaseUrl + matches[1];
                    content = content.replace(matches[0], imgUrl);
                }
            }
        }
        return content;
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

        if (content.length > 10 && _markDownGlobalConfig.extraRendererUrl) {
            pContent = _mdExtraRender(content);
            pContent.then(function (error, html) {
                $textArea.value = html;
            });
            return '<h3>Preview will be available shortly ...</h3>';
        }
        return marked(_parserBefore(content));
    }

    /**
     * todo replace {{media url=""}} with a dummy preview image, otherwise loading errors will occur
     *      also test that in product and categorie desc fields
     * @param string content
     * @param object $textArea
     * @returns string
     * @private
     */
    function _parserEpicEditor(content, $textArea) {
        var currentActiveInstance = _getEpicEditorActiveInstance(),
            pContent = {};

        if (content.length > 10 && _markDownGlobalConfig.extraRendererUrl) {
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

        return _highlight(marked(_parserBefore(content)));
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
            basePath: null, // will be set via Mage Helper
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

    /**
     *
     * @param event
     * @param element
     * @private
     */
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
     * @return false
     */
    function toggleEpicEditor(element, textAreaId) {

        var
            instanceId = textAreaId,
            instance = epicEditorInstances[instanceId] || false;

        if (false === instance) {
            _createEpicEditorInstances(null, $(textAreaId));
            element.addClassName('success');
            return false;
        }

        if (instance.is('loaded')) {
            instance.unload();
            $(textAreaId).show();
            element.removeClassName('success');
        } else {
            $(textAreaId).hide();
            instance.load();
            element.addClassName('success');
        }
        return false;
    }

    /**
     *
     * @param fileUrl
     * @returns {boolean}
     * @private
     */
    function _fileReaderAddImageToMarkdown(fileUrl) {

        var
            mdTpl = ' ![Alt_Text](' + fileUrl + ' "Logo_Title_Text") ',
            prefix = _textAreaCurrentCaretObject.value.substring(0, _textAreaCurrentCaretObject.selectionEnd),
            suffix = _textAreaCurrentCaretObject.value.substring(_textAreaCurrentCaretObject.selectionEnd);

        _textAreaCurrentCaretObject.value = prefix + mdTpl + suffix;
        prefix = '';
        suffix = '';
        return true;
    }

    /**
     *
     * @param target event.target
     * @private
     */
    function _createFileReaderInstance(target) {

        if (encode_base64 === undefined) {
            return console.log('FileReader not available because method encode_base64() is missing!');
        }

        if (false === _markDownGlobalConfig.uploadUrl) {
            return console.log('FileReader upload url not available!');
        }

        var opts = {
            dragClass: 'fReaderDrag',
            accept: 'image/*',
            readAsMap: {
                'image/*': 'BinaryString'
            },
            readAsDefault: 'BinaryString',
            on: {
                load: function (e, file) {

                    var ar = new Ajax.Request(_markDownGlobalConfig.uploadUrl, {
                        onSuccess: function (response) {
                            var result = JSON.parse(response.responseText);
                            if (result && _isObject(result)) {
                                if (result.err === false) {
                                    return _fileReaderAddImageToMarkdown(result.fileUrl);
                                }
                                if (result.err === true) {
                                    alert('An error occurred:\n' + result.msg);
                                }
                            } else {
                                alert('An error occurred after uploading. No JSON found ...');
                            }
                            return false;
                        },
                        method: 'post',
                        parameters: {
                            'binaryData': encode_base64(e.target.result),
                            'file': JSON.stringify(file)
                        }
                    });

                },
                error: function (e, file) {
                    // Native ProgressEvent
                    alert('An error occurred. Please see console.log');
                    return console.log('error: ', e, file);
                },
                skip: function (e, file) {
                    return console.log('File format is not supported', file);
                }
            }
        };

        FileReaderJS.setupDrop(target, opts);
        _initializedFileReaderContainer[target.id] = true;
    }

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/API/FileReader
     * @param _epicEditorInstance window.EpicEditor loaded
     * @private
     */
    function _createFileReader(event) {
        var target = event.target || event.srcElement;

        _textAreaCurrentCaretObject = target;

        // check if already initialized
        if (_initializedFileReaderContainer[target.id] === undefined) {
            _createFileReaderInstance(target);
        }
    }

    /**
     *
     * @returns {reMarked}
     * @private
     */
    function _getReMarked() {
        var options = {
            link_list: false,    // render links as references, create link list as appendix
            h1_setext: true,     // underline h1 headers
            h2_setext: true,     // underline h2 headers
            h_atx_suf: false,    // header suffixes (###)
            gfm_code: false,    // gfm code blocks (```)
            li_bullet: "*",      // list item bullet style
            hr_char: "-",      // hr style
            indnt_str: "    ",   // indentation string
            bold_char: "*",      // char used for strong
            emph_char: "_",      // char used for em
            gfm_del: true,     // ~~strikeout~~ for <del>strikeout</del>
            gfm_tbls: false,     // markdown-extra tables @SchumacherFM: if true the error on line 518 in remarked.js :-(
            tbl_edges: false,    // show side edges on tables
            hash_lnks: false,    // anchors w/hash hrefs as links
            br_only: false    // avoid using "  " as line break indicator
        };
        Object.extend(options, _markDownGlobalConfig.reMarkedCfg);
        return new reMarked(options);
    }

    /**
     * renders html to markdown
     * @param textAreaId string
     */
    function htmlToMarkDown(element, textAreaId) {
        var html = $(textAreaId).value || '';

        var _instance = epicEditorInstances[textAreaId] || false;
        var _loadedEpic = _isEpicEditorEnabled() && false !== _instance && _instance.is('loaded');
        if (true === _loadedEpic) {
            toggleEpicEditor(element, textAreaId);
        }


        if (_markDownGlobalConfig.tag !== '' && html.indexOf(_markDownGlobalConfig.tag) === -1) {
            $(textAreaId).value = _markDownGlobalConfig.tag + '\n' + _getReMarked().render(html);
        }
        if (_markDownGlobalConfig.tag === '') {
            $(textAreaId).value = _getReMarked().render(html);
        }
    }

    /**
     * loads the filereader, epiceditor
     */
    function _mdInitialize() {
        _initGlobalConfig();
        var parentElementIds = ['product_edit_form', 'edit_form', 'category-edit-container', 'email_template_edit_form'];
        if (varienGlobalEvents) {
            varienGlobalEvents.fireEvent('mdLoadForms', parentElementIds);
        }

        //  loading multiple instances on one page
        // only works with event delegation due category edit page ...
        // fire event for customization varienGlobalEvents.attachEventHandler('showTab', function (e) {...}
        parentElementIds.forEach(function (elementId) {
            var $elementId = $(elementId);
            if ($elementId) {
                // some things are only possible with event delegation ...
                if (true === _isEpicEditorEnabled() && true === _markDownGlobalConfig.eeLoadOnClick) {
                    $elementId.on('click', 'textarea.initEpicEditor', _createEpicEditorInstances);
                }
                if (true === _isFileReaderEnabled()) {
                    $elementId.on('click', 'textarea.initFileReader', _createFileReader);
                }

            }
        });
    }

    this.mdExternalUrl = mdExternalUrl;
    this.toggleMarkdown = toggleMarkdown;
    this.toggleEpicEditor = toggleEpicEditor;
    this.toggleMarkdownSource = toggleMarkdownSource;
    this.htmlToMarkDown = htmlToMarkDown;

    document.observe('dom:loaded', _mdInitialize);

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