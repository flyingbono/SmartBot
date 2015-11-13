<?php
namespace SmartBot\Bot;

use SmartBot\Di\Injectable;
use SmartBot\Bot\Responder\Response;

class Responder extends Injectable {
    
    protected $_responses = array();
    
    public function __construct(){
        
    }
    
    final public function add( $responses, $rule = null ){
        
       if( false == is_array($responses) )
           $responses = array($responses);
           
        foreach( $responses as $response ){
            $this -> _responses[] = new Response($response, $rule);
        }
        
        
        return $this;
    }
    
    public function getResponses(){
        return $this -> _responses;
    }
    
    public function handle( $message, $args = array() ){
        $results = array();
        foreach( $this -> _responses as $response ){
            if( $this -> getDi() -> get('Brain') -> isRuleSatisfied($response -> getRule() ) ){
                $results[] = $this -> _parseArgs( $response -> getMessage(), $args );
            } else {
            }
        }
        
        return $results;
    }
    
    protected function _parseArgs( $message, $args ){
        preg_match_all('/\$([0-9])/', $message,$matches );
        foreach( $matches[0] as $index => $param ){
            $key   = (int) $matches[1][$index];
            $value = array_key_exists($key, $args)? $args[$key]:'';
            
            $message = str_replace($param, $value, $message);
        }
        
        return $message;
    }
    
    
    
    
}