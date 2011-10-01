<?php
/**
 * Description of BbbQuery
 *
 * @author estheban
 */

class BbbQuery {
    private $function;
    private $parameters = array();

    public function __construct($function = "", $queryString = "") {
        $this->setFunction($function);

        $req = explode('&',$queryString);

        foreach($req as $key => $elem) {
            $elem = explode("=",$elem);
            if(count($elem) > 1)
                $this->parameters[] = $elem;
        }
    }

    public function __toString() {
        $str = "";
        foreach($this->getParameters() as $param) {
            $str.= $param[0]."=".$param[1]."&";
        }
        return substr($str,0,-1);
    }

    protected function setFunction($function) {
        $this->function = $function;
    }
    
    public function getFunction() {
        return $this->function;
    }

    public function getParameter($key) {
        foreach($this->parameters as $param) {
            if($param[0] == $key)
                return $param[1];
        }
        return null;
    }

    public function getParameters() {
        $parameters = $this->parameters;
        for($i = count($parameters)-1; $i>0; $i--) {
            if($parameters[$i][0] == 'checksum')
                unset($parameters[$i]);
        }
        return $parameters;
    }
/*
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }
 * 
 */
}
?>
