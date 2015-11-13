<?php
namespace SmartAI\AI\Handle;

use SmartAI\AI\HandleInterface;
use SmartAI\AI\HandleAbstract;

class DoSomething extends HandleAbstract implements HandleInterface {
    
    public function initialize(){
        
    }
    
    public function handle( array $args = array() ) {
        $ai = $this -> getDi() -> get('AI');
               
        switch( strtolower($args[0] ) ){
            case 'say':
                return $ai -> getBrain() -> execute('do-something:say', array($args[1]) );
                break;
        }
        
        return []; // unhandled
        
    }
    
}