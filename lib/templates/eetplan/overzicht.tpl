<h1>Eetplan</h1>
<div class="geelblokje">
    <h3>LET OP: </h3>
    <p>Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen
        koken op het huis waarbij zij gefaeld hebben.</p>
</div>

<div class="eetplan">
    <table class="novietentabel"></table>
    <table class="eetplantabel"></table>
</div>

<script type="text/javascript">
    var $novieten = $('.novietentabel'), $eetplan = $('.eetplantabel');
    $.ajax({
        method: 'GET',
        url: '/eetplan/json'
    }).done(function (data) {
        bouwEetplanTabel($novieten, $eetplan, data);
    });
</script>
