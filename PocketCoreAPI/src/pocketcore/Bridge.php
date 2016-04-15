<?php
namespace pocketcore;

class Bridge {
    
    const API_SERVER_ADDR = 'localhost';
    const API_SERVER_PORT = 27095;
    
    private $connection;
    public static $connected = false;
    
    public static $responses = [];
    
    protected $socket;
    
    public function __construct(PocketCore $plugin){
        $this->plugin = $plugin;
        
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        try {
            if ($socket === false) {
                throw new \Exception(socket_strerror(socket_last_error()), 0);
            } else {
                $plugin->getLogger()->debug('Socket created');
            }
            $this->socket = $socket;
        } catch (\Exception $ex) {
            $plugin->getLogger()->critical("Failed to create socket. ('".$ex->getMessage()."')");
        }
    }
    
    public function connect(){
        if($this->socket){
            try {
                $result = socket_connect($this->socket, self::API_SERVER_ADDR, self::API_SERVER_PORT);
                if ($result === false) {
                    throw new \Exception(socket_strerror(socket_last_error()), 0);
                } else {
                    $this->plugin->getLogger()->info("Connected to API server.");
                    self::$connected = true;
                }
            } catch (\Exception $ex){
                $this->plugin->getLogger()->critical("Failed to connect to API server. ('".$ex->getMessage()."')");
            }
        }
        return false;
    }
    
    public function send(array $data, $process = true){
        
        $msg = json_encode($data);
        socket_write($this->socket, $msg, strlen($msg));
        
        $out = $this->waitResponse();
        
        if($process === true) $this->processPong($out);
        return $out;
    }
    
    public function waitResponse($response = "") {
           $response = false;
           $tries = 3;
           while(true){
               $response = socket_read($this->socket, 2024);
               if($tries >= 3 or $response) break;
               $tries++;
           }
        return $response;
        }
    
    public function processPong($data){
        if($data = json_decode($data)){
            var_dump($data);
            switch(TRUE){
                case isset($data->response):
                    $this->getPlugin()->getLogger()->info($data->response);
                return true;
                break;
            
            case isset($data->return):
                return $data->return;
                break;
            default:
                $this->getPlugin()->getLogger()->debug('Server returned weird respond');
                break;
            }
        }
    }
    
    public function respondCodeToText(){
        
    }
    
    public function getConnection(){
        return $this->connection;
    }
    
    public function getPlugin(){
        return $this->plugin;
    }
    
    public function __destruct(){
        socket_close($this->socket);
        $this->getPlugin()->getLogger()->debug('Socket closed');
    }
}