<?php
namespace SmartAI\Bot\Handle;

use SmartAI\AI\HandleInterface;
use SmartAI\AI\HandleAbstract;
use SmartAI\AI\Brain\Memory\Item;

class WhoIs extends HandleAbstract implements HandleInterface {
    
    public function handle( array $args = array() ) {
       $bot = $this -> getDi() -> get('Bot');

       $recipient = $bot -> findCaller($args[0]);
       
       if( $recipient instanceof Item ){
           // recipent found. maybe confirm ??
           $recipientId     = $ai -> getCallerId($recipient -> address);
           $recipientName   = $ai -> getCallerProperty($recipientId, 'name' ); 
           return $ai -> getBrain() -> execute('who-is:friend', array($args[0] ) );
       } else if( is_array($recipient ) && count($recipient) > 1 ){
           
           return $ai -> getBrain() -> execute('who-is:many', array($args[1]) );
       } else if( is_array($recipient ) && count($recipient) == 0 ){
           
           // find the first one on internet ??
   
           $url = 'https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&redirects&titles='.urlencode($args[0]);
           $data = file_get_contents($url);
           $response = json_decode ( $data );
            
           foreach ( get_object_vars ( $response->query->pages ) as $page ) {
               
               
               $phrases = explode('.', $page -> extract );
               
               $lines = explode( PHP_EOL, $phrases[0] );
               
               if( count($lines) > 3 )
                   $lines = array_slice($lines, 0, 3);
                             
               return $ai -> getBrain() -> execute('who-is:none', array(join(' ', $lines) ));
           }
          
       }
       
       return [];
       
       
       
        
        
    }
    
}