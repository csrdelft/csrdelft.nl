var i=2;
function addOptie() {
	i++;
	
	var label = document.createElement('label');
	label.setAttribute("for", "optie");
	label.innerHTML = 'Optie '+i+':';	 
	document.getElementById("opties").appendChild(label);
	
	var input = document.createElement('input');
	input.setAttribute('name', 'opties[]');
	input.setAttribute('type', 'text');
	input.setAttribute('maxlength', 255);

	document.getElementById("opties").appendChild(input);
	document.getElementById('opties').appendChild(document.createElement('br'));
}
