<div style="width: 500px">
    <p>
        Hier kunt u aangeven of u akkoord gaat met het delen van uw gegevens met andere leden voor interne doeleinden.
    </p>
    <p>
        Kijk op de <a href="/privacy">privacy</a> pagina voor meer informatie.
    </p>

    <p>
        <label><input type="checkbox" id="toestemming-ja"/> Mijn gegevens mogen gedeeld worden voor interne doeleinden. Dit geldt totdat ik dat verander.</label>
    </p>
    <p>
        <label><input type="checkbox" id="toestemming-nee"/> Ik wil graag instellen welke gegevens met gedeeld worden.</label>
    </p>

    <div id="toestemming-opties" style="display:none; width: 500px; clear: both;"><p>Maak een keuze, voor ieder veld moet een waarde ingevuld worden. Commissies die bepaalde gegevens nodig hebben om te kunnen functioneren blijven deze mogelijkheid houden.</p>
        {foreach from=$fields item=field}
            {$field}
        {/foreach}
    </div>
</div>

<script type="text/javascript">
    $(function () {
        var toestemmingJa = $('#toestemming-ja'),
            toestemmingNee = $('#toestemming-nee'),
            toestemmingOpties = $('#toestemming-opties');

        toestemmingNee.on('change', function () {
            if (this.checked) {
                toestemmingOpties.show();
                toestemmingJa.attr('checked', false);
            } else {
                toestemmingOpties.hide();
            }
        });

        toestemmingJa.on('change', function () {
            if (this.checked) {
                selectRadioByValue('ja');
                toestemmingNee.attr('checked', false);
                toestemmingOpties.hide();
            }
        });
    });
</script>