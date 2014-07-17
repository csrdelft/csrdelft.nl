(function($) {
    /**
     * Dutch Package.
     * Created by: Ruben Verboon
     */
    $.fn.bootstrapValidator.i18n = $.extend(true, $.fn.bootstrapValidator.i18n, {
        base64: {
            'default': 'Geef alstublieft een geldig base 64 encoded'
        },
        between: {
            'default': 'Geef alstublieft een waarde tussen %s en %s',
            notInclusive: 'Geef alstublieft een waarde strict tussen %s en %s'
        },
        callback: {
            'default': 'Geef alstublieft een geldig value'
        },
        choice: {
            'default': 'Geef alstublieft een geldig value',
            less: 'Kies alstublieft minimaal %s opties',
            more: 'Kies alstublieft maximaal %s opties',
            between: 'Kies alstublieft %s - %s opties'
        },
        creditCard: {
            'default': 'Geef alstublieft een geldig creditcardnummer'
        },
        cusip: {
            'default': 'Geef alstublieft een geldig CUSIP-nummer'
        },
        cvv: {
            'default': 'Geef alstublieft een geldig CVV-nummer'
        },
        date: {
            'default': 'Geef alstublieft een geldige datum'
        },
        different: {
            'default': 'Geef alstublieft een andere datum'
        },
        digits: {
             'default': 'Geef alstublieft alleen maar cijfers'
        },
        ean: {
            'default': 'Geef alstublieft een geldig EAN-nummer'
        },
        emailAddress: {
            'default': 'Geef alstublieft een geldig e-mailadres'
        },
        file: {
            'default': 'Kies alstublieft een geldig bestand'
        },
        greaterThan: {
            'default': 'Geef alstublieft een waarde groter dan of gelijk aan %s',
            notInclusive: 'Geef alstublieft een waarde groter dan %s'
        },
        grid: {
            'default': 'Geef alstublieft een geldig GRId-nummer'
        },
        hex: {
            'default': 'Geef alstublieft een geldig hexadecimal-nummer'
        },
        hexColor: {
            'default': 'Geef alstublieft een geldige hex-kleur'
        },
        iban: {
            'default': 'Geef alstublieft een geldig IBAN-nummer',
            countryNotSupported: 'De landcode %s is niet ondersteund',
            country: 'Geef alstublieft een geldig IBAN-nummer in %s',
            countries: {
                AD: 'Andorra',
                AE: 'Verenigde Arabische Emiraten',
                AL: 'Albanië',
                AO: 'Angola',
                AT: 'Oostenrijk',
                AZ: 'Azerbeidzjan',
                BA: 'Bosnië en Herzegovina',
                BE: 'België',
                BF: 'Burkina Faso',
                BG: 'Bulgarije',
                BH: 'Bahrein',
                BI: 'Burundi',
                BJ: 'Benin',
                BR: 'Brazilië',
                CH: 'Zwitserland',
                CI: 'Ivoorkust',
                CM: 'Kameroen',
                CR: 'Costa Rica',
                CV: 'Kaapverdië',
                CY: 'Cyprus',
                CZ: 'Tsjechië',
                DE: 'Duitsland',
                DK: 'Denemarken',
                DO: 'Dominicaanse Republiek',
                DZ: 'Algerije',
                EE: 'Estland',
                ES: 'Spanje',
                FI: 'Finland',
                FO: 'Faeröer',
                FR: 'Frankrijk',
                GB: 'Verenigd Koninkrijk',
                GE: 'Georgië',
                GI: 'Gibraltar',
                GL: 'Groenland',
                GR: 'Griekenland',
                GT: 'Guatemala',
                HR: 'Kroatië',
                HU: 'Hongarije',
                IE: 'Ierland',
                IL: 'Israël',
                IR: 'Iran',
                IS: 'IJsland',
                IT: 'Italië',
                JO: 'Jordanië',
                KW: 'Koeweit',
                KZ: 'Kazachstan',
                LB: 'Libanon',
                LI: 'Liechtenstein',
                LT: 'Litouwen',
                LU: 'Luxemburg',
                LV: 'Letland',
                MC: 'Monaco',
                MD: 'Moldavië',
                ME: 'Montenegro',
                MG: 'Madagaskar',
                MK: 'Macedonië',
                ML: 'Mali',
                MR: 'Mauritanië',
                MT: 'Malta',
                MU: 'Mauritius',
                MZ: 'Mozambique',
                NL: 'Nederland',
                NO: 'Noorwegen',
                PK: 'Pakistan',
                PL: 'Polen',
                PS: 'Palestina',
                PT: 'Portugal',
                QA: 'Qatar',
                RO: 'Roemenië',
                RS: 'Servië',
                SA: 'Saoedi-Arabië',
                SE: 'Zweden',
                SI: 'Slovenië',
                SK: 'Slowakije',
                SM: 'San Marino',
                SN: 'Senegal',
                TN: 'Tunesië',
                TR: 'Turkije',
                VG: 'Britse Maagdeneilanden'
            }
        },
        id: {
            'default': 'Geef alstublieft een geldig identificatienummer',
            countryNotSupported: 'De landcode %s is niet ondersteund',
            country: 'Geef alstublieft een geldig %s identificatienummer',
            countries: {
                BA: 'Bosnisch',
                BG: 'Bulgaars',
                BR: 'Braziliaans',
                CH: 'Zwitsers',
                CL: 'Chileens',
                CZ: 'Tsjechisch',
                DK: 'Deens',
                EE: 'Estlands',
                ES: 'Spaans',
                FI: 'Fins',
                HR: 'Kroatisch',
                IE: 'Iers',
                IS: 'IJslands',
                LT: 'Litouws',
                LV: 'Lets',
                ME: 'Montenegrijns',
                MK: 'Macedonisch',
                NL: 'Nederlands',
                RO: 'Roemeens',
                RS: 'Servisch',
                SE: 'Zweeds',
                SI: 'Slovenisch',
                SK: 'Slowakisch',
                SM: 'San Marino',
                ZA: 'Zuid-Afrikaans'
            }
        },
        identical: {
            'default': 'Geef alstublieft dezelfde waarde'
        },
        imei: {
            'default': 'Geef alstublieft een geldig IMEI-nummer'
        },
        integer: {
            'default': 'Geef alstublieft een geldig nummer'
        },
        ip: {
            'default': 'Geef alstublieft een geldig IP-adres',
            ipv4: 'Geef alstublieft een geldig IPv4-adres',
            ipv6: 'Geef alstublieft een geldig IPv6-adres'
        },
        isbn: {
            'default': 'Geef alstublieft een geldig ISBN-nummer'
        },
        isin: {
            'default': 'Geef alstublieft een geldig ISIN-nummer'
        },
        ismn: {
            'default': 'Geef alstublieft een geldig ISMN-nummer'
        },
        issn: {
            'default': 'Geef alstublieft een geldig ISSN-nummer'
        },
        lessThan: {
            'default': 'Geef alstublieft een waarde minder dan of gelijk aan %s',
            notInclusive: 'Geef alstublieft een waarde minder dan %s'
        },
        mac: {
            'default': 'Geef alstublieft een geldig MAC-adres'
        },
        notEmpty: {
            'default': 'Geef alstublieft een waarde'
        },
        numeric: {
            'default': 'Geef alstublieft een geldig zwevendekommagetal'
        },
        phone: {
            'default': 'Geef alstublieft een geldig telefoonnummer',
            countryNotSupported: 'De landcode %s is niet ondersteund',
            country: 'Geef alstublieft een geldig telefoonnummer in %s',
            countries: {
                GB: 'het Verenigd Koninkrijk',
                US: 'de Verenigde Staten'
            }
        },
        regexp: {
            'default': 'Geef alstublieft een getal wat overeenkomt met het patroon'
        },
        remote: {
            'default': 'Geef alstublieft een geldige waarde'
        },
        rtn: {
            'default': 'Geef alstublieft een geldig RTN-nummer'
        },
        sedol: {
            'default': 'Geef alstublieft een geldig SEDOL-nummer'
        },
        siren: {
            'default': 'Geef alstublieft een geldig SIREN-nummer'
        },
        siret: {
            'default': 'Geef alstublieft een geldig SIRET-nummer'
        },
        step: {
            'default': 'Geef alstublieft een geldig stap van %s'
        },
        stringCase: {
            'default': 'Geef alstublieft alleen kleine letters',
            upper: 'Geef alstublieft alleen hoofdletters'
        },
        stringLength: {
            'default': 'Geef alstublieft een waarde met een geldige lengte',
            less: 'Geef alstublieft minder dan %s tekens',
            more: 'Geef alstublieft minder dan %s tekens',
            between: 'Geef alstublieft een waarde tussen %s en %s tekens lang'
        },
        uri: {
            'default': 'Geef alstublieft een geldige URI'
        },
        uuid: {
            'default': 'Geef alstublieft een geldig UUID-nummer',
            version: 'Geef alstublieft een geldig UUID-versie %s nummer'
        },
        vat: {
            'default': 'Geef alstublieft een geldig BTW-nummer',
            countryNotSupported: 'De landcode %s is niet ondersteund',
            country: 'Geef alstublieft een geldig %s BTW-nummer',
            countries: {
                AT: 'Australisch',
                BE: 'Belgisch',
                BG: 'Bulgaars',
                CH: 'Zwitsers',
                CY: 'Cyprisch',
                CZ: 'Tsjechisch',
                DE: 'Duits',
                DK: 'Deens',
                EE: 'Ests',
                ES: 'Spaans',
                FI: 'Fins',
                FR: 'Frans',
                GB: 'Verenigd Koninkrijk',
                GR: 'Grieks',
                EL: 'Grieks',
                HU: 'Hongaars',
                HR: 'Kroatisch',
                IE: 'Iers',
                IT: 'Italiaans',
                LT: 'Litouws',
                LU: 'Luxemburgs',
                LV: 'Lets',
                MT: 'Maltaas',
                NL: 'Nederlands',
                NO: 'Noors',
                PL: 'Pools',
                PT: 'Portugees',
                RO: 'Roemeens',
                RU: 'Russisch',
                RS: 'Servisch',
                SE: 'Swedish',
                SI: 'Slovenian',
                SK: 'Zweeds'
            }
        },
        vin: {
            'default': 'Geef alstublieft een geldig VIN-nummer'
        },
        zipCode: {
            'default': 'Geef alstublieft een geldige postcode',
            countryNotSupported: 'De landcode %s is niet ondersteund',
            country: 'Geef alstublieft een geldige %s',
            countries: {
                'CA': 'Canadese postcode',
                'DK': 'Deense postcode',
                'GB': 'postcode uit het Verenigd Koninkrijk',
                'IT': 'Italiaanse postcode',
                'NL': 'Nederlandse postcode',
                'SE': 'Zwitserse postcode',
                'SG': 'postcode uit Singapore',
                'US': 'postcode uit de VS'
            }
        }
    });
}(window.jQuery));
