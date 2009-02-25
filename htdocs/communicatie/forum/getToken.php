<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# getToken.php
# -------------------------------------------------------------------
# Geef een persoonlijke rss-feed-link.
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');
?>
<h1>RSS-feed zonder cookie</h1>
Het is met de onderstaande link mogelijk het RSS-feed van het forum op <a href="http://csrdelft.nl">csrdelft.nl</a> te bekijken zonder in te loggen met een cookie. Dat houdt dus in dat <em>iedereen</em> die deze link heeft de hele stylesheet kan zien zoals u die ook ziet.<br />
<pre>
<?php
echo CSR_SERVER.'/communicatie/forum/rss/'.$lid->getToken().'.xml';
?>
</pre>
<br />
<br />
Elke keer dat deze pagina opgevraagd wordt wordt een nieuwe code gegenereerd en is de oude niet meer bruikbaar.
