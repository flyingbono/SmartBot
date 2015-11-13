<?php
namespace SmartBot\Bot\Brain\Memory;

use SmartBot\Bot\Brain\Memory;

class Item {
    
    const DATE_REGEX = '/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/';
    
    public $address;
    public $acquired;
    public $value;    
    public $range;
    public $type = Memory::TYPE_NONE;
    
    
    private function __construct(){
    
    }
    
    public function isCaller(){
        return (substr($this -> address, 0, 7 ) == 'Caller:');
    }
    
    public static function factory(array $data = array()){
        $item = new self;
        foreach( $data as $key => $value ){
            if( property_exists($item, $key)) {
                if( preg_match( self::DATE_REGEX, $value ) )
                    $value = new \DateTime($value);
                
                $item -> $key = $value;
            }
        }
        
        return $item;
    }
    
    public function getValue(){
        if( $this -> value instanceof \DateTime )
            return $this -> value -> format('d/m/Y');
        
        return $this -> value;
    }
}