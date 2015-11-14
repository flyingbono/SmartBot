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
    
    private $_defaultListenerClass = 'SmartBot\Bot\Listener\EnUSListener';
    
    private $_di;
    
    /**
     * My brain
     * 
     * @var Brain
     */
    protected $_brain;
    
    protected $_dataPath;
        
    protected $_contexts = array();
    
    protected $_caller = null;
    
    protected $_handleDirs = array();
    
    protected $_listenersDirs = array();
    protected $_listenerInstances = array();
    protected $_listeners = array();
    
    protected $_responders = array();
    
    public function __construct( $dataPath, array $options = array() ){
                
        
        $builder = new ContainerBuilder;
        $builder -> useAnnotations(true);
        $builder -> addDefinitions( __DIR__.'/Di/Config.php');
        
        $this -> _di    = $builder->build();       
        $this -> _dataPath  = realpath($dataPath);
        
        if( false == is_dir( $this -> _dataPath )) 
            throw new Exception('SmartBot : data path doesn\'t exists');
        
        $test = $this -> _dataPath.'/'.uniqid();
        @file_put_contents( $test, 'SmartBot test');
        if (false == file_exists($test) )
            throw new Exception('SmartBot : data path is not writtable');
        
        @unlink($test);
              
        
        $this -> _di -> set('Bot', $this);
        $this -> _di -> set('DI', $this -> _di);
        $this -> _brain = $this -> _di -> get('Brain' );
        
        // Create the acquire responder
        $this -> _responders['acquire'] = $this -> _di -> get('Responder\Acquire');
        
        // Add the  main listener
        $this -> addListenerDir(__DIR__.'/Bot/Listener' );
        
        // Initialize the brain
        $this -> _brain -> initialize();
        
        // Load options
        $this -> _loadOptions($options);
        
        // Load acquired memory
        $this -> _brain -> load();
        
        // Check if there is at least 1 listener
        if( count( $this -> _listeners ) == 0 ) {
            // add a default listener
            $listener = new $this -> _defaultListenerClass($this);
            $listener -> initialize();
        }

        
    }
    
    protected function _loadOptions( array $options = array() ){
        foreach( $options as $key => $option ){
            switch(strtolower($key)){
                
                case 'listener':
                    $listener = new $option($this);
                    $listener -> initialize();
                    break;
                    
                case 'innate':
                    $items = include $option;
                    $this -> getBrain() -> getMemory() -> addInnateItems($items);
                    break;
                    
                case 'context':
                    $this -> addContext($option);
                    break;
                    
                case 'caller':
                    $this -> setCaller($option);
                    break;
            }
        }
        
        return $this;
    }
    
    public function setInnateMemory( $memoryFile ){
        
        if( false == file_exists($memoryFile) ) {
            error_log(sprintf('SmartBot : innate memory file "%s"does not exists', $memoryFile ) );
            return $this;
        }
        
        if( false == is_readable($memoryFile) )
            throw new Exception('SmartBot : innate memory file is not readable');
        
        
            
            
    }
    
    public function learn( $what, $value ){
        $this -> getBrain() -> learn($what, $value);
        return $this;
    }
    
    public function getDataPath(){
        return $this -> _dataPath;
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
        
        $this -> _listenerInstances[] = $listener;
        return $this;
    }
    

    
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
    
    public function talk( $input, $callerUid = null, $callback = null ){
        
        if( true == is_null($callerUid) )
            $callerUid = $this -> getCaller();
        
        if( true == is_null($callerUid) )
            return 'Who is talking to me ??';
            
        $this -> setCaller($callerUid);
            
        $output = $this ->  getBrain() -> input( $input );

       if( false === is_null($callback) && is_callable($callback) )
           return $callback($input, $output );
        
        return $output;
    }
    
}