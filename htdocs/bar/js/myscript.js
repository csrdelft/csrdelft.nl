/**
 * Dit script voegt functionaliteit toe aan het barsysteem.
 */
$(function () {

        /**
         * Deze persoon is geselecteerd, dit wordt oa. gebruikt bij de invoer van bestellingen, inleg en laden van de bestellingen van die persoon.
         */
        var selectedPerson;


        var beheer = true;

        /**
         * Dit is de lijst met bestellingen.
         * @type {{}} deze lijst mapt het productId naar het aantal dat er besteld zijn. Bijv 1=>2, dit betekent bijvoorbeeld dat er twee bier is besteld.
         */
        var bestelLijst = {};

        /**
         * Hierin zit de oude bestelling, in het geval we een bestelling verwerken.
         */
        var oudeBestelling;

        $("#besteLijstBeheer").tablesorter();

        function zetInTabel(persoon) {
            var naam = persoon.naam;
            $("#selectieTabel > tbody").append("<tr id='persoon" + persoon.socCieId + "'><td>" + persoon.bijnaam + "</td><td>" + naam + "</td></tr>");
            $("#persoon" + persoon.socCieId).click(function () {
                cancel();
                $.ajax({
                    url: "ajax.php",
                    method: "POST",
                    async: false,
                    data: {"saldoSocCieId": persoon.socCieId}
                }).done(function (data) {
                        persoon.saldo = 1 * data;
                    });
                selectedPerson = persoon;
                if (persoon.saldo >= 0)
                    zetSucces("Geselecteerde persoon: " + persoon.bijnaam + " - " + naam + " | Saldo: " + saldoStr(persoon.saldo));
                if (persoon.saldo < 0)
                    zetFaal("Geselecteerde persoon: " + persoon.bijnaam + " - " + naam + " | Saldo: " + saldoStr(persoon.saldo));

                $("#invoerveld").trigger("click");
                $("#besteLijstBeheerLaadPersoon").empty();
                $("#besteLijstBeheerLaadPersoon").append("Laad bestellingen van: " + naam);
                $("#persoonInput").val(null);
                updateOnKeyPress();
                resetTeller();
                resetLijst();

            });
        }

        function resetLijst() {
            bestelLijst = {};
            zetBestelLijstGoed();
        }

        function resetTeller() {
            $("#aantalInput")[0].value = null;
        }

        function zetProductInLijst(product) {
            $("#bestelKnoppenLijst").append("<button type='button' class='btn btn-bestel btn-default' id='bestelKnop" + product.productId + "'>" + product.beschrijving + "<br />" +
                saldoStr(product.prijs) + "</button>");
            $("#bestelKnop" + product.productId).click(function () {
                var aantal = $("#aantalInput")[0].value;
                if (aantal == "" || aantal == 0) {
                    aantal = 1;
                }
                if (aantal == "-") {
                    aantal = -1;
                }
                if (product.productId in bestelLijst) {
                    var nieuw = bestelLijst[product.productId] + (1 * aantal);
                    if (nieuw <= 0) {
                        delete bestelLijst[product.productId];
                    }
                    else {
                        bestelLijst[product.productId] = nieuw;
                    }
                } else if (aantal > 0) {
                    bestelLijst[product.productId] = (1 * aantal);
                }
                resetTeller();
                zetBestelLijstGoed();
            })

        }

        function bestelTotaal() {
            var bestelTotaal = 0;
            for (key in bestelLijst) {
                bestelTotaal += 1.0 * bestelLijst[key] * producten[key].prijs;
            }
            return bestelTotaal;
        }

        function zetBestelLijstGoed() {
            $(".bestelLijst").empty();
            var totaal = bestelTotaal();
            var teller = 0;
            for (key in bestelLijst) {
                var aantal = bestelLijst[key];
                if (producten[key].prijs < 0) aantal = saldoStr(aantal);
                $("#bestelLijst" + (teller % 3 + 1)).append("<li>" + aantal + "&#09" + producten[key].beschrijving + "</li>");
                teller++;
            }
            if (oudeBestelling) {

            }

            if (selectedPerson) {
                $("#huidigSaldo").empty();
                $("#huidigSaldo").append(saldoStr(selectedPerson.saldo));
                $("#nieuwSaldo").empty();
                $("#nieuwSaldo").append(saldoStr(selectedPerson.saldo - totaal));
            } else {
                $("#huidigSaldo").empty();
                $("#huidigSaldo").append("<span>-</span>");
                $("#nieuwSaldo").empty();
                $("#nieuwSaldo").append("<span>-</span>");
            }
            $("#totaalBestelling").empty();
            $("#totaalBestelling").append(saldoStr(totaal));
        }

        function saldoStr(saldo) {
            var achterKomma = Math.abs(saldo % 100);
            if (achterKomma == 0) achterKomma = "00";
            else if (achterKomma < 10) achterKomma = "0" + achterKomma;
            if (saldo > -100 && saldo < 0) return "€-0," + achterKomma;
            return "€" + (saldo - (saldo % 100)) / 100 + "," + achterKomma;
        }

        function zetSucces(bericht) {
            $("#waarschuwing").empty();
            $("#waarschuwing").append("<div class='alert alert-success' role='alert'>" + bericht + "</div>")
        }

        function zetWaarschuwing(bericht) {
            $("#waarschuwing").empty();
            $("#waarschuwing").append("<div class='alert alert-warning   ' role='alert'>" + bericht + "</div>")
        }

        function zetFaal(bericht) {
            $("#waarschuwing").empty();
            $("#waarschuwing").append("<div class='alert alert-danger   ' role='alert'>" + bericht + "</div>")
        }

        function zetInfo(bericht) {
            $("#waarschuwing").empty();
            $("#waarschuwing").append("<div class='alert alert-info   ' role='alert'>" + bericht + "</div>")
        }

        var personen = {};
        var producten = {};

        $.ajax({
            url: "ajax.php",
            method: "POST",
            data: {"personen": "waar"}
        })
            .done(function (data) {
                personen = $.parseJSON(data);
                updateOnKeyPress();
            });

        $.ajax({
            url: "ajax.php",
            method: "POST",
            data: {"producten": "waar"}
        })
            .done(function (data) {
                productenTemp = $.parseJSON(data);
                //console.log(productenTemp);
                var sorteerbaar = [];
                $.each(productenTemp, function () {
                    sorteerbaar.push([this, this.prioriteit]);
                    producten[this.productId] = this;
                });
                sorteerbaar.sort(function (a, b) {
                    return b[1] - a[1];
                });
                $.each(sorteerbaar, function () {
                    zetProductInLijst(this[0])
                });
            });

        function updateOnKeyPress() {
            var item = new RegExp($("#persoonInput").val(), "gi");
            var output = new Array();
            $("#selectieTabel > tbody").empty();
            $.each(personen, function () {

                if (this.bijnaam.match(item) || this.naam.match(item)) {
                    output.push(this);
                    zetInTabel(this);
                }

            });
        }

        $("#keyboardToggle").click(function () {
            $("#keyboardContainer").toggle();
        })


        $("#persoonInput").bind("change keyup", updateOnKeyPress);

        for (i = 0; i < 10; i++) {
            (function (j) {
                $("#knop" + i).click(function () {
                    if ($("#aantalInput")[0].value == "0") resetTeller();
                    $("#aantalInput")[0].value = $("#aantalInput")[0].value + "" + j;
                });
            })(i);
        }

        $("#knopC").click(function () {
            if ($(isNaN("#aantalInput"))[0].value) resetTeller();
            else {
                $("#aantalInput")[0].value = ($("#aantalInput")[0].value - $("#aantalInput")[0].value % 10) / 10;
                if ($("#aantalInput")[0].value == "0") resetTeller();
            }
        })

        $("#knop-").click(function () {
            $("#aantalInput")[0].value = $("#aantalInput")[0].value * -1;
            if ($("#aantalInput")[0].value == "0") {
                resetTeller();
                $("#aantalInput")[0].value = "-";
            }
        })

        $("#knopConfirm").click(function () {
            if (selectedPerson && bestelLijst.length != 0) {
                var result = {};
                result["bestelLijst"] = bestelLijst;
                result["bestelTotaal"] = bestelTotaal();
                result["persoon"] = selectedPerson;
                if (oudeBestelling) result["oudeBestelling"] = oudeBestelling;
                $.ajax({
                    url: "ajax.php",
                    method: "POST",
                    data: {"bestelling": JSON.stringify(result)}
                }).done(function (data) {
                        if (data == "1") {
                            //succes! de bestelling is goed verwerkt
                            cancel();
                        } else {
                            alert("er gaat iets verkeert met de bestelling, hij is niet verwerkt!")
                        }
                    });

            } else if (!selectedPerson) {
                zetFaal("geen geldig persoon geselecteerd");
            } else if (bestelTotaal == 0) {
                zetFaal("geen bestelling");
            }
        })

        $(function () {
            var shift = false,
                capslock = false;

            $('#keyboard li').not('.spacer').click(function () {
                var $this = $(this),
                    character = $this.html().toLowerCase(); // If it's a lowercase letter, nothing happens to this variable

                // Delete
                if ($this.hasClass('delete')) {
                    $("#persoonInput").val($("#persoonInput").val().slice(0, -1)).focus();
                    updateOnKeyPress();
                    return false;
                } else if($this.hasClass('leeg')) {
					 $("#persoonInput").val('').focus();
					updateOnKeyPress();
					return false;
				}

                if ($this.hasClass('space')) character = ' ';

                // Add the character
                $("#persoonInput").val($("#persoonInput").val() + character).focus();
                updateOnKeyPress();
            });
        });

        if (beheer) {
            $.each(producten, function (product) {
                $("#productBeheerLijst").append("<li class='list-group-item' id='productBeheerLijst" + product.productId + "'>" + product.beschrijving + "</li>");
                $("#productBeheerLlijst" + product.productId).click(setProduct(product));
            })

            function setProduct(product) {
                //todo
            }
        }

        $("#knopCancel").click(cancel);

        function cancel() {
            selectedPerson = null;
            oudeBestelling = null;
            resetLijst();
            resetTeller();
            zetInfo("Geen persoon geselecteerd");
            $("#besteLijstBeheerContent tbody").empty();
            $("#besteLijstBeheerLaadPersoon").empty();
            $("#besteLijstBeheerLaadPersoon").append("Laad bestellingen van: -");
            $("#persoonselectieVeld").trigger("click");
        }

        cancel();

        $("#besteLijstBeheerLaadPersoon").click(function () {
            $.ajax({
                url: "ajax.php",
                method: "POST",
                data: {"persoonBestellingen": selectedPerson.socCieId}
            }).done(function (data) {
                    zetOudeBestellingen($.parseJSON(data));
                });
        })

        $("#besteLijstBeheerLaadLaatste100").click(function () {
            $.ajax({
                url: "ajax.php",
                method: "POST",
                data: {"laadLaatste": 100}
            }).done(function (data) {
                    zetOudeBestellingen($.parseJSON(data));
                });
        })
        /**
         * Deze functie zet oude bestellingen in de tab 'bestellingen'.
         * Het voegt functies toe om bestellingen te bewerken op persoon en inhoud.
         * Het geeft tevens de mogelijkheid bestellingen te verwijderen.
         * @param bestellingen een lijst in JSON met allen bestellingen.
         */
        function zetOudeBestellingen(bestellingen) {
            $("#besteLijstBeheerContent tbody").empty();
            $.each(bestellingen, function (item) {
                var bestelling = bestellingen[item];
                var bestel = [];
                for (key in bestelling.bestelLijst) {
                    bestel.push(bestelling.bestelLijst[key] + " " + producten[key].beschrijving);
                }
                bestel = bestel.join(", ");
                $("#besteLijstBeheerContent tbody").append("<tr id='tabelRijBeheerLijst" + item + "'><td>" + personen[bestelling.persoon].naam + "</td><td>"
                    + bestelling.tijd + "</td><td>" + saldoStr(bestelling.bestelTotaal) + "</td><td>" + bestel + "</td>" +
                    "<td><div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'>Opties <span class='caret'></span></button>" +
                    "<ul class='dropdown-menu dropdown-menu-right' role='menu'>" +
                    "<li><a href='#' id='anderePersoon" + item + "'>Zet bestelling op andere persoon</a></li>" +
                    "<li><a href='#' id='bewerkInhoud" + item + "'>Bewerk inhoud bestelling</a></li>" +
                    "<li><a href='#' id='verwijderBestelling" + item + "'>Verwijder bestelling</a></li>" +
                    "</ul></div></td></tr>");

                $("#besteLijstBeheer").trigger("update")

                $("#anderePersoon" + item).click(function () {
                    //todo
                });
                $("#bewerkInhoud" + item).click(function () {
                    zetWaarschuwing("U bewerkt een bestelling!");
                    //console.log(bestelling);
                    bestelLijst = bestelling.bestelLijst;
                    oudeBestelling = bestelling;
                    selectedPerson = personen[bestelling.persoon]
                    resetTeller();
                    zetBestelLijstGoed();
                    $("#invoerveld").trigger("click");
                });
                $("#verwijderBestelling" + item).click(function () {
                    if (confirm("Weet u zeker dat u de bestelling van " + bestel + " op: " + bestelling.tijd + " wilt verwijderen?")) {
                        $.ajax({
                            url: "ajax.php",
                            method: "POST",
                            data: {"verwijderBestelling": JSON.stringify(bestelling)}
                        }).done(function (data) {
                                if (data = "1") {
                                    $("#tabelRijBeheerLijst" + item).remove();
                                }
                            });
                    }
                });
            })
        }

        $('.input-daterange').datepicker({
            format: "dd MM yyyy",
            language: "nl",
            autoclose: true,
            todayHighlight: true,
            beforeShowDay: function (date){
                if (date.getMonth() == (new Date()).getMonth())
                    switch (date.getDate()){
                        case 4:
                            return {
                                tooltip: 'Example tooltip',
                                classes: 'active'
                            };
                        case 8:
                            return false;
                        case 12:
                            return "green";
                    }
            }
        });
		
});