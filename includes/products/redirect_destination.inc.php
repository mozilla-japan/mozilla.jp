<?php
class RedirectDestination{
    private $destination, $rewrite;
    
    public function __construct($dest, $rewrite_required=false){
        $this->destination = $dest;
        $this->rewrite = $rewrite_required;
    }

    public function destination(){
        return $this->destination;
    }
    public function rewrite(){
        return $this->rewrite;
    }
}
?>