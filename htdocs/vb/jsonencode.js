function formToJSON(formObj) {
	var request = "{";
	for (var i = 0; i < formObj.length; i++) 
		if (formObj.elements[i].name != "")
		{
			request = request + "\""+formObj.elements[i].name+"\" : \""+ formObj.elements[i].value + "\"";
			if (i < formObj.length -1)
				request += ",";
		}
	request = request+ "}";
	return request;
};

function newRequest()
{
	xmlHttp = null;
	try {	xmlHttp = new XMLHttpRequest();  } catch (e) {
		try {  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
		   xmlHttp=new ActiveXObject("Microsoft.XMLHTTP"); }	 }
	return xmlHttp;

}