<?php
namespace pocketcore;

use pocketmine\plugin\PluginBase;

class PocketCore extends PluginBase {
    
    protected $bridge;
    
    public function onEnable(){
        $this->bridge = new Bridge($this);
        $this->bridge->connect();
        if(Bridge::$connected){
            $this->getLogger()->info("Connected");
            $this->bridge->send(array('api_key' => ''));
        } else {
            $this->getLogger()->info("Failed to connect!");
        }
    }
    
    public function getBridge() { # Scallar hinting is syntax error :(
        return $this->bridge;
    }
    
}