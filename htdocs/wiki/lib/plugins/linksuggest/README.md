plugin-linksuggest
==================

Dokuwiki Plugin

Example:

If following Namespaces are available
- `ns:ns1:page1`
- `ns:page`
- `page0`

and user is on page `ns:page`

Possible links to `ns:ns1:page1`

- `[[ns:ns1:page1]]` (absolute)  //not supported by  this plugin
- `[[:ns:ns1:page1]]` (explizite absolute)
- `[[ns1:page1]` (relative)
- `[[.:ns1:page1]]` (explizite relative)
- `[[..:ns:ns1:page1]]` (relative, with backlink)

