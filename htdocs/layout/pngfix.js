var pngfix_arVersion = navigator.appVersion.split("MSIE")
var pngfix_version = parseFloat(pngfix_arVersion[1])
var pngfix_checkfixed = new Array(4);

function fixPNG(imageID) 
{
	if (pngfix_checkfixed[imageID] != 1) {
		pngfix_checkfixed[imageID] = 1;
		myImage = document.getElementById(imageID)
		if ((pngfix_version >= 5.5) && (pngfix_version < 7) && (document.body.filters)) 
		{
			var imgID = (myImage.id) ? "id='" + myImage.id + "' " : ""
			var imgClass = (myImage.className) ? "class='" + myImage.className + "' " : ""
			var imgTitle = (myImage.title) ? 
			             "title='" + myImage.title  + "' " : "title='" + myImage.alt + "' "
			var imgStyle = "display:inline-block;" + myImage.style.cssText
			var strNewHTML = "<span " + imgID + imgClass + imgTitle
	                  + " style=\"" + "width:" + myImage.width 
	                  + "px; height:" + myImage.height 
	                  + "px;" + imgStyle + ";"
	                  + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
	                  + "(src=\'" + myImage.src + "\', sizingMethod='scale');\"></span>"
			myImage.outerHTML = strNewHTML	  
		}
	}
}