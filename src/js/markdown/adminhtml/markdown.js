/**
 * @category    SchumacherFM_Markdown
 * @package     JavaScript
 * @author      Cyrill at Schumacher dot fm / @SchumacherFM
 * @copyright   Copyright (c)
 */
/*global $,$$,marked,varienGlobalEvents,Ajax,hljs,FileReaderJS,Event,encode_base64,reMarked,Effect,Element*/
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, noarg:true, noempty:true, nonew:true, undef:true, strict:false, browser:true, prototypejs:true */
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
        _toggleMarkdownSourceOriginalMarkdown = '',
        tempIframeJSSource = '',
        iFrameScrollPositions = {};

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
            isHiddenInsertImageButton: config.hideIIB || true,
            previewCSS: config.mdCss || false,
            previewUrl: config.lpUrl || '',
            stores: config.stores || [],
            featureBaseUrl: config.feaBUrl || '',
            highLightCSS: config.hlCss || false,
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
            $textAreaId = $(textAreaId),

            /**
             *
             * @param content
             * @param $textArea
             * @returns {*}
             * @private
             */
                _parserDefault = function (content, $textArea) {
                var pContent = {}, renderedMarkDown = '';

                if (content.length > 10 && _markDownGlobalConfig.extraRendererUrl) {
                    pContent = _mdExtraRender(content);
                    pContent.then(function (error, html) {
                        $textArea.value = html;
                    });
                    return '<h3>Preview will be available shortly ...</h3>';
                }
                return marked(_handleMagentoMediaUrl(content));
            };

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
     * @param htmlString
     * @param options
     * @returns {string}
     * @private
     */
    function _highlightOpt(htmlString, options) {
        options = options || {};
        var _hlPre = options.hlPre || '<pre><code>',
            _hlPost = options.hlPost || '</code></pre>';
        return _hlPre + hljs.highlight('xml', htmlString).value + _hlPost;
    }

    /**
     * handles magento special tag {{media url=""}}
     *
     * @param content string
     * @returns string
     * @private
     */
    function _handleMagentoMediaUrl(content) {
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
     * @param htmlString
     * @returns {*}
     * @private
     */
    function _htmlBeautify(htmlString) {
        return window.html_beautify(htmlString, {
            'indent_inner_html': false,
            'indent_size': 2,
            'indent_char': ' ',
            'wrap_line_length': 78,
            'brace_style': 'expand',
            'unformatted': ['a', 'sub', 'sup', 'b', 'i', 'u'],
            'preserve_newlines': true,
            'max_preserve_newlines': 5,
            'indent_handlebars': false
        });
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
            pContent = {},
            _highlight = function (htmlString) {
                if (true === isViewMarkdownSourceHtml) {
                    htmlString = _highlightOpt(htmlString, {'hlPre': '<pre class="hljs">'});
                }
                return htmlString;
            };

        if (content.length > 10 && _markDownGlobalConfig.extraRendererUrl) {
            pContent = _mdExtraRender(content);
            pContent.then(function (error, html) {
                if (currentActiveInstance && currentActiveInstance.is('loaded')) {
                    currentActiveInstance.getElement('previewer').body.innerHTML = _highlight(html);
                } else {
                    $textArea.value = html; // @todo bug if epicEditor is disabled an extra enabled
                }
            });
            return _highlight('<h3>Preview will be available shortly ...</h3>');
        }

        return _highlight(marked(_handleMagentoMediaUrl(content)));
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
                'image/*': 'BinaryString' // @todo refactor for using: ArrayBuffer
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
                            'binaryData': encode_base64(e.target.result), // @todo refactor use real file uploads -> ArrayBuffer
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
        FileReaderJS.setupInput($('man_chooser_' + target.id), opts);
        FileReaderJS.setupClipboard(target, opts);
        _initializedFileReaderContainer[target.id] = true;
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
            gfm_tbls: true,     // markdown-extra tables
            tbl_edges: false,    // show side edges on tables
            hash_lnks: false,    // anchors w/hash hrefs as links
            br_only: false    // avoid using "  " as line break indicator
        };
        Object.extend(options, _markDownGlobalConfig.reMarkedCfg);
        return new reMarked(options);
    }

    /**
     *
     * @param text
     * @constructor
     */
    function MagentoTagPreserver(text) {
        this.text = text;
        this.container = {};
    }

    MagentoTagPreserver.prototype = {
        getPreserved: function () {
            var
                tagRegex = /(\{\{[^\}]+\}\})/,  // e.g.: {{store direct_url="about-us"}}
                matches = null,
                key = '',
                i = 0;

            while (tagRegex.test(this.text)) {
                matches = tagRegex.exec(this.text);
                if (null !== matches && matches[1] !== undefined) {
                    key = Math.random() + '_' + i;
                    this.text = this.text.replace(matches[1], key);
                    this.container[key] = matches[1];
                    i = i + 1;
                }
            }

            return this.text;
        },
        restore: function (transformedString) {
            var key = '';
            this.text = '';

            for (key in this.container) {
                if (this.container.hasOwnProperty(key)) {
                    transformedString = transformedString.replace(key, this.container[key]);
                }
            }
            this.container = {};
            return transformedString;
        }

    };

    /**
     * renders html to markdown
     * @param textAreaId string
     */
    function htmlToMarkDown(element, textAreaId) {
        var html = '',
            thePreserver = new MagentoTagPreserver($(textAreaId).value || ''),
            markDownGlobalConfigTag = '',
            _instance = epicEditorInstances[textAreaId] || false,
            _loadedEpic = _isEpicEditorEnabled() && false !== _instance && _instance.is('loaded');

        if (true === _loadedEpic) {
            toggleEpicEditor(element, textAreaId);
        }

        html = thePreserver.getPreserved();
        if (_markDownGlobalConfig.tag !== '' && html.indexOf(_markDownGlobalConfig.tag) === -1) {
            markDownGlobalConfigTag = _markDownGlobalConfig.tag;
        }

        $(textAreaId).value = thePreserver.restore(markDownGlobalConfigTag + '\n' + _getReMarked().render(html));
    }

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/API/FileReader
     * @param _epicEditorInstance window.EpicEditor loaded
     * @private
     */
    function _createFileReaderFactory(event) {
        var target = event.target || event.srcElement;
        _textAreaCurrentCaretObject = target;

        // check if already initialized
        if (_initializedFileReaderContainer[target.id] === undefined) {
            _createFileReaderInstance(target);
        }
    }

    /******************************************************************************************************
     *
     * @constructor
     */
    function TabPreviewHandler() {
        this.data = {};
        this._isHtmlPreview = false;
        this._livePreviewSetUpDone = false;
        this._reloadCounter = 0;
        this.lpInputElement = new Element('input', {
            'type': 'text',
            'class': 'input-text',
            'value': ''
        });
        this._reloadAnswers = [
            'Cannot find a URL ...',
            'As I\'ve said before: Cannot find a URL ...',
            'Would you please stop clicking on me?',
            'Again ... you have to put in a valid URL!',
            'One more click ...'
        ];
        this._lockedRaptor = false;
    }

    /**
     *
     *
     */
    TabPreviewHandler.prototype = {
        _localStorageGet: function (key) {
            return window.localStorage.getItem('schumacherfm_markdown_' + this.data.textAreaId + '_' + key);
        },
        _localStorageSet: function (key, value) {
            return window.localStorage.setItem('schumacherfm_markdown_' + this.data.textAreaId + '_' + key, value);
        },
        setData: function (data) {
            this._isHtmlPreview = false;
            this.data = data;
            this._reloadCounter = 0;
            return this;
        },
        _preview: function () {
            var pContent = new promise.Promise(),
                self = this,
                content = $(this.data.textAreaId).value;

            if (content.replace(/\s*/g, '') === '') {
                self._setIframe('');
                return false;
            }

            if (_markDownGlobalConfig.extraRendererUrl) {
                pContent = _mdExtraRender(content);
                pContent.then(function (error, html) {
                    self._setIframe(html);
                });
            } else {
                pContent.then(function (error, markdownContent) {
                    markdownContent = marked(_handleMagentoMediaUrl(markdownContent));
                    this._setIframe(markdownContent);
                }, this);
                pContent.done(null, content);
            }
        },
        _getHtmlStyleSheet: function (styleUrl) {
            return '<link href="' + styleUrl + '" rel="stylesheet" type="text/css" />';
        },
        _getStyleSheets: function () {
            return _markDownGlobalConfig.highLightCSS && this._isHtmlPreview
                ? this._getHtmlStyleSheet(_markDownGlobalConfig.highLightCSS)
                : this._getHtmlStyleSheet(_markDownGlobalConfig.previewCSS);
        },
        _setIframeSrc: function (theSrc) {
            var theIframe = this.data.tabBody.select('.iframePreview')[0];
            theIframe.src = theSrc;
            return this;
        },
        _getJavaScript: function () {
            var insertJS = tempIframeJSSource.replace('~~id~~', this.data.tabBody.id);
            insertJS = insertJS.replace('~~scrollto~~', iFrameScrollPositions[this.data.tabBody.id] || 0);
            return '<script type="text/javascript">' + insertJS + '</script>';
        },
        _setIframe: function (htmlString) {
            if (_markDownGlobalConfig.previewCSS === false) {
                alert('Markdown Preview Style Sheet not available!');
                console.log(_markDownGlobalConfig);
                return false;
            }

            var bodyStyle = 'pointer-events:none;';
            if (true === this._isHtmlPreview) {
                htmlString = _highlightOpt(_htmlBeautify(htmlString));
                bodyStyle += 'padding:0; margin:0;';
            }

            // http://www.thecssninja.com/javascript/pointer-events-60fps
            this._setIframeSrc('data:text/html;charset=utf-8,' +
                encodeURIComponent('<html><head>' + this._getStyleSheets() +
                    '</head><body style="' + bodyStyle + '">' +
                    this._getJavaScript()
                    +
                    htmlString
                    + '</body></html>'));
            return true;
        },
        preview: function () {
            this._preview();
        },
        htmlPreview: function () {
            this._isHtmlPreview = true;
            this._preview();
        },
        _setUpLivePreview: function () {
            if (true === this._livePreviewSetUpDone) {
                return null;
            }

            var self = this,
                ul = self.data.tabBody.select('.previewStores')[0],
                liElement = new Element('li'),
                reload = new Element('a', {'href': '#'}),
                url = _markDownGlobalConfig.previewUrl + '?___store=';

            if (_markDownGlobalConfig.previewUrl !== '') {
                _markDownGlobalConfig.stores.forEach(function (storeCode) {
                    ul.insert('<li><a href="' + url + storeCode + '" target="' + self.data.textAreaId + '__livePreviewB">' + storeCode + '</a></li>');
                });
            }

            if (true === _markDownGlobalConfig.previewUrl.empty()) {

                this.lpInputElement.value = self._localStorageGet('lpUrl');
                this.lpInputElement.observe('change', this._observeUserLivePreviewUrl.bind(this));
                reload.update('Reload?');
                reload.observe('click', self._observeUserLivePreviewUrlReload.bind(this));
                liElement.update('Please enter live preview URL. (Cannot be detected automatically) ');
                liElement.insert(reload);
                liElement.insert(self.lpInputElement);
                ul.insert(liElement);
            }

            this._livePreviewSetUpDone = true;
            return _markDownGlobalConfig.previewUrl !== '' ? url + (_markDownGlobalConfig.stores[0] || 'default') : null;
        },
        _isUrl: function (url) {
            return url.search(/^htt(p|ps):\/\/[a-z0-9]+/) !== -1;
        },
        _observeUserLivePreviewSetiFrame: function (url) {
            if (true === this._isUrl(url)) {
                this._setIframeSrc(url);
            } else {
                if (this._lockedRaptor === false) { // only load once the raptor
                    this.lpInputElement.value = (this._reloadAnswers[this._reloadCounter] || this._reloadAnswers[0]);
                    this._reloadCounter = this._reloadCounter + 1;
                    if (this._reloadCounter === this._reloadAnswers.length) {
                        this._raptorize();
                        this._reloadCounter = 0;
                    }
                } else {
                    this.lpInputElement.value = this._reloadAnswers[0];
                }
            }
        },
        _observeUserLivePreviewUrl: function (event) {
            var value = (event.srcElement || event.target).value.toLowerCase();
            if (true === this._isUrl(value)) {
                this._localStorageSet('lpUrl', value);
            }
            this._observeUserLivePreviewSetiFrame(value);
        },
        _observeUserLivePreviewUrlReload: function (event) {
            event.preventDefault();
            var value = this.lpInputElement.value.toLowerCase(),
                rand = 'rand=' + Math.random(),
                randPos = value.indexOf('rand=');

            if (randPos !== -1) {
                value = value.substr(randPos - 1, 20);
            }
            if (true === this._isUrl(value)) {
                this._localStorageSet('lpUrl', value);
            }
            if (value.indexOf('?') !== -1) {
                value = value + '&' + rand;
            } else {
                value = value + '?' + rand;
            }
            this._observeUserLivePreviewSetiFrame(value);
        },
        livePreview: function () {
            var firstUrl = this._setUpLivePreview();

            if (null !== firstUrl) {
                this._setIframeSrc(firstUrl);
            }
        },
        _raptorize: function (options) {
            // based on http://zurb.com/playground/jquery-raptorize
            //the defaults
            var self = this,
                myOptions = options || {}, // make sure options object is valid
                enterOn = myOptions.appearOn || 'time', //time, konami-code, click, code
                delayTime = myOptions.delayTime || 2000, //time before raptor attacks on timer mode

                sound = false,
                canPlayMp3 = false,
                canPlayOgg = false,

                viewport = document.viewport.getDimensions(),
                type = '',
                src = '',
                raptorAudioMarkup = {},
                html5 = {},

                myAudio = document.createElement('audio');

            if (myAudio.canPlayType) {
                canPlayMp3 = !!myAudio.canPlayType && '' !== myAudio.canPlayType('audio/mpeg');
                canPlayOgg = !!myAudio.canPlayType && '' !== myAudio.canPlayType('audio/ogg; codecs="vorbis"');
            }
            if (canPlayMp3) {
                type = 'audio/mp3';
                src = _markDownGlobalConfig.featureBaseUrl + 'rs.mp3';
                sound = true;
            }
            if (canPlayOgg) {
                type = 'audio/ogg';
                src = _markDownGlobalConfig.featureBaseUrl + 'rs.ogg';
                sound = true;
            }

            //Raptor Sound
            if (sound) {
                raptorAudioMarkup = new Element('audio', {id: "elRaptorShriek", "preload": "auto"});
                document.body.appendChild(raptorAudioMarkup);

                html5 = new Element('source');
                html5.type = type;
                html5.src = src;
                raptorAudioMarkup.appendChild(html5);
            }
            //Append Raptor and Style
            var raptorImageMarkup = new Element('img', {
                    id: 'elRaptor',
                    src: _markDownGlobalConfig.featureBaseUrl + 'r.png'
                }),
                imgSize = {
                    width: 400,
                    height: 600
                },
                raptorPosition = {
                    width: viewport.width - imgSize.width,
                    height: viewport.height - imgSize.height
                };

            raptorImageMarkup.setStyle({
                'position': 'fixed',
                'opacity': 0,
                'top': viewport.height + 'px',
                'left': raptorPosition.width + 'px',
                'zIndex': '10001',
                'display': 'none'
            });
            document.body.insert(raptorImageMarkup);

            function go() {
                self.lpInputElement.value = ''; // clear input field where the hilarious ;-) text appears
                self._lockedRaptor = true;

                if (sound) {
                    document.getElementById('elRaptorShriek').play();
                }

                var raptor = $('elRaptor').setStyle({
                    "display": "block"
                }), ep1, ep2;

                function removeRaptor() {
                    raptor.remove();
                    $('elRaptorShriek').remove();
                }

                ep1 = new Effect.Parallel([
                    new Effect.Opacity(raptor, { sync: true, from: 0, to: 1 }),
                    new Effect.Move(raptor, {
                        sync: true,
                        x: raptorPosition.width,
                        y: raptorPosition.height,
                        mode: 'absolute',
                        transition: Effect.Transitions.spring
                    })
                ], {duration: 1});

                ep2 = new Effect.Move(raptor, {
                    xsync: true,
                    x: -1 * imgSize.width,
                    y: viewport.height - 100,
                    mode: 'absolute',
                    transition: Effect.Transitions.spring,
                    duration: 15,
                    delay: 1.1
                });
                removeRaptor.delay(5);
            }

            //Determine Entrance
            if (enterOn === 'time' && this._lockedRaptor === false) {
                setTimeout(go, delayTime);
            }

        } // end func raptorize
    };

    /**
     * event on click at textarea.initMarkdown
     * @param event
     * @private
     */
    function _onClickBuildTabsFactory(event) {
        var target = event.target || event.srcElement,
            $mdTextArea = {},
            mageButtons = [],
            $parentTd = target.parentNode,
            $mageButtons = $('buttons' + target.id),
            iFrameJs = null,
            tabPreview = new TabPreviewHandler();

        if (target.readAttribute('data-tabsBuilt')) {
            return;
        }

        if ($mageButtons) {
            mageButtons = $mageButtons.select('button');
            mageButtons.each(function (buttonElement) {
                $(target.id + '__writeB').insert({
                    top: buttonElement
                });
            });
        }

        $mdTextArea = $parentTd.select('.mdTextArea')[0];
        $mdTextArea.insert(target);

        $parentTd.select('.mdTabContainer')[0].show();
        target.writeAttribute('data-tabsBuilt', 1);

        if (false === _isFileReaderEnabled()) {
            $parentTd.select('.md-filereader-text')[0].remove();
        }

        iFrameJs = $(target.id + '__iFrameJS'); // in category edit the security of prototype strips out the script template
        if (iFrameJs) { // so this is null in category edit
            tempIframeJSSource = iFrameJs.innerHTML.replace('~~origin~~', document.location.origin);

            //        window.addEventListener("message", receiveMessage, false);
            Event.observe(window, 'message', function (event) {
                var data = event.data.split('=');
                iFrameScrollPositions[data[0]] = ~~data[1]; // convert to int via ~~
            });
        }
        /**
         * creating clickable tabs
         */
        $$('.mdTabs ul li a').each(function (aElement) {
            aElement.observe('click', function (event) {
                Event.stop(event);
                var grandParentsNode = this.parentNode.parentNode,
                    current = grandParentsNode.getAttribute('data-current'),
                    idSplit = this.id.split('__'),
                    taId = idSplit[0],
                    ident = idSplit[idSplit.length - 1],
                    $tabBody = document.getElementById(taId + '__' + ident + 'B');

                // hide
                document.getElementById(taId + '__' + current).removeClassName('active'); // header
                document.getElementById(taId + '__' + current + 'B').removeClassName('active'); // page

                // show
                this.setAttribute('class', 'active'); // header

                $tabBody.addClassName('active'); // page
                grandParentsNode.setAttribute('data-current', ident);

                if (typeof tabPreview[ident] === 'function') {
                    tabPreview.setData({
                        tabBody: $tabBody,
                        textAreaId: taId
                    });
                    tabPreview[ident]();
                }
            });
        });
    } // end _onClickBuildTabsFactory

    /*************************************************************************************************************
     * loads the filereader, epiceditor
     */
    function _mdInitialize() {
        _initGlobalConfig();

        var parentElementIds = ['product_edit_form', 'edit_form', 'category-edit-container', 'email_template_edit_form'];

        if (varienGlobalEvents) {
            varienGlobalEvents.fireEvent('mdLoadForms', parentElementIds);
        }

        //  loading multiple instances on one page
        // only works with event delegation due category edit page ... and the varientabs js class ...
        // fire event for customization varienGlobalEvents.attachEventHandler('showTab', function (e) {...}
        parentElementIds.forEach(function (elementId) {
            var $elementId = $(elementId);
            if ($elementId) {

                $elementId.on('click', 'textarea.initMarkdown', _onClickBuildTabsFactory);

                // some things are only possible with event delegation ...
                if (true === _isEpicEditorEnabled() && true === _markDownGlobalConfig.eeLoadOnClick) {
                    $elementId.on('click', 'textarea.initEpicEditor', _createEpicEditorInstances);
                }
                if (true === _isFileReaderEnabled()) {
                    $elementId.on('click', 'textarea.initMarkdown', _createFileReaderFactory);
                }
            }
        });

        if (_markDownGlobalConfig.isHiddenInsertImageButton === true) {
            $$('button.add-image').each(function (element) {
                element.remove();
            });
        }
    }

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