<h1>Civisaldo overzicht</h1>
<ul>
    <li><a href="/fiscaat/producten">Producten beheer</a></li>
    <li><a href="/fiscaat/saldo">Saldo beheer</a></li>
    <li><a href="/fiscaat/bestellingen">Bestellingen beheer</a></li>
    <li><a href="/fiscaat/pin">Pin transacties</a></li>
</ul>

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