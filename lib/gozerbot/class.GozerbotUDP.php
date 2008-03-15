<?php

define('GOZERBOT_MAX_UDP_PACKET', 400);

class GozerbotUDP
{
    /**
     * The host on which gozerbot UDP listens.
     * @var string
     */
    private $host     = 'localhost';

    /**
     * The port on which gozerbot UDP listens.
     * @var int
     */
    private $port     = 5500;

    /**
     * The gozerbot UDP password.
     * @var string
     */
    private $passwd   = NULL;

    /**
     * The target channel / nick to send the messages to.
     * @var string
     */
    private $target   = NULL;

    /**
     * The encryption key. Set this to NULL if no encryption is used
     * or a 16 character key string if encryption is used. This one
     * should match the key that is configured in the gozerbot UDP settings.
     * @var string
     */
    private $cryptkey = NULL;

    public function GozerbotUDP()
    {
    }

    /**
     * This method sets the gozerbot UDP host to connect to.
     *
     * @param string $host
     *     The host to connect to.
     */
    public function setHost($host)     { $this->host     = $host;   }

    /**
     * This method sets the gozerbot UDP port to connect to.
     *
     * @param int $port
     *     The port to connect to.
     */
    public function setPort($port)     { $this->port     = $port;   }

    /**
     * This method sets the gozerbot UDP password.
     *
     * @param string $pass
     *     The password to use.
     */
    public function setPassword($pass) { $this->password = $pass;   }

    /**
     * This method sets the default target for gozerbot UDP messages.
     *
     * @param string $target
     *     The #channel or nickname to use as the default target.
     */
    public function setTarget($target) { $this->target   = $target; }

    /**
     * This method sets the encryption key that is used for encrypting
     * the traffic between the gozerbot UDP server and this client.
     *
     * @param mixed $key
     *     The encryption key to used (16 characters long) or NULL to
     *     disable message encryption.
     */
    public function setCryptKey($key)  { $this->cryptkey = $key;    }

    /**
     * Convert binary string data to hex data.
     *
     * @param string $bin
     *     The binary string to convert.
     *
     * @return string
     *     The hexadecimal representation of the string.
     */
    private function bin2hex($str)
    {
        $p = unpack('H*', $str);
        return $p[1];
    }

    /**
     * Convert hex data to binary string data.
     *
     * @param string $hex
     *     The hexadecimal data string to convert.
     *
     * @return string
     *     The binary string.
     */
     private function hex2bin($hex)
     {
         return pack('H*', $hex);
     }

    /**
     * This method sends a message to the gozerbot UDP server.
     *
     * @param string $msg
     *     The message to send.
     *
     * @param string $target
     *     The target to send the message to or NULL to use the default
     *     target that is set using {@link setTarget()}.
     */
    public function send($msg, $target = NULL)
    {
        // Determine the target to send the message to.
        $target = $target === NULL ? $this->target : $target;
        if ($target === NULL) die(
            'GozerbotUDP::send(): no target, please set a default target ' .
            'using the setTarget() method or provide a target to the ' .
            'send() method.'
        );

        // Initialize crypter code.
        require_once(dirname(__FILE__).'/class.AES.php');
        $crypt = new AES(AES::AES128);
        $hexkey = $this->bin2hex($this->cryptkey);

        // Initialize UDP socket for sending the message.
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        // The prefix, for sending the password and target nick/channel.
        $prefix = $this->password . ' ' . $target . ' ';

        // Strip the message.
        $msg = preg_replace('/\n/', ' ', $msg);
        $msg = preg_replace('/\s+/', ' ', $msg);
        $msg = trim($msg);

        // Gozerbot only accepts 400 bytes of data on the UDP socket.
        // If we need to send more data, then split up the data in
        // multiple messages.
        $len_max = GOZERBOT_MAX_UDP_PACKET - 16 - strlen($prefix);
        $msgs = explode("\n", chunk_split($msg, $len_max, "\n"));

        $id_max = count($msgs) - 2;
        if ($id_max < 0) die(
            'GozerbotUDP::send(): prefix + 8 spare bytes exceeds the maximum ' .
            'gozerbot UDP packet size of ' . GOZERBOT_MAX_UDP_PACKET . ' bytes!'
        );

        foreach ($msgs as $id => $msg)
        {
            if ($msg == '') continue;

            // This is what we reserved 16 chars for above. We could do 8, but
            // then gozerbot will kick in (+1) markers, overwriting our "..."
            $msg = $prefix .
                   ($id > 0 ? '... ' : '') .
                   $msg .
                   ($id < $id_max ? ' ...': '');

            // Handle crypting if a crypt key is set.
            if ($this->cryptkey !== NULL)
            {
                if (strlen($this->cryptkey) != 16) die(
                    'GozerbotUDP::send(): the cryptkey must be 16 characters ' .
                    'long. The current length is ' . strlen($this->cryptkey)
                );

                while (strlen($msg) % 16) $msg .= "\0";
                $parts = explode("\n", chunk_split($msg, 16, "\n"));
                $len = 16 * count($parts);
                foreach ($parts as $id => $part)
                {
                    if ($part == '') continue;
                    $part = $crypt->encrypt($this->bin2hex($part), $hexkey);
                    $part = $this->hex2bin($part);
                    while (strlen($part) % 16) $part .= "\0";
                    $parts[$id] = $part;
                }
                $msg = implode('', $parts);
            }
            else
            {
                $len = strlen($msg);
            }

            socket_sendto($sock, $msg, $len, 0, $this->host, $this->port);
        }
    }
}

?>
