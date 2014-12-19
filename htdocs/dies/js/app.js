'use strict';
var setbg = function(el, imgwidth, imgheight) {
    if($(window).width() <= 1280) {
        imgwidth = imgwidth/4*3;
        imgheight = imgheight/4*3;
    }
    var centerx = $(el).width()/2;
    var centery = $(el).height()/2;
    var posx = centerx-(imgwidth/2);
    var posy = centery-(imgheight/2);
    $(el).css('background-position', posx + 'px ' + posy + 'px');   
}

var main = function(){
    $('#welkom h1').remove();
    $('#welkom h2').remove();
}

$(document).ready(function(event){
    main();
    setbg($('section#welkom'), 1101, 950);
    setbg($('section div#spark'), 1280, 800);
    setbg($('section div#logo'), 800, 169);
});

var parallax = function(el, event, imgwidth, imgheight, delay) {
    if($(window).width() <= 1280) {
        imgwidth = imgwidth/4*3;
        imgheight = imgheight/4*3;
    }
    var x = event.pageX;
    var y = event.pageY;
    var centerx = $(el).width()/2;
    var centery = $(el).height()/2;
    var posx = centerx-(imgwidth/2)-(x-centerx)*delay;
    var posy = centery-(imgheight/2)-(y-centery)*delay;
    $(el).css('background-position', posx + 'px ' + posy + 'px');
}

$('#welkom').mousemove(function(event) {
    parallax(this, event, 1101, 950, 0.01);
});

$('#spark').mousemove(function(event) {
    parallax(this, event, 1280, 800, 0.06);
});

$('#logo').mousemove(function(event) {
    parallax(this, event, 800, 169, 0.02);
});

$('nav a').click(function(event) {
    event.preventDefault();
    var el = $(this).attr('href');
    $('html, body').animate({
        scrollTop: $(el).offset().top
    }, 800);
});

$('nav a[href=#video]').click(function(event){
    player.playVideo();
})

/**
* Video player
*/
var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var player;
function onYouTubeIframeAPIReady() {
player = new YT.Player('player', {
  height: '100%',
  width: '100%',
  videoId: 'kWs9rxfamyo',
  playerVars: {
      'controls': 0,
      'modestbranding': 1,
      'showinfo': 0
  },
  events: {
    'onReady': onPlayerReady,
    'onStateChange': onPlayerStateChange
  }
});
}

function onPlayerReady(event) {
    
}

function onPlayerStateChange(event) {
    
}

function stopVideo() {
    player.stopVideo();
}