let i = 2;

/**
 * @see templates/peiling/beheer.tpl
 */
window.addOptie = function() {
	i++;

    let peilingOpties = document.getElementById('peilingOpties');

    let label = document.createElement('label');
    label.setAttribute('for', 'optie');
	label.innerHTML = 'Optie '+i+':';
	peilingOpties.appendChild(label);

    let input = document.createElement('input');
    input.setAttribute('name', 'opties[]');
	input.setAttribute('type', 'text');
	input.setAttribute('maxlength', 255);

	peilingOpties.appendChild(input);
	peilingOpties.appendChild(document.createElement('br'));
};
