<?php
namespace SmartBot\Tests;

/**
 * Bot test
 *
 */
class BotTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Test standard instanciation
     */
    public function testBotInstanciate()
    {
        $entityId = 123;
        
        $options = array(
                'listener'  => 'SmartBot\Bot\Listener\EnUSListener',
                'innate'    => __DIR__.'/../data/smart-bot-memory-innate.php',
                'entity'    => $entityId,
                'context'   => ['Foo:Bar','Foo:Bar','Foo:Baz']
        );
        
        $bot = new \SmartBot\Bot( __DIR__.'/../data/', $options );
        
        $this -> assertInstanceOf('\SmartBot\Bot', $bot );
        $this -> assertEquals( $bot -> getEntity(), 123 );
        $this -> assertEquals( $bot -> hasContext('Foo:Bar'), true );
        $this -> assertEquals( $bot -> hasContext('Foo:Boz'), false );
        
        // Learning capabilities
        $bot -> learn('Entity:birdthdate',  '1975-06-30');
        $bot -> learn('Entity:name',        'Bruno VIBERT');
        
        $this -> assertEquals( $bot -> getEntityProperty($entityId, 'name'), 'Bruno VIBERT' );
        
        $response = $bot -> getBrain() -> getMemory() -> searchSomeone('Bruno');
        $this -> assertInternalType('array', $response);
        $this -> assertCount(1, $response);
        $this -> assertInstanceOf('SmartBot\Bot\Brain\Memory\Item', $response[0] );
        
        $response = $bot -> getBrain() -> getMemory() -> searchSomeone('Bruno VIBERT');
        $this -> assertInstanceOf('SmartBot\Bot\Brain\Memory\Item', $response );
        
        // Test talk
        $output = $bot -> talk('__test__');
        $this -> assertEquals( 'Succeed!', $output );
        
        // Test talk callback
        $_callback  = function($input, $output) {
            return '*'.$output;
        };
        
        $output = $bot -> talk('__test__', null, $_callback );
        $this -> assertEquals( '*Succeed!', $output );
        
        $output = $bot -> talk('Hello');
        $output = $bot -> talk('My name is Bruno');
        $output = $bot -> talk('Who is Bruno?'); 
        
        $output = $bot -> talk('Who is Bill Gates ?');
        $this -> assertEquals(0, strpos('Wikipedia said', $output));

        $bot -> getBrain() -> flush();
        
    }
    
    /**
     * Test no-entity instanciation
     */
    public function testBotInstanciateNoEntity()
    {
        $bot = new \SmartBot\Bot( __DIR__.'/../data/' );
    
        $output = $bot -> talk('__test__' );
        
        $this -> assertEquals('Who is talking to me ??', $output );
    }
    
    /**
     * Test no-option instanciation
     */
    public function testBotInstanciateNoOption()
    {
        $bot = new \SmartBot\Bot( __DIR__.'/../data/' );
    
        $this -> assertInstanceOf('\SmartBot\Bot', $bot );
        $this -> assertNotEmpty( $bot -> getListeners() );
    }
    
    /**
     * Test no existant data path
     * 
     * @expectedException \SmartBot\Bot\Exception
     */
    public function testBotDataPathNotExists()
    {
        $bot = new \SmartBot\Bot( '/foo/bar' );
    }
    
    /**
     * Test not writable data path
     *
     * @expectedException \SmartBot\Bot\Exception
     */
    public function testBotDataPathNotWritable()
    {
        $bot = new \SmartBot\Bot( '/' );
    }
    
    
    
    
}
