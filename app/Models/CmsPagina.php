<?php

namespace App\Models;

use CsrDelft\model\security\LoginModel;
use Illuminate\Database\Eloquent\Builder;


/**
 * App\Models\CmsPagina
 *
 * @property string $naam
 * @property string $titel
 * @property string $inhoud
 * @property string $laatst_gewijzigd
 * @property string $rechten_bekijken
 * @property string $rechten_bewerken
 * @property int $inline_html
 * @method static Builder|CmsPagina whereInhoud($value)
 * @method static Builder|CmsPagina whereInlineHtml($value)
 * @method static Builder|CmsPagina whereLaatstGewijzigd($value)
 * @method static Builder|CmsPagina whereNaam($value)
 * @method static Builder|CmsPagina whereRechtenBekijken($value)
 * @method static Builder|CmsPagina whereRechtenBewerken($value)
 * @method static Builder|CmsPagina whereTitel($value)
 * @mixin \Eloquent
 */
class CmsPagina extends BaseModel
{
    protected $table = 'cms_paginas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'naam';

    public $timestamps = false; // TODO make true

    protected $fillable = ['naam', 'titel', 'inhoud', 'laatst_gewijzigd', 'rechten_bekijken', 'rechten_bewerken', 'inline_html'];

    public function magRechtenWijzigen() {
        return LoginModel::mag('P_ADMIN');
    }
}
