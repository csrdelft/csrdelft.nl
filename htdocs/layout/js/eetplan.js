$(function () {
    var $novieten = $('.novietentabel'),
        $eetplan = $('.eetplantabel'),
        eetplan = null;

    function bouwEetplanTabel($novieten, $eetplan, data) {
        $eetplan.empty();
        $novieten.empty();

        var $eetplanHeader = $('<tr>'),
            $novietenHeader = $('<tr><th>Novieten</th></tr>');

        $novieten.append($novietenHeader);

        $.each(data.avonden, function (key, avond) {
            $eetplanHeader.append($('<th>').text(avond));
        });

        $eetplan.append($eetplanHeader);

        $.each(data.novieten, function (key, noviet) {
            var $row = $('<tr>').append('<td>' + noviet.naam + '</td>'),
                $avonden = $('<tr>');

            $.each(noviet.avonden, function (key, avond) {
                $avonden.append($('<td>').text(avond.woonoord));
            });

            $eetplan.append($avonden);
            $novieten.append($row);
        });
    }

    $.ajax({
        method: 'GET',
        url: '/eetplan/json'
    }).done(function (data) {
        bouwEetplanTabel($novieten, $eetplan, data);
    })
});