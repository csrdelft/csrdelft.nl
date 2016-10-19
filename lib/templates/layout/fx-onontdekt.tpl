<script type="text/javascript">
    var parallax = function (el, event, imgwidth, imgheight, delay) {
        if ($(window).width() <= 1280) {
            imgwidth = imgwidth / 4 * 3;
            imgheight = imgheight / 4 * 3;
        }
        if (imgwidth === 0 && imgheight === 0) {
            imgwidth = $(el).width() * 1.05;
            imgheight = $(el).height() * 1.05;
        }
        var x = event.pageX;
        var y = event.pageY;
        var centerx = $(el).width() / 2;
        var centery = $(el).height() / 2;
        var posx = centerx - (imgwidth / 2) - (x - centerx) * delay;
        var posy = (imgheight / 2) - centery - (y - centery) * delay;
        $(el).css('background-position', posx + 'px ' + posy + 'px');
    };

    var container;

    var initOnontdekt = function () {
        container = $('#cd-main-overlay');

        var layers = ['laag1.png', 'laag2.png', 'laag3.png', 'laag4.png', 'laag5.png'];
        var prefix = '/assets/layout/plaetjes/onontdekt/';

        var div = $(document.createElement('div'))
                .css({
                    'width': '100%',
                    'height': '100%',
                    'position': 'absolute',
                    'background-size': 'cover',
                    'background-position': 'center',
                    'background-image': 'url(' + prefix + layers[0] + ')'
                });

        container.append(div);

        for (var i = 1; i < layers.length; i++) {
            var width = 100 + i * 2;

            div = $(document.createElement('div'))
                    .css({
                        'width': width + '%',
                        'height': width + '%',
                        'top': '-' + i*2 + '%',
                        'position': 'absolute',
                        'background-size': '100% 100%',
                        'background-position': 'bottom left',
                        'background-repeat': 'no-repeat',
                        'background-image': 'url(' + prefix + layers[i] + ')'
                    });


            evlisten(div, i);

            container.append(div);
        }
    };

    var evlisten = function (div, i) {
        $(document).on('mousemove', function(event) {
            parallax(div, event, 0, 0, 0.01 * i)
        });
    };

    initOnontdekt();
</script>