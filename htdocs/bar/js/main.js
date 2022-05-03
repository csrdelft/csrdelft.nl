/**
 * Dit script voegt functionaliteit toe aan het barsysteem.
 */
$(function () {

    /*************************************************************************************************/
    /* Clock
    /*************************************************************************************************/

    $("#clock").each(function () {

        function addLeading(number) {

            if ((number + "").length == 2)
                return number;

            return "0" + number;

        }

        function update() {

            var currentDate = new Date();
            $("#clock").html(
				addLeading(currentDate.getHours()) + ":" +
				addLeading(currentDate.getMinutes()) + ":" +
				addLeading(currentDate.getSeconds())
            );

        }

        update();
        setInterval(update, 1000);

    });

    // Voeg CSRF token toe aan alle ajax requests
    $.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
        if (!options.crossDomain) {
            jqXHR.setRequestHeader('X-BARSYSTEEM-CSRF', $('meta[property=\'X-BARSYSTEEM-CSRF\']').attr('content'));
        }
    });


    /*************************************************************************************************/
    /* End Clock
     /*************************************************************************************************/

		var now = new Date();
		var isStartkamp = new Date("2021-08-27") < now && new Date("2021-08-30") > now;
		// Feestmodus: niemand mag rood komen te staan
		var isFeestmodus = new Date("2022-04-21") < now && new Date("2022-04-23") > now;

    /**
     * Deze persoon is geselecteerd, dit wordt oa. gebruikt bij de invoer van bestellingen, inleg en laden van de bestellingen van die persoon.
     */
    var selectedPerson;

    /**
     * Dit is de lijst met bestellingen.
     * @type {{}} deze lijst mapt het productId naar het aantal dat er besteld zijn. Bijv 1=>2, dit betekent bijvoorbeeld dat er twee bier is besteld.
     */
    var bestelLijst = {};

    /**
     * Hierin zit de oude bestelling, in het geval we een bestelling verwerken.
     */
    var oudeBestelling;


    $.extend($.tablesorter.themes.bootstrap, {
        // these classes are added to the table. To see other table classes available,
        // look here: http://twitter.github.com/bootstrap/base-css.html#tables
        table: 'table',
        caption: 'caption',
        header: 'bootstrap-header', // give the header a gradient background
        footerRow: '',
        footerCells: '',
        icons: '', // add "icon-white" to make them white; this icon class is added to the <i> in the header
        sortNone: 'bootstrap-icon-unsorted',
        sortAsc: 'glyphicon glyphicon-chevron-up',     // includes classes for Bootstrap v2 & v3
        sortDesc: 'glyphicon glyphicon-chevron-down', // includes classes for Bootstrap v2 & v3
        active: '', // applied when column is sorted
        hover: '', // use custom css here - bootstrap class may not override it
        filterRow: '', // filter row class
        even: '', // odd row zebra striping
        odd: ''  // even row zebra striping
    });
    $("#besteLijstBeheer").tablesorter({
        theme: "bootstrap",
        widthFixed: true,
        sortList: [
            [1, 1]
        ],
        headerTemplate: '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!

        // widget code contained in the jquery.tablesorter.widgets.js file
        // use the zebra stripe widget if you plan on hiding any rows (filter widget)
        widgets: [ "uitheme", "zebra" ]

    });

    function zetInTabel(personen) {
        var tbody = $("<tbody />");
        $.each(personen, function(key, persoon) {
            var newRow = $("<tr id='persoon" + persoon.socCieId + "'><td>" + persoon.bijnaam + "</td><td>" + persoon.naam + "</td><td class=\"" + (persoon.saldo < 0 ? "bg-danger" : "bg-success") +"\">" + saldoStr(persoon.saldo) + "</td></tr>");
            newRow.click(function () {
                cancel();
                $.ajax({
                    url: "ajax.php",
                    method: "POST",
                    data: {"saldoSocCieId": persoon.socCieId}
                }).done(function (data) {
                    persoon.saldo = 1 * data;
                });
                selectedPerson = persoon;

                zetBericht("Geselecteerde persoon: " + persoon.naam + " | Saldo: " + saldoStr(persoon.saldo), persoon.saldo >= 0 ? 'success' : 'danger');

                $("#invoerveld").trigger("click");
                $("#persoonInput").val("");
                updateOnKeyPress();
                resetTeller();
                resetLijst();
            });
            tbody.append(newRow);
        });
        $("#selectieTabel").html(tbody);
    }

    function resetLijst() {
        bestelLijst = {};
        zetBestelLijstGoed();
    }

    function resetTeller() {
        $("#aantalInput")[0].value = null;
    }

    function zetProductInLijst(product) {

		if(product.beheer == 0 || beheer) {
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
			});
		}

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
            $("#bestelLijst" + (teller % 3 + 1)).append("<li class=" + key + ">" + aantal + "&#09" + producten[key].beschrijving + "</li>");
            teller++;
        }

        // Add onclick remove
        $("#bestelLijstDiv li").click(function () {

            var key = $(this).attr("class");
            delete bestelLijst[key];

            zetBestelLijstGoed();

        });

        if (oudeBestelling) {
			var before = parseInt(selectedPerson.saldo) + parseInt(oudeBestelling.bestelTotaal);
            $("#huidigSaldo").html(saldoStr(before));
            $("#nieuwSaldo").html(saldoStr(before - totaal));
        } else if (selectedPerson) {
            $("#huidigSaldo").html(saldoStr(selectedPerson.saldo));
            $("#nieuwSaldo").html(saldoStr(selectedPerson.saldo - totaal));
        } else {
            $("#huidigSaldo").html("<span>-</span>");
            $("#nieuwSaldo").html("<span>-</span>");
        }
        $("#totaalBestelling").html(saldoStr(totaal));
    }

    function saldoStr(saldo) {
        var achterKomma = Math.abs(saldo % 100);
        if (achterKomma == 0) achterKomma = "00";
        else if (achterKomma < 10) achterKomma = "0" + achterKomma;
        if (saldo > -100 && saldo < 0) return "€-0," + achterKomma;
        var string = "€" + (saldo - (saldo % 100)) / 100 + "," + achterKomma;
		return string.replace("€-", "-€");
    }

    function zetBericht(bericht, type) {
        $("#waarschuwing").removeClass().addClass("alert alert-" + type).html(bericht);
    }

    var personen = {};
    var producten = {};

    function laadPersonen() {

        $.ajax({
            url: "ajax.php",
            method: "POST",
            data: {"personen": "waar"},
            dataType: "json"
        }).done(function (data) {
           personen = data;
           updateOnKeyPress();

           var pl = $(".personList");
           if (pl.size() > 0) {
               var html = '';
               var sortedPersonen = [];
               for (var key in personen) {
                   if (personen.hasOwnProperty(key)) {
                       sortedPersonen.push(personen[key]);
                   }
               }
               sortedPersonen = sortedPersonen.filter(function (persoon) {
                   return persoon.deleted == 0
               }).sort(function (a, b) {
                   return (a.naam || "ZZ").localeCompare(b.naam || "ZZ");
               });
               $.each(sortedPersonen, function () {
                   html += '<option value="' + this.socCieId + '">' + this.naam + '</option>';
               });
               pl.html(html);
            }
        });

    }

	function laadProducten() {
		$.ajax({
			url: "ajax.php",
			method: "POST",
			data: {"producten": "waar"},
			dataType: "json"
		})
			.done(function (data) {
				producten = {};
				productenTemp = data;
				var sorteerbaar = [];
				$.each(productenTemp, function () {
					sorteerbaar.push([this, this.prioriteit]);
					producten[this.productId] = this;
				});
				sorteerbaar.sort(function (a, b) {
					return b[1] - a[1];
				});
				$("#bestelKnoppenLijst").empty();
				$.each(sorteerbaar, function () {
                    if(this[0].status == 1)
					    zetProductInLijst(this[0]);
				});
                setProductenBeheer();
                // Zet producten in search pSearchContent
                var html = '';
                $.each(sorteerbaar, function() {
                    html += '<div class="checkbox"><label><input type="checkbox" name="productType" value="'+this[0].productId+'">'+ this[0].beschrijving +'</label></div>';
                });
                $("#pSearchContent").html(html);
			});
	} laadProducten();

    function updateOnKeyPress() {
        var item = new RegExp($("#persoonInput").val(), "gi");
		var orderPersonen = [];
		$.each(personen, function (key, val) {
            if(val.deleted == 0)
			    orderPersonen.push( { key: key, value: val } );
		});
		orderPersonen.sort(function(a, b) { return b.value.recent - a.value.recent });
        var personenForTable = [];
        $.each(orderPersonen, function () {
            if (this.value.bijnaam.match(item) || this.value.naam.match(item)) {
                personenForTable.push(this.value);
            }
        });
        zetInTabel(personenForTable);
    }

    $("#keyboardToggle").click(function () {
        $("#keyboardContainer").toggle();
    });

    $("#persoonInput").bind("change keyup", updateOnKeyPress);

    /*************************************************************************************************/
    /* Order keypad
    /*************************************************************************************************/

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
    });

    function isOudLid(person) {

        return person.status != 'S_LID' && person.status != 'S_GASTLID' && person.status != 'S_NOVIET';

    }

    $("#knopConfirm").each(function() {

		var $this = $(this);

		// Set current submiting state on false
		var submitting = false;
		var warningGiven = false;

		$(this).click(function () {

			var oudlid = isOudLid(selectedPerson);

			var toRed;
			if(oudeBestelling)
				toRed = parseInt(selectedPerson.saldo) + parseInt(oudeBestelling.bestelTotaal) - bestelTotaal() < 0;
			else
				toRed = selectedPerson.saldo - bestelTotaal() < 0;

			if(bestelTotaal() <= 0 || selectedPerson.status == 'S_NOBODY' || beheer)
				toRed = false;

			if (!oudlid && isStartkamp)
				toRed = false;

			// Hack
			var emptyOrder = true;
			for(key in bestelLijst) {
				emptyOrder = false
			}

			if(oudlid && toRed) {

				zetBericht("Oudleden kunnen niet rood staan, inleg vereist!", "danger");

			} else if (isFeestmodus && toRed) {
				zetBericht("Lid heeft niet genoeg saldo. Stuur naar PIN-rij.", "danger");
			} else if (selectedPerson && !emptyOrder) {

				if(!warningGiven && toRed) {

					$this.addClass("loading");

					zetBericht("Laat lid inleggen. Saldo wordt negatief.", "danger");
					setTimeout(function() {
						warningGiven = true;
						$this.removeClass("loading");
					}, 3000);

				} else {

					// Set submitting state on true
					submitting = true;
					$this.addClass("loading");
					$this.prop("disabled", true);

					var result = {};
					result["bestelLijst"] = bestelLijst;
					result["bestelTotaal"] = bestelTotaal();
					result["persoon"] = selectedPerson;

					// If update of old order us that data
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
							zetBericht("Er gaat iets verkeerd met de bestelling, hij is niet verwerkt!", "danger");
						}
					}).always(function() {

						// After AJAX always set submitting on false
						submitting = false;
						warningGiven = false;
						$this.removeClass("loading");
						$this.prop("disabled", false);

					});

				}

			} else if (!selectedPerson) {
				zetBericht("Geen geldig persoon geselecteerd!", "danger");
			} else if (emptyOrder) {
				zetBericht("Geen bestelling ingevoerd!", "danger");
			}

		});

    });

    /*************************************************************************************************/
    /* Keyboard
    /*************************************************************************************************/

	$('#keyboard li').not('.spacer').click(function () {
		var $this = $(this),
			character = $this.html().toLowerCase(); // If it's a lowercase letter, nothing happens to this variable

		// Delete
		if ($this.hasClass('delete')) {
			$("#persoonInput").val($("#persoonInput").val().slice(0, -1)).focus();
			updateOnKeyPress();
			return false;
		} else if ($this.hasClass('leeg')) {
			$("#persoonInput").val('').focus();
			updateOnKeyPress();
			return false;
		}

		if ($this.hasClass('space')) character = ' ';

		// Add the character
		$("#persoonInput").val($("#persoonInput").val() + character).focus();
		updateOnKeyPress();
	});

    $("#knopCancel").click(function() {

		// Hack
		var emptyOrder = true;
		for(key in bestelLijst) {
			emptyOrder = false
		}

		if(emptyOrder || confirm("Weet je zeker dat je de bestelling wilt afbreken?"))
			cancel();

	});

    function cancel() {
        selectedPerson = null;
        oudeBestelling = null;
        resetLijst();
        resetTeller();
        zetBericht("Geen persoon geselecteerd", "info");
        $("#besteLijstBeheerContent tbody").empty();
        $("#besteLijstBeheerLaadPersoon").html("Laad bestellingen van: -");
        $("#persoonselectieVeld").trigger("click");
		laadPersonen();
    } cancel();

    $("#krijgBestellingen").click(function () {
        var aantal = "alles";
        if ($("#eenPersoon").hasClass("btn-primary")) {
            aantal = selectedPerson.socCieId;
        }
        var productType = $("#pSearchContent").find("input").serializeArray();
        $.ajax({
            url: "ajax.php",
            method: "POST",
            data: {"laadLaatste": "waar", "begin": $("#beginDatum").val(), "eind": $("#eindDatum").val(), "aantal": aantal, "productType": productType },
			dataType: "json"
        }).done(function (data) {
            zetOudeBestellingen(data);
        });
    });

    /**
     * Deze functie zet oude bestellingen in de tab 'bestellingen'.
     * Het voegt functies toe om bestellingen te bewerken op persoon en inhoud.
     * Het geeft tevens de mogelijkheid bestellingen te verwijderen.
     * @param bestellingen een lijst in JSON met allen bestellingen.
     */
    function zetOudeBestellingen(bestellingen) {
		$("#besteLijstBeheerContent tbody").empty();
        $.each(bestellingen, function (item) {
            var bestelling = this;
            var bestel = [];
            for (key in bestelling.bestelLijst) {
				if(producten[key].prijs != -1)
					bestel.push(bestelling.bestelLijst[key] + " " + producten[key].beschrijving);
				else
					bestel.push(saldoStr(bestelling.bestelLijst[key]) + " " + producten[key].beschrijving);
            }
            var bestelUL = '<ul><li>' + bestel.join('</li><li>') + '</li></ul>';
            var bestelComma = bestel.join(", ");

			var deleted = parseInt(bestelling.deleted) == 1;

			$("#besteLijstBeheerContent tbody").append("<tr class=\"" + (deleted ? "removed" : "") + "\" id='tabelRijBeheerLijst" + item + "'><td>" + personen[bestelling.persoon].naam + "</td><td>"
                + bestelling.tijd + "</td><td>" + saldoStr(bestelling.bestelTotaal) + "</td><td>" + bestelUL + "</td>" +
                "<td>" + (bestelling.oud == 1 ? "" : "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'>Opties <span class='caret'></span></button>" +
                "<ul class='dropdown-menu dropdown-menu-right' role='menu'>" +
                "<li><a href='#' id='undoRemove" + item + "'>Draai verwijdering terug</a></li>" +
                //"<li><a href='#' id='anderePersoon" + item + "'>Zet bestelling op andere persoon</a></li>" +
                "<li><a href='#' id='bewerkInhoud" + item + "'>Bewerk inhoud bestelling</a></li>" +
                "<li><a href='#' id='verwijderBestelling" + item + "'>Verwijder bestelling</a></li>" +
                "</ul></div>") +"</td></tr>");

            $("#undoRemove" + item).click(function (e) {
				e.preventDefault();
                if (confirm("Weet u zeker dat u de bestelling van " + bestelComma + " op: " + bestelling.tijd + " wilt terugzetten?")) {
                    $.ajax({
                        url: "ajax.php",
                        method: "POST",
                        data: {"undoVerwijderBestelling": JSON.stringify(bestelling)}
                    }).done(function (data) {
                        if (data = "1") {
                            $("#tabelRijBeheerLijst" + item).removeClass("removed");
							laadPersonen();
                        }
                    });
                }
            });
            /*$("#anderePersoon" + item).click(function (e) {
				e.preventDefault();
                //todo
            });*/
            $("#bewerkInhoud" + item).click(function (e) {
				e.preventDefault();
                zetBericht("U bewerkt een bestelling!", "warning");
                bestelLijst = bestelling.bestelLijst;
                oudeBestelling = bestelling;
                selectedPerson = personen[bestelling.persoon]
                resetTeller();
                zetBestelLijstGoed();
                $("#invoerveld").trigger("click");
            });
            $("#verwijderBestelling" + item).click(function (e) {
				e.preventDefault();
                if (confirm("Weet u zeker dat u de bestelling van " + bestelComma + " op: " + bestelling.tijd + " wilt verwijderen?")) {
                    $.ajax({
                        url: "ajax.php",
                        method: "POST",
                        data: {"verwijderBestelling": JSON.stringify(bestelling)}
                    }).done(function (data) {
                        if (data = "1") {
                            $("#tabelRijBeheerLijst" + item).addClass("removed");
							laadPersonen();
                        }
                    });
                }
            });
        });
		$("#besteLijstBeheer").trigger("update");
    }

    $("#eenPersoon").click(function () {
        $("#allePersonen").removeClass("btn-primary");
        $("#eenPersoon").addClass("btn-primary");
    });

    $("#allePersonen").click(function () {
        $("#allePersonen").addClass("btn-primary");
        $("#eenPersoon").removeClass("btn-primary");
    })

    $(".clearKruisje").click(function () {
        $(this).prev("input").val("").datepicker("show");
		$(".datepicker .clear").click();
    })

    $('.input-daterange').datepicker({
        format: "dd MM yyyy",
        language: "nl",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true
    });

    /*************************************************************************************************/
    /* Beheer
    /*************************************************************************************************/

    /* Add product */
    $("#addProduct").submit(function(e) {

        e.preventDefault();
        var $this = $(this);

        $.ajax({
            url: $(this).attr("action"),
            method: $(this).attr("method"),
            data: $(this).serializeArray(),
            success: function(data) {

                if(data == "1") {
                    zetBericht("Product toegevoegd.", "success");
                    laadProducten();
                    $this.trigger("reset");
                } else {
                    zetBericht("Er is iets misgegeaan met het toevoegen van een product!", "danger");
                }

            },
            error: function() {
                zetBericht("Er is iets misgegeaan met het toevoegen van een product!", "danger");
            }
        });

    });

    function setProductenBeheer() {

        $("#productBeheerLijst").empty();

        $.each(producten, function (id) {

            var product = producten[id];
            $("#productBeheerLijst").append("<a href='#' class='list-group-item' id='productBeheerLijst" + product.productId + "'>" + product.beschrijving + "</a>");

            $("#productBeheerLijst" + product.productId).click(function() {

                $(this).parent().find("> a").removeClass("active");
                $(this).addClass("active");
                setProduct(product)

            });

            function setProduct(product) {

                $("#editProduct").each(function() {

                    var html = '';
                    html += '<h2>Wijzigen van \'' + product.beschrijving + '\'</h2>';

                    html += '<div class="row">';

                    html += '<div class="col-xs-6">';
                    html += '<h3>Update prijs</h3>';
                    html += '<form id="editPrice" method="post" action="ajax.php" class="form-horizontal" role="form">';
                        html += '<input type="hidden" name="productId" value="' + product.productId + '" />';
                        html += '<input type="hidden" name="q" value="updatePrice" />';

                        html += '<div class="input-group">';
                            html += '<label class="input-group-addon" for="product' + product.productId + '">Nieuwe prijs in centen</label>';
                            html += '<input id="product' + product.productId + '" name="price" type="text" class="form-control" placeholder="' + producten[product.productId].prijs + '" />';
                            html += '<div class="input-group-btn"><button type="submit" class="btn btn-primary">Prijs aanpassen</button></div>';
                        html += '</div>';
                    html += '</form>';
                    html += '</div>';

                    html += '</div>';

                    html += '<div class="row">';

                    html += '<div class="col-xs-6">';
                    html += '<h3>Update zichtbaarheid</h3>';
                    html += '<form id="editVisibility" method="post" action="ajax.php" class="form-horizontal" role="form">';
                    html += '<input type="hidden" name="productId" value="' + product.productId + '" />';
                    html += '<input type="hidden" name="q" value="updateVisibility" />';

                    html += '<div class="input-group">';
                    html += '<label class="input-group-addon" for="product' + product.productId + 'visibility">Zichtbaarheid</label>';
                    html += '<select id="product' + product.productId + 'visibility" name="visibility" class="form-control">';
                    html += '<option value="1"' + (product.status == 1 ? ' selected="selected"' : '') +'>Zichtbaar</option>';
                    html += '<option value="0"' + (product.status == 0 ? ' selected="selected"' : '') +'>Niet zichtbaar</option>';
                    html += '</select>';
                    html += '<div class="input-group-btn"><button type="submit" class="btn btn-primary">Zichtbaarheid aanpassen</button></div>';
                    html += '</div>';
                    html += '</form>';
                    html += '</div>';

                    html += '</div>';

                    $(this).html(html);

                });

                $("#editPrice").submit(function(e) {

                    e.preventDefault();
                    var $this = $(this);

                    var postdata = $(this).serializeArray();

                    $.ajax({
                        url: $(this).attr("action"),
                        method: $(this).attr("method"),
                        data: postdata,
                        success: function(data) {

                            if(data == "1") {
                                zetBericht("Prijs van '" + product.beschrijving + "' gewijziged.", "success");
                                $this.find("input[name=price]").attr("placeholder", postdata[2].value);
                                $this.trigger("reset");
                                laadProducten();
                            } else {
                                zetBericht("Er is iets misgegeaan met het wijzigen van de prijs!", "danger");
                            }

                        },
                        error: function() {
                            zetBericht("Er is iets misgegeaan met het wijzigen van de prijs!", "danger");
                        }
                    });

                });

                $("#editVisibility").submit(function(e) {

                    e.preventDefault();
                    var $this = $(this);

                    var postdata = $(this).serializeArray();

                    $.ajax({
                        url: $(this).attr("action"),
                        method: $(this).attr("method"),
                        data: postdata,
                        success: function(data) {

                            if(data == "1") {
                                zetBericht("Zichtbaarheid van '" + product.beschrijving + "' gewijziged.", "success");
                                $this.find("input[name=visibility]").attr("placeholder", postdata[2].value);
                                laadProducten();
                            } else {
                                zetBericht("Er is iets misgegeaan met het wijzigen van de zichtbaarheid!", "danger");
                            }

                        },
                        error: function() {
                            zetBericht("Er is iets misgegeaan met het wijzigen van de zichtbaarheid!", "danger");
                        }
                    });

                });

            }

        });

    }

    $("#laadProducten").click(function () {

		$(this).parent().find("> button").addClass("btn-default").removeClass("btn-primary");
		$(this).removeClass("btn-default").addClass("btn-primary");

		$("#productBeheer").removeClass("hidden");
		$("#grootboekInvoer, #persoonBeheer, #tools").addClass("hidden");

    });

	$("#laadPersonen").click(function() {

		$(this).parent().find("> button").addClass("btn-default").removeClass("btn-primary");
		$(this).removeClass("btn-default").addClass("btn-primary");

		$("#persoonBeheer").removeClass("hidden");
		$("#grootboekInvoer, #productBeheer, #tools").addClass("hidden");

	});

	$("#addPerson").submit(function(e) {

		e.preventDefault();
		var $this = $(this);

		$.ajax({
			url: $(this).attr("action"),
			method: $(this).attr("method"),
			data: $(this).serializeArray(),
			success: function(data) {

				if(data == "1") {
					zetBericht("Persoon toegevoegd.", "success");
					laadPersonen();
					$this.trigger("reset");
				} else {
					zetBericht("Er is iets misgegeaan met het toevoegen van een persoon!", "danger");
				}

			},
			error: function() {
				zetBericht("Er is iets misgegeaan met het toevoegen van een persoon!", "danger");
			}
		});

	});

	$("#updatePerson").submit(function(e) {

		e.preventDefault();
		var $this = $(this);

		$.ajax({
			url: $(this).attr("action"),
			method: $(this).attr("method"),
			data: $(this).serializeArray(),
			success: function(data) {

				if(data == "1") {
					zetBericht("Persoon aangepast.", "success");
					laadPersonen();
					$this.trigger("reset");
				} else {
					zetBericht("Er is iets misgegeaan met het aanpassen van een persoon!", "danger");
				}

			},
			error: function() {
				zetBericht("Er is iets misgegeaan met het aanpassen van een persoon!", "danger");
			}
		});

	});

	$("#removePerson").submit(function(e) {

		e.preventDefault();
		var $this = $(this);

		if(confirm("Weet je zeker dat je " + $(".personList :selected", this).html() + " wilt verwijderen?")) {

			$.ajax({
				url: $(this).attr("action"),
				method: $(this).attr("method"),
				data: $this.serializeArray(),
				success: function(data) {

					if(data == "1") {
						zetBericht("Persoon verwijderd.", "success");
						laadPersonen();
						$this.trigger("reset");
					} else {
						zetBericht("Er is iets misgegeaan met het verwijderen van een persoon!", "danger");
					}

				},
				error: function() {
					zetBericht("Er is iets misgegeaan met het verwijderen van een persoon!", "danger");
				}
			});

		}

	});

	$("#laadGrootboekInvoer").click(function() {

		var button = $(this);

		$.ajax({
			url: "ajax.php?q=grootboek",
			method: "GET",
			dataType: "json",
			success: function(data) {

				button.parent().find("> button").addClass("btn-default").removeClass("btn-primary");
				button.removeClass("btn-default").addClass("btn-primary");

				var nietInTotaal = ["PIN", "Overgemaakt", "Contant", "Cent"]

				var html = [];

				$.each(data, function(weeknummer) {

					addhtml = '';

					addhtml += '<h2>' + this.title + '</h2>';
					addhtml += '<table class="table table-striped"><thead><tr><th>Soort</th><th>Prijs</th></tr></thead><tbody>';

					var total = 0;
					$.each(this.content, function() {
						var inTotaal = !nietInTotaal.includes(this.type) || weeknummer >= 202011;

						if (inTotaal) {
							total += parseFloat(this.total);
							addhtml += '<tr><td>' + this.type + '</td><td>' + saldoStr(this.total) + '</td></tr>';
						} else {
							addhtml += '<tr><td>' + this.type + ' <strong>(niet in totaal)</strong></td><td>' + saldoStr(this.total) + '</td></tr>';
						}
					});

					addhtml += '<tr><td>Week totaal</td><td>' + saldoStr(total) + '</td></tr>';
					addhtml += '</tbody></table>';

					html.push(addhtml);

				});

				$("#productBeheer, #persoonBeheer, #tools").addClass("hidden");
				$("#grootboekInvoer").html(html.reverse()).removeClass("hidden");

			}
		});

	});

	$("#laadTools").click(function() {

		var button = $(this);

		$.ajax({
			url: "ajax.php?q=tools",
			method: "GET",
			dataType: "json",
			success: function(data) {

				button.parent().find("> button").addClass("btn-default").removeClass("btn-primary");
				button.removeClass("btn-default").addClass("btn-primary");

				$("#sumSaldi").html(saldoStr(data.sum_saldi.sum));
				$("#sumSaldiLid").html(saldoStr(data.sum_saldi_lid.sum));

                var html = '';
                $.each(data.red, function() {

                    html += '<tr><th>' + this.naam + '</th><td>' + saldoStr(this.saldo) + '</td><td>' + this.email + '</td></tr>';

                });
                $("#red").html(html);

                var html = '';
                $.each(data.red, function() {

                    if(isOudLid(this))
                        html += '<tr><th>' + this.naam + '</th><td>' + saldoStr(this.saldo) + '</td><td>' + this.email + '</td></tr>';

                });
                $("#red-old").html(html);

				$("#productBeheer, #persoonBeheer, #grootboekInvoer").addClass("hidden");
				$("#tools").removeClass("hidden");

			}
		});

	});

    /**
    /* Toggle search on selective products
     */
    $("#pSearch").click(function() {
        if($(this).hasClass("btn-primary")) {
            $("#pSearchContent").removeClass("hidden");
        } else {
            $("#pSearchContent").addClass("hidden").find("input").prop("checked", false);
        }
        $(this).toggleClass("btn-primary");
    });

});
