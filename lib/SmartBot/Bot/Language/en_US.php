<?php
die('...');
return [
        
        // Detect what the user say (SmartAI\AI\Listener)
        '/(hello| hi |good morning)/i' => 'exec:hello',
        '/(what).+(your).+(name).+\?/i' => 'exec:whoami',
        '/(where).+(you).+(leave|come from).+\?/i' => 'exec:whereami',
        '/my name is ([a-z ]+)/i' => 'acquire(1,Caller:name,LONG)',
        '/(say|tell) (.+) to ([a-z ]+)/i' => 'say-to(2,3)',
        '/who is ([a-z ]+) ?/i' => 'who-is(1)',
        '/what is ([a-z ]+)/i' => 'who-is(1)',
        
        '/can you (say|tell me) ([a-z ]+)?/i' => 'do-something(1,2)',
        '/what time is it/i' => 'exec:time',
        '/(kill) ([a-z ]+).*/i' => 'do-something(1,2)',
        
        // Handler method
        'say-to:send' => array (
                array (
                        'data' => array (
                                'Ok, {Caller:name}. I transfer your message to $1 !'
                                .PHP_EOL.'[$2] $1.../1'
                                .PHP_EOL.'[$2] {Caller:name} tells you : $0'
                        ) 
                ) 
        ),
        
        'say-to:none' => array (
                array (
                        'data' => array (
                                'Sorry, {Caller:name}, I do not have any "$0" in my friends...'
                        )
                )
        ),
        
        'say-to:many' => array (
                array (
                        'data' => array (
                                'Its seems that the name "$0" is very used ! I cannot decide the good one :)'
                        )
                )
        ),
        'who-is:friend' => array (
                array (
                        'data' => array (
                                '$0 is a friend of mines ;)'
                        )
                )
        ),
        
        'who-is:none' => array (
                array (
                        'data' => array (
                                'Wikipedia said that $0'
//                                  .PHP_EOL.'That\'s what Wikipedia said.../2'
                        )
                )
        ),
        
        'who-is:many' => array (
                array (
                        'data' => array (
                                'Some peoples names "$0"...'
                        )
                )
        ),
        'do-something:say' => array (
                array (
                        // 'ifknown' => array('Caller:name'),
                        // 'ifunknown' => array(),
                        'data' => array (
                                array (
                                        'Time:Afternoon' => 'I never say that before lunch, {Caller:name} !' 
                                ),
                                'No, i could not, sorry',
                                'Of course I can ! $0 $2' 
                        ) 
                ) 
        ),
        
        'do-something:say' => array (
                array (
                        'data' => array (
                                array (
                                        'Time:Afternoon' => 'I never say that before lunch, {Caller:name} !' 
                                ),
                                'No, i could not, sorry',
                                'Of course I can ! $0 $2' 
                        ) 
                ) 
        ),
        
        // Internal methods
        'time' => array (
                array (
                        // 'ifknown' => array('Caller:name'),
                        // 'ifunknown' => array(),
                        'data' => array (
                                array (
                                        'Time:Afternoon&Humor:Funny' => 'Toooo late ! I\'m tired !!!',
                                        'Humor:Funny' => 'Really... you don\'t have watch ??'
                                ),
                                'It\'s actualy {Time.hour} {Caller:name}' 
                        ) 
                ) 
        ),
        
        'hello' => array (
                array (
                        'ifknown' => array (
                                'Caller:name' 
                        ),
                        'ifunknown' => array (),
                        'data' => array (
                                
                                array (
                                        'Humor:Funny' => 'Hi, {Caller:name} :)' 
                                ),
                                array (
                                        'Humor:Funny' => 'Hellooooo {Caller:name} !' 
                                ),
                                array (
                                        'Humor:Funny&Time:Morning' => 'Goooood morning, {Caller:name} !' 
                                ),
                                array (
                                        'Time:Morning' => 'Good morning, {Caller:name} !' 
                                ),
                                
                                'Hi, {Caller:name}',
                                'Hello, {Caller:name}' 
                        ) 
                ),
                array (
                        'ifknown' => array (),
                        'ifunknown' => array (
                                'Caller:name' 
                        ),
                        'data' => array (
                                
                                array (
                                        'Humor:Funny' => 'Hi :) My name is {Myself:name} :)' 
                                ),
                                array (
                                        'Humor:Funny' => 'Hellooooo !' 
                                ),
                                array (
                                        'Time:Morning' => 'Goooood morning !' 
                                ),
                                'Hi !',
                                'Hello' 
                        ) 
                ) 
        ),
        'whoami' => array (
                array (
                        'ifknown' => array (),
                        'ifunknown' => array (),
                        'data' => array (
                                
                                array (
                                        'Humor:Funny' => 'My (beautifull) name is {Myself:name} :)' 
                                ),
                                'My name is {Myself:name}',
                                'My name\'s {Myself:name}' 
                        ) 
                ) 
        ),
        'whereami' => array (
                array (
                        'ifknown' => array (),
                        'ifunknown' => array (),
                        'data' => array (
                                
                                array (
                                        'Humor:Funny' => 'I leave in {Myself:country}. Its really beautifull there !' 
                                ),
                                'I leave in {Myself:country}' 
                        ) 
                ) 
        ) 
]
;