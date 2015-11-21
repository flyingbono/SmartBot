<?php
namespace SmartBot\Tests;


use SmartBot\Bot\Utils;


/**
 * Bot Utils test
 *
 */
class ListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testCustomListener(){
        $entityId = 123;
        
        $options = array(
                'listener'  => 'SmartBot\Bot\Listener\EnUSListener',
                'innate'    => __DIR__.'/../data/smart-bot-memory-innate.php',
                'entity'    => $entityId,
                'context'   => ['Foo:Bar','Foo:Bar','Foo:Baz']
        );
        
        $bot = new \SmartBot\Bot( __DIR__.'/../data/', $options );
        
        
        require_once __DIR__.'/../listeners/CustomListener.php';
        $bot -> addListener( 'SmartBot\Bot\Listener\CustomListener' );
        
        $output = $bot -> talk('foo' );

        $this -> assertEquals('bar',$output);
        
    }
}