# Filestructuur uitleg
* `bin` -> Scripts die worden gerunt
* `config` -> Configuratie bestanden (voornamelijk `yaml` bestanden)
* `data` -> Bevat de database dump, foto's en andere informatie van de stek
* `db/migrations` -> Database migraties waarmee de database aangepast wordt. Migraties kunnen ook een oude staat terugrollen
* `docker` -> Docker images voor development aan de stek via Docker
* `docs` -> **Niet echt relevant.** Oude afbeeldingen van architecture design
* `htdocs` -> Bestanden die de webserver inlaadt. Alle submodules zijn hier ook te vinden (Bijvoorbeeld `bakeliet` folder hoort bij [www.csrdelft.nl/bakeliet](https://csrdelft.nl/bakeliet/)). `htdocs/index.php` wordt als eerst geladen voor iedere pagina van de stek.
* `lib` -> Alle PHP bestanden
* `node_modules` -> **Niet echt relevant.** Modules die NPM (package manager) gebruikt
* `resources` -> Template pagina's die worden gecompiled en vervolgens in `htdocs/dist` gezet
* `sessie` -> **Niet echt relevant.** Map waar sessiebestanden in worden opgeslagen. 
* `sql` -> **Niet echt relevant.** Oude database migraties. Alleen fixturesSQL_mininal.sql blijkt nog een beetje interessant te zijn
* `templates` -> Symfony Twig templates. Vervolg op blade templates
* `tests` -> Tests voor de stek. Wordt niet zoveel mee gedaan en er wordt vrij weinig getest.
* `vendor` -> **Niet echt relevant.** Modules die Composer (package manager) gebruikt.