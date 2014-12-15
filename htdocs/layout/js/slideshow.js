/***************************************************************************************
Copyright (C) 2001 ab_ditto
This script is made by and copyrighted to ab_ditto at back.to/ab or ab_ditto@hotmail.com
This may be used freely as long as this msg is intact!
date:2001-09-30
***************************************************************************************/

var undefined;

//*****parameters to set*****
picleft=0; //set this to the left position of your pics to be shown on the page
pictop=0; //set thid to the top position of your pics to be shown on the page
picwid=355; //set this to the width of your widest pic
pichei=237; //... and this to the height of your highest pic
backgr="#ffffff"; //set this to the background color you want to use for the slide-area
//(for example the body-background-color) if your pics are of different size
sdur=3; //time to show a pic between fades in seconds
fdur=0.5; //duration of the complete fade in seconds
steps=10; //steps to fade from on pic to the next
startwhen=1;

paused=0;
active=0;

aantal_per_serie=10; // Het aantal foto's per serie
// "startwhen" leave it at "null" to start the function by calling it from your page by link
// or set it to 1 to start the slide automatically as soon as the images are loaded
//*****nothing more to do, have fun :)
i=0;
parr = new Array();
//**************************************************************************************
ftim=fdur*1000/steps;stim=sdur*1000;
serie = Math.ceil(Math.random()*6);
for(e = 0; e < aantal_per_serie; e++) {
	theid="img"+e;
	thefile=e+1;
	if(thefile<10){
		thefile="0"+thefile;
	}
	document.write('<div id="'+theid+'"><img src="//csrdelft.nl/plaetjes/voorpagina/slides/serie'+serie+'_'+thefile+'.jpg" /></div>');
}
document.write('<img src="//csrdelft.nl/plaetjes/layout/pauze.gif" onclick="if(paused==0){paused=1;this.src=\'//csrdelft.nl/plaetjes/layout/speel.gif\';pause();}else{paused=0;this.src=\'//csrdelft.nl/plaetjes/layout/pauze.gif\';resume(true);}" style="display: block; position: absolute; left: 310px; top: 193px; z-index: 3; opacity: .4; filter: alpha(opacity=40);cursor: pointer;" />');
//document.write('<a href="#" onclick="next();" style="display: block; position: absolute; left: 150px; top: 200px; z-index: 3; opacity: .4; filter: alpha(opacity=40);"><img src="pauze.gif" /></a>');
//document.write('<a href="#" onclick="previous();" style="display: block; position: absolute; left: 50px; top: 200px; z-index: 3; opacity: .4; filter: alpha(opacity=40);"><img src="pauze.gif" /></a>');


document.write('<style type="text/css">');
for(b = 0; b < aantal_per_serie; b++) {
	thestylid="img"+b;
	thez=1;
	thevis='hidden';
	if(b<1) {thez=2; thevis='visible';}
	document.write("#"+thestylid+" {position:absolute; left:"+picleft+"px; top:"+pictop+"px; width:"+picwid+"px; height:"+pichei+"px; background-color:"+backgr+"; layer-background-color:"+backgr+"; visibility:"+thevis+"; z-index:"+thez+";}");
	}
document.write('</style>');

function myfade() {
	parr = new Array();
	for(a = 0; a < aantal_per_serie; a++) {
		idakt="img"+a;
		paktidakt=document.getElementById(idakt);
	    ie5exep=new Array(paktidakt);
	    parr=parr.concat(ie5exep);
	}
	i=1;u=0;
	slide(i);
}

function slide(numa){
	ptofade = parr[numa-1];
	if(numa>=9){pnext=parr[0];}else{pnext=parr[numa];}
	pnext.style.visibility = "visible";
	pnext.style.filter = "Alpha(Opacity=100)";
	pnext.style.opacity = 1;
	ptofade.style.filter = "Alpha(Opacity=100)";
	ptofade.style.opacity = 1;
	factor = 100/steps;
	slidenow();
}

function slidenow(){
	check1=ptofade.style.opacity;
	maxalpha = (100 - factor*u)/100*105;
	if(check1<=maxalpha/100){u=u+1;}
	curralpha = 100 - factor*u;
	ptofade.style.filter = "Alpha(Opacity="+curralpha+")";
	ptofade.style.opacity = curralpha/100;
	
	if(u<steps){
		window.setTimeout("slidenow()",ftim);
	}else{
		ptofade.style.visibility = "hidden";
		ptofade.style.zIndex = 1;
		pnext.style.zIndex = 2;
		u=0;
		if(i<aantal_per_serie-1){
			i=i+1;	
		}else{
			i=1;
		}
		if (active==1){
			tid=window.setTimeout("slide(i)",stim);
		}
	}
}

function next(){
	try{
		window.clearTimeout(tid);
	}catch(e){}
	if(i>aantal_per_serie-1){i=0;}
	slide(i);
}
function previous(){
	try{
		window.clearTimeout(tid);
	}catch(e){}
	if(i==0){i=aantal_per_serie-1;}
	slide(i-1);
}

function start(){
	active=1;
	tid=window.setTimeout("myfade()",stim);
}
function pause(){
    if (active==1) {
    	active=0;
    	try{
	    	window.clearTimeout(tid);
    	}catch(e){}
    }
}
function resume(direct){
	if (active==0) {
    	active=1;
    	if(paused==0){
    		try{
    			window.clearTimeout(tid);
    		}catch(e){}
    		if(direct){
    			slide(i);
    		}else{
	    		tid=window.setTimeout("slide(i)",stim);
    		}
    	}
    }	
}

onload=start;

window.onblur=pause;
window.onfocus="resume(false)";