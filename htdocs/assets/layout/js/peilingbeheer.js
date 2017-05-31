var i=2;
function addOptie() {
	i++;

	var peilingOpties = document.getElementById('peilingOpties');

	var label = document.createElement('label');
	label.setAttribute('for', 'optie');
	label.innerHTML = 'Optie '+i+':';
	peilingOpties.appendChild(label);

	var input = document.createElement('input');
	input.setAttribute('name', 'opties[]');
	input.setAttribute('type', 'text');
	input.setAttribute('maxlength', 255);

	peilingOpties.appendChild(input);
	peilingOpties.appendChild(document.createElement('br'));
}
