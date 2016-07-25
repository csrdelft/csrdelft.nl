<?php

class GoogleCallbackView
{
    private $code;
    private $error;
    private $state;

    public function __construct($state, $code, $error)
    {
        $this->code = $code;
        $this->error = $error;
        $this->state = urldecode($state);
    }

    public function view()
    {
        $google_redirect_uri = CSR_ROOT . '/google/callback';

//setup new google client
        $client = new Google_Client();
        $client->setApplicationName('Stek');
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri($google_redirect_uri);
        $client->setAccessType('offline');
        $client->setScopes('https://www.google.com/m8/feeds');


//google response with contact. We set a session and redirect back
        if ($this->code) {
            $_SESSION['google_token'] = $this->code;
            $_SESSION['google_access_token'] = $client->authenticate($this->code);
            redirect($this->state);
        }

        if ($this->error) {
            setMelding("Verbinding met Google niet geaccepteerd", 2);
            $state = substr(strstr($this->state, 'addToGoogleContacts', true), 0, -1);

            redirect($state);
        }
    }
}