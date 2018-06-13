<?php

use DaveJamesMiller\Breadcrumbs\BreadcrumbsGenerator;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

Breadcrumbs::register('home', function(BreadcrumbsGenerator $breadcrumbs) {
    $breadcrumbs->push('Home', route('thuis'));
});

Breadcrumbs::register('account.bewerken', function (BreadcrumbsGenerator $breadcrumbs, $account) {
    $breadcrumbs->push('Home', route('thuis'));
    $breadcrumbs->push($account->profiel->getNaam(), route('profiel', '/' . $account->uid)); // profiel is route oude stijl.
    $breadcrumbs->push('Bewerken');
});

Breadcrumbs::register('eetplan.overzicht', function (BreadcrumbsGenerator $breadcrumbs) {
    $breadcrumbs->push('Home', route('thuis'));
    $breadcrumbs->push('Eetplan');
});

Breadcrumbs::register('eetplan.noviet', function (BreadcrumbsGenerator $breadcrumbs, $naam) {
    $breadcrumbs->push('Home', route('thuis'));
    $breadcrumbs->push('Eetplan', route('eetplan.overzicht'));
    $breadcrumbs->push($naam);
});

Breadcrumbs::register('eetplan.huis', function (BreadcrumbsGenerator $breadcrumbs, $huis) {
    $breadcrumbs->push('Home', route('thuis'));
    $breadcrumbs->push('Eetplan', route('eetplan.overzicht'));
    $breadcrumbs->push($huis);
});

Breadcrumbs::register('eetplan.beheer', function (BreadcrumbsGenerator $breadcrumbs) {
    $breadcrumbs->push('Home', route('thuis'));
    $breadcrumbs->push('Eetplan', route('eetplan.overzicht'));
    $breadcrumbs->push('Beheer');
});