//\/////
//\  overLIB W3C Class Plugin
//\
//\  You may not remove or change this notice.
//\  Copyright Erik Bosrup 1998-2003. All rights reserved.
//\  Contributors are listed on the homepage.
//\  See http://www.bosrup.com/web/overlib/ for details.
//\/////
////////
// PRE-INIT
// Ignore these lines, configuration is below.
////////
if (typeof olInfo == 'undefined' || typeof olInfo.meets == 'undefined' || !olInfo.meets(4.14)) alert('overLIB 4.14 or later is required for the W3C Class Plugin.');
else {
registerCommands('cssw3c,divclass,bodyclass,captionclass,closeclass,usedivcls,usestd');
////////
// DEFAULT CONFIGURATION
// Settings you want everywhere are set here. All of this can also be
// changed on your html page or through an overLIB call.
////////
if (typeof ol_divclass=='undefined') var ol_divclass="overlay";
if (typeof ol_bodyclass=='undefined') var ol_bodyclass="overlayBody";
if (typeof ol_captionclass=='undefined') var ol_captionclass="";
if (typeof ol_closeclass=='undefined') var ol_closeclass="";
if (typeof ol_usedivcls=='undefined') var ol_usedivcls=0;
if (typeof ol_usestd=='undefined') var ol_usestd=1;
////////
// END OF CONFIGURATION
// Don't change anything below this line, all configuration is above.
////////
////////
// INIT
////////
// Runtime variables init. Don't change for config!
var o3_divclass="";
var o3_bodyclass="";
var o3_captionclass="";
var o3_closeclass="";
var o3_usedivcls=0;
var o3_usestd=1;
var olStdOn=false,olClass='';
////////
// PLUGIN FUNCTIONS
////////
function setCSSW3cVariables() {
	o3_divclass=ol_divclass;
	o3_bodyclass=ol_bodyclass;
	o3_captionclass=ol_captionclass;
	o3_closeclass=ol_closeclass;
	o3_usedivcls=ol_usedivcls;
	o3_usestd=ol_usestd;
}
// Parses W3C Class commands
function parseCSSW3cExtras(pf,i,ar) {
	var k=i;
	if (k < ar.length) {
		if (ar[k]==CSSW3C) {eval(pf+'css=(olNs4 ? CSSOFF : ar[k])'); return k; }
		if (ar[k]==DIVCLASS) { eval(pf+'divclass="'+ar[++k]+'"'); return k; }
		if (ar[k]==BODYCLASS) { eval(pf+'bodyclass="'+ ar[++k]+'"'); return k; }
		if (ar[k]==CAPTIONCLASS) { eval(pf+'captionclass="'+ ar[++k]+'"'); return k; }
		if (ar[k]==CLOSECLASS) { eval(pf+'closeclass="'+ ar[++k]+'"'); return k; }
		if (ar[k]==USEDIVCLS) { eval(pf +'usedivcls=('+pf+'usedivcls==0) ? 1 : 0'); return k; }
		if (ar[k]==USESTD) { eval(pf +'usestd=('+pf+'usestd==1) ? 0 : 1'); return k; }
	}
	return -1;
}
//////
// SUPPORT FUNCTIONS FOR W3C STYLED POPUPS
//////
function addOvDivCSS() {
	if (olNs4) return;
	var doClass=(o3_css==CSSW3C);
	if (doClass) {
		if (!o3_divclass&&o3_usestd) setStyle(1);
		else setStyle(0);
		over.className=olClass;
	} else {
		setStyle(0);
		if (o3_usedivcls&&o3_divclass) over.className=o3_divclass;
		else over.className='';
	}
}
// Sets/removes styling
function setStyle(On) {
	var curStyle,regA=/border[-a-z:(),#0-9\s*]+;/g,regB=/background[-a-z:(),#0-9\s*]+;/g;
	var sheet,theRule,l,le,theClass='Olstdclass';
	theRule='border: '+o3_border+'px solid '+o3_bgcolor+'; background-color: '+o3_fgcolor+'; padding: 2px;';
	if (olIe4&&!olOp) {
		if (!olStdOn) {
			olStdOn=true;
			sheet=o3_frame.document.styleSheets[0];
			sheet.addRule('.'+theClass,theRule);
		}
		olClass=(On ? theClass : o3_divclass);
	} else {
		if (!(On&&olStdOn)) {
			curStyle=over.getAttribute("style").toLowerCase();
			curStyle=curStyle.replace(/^[ ]+/,'').replace(/[ ]+$/,'');
			if (curStyle.charAt(curStyle.length-1)!=';') curStyle += ';'
			if (olStdOn) {
				curStyle=curStyle.replace(regA,'');
				curStyle=curStyle.replace(/[ ]+/g,' ');
				curStyle=curStyle.replace(regB,'');
				olStdOn=false;
			}
			if (On) {olStdOn=true; curStyle=curStyle+' '+theRule; }
			over.setAttribute("style",curStyle);
		}
		if (On) olClass='';
		else {
			cleanUpStyle();
			olClass=o3_divclass;
		}
	}
}
// function removes any padding setting applied to the Div container
function cleanUpStyle() {
	var curStyle, regC = /padding[-a-z]*:\s*[0-9]*[a-z\%;]*\s*/ig
	curStyle=over.getAttribute("style");
	curStyle=curStyle.replace(regC,'');
	over.setAttribute("style", curStyle);
}
////////
// LAYER GENERATION FUNCTIONS
////////
// Makes simple table without caption
function ol_content_simple_cssw3c(text) {
	txt='<table width="'+o3_width+ '" border="0" cellpadding="0" cellspacing="0"><tr><td class="'+o3_bodyclass+'">'+text+'</td></tr></table>';
	set_background("");
	return txt;
}
// Makes table with caption and optional close link
function ol_content_caption_cssw3c(text,title,close) {
	var nameId;
	closing="";
	closeevent="onmouseover";
	if (o3_closeclick==1) closeevent=(o3_closetitle ? "title='" + o3_closetitle +"'" : "") + " onclick";
	if (o3_capicon!="") {
	  nameId=' hspace=\"5\"'+' align=\"middle\" alt=\"\"';
	  if (typeof o3_dragimg!='undefined'&&o3_dragimg) nameId=' hspace=\"5\"'+' name=\"'+o3_dragimg+'\" id=\"'+o3_dragimg+'\" align=\"middle\" alt=\"Drag Enabled\" title=\"Drag Enabled\"';
	  o3_capicon='<img src=\"'+o3_capicon+'\"'+nameId+' />';
	}
	if (close!="") {
	  closing='<td align="right" class="'+o3_closeclass+'"><a href="javascript:return '+fnRef+'cClick();" '+closeevent+'="return cClick();">'+close+'</a></td>';
	}
	txt='<table width="'+o3_width+ '" border="0" cellpadding="0" cellspacing="0"><tr><td class="'+o3_captionclass+'">'+o3_capicon+title+'</td>'+closing+'</tr><tr><td'+(closing ? ' colspan="2"' : '')+' class="'+o3_bodyclass+'">'+text+'</td></tr></table>';
	set_background("");
	return txt;
}
// Sets the background picture,padding and lots more. :)
function ol_content_background_cssw3c(text,picture,hasfullhtml) {
	if (hasfullhtml) {
		txt=text;
	} else {
	  var pU, hU, wU;
	  pU=(typeof o3_padunit!='undefined'&&o3_padunit=='%' ? '%' : '');
		hU=(typeof o3_heightunit!='undefined'&&o3_heightunit=='%' ? '%' : '');
	  wU=(typeof o3_widthunit!='undefined'&&o3_widthunit=='%' ? '%' : '');
		txt='<table width="'+o3_width+wU+'" border="0" cellpadding="0" cellspacing="0" height="'+o3_height+hU+'"><tr><td colspan="3" height="'+o3_padyt+pU+'">&nbsp;</td></tr><tr><td width="'+o3_padxl+pU+'">&nbsp;</td><td width="'+(o3_width-o3_padxl-o3_padxr)+pU+'" class="'+o3_bodyclass+'">'+text+'</td><td width="'+o3_padxr+pU+'">&nbsp;</td></tr><tr><td colspan="3" height="'+o3_padyb+pU+'">&nbsp;</td></tr></table>';
	}
	set_background(picture);
	return txt;
}
////////
// PLUGIN REGISTRATIONS
////////
registerRunTimeFunction(setCSSW3cVariables);
registerCmdLineFunction(parseCSSW3cExtras);
registerHook("ol_content_simple",ol_content_simple_cssw3c,FALTERNATE,CSSW3C);
registerHook("ol_content_caption",ol_content_caption_cssw3c,FALTERNATE,CSSW3C);
registerHook("ol_content_background",ol_content_background_cssw3c,FALTERNATE,CSSW3C);
registerHook("createPopup",addOvDivCSS,FAFTER,(typeof generateShadow!='undefined' ? generateShadow : null));
if (olInfo.meets(4.14)) registerNoParameterCommands('usedivcls,usestd');
}
//end 
