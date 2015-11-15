<?php
namespace SmartBot\Bot\Responder;

use SmartBot\Bot\Responder;
class Acquire extends Responder
{
    
    public function handle($message, $args )
    {
        
        $this -> getDi() -> get('Brain') -> learn('Caller:'.$args[0], $args[1]);
        
        
        return;
    }
}
