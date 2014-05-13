#TemplatePagename Plugin for DokuWiki

Let you configure self the wikipage which is used as template for the new 
created pages in the namespace.

Some configuration examples
 * the plugin defaults are: `c_template` and `i_template` (editable by who has write permission)
 * or DokuWiki defaults are: `_template` and `__template` (only editable via filesystem)
 * or use simple `template` 
 * it is now up to your choice.

Be aware, when the template name starts with characters like `_` it is not 
editable online in the wiki, only by server admins via the file system.
When you follow the [page name conventions](https://www.dokuwiki.org/pagename) people who has write permission on it
may modify the page.


All documentation for this plugin can be found at
https://www.dokuwiki.org/plugin:templatepagename

If you install this plugin manually, make sure it is installed in
`lib/plugins/templatepagename/` - if the folder is called different it
will not work!

Please refer to https://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----
Copyright (C) Martin <martin@sound4.biz>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the COPYING file in your DokuWiki folder for details
