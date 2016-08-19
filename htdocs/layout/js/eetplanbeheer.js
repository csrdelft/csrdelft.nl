$(function () {
    var $woonoordstatus = $('.woonoord-status');

    $woonoordstatus.one('click', function updateHuisHandler(eve) {
        var $this = $(this);
        $this.removeClass('ja nee').addClass('loading');
        $.ajax({
            url: '/eetplan/huisstatus',
            method: 'POST',
            dataType: 'json',
            data: {
                'woonoordid': $this.data('id'),
                'eetplanstatus': !$this.data('status')
            }

        }).done(function (data) {
            $this.removeClass('loading');
            $this.data('status', data.eetplan);
            if (data.eetplan) {
                $this.addClass('ja');
            } else {
                $this.addClass('nee');
            }

            $this.one('click', updateHuisHandler);
        });
    });

});
