<?php

/**
 * User defined box configuration of the "vector" DokuWiki template
 *
 * If you want to add/remove some boxes, have a look at the comments/examples
 * and the DocBlock of {@link _vector_renderBoxes()}, main.php
 *
 * To change the non-box related config, use the admin webinterface of DokuWiki.
 *
 *
 * LICENSE: This file is open source software (OSS) and may be copied under
 *          certain conditions. See COPYING file for details or try to contact
 *          the author(s) of this file in doubt.
 *
 * @license GPLv2 (http://www.gnu.org/licenses/gpl2.html)
 * @author Andreas Haerter <andreas.haerter@dev.mail-node.com>
 * @link http://andreas-haerter.com/projects/dokuwiki-template-vector
 * @link http://www.dokuwiki.org/template:vector
 * @link http://www.dokuwiki.org/devel:configuration
 */


//check if we are running within the DokuWiki environment
if (!defined("DOKU_INC")){
    die();
}


//note: The boxes will be rendered in the order they were defined. Means:
//      first box will be rendered first, last box will be rendered at last.

//hide boxes for anonymous clients (closed wiki)?
if (empty($conf["useacl"]) || //are there any users?
    $loginname !== "" || //user is logged in?
    !tpl_getConf("vector_closedwiki")){

	//Languages/translations provided via Andreas Gohr's translation plugin,
	//see <http://www.dokuwiki.org/plugin:translation>
	if (file_exists(DOKU_PLUGIN."translation/syntax.php") &&
		!plugin_isdisabled("translation")){
		$translation = &plugin_load("syntax", "translation");
		$_vector_boxes["p-lang"]["headline"] = $lang["vector_translations"];
		$_vector_boxes["p-lang"]["xhtml"]    = $translation->_showTranslations();
	}


	//examples: remove comments to see what is happening
	/*
	$_vector_boxes["example1"]["headline"] = "Hello World!";
	$_vector_boxes["example1"]["xhtml"] = "DokuWiki with vector... <em>rules</em>!";
	*/


	/*
	//QR-Code of the current page (powered by <http://QR-Server.com/api/>)
	$_vector_boxes["qrcode"]["headline"] = "QR-Code";
	$_vector_boxes["qrcode"]["xhtml"] = '<img src="http://api.qrserver.com/v1/create-qr-code/?data='.urlencode(wl(cleanID(getId()), false, true, "&")).'&amp;size=135x135" style="margin:0.5em 0 0.3em -0.2em;" alt="QR-Code: '.wl(cleanID(getId()), false, true).'" title="QR-Code: '.wl(cleanID(getId()), false, true).'" /><p style="font-size:6px !important;margin:0;padding:0;color:#aaa;"><a href="http://goqr.me/" style="color:#aaa;">QR Code</a> by <a href="http://qrserver.com/" style="color:#aaa;">QR-Server</a></p>';
	*/


	/*
	$_vector_boxes["example2"]["headline"] = "Some links";
	$_vector_boxes["example2"]["xhtml"] =  "<ul>\n"
		                                  ."  <li><a href=\"".wl(cleanID(getId()), array("do" => "backlink"))."\" rel=\"nofollow\">".hsc($lang["vector_toolbxdef_whatlinkshere"])."</a></li>\n" //we might use tpl_actionlink("backlink", "", "", hsc($lang["vector_toolbxdef_whatlinkshere"]), true), but it would be the only toolbox link where this is possible... therefor I don't use it to be consistent
		                                  ."  <li><a href=\"http://www.example.com\">Example link</a></li>\n"
		                                  ."  <li><a href=\"".wl(cleanID(getId()), array("rev" => 0, "vecdo" => "cite"))."\" rel=\"nofollow\">Cite newest version</a></li>\n"
		                                  ."</ul>";
	*/


	/*
	$_vector_boxes["example3"]["headline"] = "Buttons";
	$_vector_boxes["example3"]["xhtml"] = "<a href=\"http://andreas-haerter.com/donate/vector/\" title=\"Donate\" target=\"_blank\"><img src=\"".DOKU_TPL."static/img/button-donate.gif\" width=\"80\" height=\"15\" alt=\"Donate\" border=\"0\" /></a>";
	*/


	/*
	//include the content of another wiki page (you have to create it first, for
	//sure. In this example, the page "wiki:your_page_here" is used)
	$_vector_boxes["example4"]["headline"] = "wiki:your_page_here";
	$_vector_boxes["example4"]["xhtml"] = tpl_include_page("wiki:your_page_here", false);
	*/

	/*
	//navigation namespace
	if (tpl_getConf("vector_navigation")){
	*/

	/*
	//headline
	$_vector_boxes["p-nsnavigation"]["headline"] = "Namespace navigatie";

	//content
	$_vector_boxes["p-nsnavigation"]["xhtml"] = getNS(cleanID(getID())).":sidebar";
	*/

	/*
	//relative URL
	echo wl(cleanID(getID()));
	echo getNS(cleanID(getID()));
	//absolute URL
	echo wl(cleanID(getID()), "", true);
	*/

    /**
     * ====Toolbox=====
     * kopie van conf/boxes.php;
     * in de conf zijn de toolbox en export box uitgeschakeld, dit schakelt code in conf/boxes.php uit. Deze checks zijn hieronder verwijderd.
     * de id's van een box moeten uniek zijn, dus onderstaande box kan niet tegelijkertijd worden weergeven met de oorspronkelijke box.
     *
     * toelichting id's:
     * p-tb = toolbox
     */

    //headline
    $_vector_boxes["p-tb"]["headline"] = $lang["vector_toolbox"];

    //content:
	//backlinks
    $_vector_boxes["p-tb"]["xhtml"] = "      <ul>\n";
    if (actionOK("backlink")){ //check if action is disabled
        $_vector_boxes["p-tb"]["xhtml"] .= "        <li id=\"t-whatlinkshere\"><a href=\"".wl(cleanID(getId()), array("do" => "backlink"))."\">".hsc($lang["vector_toolbxdef_whatlinkshere"])."</a></li>\n";
	//we might use tpl_actionlink("backlink", "", "", hsc($lang["vector_toolbxdef_whatlinkshere"]), true),
	//but it would be the only toolbox link where this is possible... therefore I don't use it to be consistent
    }

//	//recent changes   -> header sitetools
//    if (actionOK("recent")){ //check if action is disabled
//        $_vector_boxes["p-tb"]["xhtml"] .= "        <li id=\"t-recentchanges\"><a href=\"".wl(cleanID(getId()), array("do" => "recent"))."\" rel=\"nofollow\">".hsc($lang["btn_recent"])."</a></li>\n";
//		//language comes from DokuWiki core
//    }
//
//	//upload     -> header sitetools
//    if (actionOK("media")){ //check if action is disabled
//		$_vector_boxes["p-tb"]["xhtml"] .= "        <li id=\"t-upload\"><a href=\"".wl(cleanID(getId()), array("do" => "media","ns" => getNS(cleanID(getID()))))."\" rel=\"nofollow\">".hsc($lang["vector_toolbxdef_upload"])."</a></li>\n"; //language comes from DokuWiki core
//    }
//
//	//index     -> header sitetools
//    if (actionOK("index")){ //check if action is disabled
//        $_vector_boxes["p-tb"]["xhtml"] .= "    <li id=\"t-special\"><a href=\"".wl(cleanID(getId()), array("do" => "index"))."\" rel=\"nofollow\">".hsc($lang["vector_toolbxdef_siteindex"])."</a></li>\n";
//    }

	//permanent link to page
    //$_vector_boxes["p-tb"]["xhtml"] .=  "        <li id=\"t-permanent\"><a href=\"".wl(cleanID(getId()), array("rev" =>(int)$rev))."\" rel=\"nofollow\">".hsc($lang["vector_toolboxdef_permanent"])."</a></li>\n"

	//citeer mogelijkheden
    //                                   ."        <li id=\"t-cite\"><a href=\"".wl(cleanID(getId()), array("rev" =>(int)$rev, "vecdo" => "cite"))."\" rel=\"nofollow\">".hsc($lang["vector_toolboxdef_cite"])."</a></li>\n";

	//ODT plugin
    //see <http://www.dokuwiki.org/plugin:odt> for info
    if (file_exists(DOKU_PLUGIN."odt/syntax.php") &&
        !plugin_isdisabled("odt")){
        $_vector_boxes["p-tb"]["xhtml"]  .= "        <li id=\"coll-download-as-odt\"><a href=\"".wl(cleanID(getId()), array("do" => "export_odt"))."\" rel=\"nofollow\">".hsc($lang["vector_exportbxdef_downloadodt"])."</a></li>\n";
    }
    //dw2pdf plugin
    //see <http://www.dokuwiki.org/plugin:dw2pdf> for info
    if (file_exists(DOKU_PLUGIN."dw2pdf/action.php") &&
        !plugin_isdisabled("dw2pdf")){
        $_vector_boxes["p-tb"]["xhtml"]  .= "        <li id=\"coll-download-as-rl\"><a href=\"".wl(cleanID(getId()), array("do" => "export_pdf"))."\" rel=\"nofollow\">".hsc($lang["vector_exportbxdef_downloadpdf"])."</a></li>\n";
    //html2pdf plugin
    //see <http://www.dokuwiki.org/plugin:html2pdf> for info
    } else if (file_exists(DOKU_PLUGIN."html2pdf/action.php") &&
               !plugin_isdisabled("html2pdf")){
        $_vector_boxes["p-tb"]["xhtml"]  .= "        <li id=\"coll-download-as-rl\"><a href=\"".wl(cleanID(getId()), array("do" => "export_pdf"))."\" rel=\"nofollow\">".hsc($lang["vector_exportbxdef_downloadpdf"])."</a></li>\n";
    }
    $_vector_boxes["p-tb"]["xhtml"] .=  "        <li id=\"t-print\"><a href=\"".wl(cleanID(getId()), array("rev" =>(int)$rev, "vecdo" => "print"))."\" rel=\"nofollow\">".hsc($lang["vector_exportbxdef_print"])."</a></li>\n"
                                                      ."      </ul>";


	if (empty($conf["useacl"]) ||
		auth_quickaclcheck(cleanID(tpl_getConf("vector_navigation_location"))) >= AUTH_READ){ //current user got access?
		//get the rendered content of the defined wiki article to use as custom navigation
		$interim = tpl_include_page(getNS(cleanID(getID())).":sidebar", false);
		if ($interim === "" ||
		    $interim === false){
		    //creation/edit link if the defined page got no content
		    $_vector_boxes["p-nsnavigation"]["xhtml"] = html_wikilink(getNS(cleanID(getID())).":sidebar",".");
		}else{
		    //headline
			$_vector_boxes["p-nsnavigation"]["headline"] = "Namespace navigatie";
			//the rendered page content
		    $_vector_boxes["p-nsnavigation"]["xhtml"] = $interim;
		}
	}
}

