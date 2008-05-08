/* Kleine vertraging bij wisselen tussen menu items */
var t
var timeout = 250;
var timeout_next = 100;

function ShowMenu(div)
{
	timeout = timeout_next;
	
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
		document.getElementById('banner'+div).style.display = "inline";
		document.getElementById('sub'+div).style.display = "block";
		document.getElementById('top'+div).className = 'active';
	}
}

function StartShowMenu(div)
{
	t = setTimeout("ShowMenu("+div+")", timeout);
}

function ResetShowMenu()
{
	clearTimeout(t);
}

        
/* Na bepaalde tijd uit menu, terugswitchen naar actieve menu item */
var active;
var t2
var timeout2 = 4000;

function ResetTimer()
{
	clearTimeout(t2);
}

function StartTimer()
{
	t2 = setTimeout("ShowMenu("+active+")", timeout2);
}

function SetActive(a)
{
	active = a;
}


/* Menu rechts tonen/verbergen */
function ShowLogin()
{
	document.getElementById('login').style.display = "block";
}

function ToggleLogin()
{
	document.getElementById('login').style.display = (document.getElementById('login').style.display == "none" ? "inline" : "none"); 
}