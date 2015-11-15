<?php
namespace SmartBot\Bot\Listener;

use SmartBot\Bot\ListenerAbstract;
use SmartBot\Bot\ListenerInterface;


class EnUSListener extends ListenerAbstract implements ListenerInterface
{
    
    
    public function initialize()
    {
        
        $ai = $this ->_smartBot;
                
        // Create listeners and associate to responders
        $ai -> listen(['/(hello| hi |good morning)/i'],    $this->responder('hello'));
        $ai -> listen(['/what time is it/i'],              $this->responder('time'));
        $ai -> listen(['/my ([a-z]+) is ([a-z]+)/i'],      [$this->responder('acquire'),$this->responder('acquired')]);
        $ai -> listen(['/(what).+(your|ur).+(name).+\?/i'],   $this->responder('whoami'));
        $ai -> listen(['/(who).+(are|r|is).+(you|u).+\?/i'],      $this->responder('whoami'));
        $ai -> listen(
            ['/(wher).+(are|r).+(you|u).+\?/i', '/(wher).+(you|u).+(leave|come from).+\?/i'],      
            $this->responder('whereami') 
        );
        
        $ai -> listen(
            ['/who is ([a-z ]+) ?/i','/what is ([a-z ]+)/i'],      
            function ($message, $args) {
                    return $this -> whois($message, $args);
            }
        );
        
        
    }
    
    function whois( $message, $args)
    {
        $recipient  = $this ->_smartBot -> findCaller($args[0]);
        
        if($recipient instanceof Item ) {
            // recipent found. maybe confirm ??
            $recipientId     = $ai -> getCallerId($recipient -> address);
            $recipientName   = $ai -> getCallerProperty($recipientId, 'name');
            
            return $this -> responder('whois-friend') -> handle($message, $args);
            
        } else if(is_array($recipient) && count($recipient) > 1 ) {
            return $this -> responder('whois-many') -> handle($message, $args);
            //             return $this -> getStrings('whois:many', array($args[1]) );
            
        } else if(is_array($recipient) && count($recipient) == 0 ) {
             
            // find the first one on internet ??
            $url = 'https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&redirects&titles='.urlencode($args[0]);
            $data = file_get_contents($url);
            $response = json_decode($data);
        
            $what = $args[0];
            if(true == isset($response -> query -> redirects)) {
                $what = urlencode(str_replace(' ', '_', $response -> query -> redirects[0] -> to)); 
            }
            
            foreach ( get_object_vars($response->query->pages) as $page ) {
                 
                if(false == isset($page -> extract) ) {
                    return $this -> responder('noresponse') -> handle($message); 
                }
                      
                
                    
                $phrases = explode('.', $page -> extract);
                 
                $lines = explode(PHP_EOL, $phrases[0]);
                 
                if(count($lines) > 3 ) {
                    $lines = array_slice($lines, 0, 3); 
                }
                     
                    return $this -> responder('whois-wiki') -> handle($message, array(join(' ', $lines), 'https://en.wikipedia.org/wiki/'.$what ));            
            }
        
        }
         
        return [];
    }
    
    
    
    
    
}
