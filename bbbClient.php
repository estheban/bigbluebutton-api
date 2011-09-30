<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of bbbClient
 *
 * @author estheban
 */
class bbbClient {
    private $server;
    private $securitySalt;
    private $user;

    public function __contruct($server, $securitySalt, $user = "bigbluebutton") {
        $server;
        $this->server = $server;
        $this->securitySalt = $securitySalt;
        $this->user = $user;
    }

    protected function crypt($func, $str) {
        $str = $func.$str.$this->securitySalt;
        $crypt = sha1($str);
        return $crypt;
    }

    protected function generateQueryString($func, $params) {
        $str = "";
        $max = count($params);

        foreach($params as $key => $elem) {
            $str.= $key."=".urlencode($elem);
            $max--;
            if($max >0)
                $str.="&";
        }
        //$str.= "&checksum=".$this->crypt($func, $str);
        return $str;
    }

    protected function generateChecksum($func, $params) {
        return $this->crypt($func, $this->generateQueryString($func, $params));
    }

    public function query($func, $params = array("random"=>"asdf")) {
        $queryStr = $this->generateQueryString($func, $params)."&checksum=".$this->generateChecksum($func, $params);
        try {
            $result =  @file_get_contents("http://".$this->server."/".$this->user."/api/".$func."?".$queryStr);
        } catch(Exception $e) { }
        if(!$result) {
            $result = '<response>
            <returncode>FAILED</returncode>
            <messageKey></messageKey>
            <message></message>
        </response>';
        }

        return $result;
    }

    public function isValidQuery($func, $params) {
        if(array_key_exists('checksum', $params)) {
            $checksum = $params['checksum'];
            unset($params['checksum']);
            var_dump($params);
            if($checksum == $this->generateChecksum($func, $params)) {
                return true;
            }
        }
        return false;
    }
}
?>
