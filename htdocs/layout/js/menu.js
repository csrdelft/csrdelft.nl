/* Kleine vertraging bij wisselen tussen menu items */
var menu_t
var menu_timeout = 250;
var menu_timeout_next = 100;

function ShowMenu(div) {
	menu_timeout = menu_timeout_next;

	document.getElementById('sub1').style.display = "none";
	document.getElementById('sub2').style.display = "none";
	document.getElementById('sub3').style.display = "none";
	document.getElementById('sub4').style.display = "none";
	document.getElementById('banner1').style.display = "none";
	document.getElementById('banner2').style.display = "none";
	document.getElementById('banner3').style.display = "none";
	document.getElementById('banner4').style.display = "none";
	document.getElementById('top1').className = '';
	document.getElementById('top2').className = '';
	document.getElementById('top3').className = '';
	document.getElementById('top4').className = '';

	if (div > 0) {
		document.getElementById('sub' + div).style.display = "block";
		document.getElementById('top' + div).className = 'active';
		document.getElementById('banner' + div).style.display = "block";
		if (typeof fixPNG != "undefined") {
			fixPNG('imgbanner' + div);
		}
	}
}

function StartShowMenu(div) {
	menu_t = setTimeout("ShowMenu(" + div + ")", menu_timeout);
}

function ResetShowMenu() {
	clearTimeout(menu_t);
}

/* Na bepaalde tijd uit menu, terugswitchen naar actieve menu item */
var menu_active = 0;
var menu_t2
var menu_timeout2 = 4000;

function ResetTimer() {
	clearTimeout(menu_t2);
}

function StartTimer() {
	menu_t2 = setTimeout("ShowMenu(" + menu_active + ")", menu_timeout2);
}

function SetActive(a) {
	menu_active = a;
}