<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', 'ThuisController@index')->name('thuis');

Route::middleware(['auth', 'mag:P_LOGGED_IN'])->group(function () {
    Route::prefix('account')->group(function () {
        Route::get('endsu', 'Accountcontroller@endsu')->name('acocunt.endsu');
    });

    Route::prefix('account/{account}')->group(function () {
        Route::any('bewerken', 'AccountController@bewerken')->name('account.bewerken');
        Route::get('su', 'AccountController@su')->name('account.su')->middleware('mag:P_ADMIN');
    });


    Route::prefix('tools')->group(function () {
        Route::get('naamsuggesties', 'ToolsController@naamsuggesties');
        Route::any('naamlink', 'ToolsController@naamlink');
        Route::post('interesse', 'ToolsController@interesse')->name('interesseformulier');
        Route::post('dragobject', 'ToolsController@dragobject');
    });


    Route::prefix('eetplan')->group(function () {
        Route::get('', 'EetplanController@overzicht')->name('eetplan.overzicht');
        Route::get('noviet/{profiel}', 'EetplanController@noviet')->name('eetplan.noviet');
        Route::get('huis/{id}', 'EetplanController@huis')->name('eetplan.huis');

        Route::prefix('beheer')->middleware('mag:P_ADMIN,commissie:NovCie')->group(function () {
            Route::get('', 'EetplanController@beheer')->name('eetplan.beheer');
            Route::post('nieuw', 'EetplanController@action_nieuw')->name('eetplan.nieuw');
            Route::post('verwijderen', 'EetplanController@action_verwijderen')->name('eetplan.verwijderen');
            Route::post('huis', 'EetplanController@dt_huis');
            Route::post('huis/{status}', 'EetplanController@action_toggle_huis')->name('eetplan.beheer.huis');
            Route::post('bekendhuis', 'EetplanController@dt_bekendhuis');
            Route::post('bekendhuis/toevoegen', 'EetplanController@action_bekendhuis_toevoegen');
            Route::post('bekenden', 'EetplanController@dt_bekenden');
            Route::get('bekenden/toevoegen', 'EetplanController@form_bekenden_toevoegen');
            Route::post('bekenden/toevoegen', 'EetplanController@action_bekenden_toevoegen');
            Route::post('bekendehuizen', 'EetplanController@dt_bekendehuizen');
            Route::get('bekendehuizen/toevoegen', 'EetplanController@form_bekendehuizen_toevoegen');
            Route::post('bekendehuizen/toevoegen', 'EetplanController@action_bekendehuizen_toevoegen');
            Route::get('bekendehuizen/zoeken', 'EetplanController@action_bekendehuizen_zoeken');
        });
    });

    Route::any('/ledenlijst', 'ToolsController@lijst')->name('ledenlijst');

    Route::any('/leden{any}', 'LegacyController@profiel')->name('leden')->where('any', '.*');
    Route::any('/profiel{any}', 'LegacyController@profiel')->name('profiel')->where('any', '.*');

    Route::any('/forum{any}', 'LegacyController@forum')->name('forum')->where('any', '.*');
    Route::any('/agenda{any}', 'LegacyController@agenda')->name('agenda')->where('any', '.*');
    Route::any('/documenten{any}', 'LegacyController@documenten')->name('documenten')->where('any', '.*');
    Route::any('/bibliotheek{any}', 'LegacyController@bibliotheek')->name('bibliotheek')->where('any', '.*');
    Route::any('/mededelingen{any}', 'LegacyController@mededelingen')->name('mededelingen')->where('any', '.*');
    Route::any('/peilingen{any}', 'LegacyController@peilingen')->name('peilingen')->where('any', '.*');

    Route::any('/google{any}', 'LegacyController@google')->name('google')->where('any', '.*');

    Route::any('/groepen{any}', 'LegacyController@groepen')->name('groepen')->where('any', '.*');
    Route::any('/maaltijden{any}', 'LegacyController@maalcie')->name('maaltijden')->where('any', '.*');
    Route::any('/corvee{any}', 'LegacyController@maalcie')->name('corvee')->where('any', '.*');
    Route::any('/fiscaat{any}', 'LegacyController@fiscaat')->name('fiscaat')->where('any', '.*');
    Route::any('/courant{any}', 'LegacyController@courant')->name('courant')->where('any', '.*');
    Route::any('/fotoalbum{any}', 'LegacyController@fotoalbum')->name('fotoalbum')->where('any', '.*');
    Route::any('/gesprekken{any}', 'LegacyController@gesprekken')->name('gesprekken')->where('any', '.*');
    Route::any('/rechten{any}', 'LegacyController@rechten')->name('rechten')->where('any', '.*');
    Route::any('/instellingenbeheer{any}', 'LegacyController@instellingenbeheer')->name('instellingenbeheer')->where('any', '.*');
    Route::any('/instellingen{any}', 'LegacyController@instellingen')->name('instellingen')->where('any', '.*');
    Route::any('/menubeheer{any}', 'LegacyController@menubeheer')->name('menubeheer')->where('any', '.*');

    Route::get('/pagina/bewerken/{pagina}', 'CmsPaginaController@bewerken')->name('cmspagina.opslaan')->where('naam', '.*');
    Route::post('/pagina/bewerken/{pagina}', 'CmsPaginaController@opslaan')->name('cmspagina.bewerken')->where('naam', '.*');
});

Route::get('/{pagina}', 'CmsPaginaController@route')->name('cmspagina.bekijken')->where('pagina', '.*');
