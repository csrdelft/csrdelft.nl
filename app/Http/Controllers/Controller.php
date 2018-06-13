<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $acl = [];

    public function __construct()
    {
        $this->setAcl($this->acl);
    }

    protected function setAcl(array $acl)
    {
        foreach ($acl as $route => $item) {
            $this->middleware('mag:' . $item, ['only' => $route]);
        }
    }
}
