<h1>Civisaldo overzicht</h1>
<h2>Som van saldi</h2>
<div><b>Iedereen in de database:</b> {$saldisom|bedrag}</div>
<div><b>Alleen leden en oudleden:</b> {$saldisomleden|bedrag}</div>
<div class="container-fluid">
    <div class="col-lg-6">
        {$productenbeheer->view()}
    </div>
    <div class="col-lg-6">
        {$saldobeheer->view()}
    </div>
</div>