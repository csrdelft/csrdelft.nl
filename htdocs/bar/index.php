<?php
require_once 'configuratie.include.php';

if (LoginLid::mag("P_ADMIN")) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Barsysteem C.S.R.</title>

        <!-- Bootstrap core CSS -->
        <link href="css/bar.css" rel="stylesheet">
        <link href="css/bootstrap.min.css" rel="stylesheet">
		
        <!-- Custom styles for this template -->
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body id="body">
	
	<div id="waarschuwing"></div>

    <!-- Nav tabs -->
    <ul class="nav nav-pills nav-justified" role="tablist">
        <li class="active"><a href="#persoonselectie" role="tab" data-toggle="tab" id="persoonselectieVeld">Persoonselectie</a>
        </li>
        <li><a href="#invoer" role="tab" data-toggle="tab" id="invoerveld">Invoer</a></li>
        <li><a href="#bestelLijstBeheer" role="tab" data-toggle="tab">Bestellingen</a></li>
        <li><a href="#productBeheer" role="tab" data-toggle="tab">Productenbeheer</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="persoonselectie">
            <div class="input-group input-group-lg">
                <input id="persoonInput" type="text" class="form-control" placeholder="naam">
            <span class="input-group-btn input-group-lg">
                <button class="btn btn-default" type="button" id="keyboardToggle">keyboard</button>
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
            <table id="selectieTabel" class="table">
                <thead>
                <tr>
                    <td><b>Bijnaam</b></td>
                    <td><b>Naam</b></td>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </div>
        <div class="tab-pane" id="invoer">

            <div class="row">
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
                    <div id=bestelLijstDiv>
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
        </div>
        <div class="tab-pane" id="bestelLijstBeheer">
            <div class="btn-group btn-group-lg" id="laadKnoppen">
                <button class="btn btn-default btn-lg" id="besteLijstBeheerLaadPersoon">Geen</button>
                <button class="btn btn-default btn-lg" id="besteLijstBeheerLaadLaatste100">Laad laatste 100
                    bestellingen
                </button>
            </div>
            <div id="besteLijstBeheerContent">
                <table class="table tablesorter" id="besteLijstBeheer">
                    <thead>
                    <tr>
                        <th id="persoon">Persoon</th>
                        <th id="datum">Datum en tijd</th>
                        <th id="totaal">Besteltotaal</th>
                        <th id="bestelling">Bestelling</th>
                        <th id="opties">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane" id="productBeheer">
            <div class="input-daterange input-group" id="datepicker">
                <input type="text" class="input-sm form-control" name="start"/>
                <span class="input-group-addon">tot</span>
                <input type="text" class="input-sm form-control" name="end"/>
            </div>
            <div id="productBeheerLijstDiv">
                <ul class="list-group" id="productBeheerLijst">
                </ul>
            </div>
        </div>

    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="js/bootstrap-datepicker.js"></script>

    <script src="js/myscript.js"></script>

    </body>
    </html>
<?php } ?>