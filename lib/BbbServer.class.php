<?php

/**
 * Description of BbbServer
 *
 * @author estheban
 */
class BbbServer {
    //put your code here
    private $url;
    private $securitySalt;

    public function __construct($securitySalt, $url = '"') {
        $this->securitySalt = $securitySalt;
        $this->url = $url;
    }

    public function getSecuritySalt() {
        return $this->securitySalt;
    }

    public function getUrl() {
        return $this->url;
    }
}
?>
