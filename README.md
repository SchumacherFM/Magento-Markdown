Magento: Mage Markdown Module [M↓]
==================================

![image](https://raw.github.com/SchumacherFM/Magento-Markdown/master/logo/magento-markdown.png)

Markdown is a text-to-HTML conversion tool for web writers. Markdown
allows you to write using an easy-to-read, easy-to-write plain text
format, then convert it to structurally valid XHTML (or HTML).

Full documentation of Markdown's syntax is available on John's
Markdown page: <http://daringfireball.net/projects/markdown/>

Full support of Markdown Extra: <http://michelf.ca/projects/php-markdown/extra/>

This module renders all CMS pages and all CMS blocks (Mage_Cms_Block_Block and Mage_Cms_Block_Widget_Block).

Renders every transactional email as Markdown (or MD Extra) when the email templates includes a special tag.

Rendering of catalog description fields have to be implemented in the phtml files by yourself.

Integrates the awesome [EpicEditor](http://epiceditor.com): split fullscreen editing,
live previewing, automatic draft saving and offline support.

Bugs
----

#### CSS

CSS is included in the transactional emails in their style tag. Maybe some mail providers removes that
or cannot render it. So maybe there has to be some transformation that the CSS will be added
into each html tag attribute: style.

```
	<h1 style="font-size..."></h1>
```

Why do I need this?
-------------------

Because you want to get rid of the TinyMCE and force your customer to use easy and limited syntax.

You can edit your text with external editors:

#### Mac OS X

- [Mou The missing Markdown editor for web developers](http://mouapp.com/)

#### All platforms

- PhpStorm
- Sublime Text

#### Windows

- [MarkdownPad is a full-featured Markdown editor for Windows](http://markdownpad.com/)

### Mashable: [78 Tools for Writing and Previewing Markdown](http://mashable.com/2013/06/24/markdown-tools/)

Developer Usage
---------------

Anywhere in a .phtml file you can access the renderer via:

```
<?php echo Mage::helper('markdown')->render($_description, [array $options] ); ?>
```

Catalog product and category description fields have already enabled the markdown feature in the backend.

CMS pages (instance of Mage_Cms_Model_Page) and blocks (instance of Mage_Cms_Block_Block and
Mage_Cms_Block_Widget_Block) will be rendered automatically ... but only if the detection tag is present.

Magento Widgets and Variables will be automatically preserved:

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
));
echo $instance->renderMarkdown('text goes here');
```

Configuration
-------------

- Enable or disable Markdown parser per store view
- Enable or disable Markdown extra parser per store view
- Set Markdown detection tag per store view
- Add path to css file if using in transactional emails per store view

Every field which contains Markdown syntax must contain that detection tag otherwise it will not be parsed.

Demo Content: [http://daringfireball.net/projects/markdown/syntax.text](http://daringfireball.net/projects/markdown/syntax.text)

Todo
----

 * Better usability in the backend

Installation Instructions
-------------------------
1. Install modman from https://github.com/colinmollenhour/modman
2. Switch to Magento root folder
3. `modman init`
4. `modman clone git://github.com/SchumacherFM/Magento-Markdown.git`

Composer …


About
-----
- Key: SchumacherFM_Markdown
- Current Version: see History section
- [Download tarball](https://github.com/SchumacherFM/Magento-Markdown/tags)

History
-------

#### 1.5.0

- Remove live preview
- Remove library marked.js and markdown.css
- Remove support for <= IE8
- Add [EpicEditor](http://epiceditor.com) with built in marked.js, split fullscreen editing,
    live previewing, automatic draft saving and offline support.
    Down side: when inserting Magento widgets, images or variables you have to turn of the editor insert that item
    and then turn it on. (Missing bi-directional synchronization between textarea and editor)

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

I am using that model: [A successful Git branching model](http://nvie.com/posts/a-successful-git-branching-model/)

Licence
-------

#### Magento Markdown Implementation

Copyright (c) 2013 Cyrill (at) Schumacher dot fm

All rights reserved.

#### PHP Markdown Lib

Copyright (c) 2004-2013 Michel Fortin

<http://michelf.ca/> <https://github.com/michelf/php-markdown/>

All rights reserved.

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
