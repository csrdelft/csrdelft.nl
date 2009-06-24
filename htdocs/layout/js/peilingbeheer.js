var i=2;
function addOptie() {
	i++;
	
	var optiediv_l = document.createElement('div');
	optiediv_l.setAttribute("class", "optie");
	optiediv_l.innerHTML = 'Optie '+i+':';	 
	document.getElementById("opties_l").appendChild(optiediv_l);
	
	var optiediv_r = document.createElement('div');
	optiediv_r.setAttribute("class", "optie");
	optiediv_r.innerHTML = '<input name="optie'+i+'" type="text"/>';	 
	document.getElementById("opties_r").appendChild(optiediv_r);
}