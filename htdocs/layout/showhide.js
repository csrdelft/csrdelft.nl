function showhide(id)
{
	el = document.getElementById(id);
	el.style.display = (el.style.display == 'none' ? '' : 'none');
}