<?php
namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\GoogleSync;
use CsrDelft\model\entity\GoogleToken;
use CsrDelft\model\GoogleTokenModel;
use CsrDelft\model\security\LoginModel;
use function CsrDelft\redirect;
use function CsrDelft\setMelding;
use Google_Client;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Class GoogleController.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property GoogleTokenModel $model
 */
class GoogleController extends AclController
{
    public function __construct($query)
    {
        parent::__construct($query, GoogleTokenModel::instance());
        $this->acl = array(
            'callback' => 'P_LOGGED_IN'
        );
    }

    public function performAction(array $args = array())
    {
        $this->action = $this->getParam(2);
        $args = array(
            'state' => $this->hasParam('state') ? $this->getParam('state') : null,
            'code' => $this->hasParam('code') ? $this->getParam('code') : null,
            'error' => $this->hasParam('error') ? $this->getParam('error') : null,
        );
        return parent::performAction($args);
    }

    public function callback($state, $code, $error)
    {
        if ($code) {
            $client = GoogleSync::createGoogleCLient();
            $access_token = $client->fetchAccessTokenWithAuthCode($code);

            $token = new GoogleToken();
            $token->uid = LoginModel::getUid();
            $token->token = json_encode($access_token);

            if ($this->model->exists($token)) {
                $this->model->update($token);
            } else {
                $this->model->create($token);
            }

            redirect(urldecode($state));
        }

        if ($error) {
            setMelding('Verbinding met Google niet geaccepteerd', 2);
            $state = substr(strstr($state, 'addToGoogleContacts', true), 0, -1);

            redirect($state);
        }

        throw new Exception('Geen error en geen code van Google gekregen.');
    }
}
