<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/flyingbono/SmartBot>.
 */
namespace SmartBot;

use SmartBot\Bot\Brain;
use SmartBot\Bot\Exception;
use SmartBot\Bot\Context;
use SmartBot\Bot\Utils;
use SmartBot\Bot\Responder;
use SmartBot\Bot\ListenerAbstract;

use DI\ContainerBuilder;

/**
 * SmartBot Bot Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Bot
{
    
    /**
     * Dependcy container instance
     * 
     * @var DI\Container
     */
    private $_di;
    
    /**
     * Bot brain instance
     * 
     * @var Brain
     */
    protected $_brain;
    
    /**
     * Bot storage
     * 
     * @var string
     */
    protected $_dataPath;
    
    /**
     * Contexts strings
     * 
     * @var array
     */
    protected $_contexts = [];
    
    /**
     * Bot's caller (who speak with me)
     * 
     * @var string
     */
    protected $_caller = null;
    

    /**
     * Registred listeners dirs
     * 
     * @var array
     */
    protected $_listenersDirs = [];
    
    /**
     * Registred listeners instances
     * 
     * @var array
     */
    protected $_listenerInstances = [];
    
    /**
     * Registred listeners map
     * 
     * @var array
     */
    protected $_listeners = [];
    
    /**
     * Responders
     * 
     * @var array
     */
    protected $_responders = [];
    
    /**
     * Bot Class constructor
     * 
     * @param  string $dataPath
     * @param  array  $options
     * @throws Exception
     */
    public function __construct( $dataPath, array $options = array() ) 
    {
                
        // Loading DI container
        $builder = new ContainerBuilder;
        $builder -> useAnnotations(true);
        $builder -> addDefinitions(__DIR__.'/Di/Config.php');
        
        $this -> _di    = $builder->build();       
        $this -> _dataPath  = $dataPath;
        
        // Test data path
        if (false == is_dir($this -> _dataPath)) { 
            throw new Exception(sprintf('SmartBot : data path "%s" doesn\'t exists', $this -> _dataPath)); 
        }
        
        $test = $this -> _dataPath.'/'.uniqid();
        @file_put_contents($test, 'SmartBot test');
        if (false == file_exists($test) ) {
            throw new Exception(sprintf('SmartBot : data path "%s" is not writtable', $this -> _dataPath)); 
        }
        
        @unlink($test);
              
        // Register objects in DI
        $this -> _di -> set('Bot', $this);
        $this -> _di -> set('DI', $this -> _di);
        $this -> _brain = $this -> _di -> get('Brain');
        
        // Create the acquire responder
        $this -> _responders['acquire'] = $this -> _di -> get('Responder\Acquire');
        
        // Add the  main listener
        $this -> addListenerDir(__DIR__.'/Bot/Listener');
        
        // Initialize the brain
        $this -> _brain -> initialize();
        
        // Load options
        $this -> _loadOptions($options);
        
        // Load acquired memory
        $this -> _brain -> load();
        
        // Check if there is at least 1 listener
        if (count($this -> _listeners) == 0 ) {
            // add a default listener
            $listener = new SmartBot\Bot\Listener\EnUSListener($this);
            $listener -> initialize();
        }
    }
    
    /**
     * Load options passed in the class constructor
     * 
     * @param  array $options
     * @return \SmartBot\Bot Provide a fluent interface
     */
    protected function _loadOptions( array $options = array() )
    {
        foreach ( $options as $key => $option ) {
            switch( strtolower($key) ) {
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
    
    /**
     * Learn something
     * 
     * @param  string $what  Adress of the memory item
     * @param  string $value Value of the item
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function learn( $what, $value ) 
    {
        $this -> getBrain() -> learn($what, $value);
        return $this;
    }
    
    
    /**
     * Get the bot data path
     */
    public function getDataPath() 
    {
        return $this -> _dataPath;
    }
    
    
    /**
     * Get my own brain
     * 
     * @return \SmartBot\Bot\Brain
     */
    public function getBrain() 
    {
        return $this -> _di -> get('Brain');
    }
    
    /**
     * Get the caller
     * 
     * @return string
     */
    public function getCaller()
    {
        return $this -> _caller;
    }
    
    /**
     * Add a bot user-defined context
     * 
     * @param string|array $name The name of the context (ie Humor:Hungry)
     * 
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function addContext( $name )
    {
        
        if (is_array($name) ) {
            foreach ( $name as $context ) {
                $this -> addContext($context); 
            }
            
            return $this;
        }
            
        if ($this -> hasContext($name) ) {
            return $this; 
        }
        
        $this -> _contexts[] = $name;
        
        return $this;
    }
    
    /**
     * Check if the bot is in a particular context
     * 
     * @param  string $context
     * @return boolean
     */
    public function hasContext( $context ) 
    {
        return in_array($context, $this -> _contexts);
    }
    
    /**
     * Get caller ID with the given address
     * 
     * @param  string $address An adress (ie: Caller:#I08098088:name)
     * @return string The caller ID (ie: #I08098088)
     */
    public function getCallerId( $address )
    {
        return preg_replace('/caller:([^:]+):.*/i', '\\1', $address);
    }
    
    /**
     * Get a caller property
     * 
     * @param  string $callerId
     * @param  string $property
     * @return mixed
     */
    public function getCallerProperty( $callerId, $property )
    {
        $address = sprintf('Caller:%s:%s', $callerId, $property);
        
        $item = $this -> getBrain() -> getMemory() -> search($address, true);
        
        return $item -> getValue();
        
    }
    
    /**
     * Add a listener
     * 
     * @param  ListenerAbstract $listener
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function addListener( ListenerAbstract $listener )
    {
        
        $this -> _listenerInstances[] = $listener;
        return $this;
    }
    

    /**
     * Add a listener directory
     * 
     * @param  string $dir
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function addListenerDir( $dir )
    {
        $this ->_listenersDirs[] = $dir;
        return $this;
    }
    
    /**
     * Get listeners
     * 
     * @return array
     */
    public function getListeners()
    {
        return $this -> _listeners;
    }
    
    /**
     * Get responders
     * 
     * @return Responder[]
     */
    public function getResponders()
    {
        return $this -> _responders;
    }
    
    /**
     * Listen regex and map to responders
     * 
     * @param  array                 $regex
     * @param  Responder|Responder[] $responders
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function listen( array $regex, $responders )
    {
        
        if (true == is_array($responders) ) {
            foreach ( $responders as $responder ) {
                $this -> listen($regex, $responder); 
            }
            
            return $this;
        }
          
        $responder = $responders;
        
        foreach ( $regex as $expr ) {
                        
            $this -> _listeners[uniqid()] = array(
                    'regex' => $expr,
                    'responder' => $responder );
        }
        
        return $this;
    }
    
    
    
    /**
     * Get the responder singleton by name
     * 
     * @param  string $name
     * @return \SmartBot\Bot\Responder
     */
    public function responder( $name )
    {
        if (true == array_key_exists($name, $this -> _responders) ) {
            return $this -> _responders[$name]; 
        }
        
        $responder = $this -> _di -> make('Responder');
        $this -> _responders[$name] = $responder;  
        
        return $responder;
            
    }
    
    /**
     * Find a caller by its name
     * 
     * @param  string $caller
     * @return \SmartBot\Bot\Brain\Memory\Item|\SmartBot\Bot\Brain\Memory\Item[]
     */
    public function findCaller( $caller )
    {
        $items = $this -> getBrain() -> getMemory() -> searchSomeone($caller);

        return $items;  
    }
    
    /**
     * Get bot contexts
     * 
     * @return array
     */
    public function getContexts()
    {
        return $this -> _contexts;
    }
    
    /**
     * Sets the current bot caller
     * 
     * @param  string $callerUid
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function setCaller( $callerUid )
    {
        $this -> _caller = $callerUid;
        
        return $this;
    }
    
    /**
     * Talk to the bot
     * 
     * @param  string   $input 
     * @param  string   $callerUid
     * @param  function $callback 
     * @return string|mixed
     */
    public function talk( $input, $callerUid = null, $callback = null )
    {
        
        if (true === is_null($callerUid)) {
            $callerUid = $this -> getCaller();
        }

        if (true === is_null($callerUid)) {
            return 'Who is talking to me ??';
        }
            
        $this -> setCaller($callerUid);
            
        $output = $this ->  getBrain() -> input($input);

        if (false === is_null($callback) && is_callable($callback) ) {
            return $callback($input, $output ); 
        }
        
        return $output;
    }
    
}
