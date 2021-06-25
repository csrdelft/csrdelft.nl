<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/controller/Barsysteem.class.php';

$barsysteem = new Barsysteem();

function barCsrf() {
    global $barsysteem;
    echo "<input type='hidden' name=\"X-BARSYSTEEM-CSRF\" value=\"".htmlentities($barsysteem->getCsrfToken())."\" />";
}


if ($barsysteem->isLoggedIn()) {

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="X-BARSYSTEEM-CSRF" content="<?php echo htmlentities($barsysteem->getCsrfToken()); ?>" />
        <title>Barsysteem C.S.R.</title>

        <!-- Bootstrap core CSS -->
        <link href="css/bar.css" rel="stylesheet">
        <link href="css/bootstrap.min.css" rel="stylesheet">

		<script type="text/javascript">
		var beheer = <?= $barsysteem->isBeheer() ? "1" : "0" ?>;
		</script>

    </head>

    <body id="body">

    <table id="main" cellpadding="0" cellspacing="0">

    <tr class="top">
        <td>

            <div id="clock"></div>
            <div id="waarschuwing"></div>

            <!-- Nav tabs -->
            <ul class="nav nav-pills nav-justified" role="tablist">
                <li class="active"><a href="#persoonselectie" role="tab" data-toggle="tab" id="persoonselectieVeld"><span class="glyphicon glyphicon-user"></span>Persoonselectie</a></li>
                <li><a href="#invoer" role="tab" data-toggle="tab" id="invoerveld"><span class="glyphicon glyphicon-pencil"></span>Invoer</a></li>
                <li><a href="#bestelLijstBeheer" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-list"></span>Bestellingen</a></li>
                <li class="beheer"><a href="#beheer" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-wrench"></span></a></li>
            </ul>

        </td>
    </tr>
    <tr class="bottom">
        <td>

            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="persoonselectie">
                    <div class="input-group input-group-lg">
                        <input id="persoonInput" type="text" class="form-control" placeholder="Naam">
						<span class="input-group-btn input-group-lg">
						<button class="btn btn-default" type="button" id="keyboardToggle"><span class="glyphicon glyphicon-font"></span></button>
					</span>
                    </div>
                    <div id="keyboardContainer">
                        <ul id="keyboard">
                            <li class="letter">Q</li>
                            <li class="letter">W</li>
                            <li class="letter">E</li>
                            <li class="letter">R</li>
                            <li class="letter">T</li>
                            <li class="letter">Y</li>
                            <li class="letter">U</li>
                            <li class="letter">I</li>
                            <li class="letter">O</li>
                            <li class="letter">P</li>
                            <li class="delete">delete</li>
                            <li class="spacer clear"></li>
                            <li class="letter">A</li>
                            <li class="letter">S</li>
                            <li class="letter">D</li>
                            <li class="letter">F</li>
                            <li class="letter">G</li>
                            <li class="letter">H</li>
                            <li class="letter">J</li>
                            <li class="letter">K</li>
                            <li class="letter">L</li>
                            <li class="leeg">leeg</li>
                            <li class="spacer clear"></li>
                            <li class="spacer"></li>
                            <li class="letter">Z</li>
                            <li class="letter">X</li>
                            <li class="letter">C</li>
                            <li class="letter">V</li>
                            <li class="letter">B</li>
                            <li class="letter">N</li>
                            <li class="letter">M</li>
                            <li class="space">space</li>
                        </ul>
                    </div>
                    <table id="selectieTabel" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Bijnaam</th>
                            <th>Naam</th>
                            <th>Saldo</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                </div>
                <div class="tab-pane" id="invoer">

					<div id="knoppenGroep">

						<input id="aantalInput" type="text" class="form-control" placeholder="1">

						<div class="btn-group btn-default">
							<button type="button" class="btn btn-groot btn-default" id="knop7">7</button>
							<button type="button" class="btn btn-groot btn-default" id="knop8">8</button>
							<button type="button" class="btn btn-groot btn-default" id="knop9">9</button>
						</div>
						<div class="btn-group btn-default">
							<button type="button" class="btn btn-groot btn-default" id="knop4">4</button>
							<button type="button" class="btn btn-groot btn-default" id="knop5">5</button>
							<button type="button" class="btn btn-groot btn-default" id="knop6">6</button>
						</div>
						<div class="btn-group btn-default">
							<button type="button" class="btn btn-groot btn-default" id="knop1">1</button>
							<button type="button" class="btn btn-groot btn-default" id="knop2">2</button>
							<button type="button" class="btn btn-groot btn-default" id="knop3">3</button>
						</div>
						<div class="btn-group btn-default">
							<button type="button" class="btn btn-groot btn-default" id="knopC">&#60&#60</button>
							<button type="button" class="btn btn-groot btn-default" id="knop0">0</button>
							<button type="button" class="btn btn-groot btn-default" id="knop-">-</button>
						</div>
						<div id="saldoOverzicht">
							<table class="table">
								<tr>
									<td>Huidig saldo</td>
									<td id="huidigSaldo"></td>
								</tr>
								<tr>
									<td>Totaal bestelling</td>
									<td id="totaalBestelling"></td>
								</tr>
								<tr>
									<td>Nieuw saldo</td>
									<td id="nieuwSaldo"><span>€0,00</span></td>
								</tr>
							</table>
						</div>
						<div class="btn-group btn-default" id="ondersteRijKnoppen">
							<button type="button" class="btn btn-beneden btn-default" id="knopCancel"><span
									class="glyphicon glyphicon-remove"></span></button>
							<button type="button" class="btn btn-beneden btn-default" id="knopConfirm"><span
									class="glyphicon glyphicon-ok"></span></button>
						</div>
					</div>
					<div id="linkerKant">
						<div id="bestelLijstDiv">
							<div class="eenDerdeLijst">
								<ul class="list-group bestelLijst" id="bestelLijst1"></ul>
							</div>
							<div class="eenDerdeLijst">
								<ul class="list-group bestelLijst" id="bestelLijst2"></ul>
							</div>
							<div class="eenDerdeLijst">
								<ul class="list-group bestelLijst" id="bestelLijst3"></ul>
							</div>
						</div>
						<div id="bestelKnoppenLijst">
						</div>
					</div>

                </div>
                <div class="tab-pane <?= $barsysteem->isBeheer() ? "beheer" : "" ?>" id="bestelLijstBeheer">

                    <div class=" input-group input-group-lg input-daterange" id="datepicker">
						<span class="input-group-btn">
							<button class="btn btn-toggle btn-default" id="eenPersoon">Geselecteerde persoon</button>
							<button class="btn btn-toggle btn-default btn-primary" id="allePersonen">Alle personen
                            </button>
						</span>
                        <span class="input-group-addon">van</span><input type="text" class="input-sm form-control"
                                                                         name="start"
                                                                         placeholder="begin borrel" id="beginDatum"/>
                        <span class="clearKruisje glyphicon glyphicon glyphicon-remove-circle"></span>
                        <span class="input-group-addon">tot</span><input type="text"
                                                                         class="clearable input-sm form-control "
                                                                         name="end"
                                                                         placeholder="nu" id="eindDatum"/> <span
                            class="clearKruisje glyphicon glyphicon glyphicon-remove-circle"></span>
                        <span class="input-group-addon">naar</span>
						<span class="input-group-btn">
                            <button class="btn btn-default btn-primary" id="pSearch">alle</button>
							<button class="btn btn-primary" id="krijgBestellingen"><span
                                    class="glyphicon glyphicon glyphicon-cloud-download"></span></button>
						</span>

                    </div>
                    <div id="pSearchContent" class="hidden input-group input-group-lg">

                        <?php // Space for checkboxes ?>

                    </div>
                    <div id="besteLijstBeheerContent">
                        <table class="table table-striped tablesorter" id="besteLijstBeheer">
                            <thead>
                            <tr>
                                <th id="persoon">Persoon</th>
                                <th id="datum">Datum en tijd</th>
                                <th id="totaal">Totaal</th>
                                <th id="bestelling" class="sorter-false">Bestelling</th>
                                <th id="opties" class="sorter-false">Opties</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane" id="beheer">

                    <div id="beheer-nav" class="btn-group btn-group-lg">
                        <?php if ($barsysteem->isBeheer()): ?><button class="btn btn-default" id="laadProducten">Productbeheer</button><?php endif; ?>
                        <button class="btn btn-default" id="laadPersonen">Persoonbeheer</button>
                        <?php if ($barsysteem->isBeheer()): ?><button class="btn btn-default" id="laadGrootboekInvoer">Grootboekinvoer</button><?php endif; ?>
                        <?php if ($barsysteem->isBeheer()): ?><button class="btn btn-default" id="laadTools">Tools</button><?php endif; ?>
                    </div>

					<div id="beheerDisplay">

					<?php if ($barsysteem->isBeheer()): ?>

						<div id="productBeheer" class="hidden">

                            <h2>Product toevoegen</h2>

                            <form id="addProduct" class="form-inline" action="ajax.php" method="post">
                                <?php barCsrf(); ?>
                                <div id="input-group">

                                    <input type="hidden" name="add_product" value="on" />
                                    <input placeholder="Naam" name="name" type="text" class="form-control" />
                                    <input placeholder="Prijs in centen" name="price" type="text" class="form-control" />
                                    <select class="form-control" name="grootboekId">
                                        <option>Selecteer grootboek</option>
                                        <?php
                                            foreach($barsysteem->getGrootboeken() as $grootboek) {
                                                echo '<option value="' . $grootboek['id'] . '">' . $grootboek['type'] . '</option>';
                                            }
                                        ?>
                                    </select>

                                    <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span></button>

                                </div>

                            </form>

							<h2>Wijzig een product</h2>

							<div class="row">

								<div class="col-xs-3">

									<div class="list-group" id="productBeheerLijst">

									</div>

								</div>

								<div class="col-xs-9" id="editProduct">

								</div>

							</div>

						</div>

					<?php endif; ?>

						<div id="persoonBeheer" class="hidden">

							<h2>Persoon wijzigen</h2>

							<form id="updatePerson" class="form-inline" action="ajax.php" method="post">
								<?php barCsrf(); ?>
								<div id="input-group">

									<input type="hidden" name="update_person" value="on" />
									<select name="id" class="form-control personList"></select>
									<input placeholder="Bijnaam" name="name" type="text" class="form-control" />
									<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span></button>

								</div>

							</form>

							<?php if ($barsysteem->isBeheer()): ?>

							<h2>Persoon toevoegen</h2>

							<form id="addPerson" class="form-inline" action="ajax.php" method="post">
								<?php barCsrf(); ?>
								<div id="input-group">

									<input type="hidden" name="add_person" value="on" />
									<input placeholder="Naam" name="name" type="text" class="form-control" />
									<input placeholder="Saldo in centen" name="saldo" type="text" class="form-control" />
									<input placeholder="Leeg of lidnummer" name="uid" type="text" class="form-control" />
									<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span></button>

								</div>

							</form>

							<h2>Persoon verwijderen</h2>

							<form id="removePerson" class="form-inline" action="ajax.php" method="post">
								<?php barCsrf(); ?>
								<div id="input-group">

									<input type="hidden" name="remove_person" value="on" />
									<select name="id" class="form-control personList"></select>
									<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-remove"></span></button>

								</div>

							</form>

							<?php endif; ?>

						</div>

						<div id="grootboekInvoer" class="hidden">

						</div>

						<div id="tools" class="hidden">

							<h2>Som van saldi</h2>
							<table class="table table-striped">
								<tr>
									<th>Iedereen in de database</th><td id="sumSaldi"></td>
								</tr>
								<tr>
									<th>Alleen leden en oudleden</th><td id="sumSaldiLid"></td>
								</tr>
							</table>

                            <h2>Leden die rood staan</h2>
                            <table class="table table-striped" id="red">
                            </table>

                            <h2>Oudleden die rood staan</h2>
                            <table class="table table-striped" id="red-old">
                            </table>

						</div>

					</div>

                </div>

            </div>

        </td>
    </tr>

    </table>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.tablesorter.widgets.min.js"></script>
    <script src="js/bootstrapValidator.min.js"></script>
    <script src="js/nl_NL.js"></script>
    <script src="js/main.js"></script>

    </body>
    </html>
<?php } ?>
