'use strict';
var setbg = function (el, imgwidth, imgheight) {
    if ($(window).width() <= 1280) {
        imgwidth = imgwidth / 4 * 3;
        imgheight = imgheight / 4 * 3;
    }
    var centerx = $(el).width() / 2;
    var centery = $(el).height() / 2;
    var posx = centerx - (imgwidth / 2);
    var posy = centery - (imgheight / 2);
    $(el).css('background-position', posx + 'px ' + posy + 'px');
}

var main = function () {
    $('#welkom h1').remove();
    $('#welkom h2').remove();
}

$(document).ready(function (event) {
    main();
    setbg($('section#welkom'), 1101, 950);
    setbg($('section div#spark'), 1280, 800);
    setbg($('section div#logo'), 800, 169);
});

$(window).scroll(function () {
    var welkompos = $('#welkom').height();
    var themapos = 2 * welkompos;
    var programmapos = $('#programma').height() + themapos;
    var etiquettepos = $('#etiquette').height() + programmapos;
    var pos = $(window).scrollTop();
    if (pos >= themapos) {
        $('#m').fadeIn();
    } else {
        $('#m').fadeOut();
    }
    if (pos < welkompos) {
        $('nav li a').removeClass('selected');
        $('nav li:first-child a').addClass('selected');
    } else if (pos >= welkompos && pos < themapos) {
        $('nav li a').removeClass('selected');
        $('nav li:nth-child(2) a').addClass('selected');
    } else if (pos >= themapos && pos < programmapos) {
        $('nav li a').removeClass('selected');
        $('nav li:nth-child(3) a').addClass('selected');
    } else if (pos >= programmapos && pos < etiquettepos) {
        $('nav li a').removeClass('selected');
        $('nav li:nth-child(4) a').addClass('selected');
    } else if (pos >= etiquettepos) {
        $('nav li a').removeClass('selected');
        $('nav li:nth-child(5) a').addClass('selected');
    }
})

var parallax = function (el, event, imgwidth, imgheight, delay) {
    if ($(window).width() <= 1280) {
        imgwidth = imgwidth / 4 * 3;
        imgheight = imgheight / 4 * 3;
    }
    if (imgwidth === 0 && imgheight === 0) {
        imgwidth = $(el).width() * 1.05;
        imgheight = $(el).height() * 0.85;
    }
    var x = event.pageX;
    var y = event.pageY;
    var centerx = $(el).width() / 2;
    var centery = $(el).height() / 2;
    var posx = centerx - (imgwidth / 2) - (x - centerx) * delay;
    var posy = centery - (imgheight / 2) - (y - centery) * delay;
    $(el).css('background-position', posx + 'px ' + posy + 'px');
}

$('#welkom').mousemove(function (event) {
    parallax(this, event, 1101, 950, 0.01);
});

$('#spark').mousemove(function (event) {
    parallax(this, event, 1280, 800, 0.06);
});

$('#logo').mousemove(function (event) {
    parallax(this, event, 800, 169, 0.02);
});

$('#commissie').mousemove(function (event) {
    parallax(this, event, 0, 0, 0.02);
})

$('nav a').click(function (event) {
    event.preventDefault();
    var el = $(this).attr('href');
    $('html, body').animate({
        scrollTop: $(el).offset().top
    }, 800);
});

$('#m').click(function (event) {
    event.preventDefault();
    var el = $(this).parent().attr('href');
    $('html, body').animate({
        scrollTop: $(el).offset().top
    }, 800);
});

$('nav a[href=#video]').click(function (event) {
    player.playVideo();
});

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
    player.setPlaybackQuality('hd720');
}

function onPlayerStateChange(event) {
    var welkompos = $('#welkom').height();
    var programmapos = $('#programma').height() + 2 * welkompos;
    if (event.data === YT.PlayerState.ENDED && $(window).scrollTop() < programmapos) {
        $('html, body').animate({
            scrollTop: $('#programma').offset().top
        }, 800);
    }
}
function stopVideo() {
    player.stopVideo();
}