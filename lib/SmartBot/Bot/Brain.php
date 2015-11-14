<?php
namespace SmartBot\Bot;


use SmartBot\Bot\Exception;
use SmartBot\Bot\Brain\Output;
use SmartBot\Bot\Brain\Memory;

class Brain extends \SmartBot\Di\Injectable {
    
    /**
     * @Inject("Bot")
     * @var SmartBot\Bot
     */
    protected $_smartBot;
    
    /**
     *
     * @var SmartBot\Bot\Brain\Memory
     */
    protected $_memory;
    
    public function __construct(){
        // injectable properties are not available yet !
        
    }
    
    public function initialize(){
        
        $this -> _memory = $this -> _di -> get('Brain\Memory');

        return $this;
               
    }
    
    public function load(){
        $this -> _memory -> load();
        
        $this -> learn('Time:hour', date('H:i'), Memory::RANGE_IMMEDIATE );
        $this -> learn('Time:date', date('d/m/Y'), Memory::RANGE_IMMEDIATE );
    }
    
    
    /**
     * 
     * @return Memory
     */
    public function getMemory(){
        return $this -> _memory;
    }
    
    public function learn( $what, $value, $range = Memory::RANGE_LONG ) {
        // @todo
        $this -> _memory -> acquire( $what, $value, $range );
    }
    
    public function isRuleSatisfied( $rule ){
        
        if( true == is_null($rule) )
            return true;
        
        $test = $rule;
        preg_match_all('/([a-z]+)\(([^\&|\(\)]+)\)/i', $rule, $matches );
        
        foreach( $matches[1] as $index => $method ){
            $str    = $matches[0][$index];
            $value  = trim($matches[2][$index]);
            
            switch( strtolower(trim($method)) ){
                case 'hascontext':
                    $test = str_replace($str, ($this->_smartBot -> hasContext($value))? ' true ':' false ', $test );
                    break;
                    
                case 'isknown':
                    $test = str_replace($str, ($this-> _memory -> knows($value))? ' true ':' false ', $test );
                    break;
                    
                case 'isnotknown':
                    $test = str_replace($str, (! $this-> _memory -> knows($value))? ' true ':' false ', $test );
                    break;
            }
            
        }
        
        eval(sprintf('$result = (bool) (%s);', $test));
        return $result;
    }

    /**
     * @return Brain\Output
     */
    public function input( $message ){
        $output = new Output;
                
        $results = array();
        foreach ($this -> _smartBot -> getListeners() as $name => $config ) {

            if( preg_match( $config['regex'], $message, $matches) ){
                
                if( $config['responder'] instanceof Responder )
                    $response = $config['responder'] -> handle($message, array_slice($matches,1));
                else {
                    $response = $config['responder']($message, array_slice($matches,1));
                }
                switch( gettype($response) ) {
                    case 'string':
                        $results[] = $response;
                        break;
                    case 'array':
                        $results = array_merge($results, $response);
                        break;
                }
            }
        }
        $output -> setResults($results);
        return $this -> _memory -> parse( (string) $output );
    }
    
    public function __destruct(){
        $this -> flush();
    }
   
    /**
     * Flush brain acquired memory
     */
    public function flush() {
        
        $this -> _memory -> flush();
        return $this;
    }
    
    
    
    
}