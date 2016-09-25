<?php

require_once 'view/JsonResponse.class.php';

/**
 * JsonLijstResponse.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class JsonLijstResponse extends JsonResponse {
    public function view() {
        http_response_code($this->code);
        header('Content-Type: application/json');

        echo "[\n";
        $response = array();
        foreach ($this->model as $entity) {
            $response[] = $this->getJson($entity);
        }
        echo implode(",\n", $response);
        echo "]";
    }
}
