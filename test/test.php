<?php
header('Content-type: text/plain; charset=utf-8');

require_once 'vendor/autoload.php';

$options = array(
        'listener'  => 'SmartBot\Bot\Listener\EnUSListener',
        'innate'    => __DIR__.'/data/smart-bot-memory-innate.php',
);

$bot = new SmartBot\Bot( __DIR__.'/data/', $options );
$bot -> setCaller('test');

$callback = function( $input, $output ){
    echo '> ', $input, PHP_EOL;
    echo '< ', $output, PHP_EOL;
    
};

$bot -> talk('Hello',                           null, $callback );
$bot -> talk('What is your name ?',             null, $callback );
$bot -> talk('My name is bob',                  null, $callback );
$bot -> talk('Hello',                           null, $callback );

