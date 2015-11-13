<?php
namespace SmartBot\Bot;

use SmartBot\Bot;

abstract class ListenerAbstract {
    /**
     * The SmartBot instance
     * @var Bot
     */
    protected $_smartBot;
    
    protected $_config = array();
    
    protected $_responders = array();
    
    final public function __construct( Bot $smartBot ){
        $this -> _smartBot = $smartBot;
        
        $this -> _loadConfig();
    }
    
    protected function responder($name){
        $responder = $this -> _smartBot -> responder($name);
        
        if( false == array_key_exists($name, $this -> _responders ) )
        {
            // append config to responder
            foreach( $this -> _config as $section => $data ){
                
                if( preg_match( sprintf('/^responder:%s$/i', $name ), $section ) ) 
                    $responder -> add($data['msg']);
                
                if( preg_match( sprintf('/^responder:%s:(.*)$/i', $name ), $section, $matches ) ) 
                    $responder -> add($data['msg'], $matches[1]);

            }

            $this -> _responders[$name] = $responder;
        }
        
        return $responder;
    }
    
    
    final private function _loadConfig(){
        $name = array_slice(explode('\\', get_class($this)),-1)[0];

        $iniFile = __DIR__.'/Listener/Config/'.$name .'.ini';

        if( false == file_exists($iniFile) )
            throw new Exception(sprintf('Strings INI file not found for listener : %s', get_class($this) ));
        
            
        $this -> _config = parse_ini_file($iniFile, true);
            
        return $this;
    }
}