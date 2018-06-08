<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Wrapper voor de (hele oude) scripts in htdocs/tools
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class ToolsController extends Controller
{
    public function naamsuggesties()
    {
        return $this->wrap('naamsuggesties.php');
    }

    public function lijst()
    {
        return $this->wrap('lijst.php');
    }

    public function dragobject()
    {
        return $this->wrap('dragobject.php');
    }

    public function naamlink()
    {
        return $this->wrap('naamlink.php');
    }

    public function interesse()
    {
        return $this->wrap('interesse.php');
    }

    private function wrap($filename) {
        ob_start();
        /** @noinspection PhpIncludeInspection */
        require base_path() . '/htdocs/tools/' . $filename;
        $responseBody = ob_get_clean();

        $response = new Response();
        $response->setContent($responseBody);

        return $response;
    }
}
