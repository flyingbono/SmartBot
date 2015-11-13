<?php
namespace SmartAI\AI\Handle;

use SmartAI\AI\HandleInterface;
use SmartAI\AI\HandleAbstract;
use SmartAI\AI\Brain\Memory\Item;

class SayTo extends HandleAbstract implements HandleInterface {
    
    public function handle( array $args = array() ) {
       $ai = $this -> getDi() -> get('AI');
      
       $recipient = $ai -> findCaller($args[1]);
       
       if( $recipient instanceof Item ){
           // recipent found. maybe confirm ??
           $recipientId     = $ai -> getCallerId($recipient -> address);
           $recipientName   = $ai -> getCallerProperty($recipientId, 'name' ); 
           return $ai -> getBrain() -> execute('say-to:send', array($args[0], $recipientName, $recipientId ) );
       } else if( is_array($recipient ) && count($recipient) > 1 ){
           
           return $ai -> getBrain() -> execute('say-to:many', array($args[1], count($recipient)) );
       } else if( is_array($recipient ) && count($recipient) == 0 ){
           return $ai -> getBrain() -> execute('say-to:none', array($args[1]) );
       }
       
       return [];
       
       
       
        
        
    }
    
}