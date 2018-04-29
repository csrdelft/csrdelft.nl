<div style="width: 500px">
    <p>
        Hier kunt u aangeven of u akkoord gaat met het delen van uw gegevens.
    </p>
    <p>
        Kijk op de <a href="/privacy">privacy</a> pagina voor meer informatie.
    </p>

    <p>
        Met het volgende vinkje kun je aangeven of je akkoord gaat met het laten zien van foto's met jou erop op de externe stek en het gebruik van deze foto's voor promotie doeleinden.
    </p>

    <p>
        {$akkoordExtern}
    </p>

    <p class="clear">
        Maak hieronder een keuze of u akkoord gaat met het delen van uw gegevens met leden, novieten en oudleden van C.S.R.
    </p>

    <p>
        <label><input type="radio" name="toestemming-intern" id="toestemming-ja"{if $akkoord == 'ja'} checked="checked"{/if}/> Mijn gegevens mogen gedeeld worden voor interne doeleinden. Dit geldt totdat ik dat verander.</label>
    </p>
    <p>
        <label><input type="radio" name="toestemming-intern" id="toestemming-nee"{if $akkoord == 'nee'} checked="checked"{/if}/> Ik wil graag instellen welke gegevens met gedeeld worden.</label>
    </p>

    <div id="toestemming-opties" style="{if $akkoord != 'nee'}display:none;{/if} width: 500px; clear: both;"><p>Maak een keuze, voor ieder veld moet een waarde ingevuld worden. Commissies die bepaalde gegevens nodig hebben om te kunnen functioneren blijven deze mogelijkheid houden.</p>
        {foreach from=$fields item=field}
            {$field}
        {/foreach}
    </div>
</div>

<script type="text/javascript">
    (function () {
        var toestemmingJa = $('#toestemming-ja'),
            toestemmingNee = $('#toestemming-nee'),
            toestemmingOpties = $('#toestemming-opties');

        toestemmingNee.on('change', function () {
            if (this.checked) {
                toestemmingOpties.show();
            } else {
                toestemmingOpties.hide();
            }
        });

        toestemmingJa.on('change', function () {
            if (this.checked) {
                selectRadioByValue('ja');
                toestemmingOpties.hide();
            }
        });
    })();
</script>