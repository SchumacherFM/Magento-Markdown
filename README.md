Magento: Mage Markdown Module [M↓]
==================================

![image](https://raw.github.com/SchumacherFM/Magento-Markdown/master/logo/magento-markdown.png)

Markdown is a text-to-HTML conversion tool for web writers. Markdown
allows you to write using an easy-to-read, easy-to-write plain text
format, then convert it to structurally valid XHTML (or HTML).

- Full support of [Markdown Extra](http://michelf.ca/projects/php-markdown/extra/)
- Renders all CMS pages and all CMS blocks (Mage_Cms_Block_Block and Mage_Cms_Block_Widget_Block)
- Renders every transactional email as Markdown (or MD Extra)
- Rendering of catalog product and category short and long description fields have to be implemented in the phtml files by yourself.
- Integrates the awesome [EpicEditor](http://epiceditor.com): split fullscreen editing, live previewing, automatic draft saving and offline support.
- Drag'n'Drop of images supported in textarea fields. [Automatic image uploading](https://developer.mozilla.org/en-US/docs/Web/API/FileReader) integrated (>=
 IE10, Safari 6.0.2, FX3.6, Chrome 7, Opera 12.02)
- Converting of HTML into Markdown. Client side via JavaScript.

Full documentation of Markdown's syntax is available on [John's Markdown page](http://daringfireball.net/projects/markdown/)

Why do I need this?
-------------------

Because you want to get rid of the TinyMCE and force your customer to use easy and limited syntax.

You can edit your markdown text with external editors:

#### Mac OS X

- [Mou The missing Markdown editor for web developers](http://mouapp.com/)

#### All platforms

- PhpStorm
- Sublime Text
- Cloud based: [StackEdit](http://benweet.github.io/stackedit/)

#### Windows

- [MarkdownPad is a full-featured Markdown editor for Windows](http://markdownpad.com/)

### Mashable: [78 Tools for Writing and Previewing Markdown](http://mashable.com/2013/06/24/markdown-tools/)


Configuration
-------------

All options can be set per store view.

- Enable or disable Markdown parser
- Enable or disable Markdown extra parser
- Set Markdown detection tag
- Add path to css file if using in transactional emails
- Enable or disable Markdown EpicEditor
- Enable or disable loading of the EpicEditor via click in a textarea field
- Full configuration for Markdown EpicEditor - add a JSON object in the System -> Configuration section
- Defining a custom upload folder for Drag'n'Drop image upload. This folder will be created automatically and recursively
- Enable or disable HTML to Markdown converter reMarked.js
- Full configuration for converter reMarked.js - add a JSON object in the System -> Configuration section
- Integrate Markdown into your own module by adding the layout handle into the System -> Configuration section

Every field which contains Markdown syntax must contain that detection tag otherwise it will not be parsed.

File upload via Drag'n'Drop works only if you click on the textarea field once. During drag mode a green border will show that file upload
via Drag'n'Drop is available. If you do not see that border during a drag then there will be no file upload.

Demo Content: [http://daringfireball.net/projects/markdown/syntax.text](http://daringfireball.net/projects/markdown/syntax.text)

Bugs
----

#### CSS in transactional emails

CSS is included in the transactional emails in their style tag. Maybe some mail providers removes that style tag
or cannot render it. So maybe there has to be some transformation that the CSS will be added
into each html tag attribute: style.

```
	<h1 style="font-size..."></h1>
```

Developer Usage
---------------

Anywhere in a .phtml file you can access the renderer via:

```
<?php echo Mage::helper('markdown')->render($_description, [array $options] ); ?>
```

Catalog product and category description fields have already enabled the markdown feature in the backend. For the frontend
you have to implement the above mentioned code.

CMS pages (instance of Mage_Cms_Model_Page) and blocks (instance of Mage_Cms_Block_Block and
Mage_Cms_Block_Widget_Block) will be rendered automatically but only if the detection tag is present.

Magento Widgets and Variables will be automatically preserved and correctly rendered:

```
{{(widget|config|media|...) ... }}
```

#### Configuring the Markdown parser for custom usage

```
$instance = Mage::getModel('markdown/markdown_render');
$renderer = $instance=>getRenderer();
$renderer->empty_element_suffix = '>';
$renderer->tab_width = 5;
$instance->setOptions(array(
    'force'          => FALSE, // force rendering even if not markdown
    'protectMagento' => TRUE, // protect Magento widgets/variables ...
    'extra'          => FALSE, // force rendering of markdown extra if true
));
echo $instance->renderMarkdown('text goes here');
```

#### Tips for parsing transactional emails with Markdown Extra

- Remove body tags
- Use ```<div markdown="1">``` including markdown=1 in other tags works not always properly

#### How to integrate markdown into my module?

...

Todo
----

- For version 3.0 replace the EpicEditor with StackEdit
- For version 4.0 parallel support for Magento2
- For version 5.0 drop Magento1 support

Installation Instructions
-------------------------
1. Install modman from https://github.com/colinmollenhour/modman
2. Switch to Magento root folder
3. `modman init`
4. `modman clone git://github.com/SchumacherFM/Magento-Markdown.git`

Please read the great article from Vinai: [Composer installation](http://magebase.com/magento-tutorials/composer-with-magento/)

About
-----

- Key: SchumacherFM_Markdown
- Current Version: 2.0.2
- [Download tarball](https://github.com/SchumacherFM/Magento-Markdown/tags)

History
-------

#### 2.0.2

- Minor bug fix. Github Issue #17 (wrong module name) and #13 (reMarked.js fixed empty thead)

#### 2.0.1

- Minor bug fix. Github Issue #14

#### 2.0.0

- Major changes
- Remove support for <= IE8
- Add [EpicEditor](http://epiceditor.com) with built in marked.js, split fullscreen editing,
    live previewing, automatic draft saving and offline support.
    Down side: when inserting Magento widgets, images or variables you have to turn of the editor to insert that item
    and then turn it on. (Missing bi-directional synchronization between textarea and editor)
- Preview of HTML source code possible even if EpicEditor is not loaded or disabled.
- If EpicEditor is unloaded then dropping image files with direct upload is possible [HTML5 FileReader](http://bgrins.github.io/filereader.js/).
    Due the contenteditable mode in EpicEditor the FileReader cannot be implemented, only in a textarea field ...
- Add reMarked.js to convert HTML into Markdown. reMarked.js is fully configurable via a JSON object.

#### 1.4.2

- Magento Connect
- Compatibility Magento >= 1.5

#### 1.4.1

- Fix readme
- Magento Connect

#### 1.4.0

- Update readme
- Add modified markdown logo from [https://github.com/dcurtis/markdown-mark](https://github.com/dcurtis/markdown-mark)
- Tiny rename to Mage Markdown due to Magento Connect guidelines
- Live preview in CMS Editor fields (not for Markdown Extra)
- Email Templates can have Markdown Extra mode while the default config is "normal" Markdown mode
- Implementing your own renderer must implement SchumacherFM_Markdown_Model_Markdown_Interface

#### 1.3.0

- Backend preview for Markdown Extra via ajax loading

#### 1.2.0

- Use Markdown in transactional emails
- Bug fixes

#### 1.1.0

- Update Markdown parser
- Implemented Markdown extra

#### 1.0.0

- Initial Release


Compatibility
-------------

- Magento >= 1.5
- php >= 5.2.0

There is the possibility that this extension may work with pre-1.5 Magento versions.

Support / Contribution
----------------------

Report a bug using the issue tracker or send us a pull request.

Instead of forking I can add you as a Collaborator IF you really intend to develop on this module. Just ask :-)

We work with: [A successful Git branching model](http://nvie.com/posts/a-successful-git-branching-model/) and [Semantic Versioning 2.0.0](http://semver.org/)

Licence BSD-3-Clause
--------------------

#### Magento Markdown Implementation

Copyright (c) 2013 Cyrill (at) Schumacher dot fm

All rights reserved.

#### PHP Markdown Lib

- Copyright (c) 2004-2013 Michel Fortin
- [http://michelf.ca](http://michelf.ca)
- [https://github.com/michelf/php-markdown/](https://github.com/michelf/php-markdown/)

#### reMarked.js

- Copyright (c) 2013 Leon Sorokin / leeoniya
- [https://github.com/leeoniya/reMarked.js](https://github.com/leeoniya/reMarked.js)

#### marked.js

- Copyright (c) 2011-2013, Christopher Jeffrey. (MIT Licensed)
- [https://github.com/chjj/marked](https://github.com/chjj/marked)

#### highlight.js

- Copyright (c) 2006, Ivan Sagalaev
- [https://github.com/isagalaev/highlight.js](https://github.com/isagalaev/highlight.js)

#### Markdown Styles

- [http://mixu.net/markdown-styles/](http://mixu.net/markdown-styles/)

#### beautify-html

- Copyright (c) 2007-2013 Einar Lielmanis and contributors.
- [https://github.com/einars/js-beautify/blob/master/js/lib/beautify-html.js](https://github.com/einars/js-beautify/blob/master/js/lib/beautify-html.js)

#### EpicEditor

- Copyright (c) 2011-2013, Oscar Godson (http://oscargodson.com)
- [https://github.com/OscarGodson/EpicEditor](https://github.com/OscarGodson/EpicEditor)

####  Based on Markdown

Copyright (c) 2003-2005 John Gruber

<http://daringfireball.net/>

All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

*   Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.

*   Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the
    distribution.

*   Neither the name "Markdown" nor the names of its contributors may
    be used to endorse or promote products derived from this software
    without specific prior written permission.

This software is provided by the copyright holders and contributors "as
is" and any express or implied warranties, including, but not limited
to, the implied warranties of merchantability and fitness for a
particular purpose are disclaimed. In no event shall the copyright owner
or contributors be liable for any direct, indirect, incidental, special,
exemplary, or consequential damages (including, but not limited to,
procurement of substitute goods or services; loss of use, data, or
profits; or business interruption) however caused and on any theory of
liability, whether in contract, strict liability, or tort (including
negligence or otherwise) arising in any way out of the use of this
software, even if advised of the possibility of such damage.

Backend preview rendering via:

 * marked - a markdown parser
 * Copyright (c) 2011-2013, Christopher Jeffrey. (MIT Licensed)
 * https://github.com/chjj/marked


Author
------

[Cyrill Schumacher](https://github.com/SchumacherFM)

[My pgp public key](http://www.schumacher.fm/cyrill.asc)

Made in Sydney, Australia :-)
