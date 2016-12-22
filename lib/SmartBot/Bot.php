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
use SmartBot\Bot\Responder;
use SmartBot\Bot\ListenerAbstract;
use SmartBot\Bot\Brain\Memory;

use DI\ContainerBuilder;
use SmartBot\Bot\Conversation;

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
    private $di;
    
    /**
     * Bot brain instance
     *
     * @var Brain
     */
    protected $brain;
    
    /**
     * Bot storage
     *
     * @var string
     */
    protected $dataPath;
    
    /**
     * Contexts strings
     *
     * @var array
     */
    protected $contexts = [];
    
    /**
     * Bot's entity (who speak with me)
     *
     * @var string
     */
    protected $entity = null;
    
    /**
     * Bot's entity conversation context (who speak with me)
     *
     * @var Conversation
     */
    protected $conversation = null;

    /**
     * Registred listeners dirs
     *
     * @var array
     */
    protected $listenersDirs = [];
    
    /**
     * Registred listeners instances
     *
     * @var array
     */
    protected $listenerInstances = [];
    
    /**
     * Registred listeners map
     *
     * @var array
     */
    protected $listeners = [];
    
    /**
     * Responders
     *
     * @var array
     */
    protected $responders = [];
    
    /**
     * Bot Class constructor
     *
     * @param  string $dataPath
     * @param  array  $options
     * @throws Exception
     */
    public function __construct($dataPath, array $options = array())
    {
                
        // Loading DI container
        $builder = new ContainerBuilder;
        $builder -> useAnnotations(true);
        $builder -> addDefinitions(__DIR__.'/Di/Config.php');
        
        $this -> di    = $builder->build();
        $this -> dataPath  = $dataPath;
        
        // Test data path
        if (false == is_dir($this -> dataPath)) {
            throw new Exception(sprintf('SmartBot : data path "%s" doesn\'t exists', $this -> dataPath));
        }
        
        $test = $this -> dataPath.'/'.uniqid();
        @file_put_contents($test, 'SmartBot test');
        if (false == file_exists($test)) {
            throw new Exception(sprintf('SmartBot : data path "%s" is not writtable', $this -> dataPath));
        }
        
        @unlink($test);
              
        // Register objects in DI
        $this -> di -> set('Bot', $this);
        $this -> di -> set('DI', $this -> di);
        $this -> brain = $this -> di -> get('Brain');
        
        // Create the acquire responder
        $this -> responders['acquire'] = $this -> di -> get('Responder\Acquire');
        
        $this -> conversation = $this -> di -> make('Conversation');
        
        // Add the  main listener
//         $this -> addListenerDir(__DIR__.'/Bot/Listener');
        
        // Initialize the brain
        $this -> brain -> initialize();
        
        // Load options
        $this -> loadOptions($options);
        
        // Load acquired memory
        $this -> brain -> load();
        
        // Check if there is at least 1 listener
        if (count($this -> listeners) == 0) {
            // Add the default EnUS listener
            $this -> addListener('\SmartBot\Bot\Listener\EnUSListener');
            
//             // add a default listener
//             $listener = new Bot\Listener\EnUSListener($this);
//             $listener -> initialize();
        }
    }
    
    /**
     * Load options passed in the class constructor
     *
     * @param  array $options
     * @return \SmartBot\Bot Provide a fluent interface
     */
    protected function loadOptions(array $options = array())
    {
        foreach ($options as $key => $option) {
            switch (strtolower($key)) {
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
                        
                case 'entity':
                    $this -> setEntity($option);
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
    public function learn($what, $value, $range = Memory::RANGE_LONG)
    {
        $this -> getBrain() -> learn($what, $value, $range);
        return $this;
    }
    
    
    /**
     * Get the bot data path
     */
    public function getDataPath()
    {
        return $this -> dataPath;
    }
    
    
    /**
     * Get my own brain
     *
     * @return \SmartBot\Bot\Brain
     */
    public function getBrain()
    {
        return $this -> di -> get('Brain');
    }
    
    /**
     * Get the entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this -> entity;
    }
    
    /**
     * Add a bot user-defined context
     *
     * @param string|array $name The name of the context (ie Humor:Hungry)
     *
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function addContext($name)
    {
        
        if (is_array($name)) {
            foreach ($name as $context) {
                $this -> addContext($context);
            }
            
            return $this;
        }
            
        if ($this -> hasContext($name)) {
            return $this;
        }
        
        $this -> contexts[] = $name;
        
        return $this;
    }
    
    /**
     * Check if the bot is in a particular context
     *
     * @param  string $context
     * @return boolean
     */
    public function hasContext($context)
    {
        return in_array($context, $this -> contexts);
    }
    
    /**
     * Get entity ID with the given address
     *
     * @param  string $address An adress (ie: Entity:#I08098088:name)
     * @return string The entity ID (ie: #I08098088)
     */
    public function getEntityId($address)
    {
        return preg_replace('/entity:([^:]+):.*/i', '\\1', $address);
    }
    
    /**
     * Get a entity property
     *
     * @param  string $entityId
     * @param  string $property
     * @return mixed
     */
    public function getEntityProperty($entityId, $property)
    {
        $address = sprintf('Entity:%s:%s', $entityId, $property);
        
        $item = $this -> getBrain() -> getMemory() -> search($address, true);
        
        return $item -> getValue();
    }
    
    /**
     * Add a listener
     *
     * @param  string $listenerClass
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function addListener($listenerClass)
    {
        if (false == class_exists($listenerClass)) {
            throw new Exception(sprintf('Listener class %s not found', $listenerClass));
        }
        
        if (false == class_implements($listenerClass, '\SmartBot\Bot\ListenerInterface')) {
            throw new Exception(sprintf('Listener class %s does not implement ListenerInterface', $listenerClass));
        }
          
        
        $listener = new $listenerClass($this);
        
        if (false === $listener instanceof ListenerAbstract) {
            throw new Exception(sprintf('Listener class %s does not extends ListenerAbstract', $listenerClass));
        }
            
        
        $listener -> initialize();

        $this -> listenerInstances[] = $listener;
        return $this;
    }
    

    /**
     * Add a listener directory
     *
     * @param  string $dir
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function addListenerDir($dir)
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
        return $this -> listeners;
    }
    
    /**
     * Get responders
     *
     * @return Responder[]
     */
    public function getResponders()
    {
        return $this -> responders;
    }
    
    /**
     * Listen regex and map to responders
     *
     * @param  array                 $regex
     * @param  Responder|Responder[] $responders
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function listen(array $regex, $responders)
    {
        
        if (true == is_array($responders)) {
            foreach ($responders as $responder) {
                $this -> listen($regex, $responder);
            }
            
            return $this;
        }
          
        $responder = $responders;
        
        foreach ($regex as $expr) {
            $this -> listeners[uniqid()] = array(
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
    public function responder($name)
    {
        if (true == array_key_exists($name, $this -> responders)) {
            return $this -> responders[$name];
        }
        
        $responder = $this -> di -> make('Responder');
        $this -> responders[$name] = $responder;
        
        return $responder;   
    }
    
    /**
     * Find a entity by its name
     *
     * @param  string $entity
     * @return \SmartBot\Bot\Brain\Memory\Item|\SmartBot\Bot\Brain\Memory\Item[]
     */
    public function findEntity($entity)
    {
        $items = $this -> getBrain() -> getMemory() -> searchSomeone($entity);

        return $items;
    }
    
    /**
     * Get bot contexts
     *
     * @return array
     */
    public function getContexts()
    {
        return $this -> contexts;
    }
    
    /**
     * Sets the current bot entity
     *
     * @param  string $entityUid
     * @return \SmartBot\Bot Provide a fluent interface
     */
    public function setEntity($entityUid)
    {
        $this -> entity = $entityUid;
        
        
        $this -> conversation -> load($entityUid);
        
        
        return $this;
    }
    
    /**
     * Talk to the bot
     *
     * @param  string   $input
     * @param  string   $entityUid
     * @param  function $callback
     * @return string|mixed
     */
    public function talk($input, $entityUid = null, $callback = null)
    {
        
        if (true === is_null($entityUid)) {
            $entityUid = $this -> getEntity();
        }

        if (true === is_null($entityUid)) {
            return 'Who is talking to me ??';
        }
            
        $this -> setEntity($entityUid);
            
        $output = $this ->  getBrain() -> input($input);

        if (false === is_null($callback) && is_callable($callback)) {
            return $callback($input, $output );
        }
        
        return $output;
    }
}
