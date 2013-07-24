Magento Markdown Module
=======================

![image](https://raw.github.com/SchumacherFM/Magento-Markdown/master/logo/magento-markdown.png)

Markdown is a text-to-HTML conversion tool for web writers. Markdown
allows you to write using an easy-to-read, easy-to-write plain text
format, then convert it to structurally valid XHTML (or HTML).

Full documentation of Markdown's syntax is available on John's
Markdown page: <http://daringfireball.net/projects/markdown/>

This module renders all CMS pages and every block which extends Mage_Core_Block_Abstract.

Rendering of catalog description fields have to be implemented in the phtml files by yourself.

Preview in the backend. No live preview available maybe later.

Bugs
----

Won't parse Magento own variables like

```
{{config path="trans_email/ident_general/email"}}
```

Why do I need this?
-------------------

Because you want to get rid of the TinyMCE and force your customer to use easy and limited syntax.

Developer Usage
---------------

Anywhere in a .phtml file you can access the renderer via:

```
<?php echo Mage::helper('markdown')->render($_description); ?>
```

Catalog product and category description fields have already enabled the markdown feature in the backend.

CMS pages and nearly every blocks will be rendered automatically ... but only if no html tag is detected.

Configuration
-------------

None!

Todo
----

 * Better usability in the backend
 * Tiny markdown documentation
 * Live edit

Installation Instructions
-------------------------
1. Install modman from https://github.com/colinmollenhour/modman
2. Switch to Magento root folder
3. `modman init`
4. `modman clone git://github.com/SchumacherFM/Magento-Markdown.git`

About
-----
- Key: SchumacherFM_Markdown
- Current Version 1.0.0
- [Download tarball](https://github.com/SchumacherFM/Magento-Markdown/tags)

Compatibility
-------------
- Magento >= 1.4
- php >= 5.2.0

Support / Contribution
----------------------

Report a bug using the issue tracker or send us a pull request.

Licence
-------

Magento Markdown Implementation
Copyright (c) 2013 Cyrill (at) Schumacher dot fm
All rights reserved.

PHP Markdown Lib
Copyright (c) 2004-2013 Michel Fortin
<http://michelf.ca/>
All rights reserved.

Based on Markdown
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

Made in Sydney, Australia :-)
