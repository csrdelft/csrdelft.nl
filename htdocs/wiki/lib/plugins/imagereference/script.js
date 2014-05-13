/**
 *  Extends toolbar
 *  Image captions:
 *     - copy url from image to magnify button
 *     - try copy image title to caption
 *     - try copy alignment of image to caption
 *     - resize box to width of image
 */

if (window.toolbar !== undefined) {
    toolbar[toolbar.length] = {
        "type": "format",
        "title": "Adds an ImageCaption tag",
        "icon": "../../plugins/imagereference/button.png",
        "key": "",
        "open": "<imgcaption image1|>",
        "close": "</imgcaption>"
    };
    toolbar[toolbar.length] = {
        "type": "format",
        "title": "Adds an ImageReference tag",
        "icon": "../../plugins/imagereference/refbutton.png",
        "key": "",
        "open": "<imgref ",
        "sample": "image1",
        "close": ">"
    };
}

function checkImages() {

    jQuery('span.imgcaption').each(function () {
        var $imgcaption = jQuery(this);
        var $amedia = $imgcaption.find('a.media');
        var $img = $imgcaption.find('img');

        //copy img url to magnify button
        if ($amedia[0]) {
            var link = $amedia.attr('href');
            $imgcaption.find('span.undercaption a').last()
                .attr('href', link)//set link
                .children().show(); //display button
        }
        //copy possibly img title when no caption is set
        var captionparts = $imgcaption.find('span.undercaption').text().split(':', 2);
        if (!jQuery.trim(captionparts[1])) {
            var title = $img.attr('title');
            if (title) {
                $imgcaption.find('span.undercaption a').first().before(': ' + title);
            }
        }

        //apply alignment of image to imgcaption
        if (!($imgcaption.hasClass('left') || $imgcaption.hasClass('right') || $imgcaption.hasClass('center'))) {
            if ($img.hasClass('medialeft')) {
                $imgcaption.addClass('left');
            }
            else if ($img.hasClass('mediaright')) {
                $imgcaption.addClass('right');
            }
            else if ($img.hasClass('mediacenter')) {
                $imgcaption.addClass('center');
            }
        }
        //add wrapper to center imgcaption
        if ($imgcaption.hasClass('center')) {
            $imgcaption.wrap('<span class="imgcaption_centerwrapper"></span>');
        }
    });
}



jQuery(function () {
    checkImages();
});

// Chrome returns 0 for jQuery().width() on not scaled images, when not loaded yet before js runs
// TODO: do this in css??
jQuery(window).load(function () {
    jQuery('span.imgcaption').each(function () {
        //set imgcaption width equal to image
        var $imgcaption = jQuery(this);
        var width = $imgcaption.find('img').width();
        $imgcaption.width((width + 8) + "px");
    });

    jQuery('div.tabcaption').each(function() {
        var $imgcaption = jQuery(this);

        //add wrapper to center imgcaption
        if ($imgcaption.hasClass('center')) {
            $imgcaption.wrap('<span class="imgcaption_centerwrapper"></span>');
        }
    })
});
