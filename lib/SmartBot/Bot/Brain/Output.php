<?php
namespace SmartBot\Bot\Brain;

class Output
{
    
    private $_results = array();
    
    
    public function setResults( array $results = array() ) 
    {
        $this -> _results = $results;
    }
    
    public function getResult()
    {
        
        if(count($this -> _results) == 0 ) {
            // what to say...
            return '?';
        }
        
        $index = array_rand($this -> _results);
        
        return $this -> _results[$index];
    }
    
    public function __toString() 
    {
        return $this -> getResult();
    }
    
    
}
