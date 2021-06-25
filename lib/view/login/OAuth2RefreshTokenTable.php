<?php


namespace CsrDelft\view\login;


use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;

class OAuth2RefreshTokenTable extends DataTable
{

	public function __construct()
	{
		parent::__construct(RefreshToken::class, "/session/oauth2-refresh-token");
		$this->deleteColumn('accessToken');
		$this->addColumn('client', 'expiry');
		$this->setOrder(['expiry' => 'desc']);

		$this->addColumn('expiry', 'scopes', null, CellRender::DateTime());

		$this->addRowKnop(new DataTableRowKnop("/session/oauth2-refresh-token-revoke/:identifier", "Revoke", "delete"));
	}

}
