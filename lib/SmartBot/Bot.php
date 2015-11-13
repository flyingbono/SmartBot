<?php
namespace SmartBot;

use SmartBot\Bot\Brain;
use SmartBot\Bot\Exception;
use SmartBot\Bot\Context;
use SmartBot\Bot\Utils;
use SmartBot\Bot\Responder;
use SmartBot\Bot\ListenerAbstract;

use DI\ContainerBuilder;


class Bot {
    
    
    private $_di;
    
    /**
     * My brain
     * 
     * @var Brain
     */
    protected $_brain;
    
    protected $_path;
    
    protected $_language = array();
    
    protected $_contexts = array();
    
    protected $_caller = null;
    
    protected $_handleDirs = array();
    
    public function __construct( $path, $listenerClass = 'SmartBot\Bot\Listener\EnUSListener' ){
                
        $builder = new ContainerBuilder;
        $builder -> useAnnotations(true);
        $builder -> addDefinitions( __DIR__.'/Di/Config.php');
        
        $this -> _di    = $builder->build();       
        $this -> _path  = realpath($path);
        
        if( false == is_dir( $this -> _path )) 
            throw new Exception('SmartBot : path doesn\'t exists');
        
        $test = $this -> _path.'/'.uniqid();
        @file_put_contents( $test, 'SmartBot test');
        if (false == file_exists($test) )
            throw new Exception('SmartBot : path is not writtable');
        
        @unlink($test);
              
        
        $this -> _di -> set('Bot', $this);
        $this -> _di -> set('DI', $this -> _di);
        $this -> _brain = $this -> _di -> get('Brain' );
        
        // Create the acquire responder
        $this -> _responders['acquire'] = $this -> _di -> get('Responder\Acquire');
        
        // Add the  main listener
        $this -> addListenerDir(__DIR__.'/Bot/Listener' );
        $listener = new $listenerClass($this);
        $listener -> initialize();
        
        
        $this -> _brain -> initialize();
    }
    
    
    public function learn( $what, $value ){
        $this -> getBrain() -> learn($what, $value);
        return $this;
    }
    
    public function getPath(){
        return $this -> _path;
    }
    
    public function getLanguage(){
        return $this -> _language;
    }
    
    public function getHandleDirs(){
        return $this -> _handleDirs;
    }
    
    /**
     * 
     * @return Brain
     */
    public function getBrain(){
        return $this -> _di -> get('Brain');
    }
    
    public function getCaller(){
        return $this -> _caller;
    }
    
    public function addContext( $name ){
        
        if( is_array($name) ){
            foreach( $name as $context )
                $this -> addContext( $context );
            
                return $this;
        }
            
        if( $this -> hasContext( $name ) )
            return $this;
        
        $this -> _contexts[] = $name;
        return $this;
    }
    
    public function hasContext( $context ) {
        
        return in_array($context, $this -> _contexts );
        
    }
    
    public function getCallerId( $address ){
        return preg_replace('/caller:([^:]+):.*/i', '\\1', $address );
    }
    
    public function getCallerProperty( $callerId, $property ){
        $address = sprintf('Caller:%s:%s', $callerId, $property );
        
        $item = $this -> getBrain() -> getMemory() -> search($address, true );
        
        return $item -> getValue();
        
    }
    
    
    public function addListener( ListenerAbstract $listener ){
        
        $this -> _listenerPlugins[] = $listener;
        return $this;
    }
    
    protected $_listenersDirs = array();
    protected $_listenerPlugins;
    protected $_listeners;
    
    public function addListenerDir( $dir ){
        $this ->_listenersDirs[] = $dir;
    }
    
    public function getListeners(){
        return $this -> _listeners;
    }
    
    public function getResponders(){
        return $this -> _responders;
    }
    
    public function listen( array $regex, $responders ){
        
        if( true == is_array($responders) ){
            foreach( $responders as $responder )
                $this -> listen($regex, $responder );
            
            return $this;
        }
          
        $responder = $responders;
        
        
        foreach( $regex as $expr ){
            
//             error_log(sprintf('%s is listening for %s', get_class($responder), $expr));
            
            $this -> _listeners[uniqid()] = array(
                    'regex' => $expr,
                    'responder' => $responder );
        }
        
        return $this;
    }
    
    protected $_responders = array();
    
    /**
     * 
     * @param string $name
     * @return Responder
     */
    public function responder( $name ){
        if( true == array_key_exists($name, $this -> _responders) )
            return $this -> _responders[$name];
        
          
        $responder = $this -> _di -> make('Responder');
        $this -> _responders[$name] = $responder;  
        
        
        
        return $responder;
            
    }
    
    public function findCaller( $caller ){
        $items = $this -> getBrain() -> getMemory() -> searchSomeone($caller);
        
        
        return $items;  
        
    }
    
    public function getContexts( $types = array()){
        return $this -> _contexts;
    }
    
    public function setCaller( $callerUid ){
        $this -> _caller = $callerUid;
        
        return $this;
    }
    
    public function talk( $message, $callerUid = null ){
        
        if( true == is_null($callerUid) )
            $callerUid = $this -> getCaller();
        
        if( true == is_null($callerUid) )
            return 'Who is talking to me ??';
            
        $this -> setCaller($callerUid);
            
        $output = $this ->  getBrain() -> input( $message );

        return $output;
    }
    
}