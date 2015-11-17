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
        $callerId = 123;
        
        $options = array(
                'listener'  => 'SmartBot\Bot\Listener\EnUSListener',
                'innate'    => __DIR__.'/../data/smart-bot-memory-innate.php',
                'caller'    => $callerId,
                'context'   => ['Foo:Bar','Foo:Bar','Foo:Baz']
        );
        
        $bot = new \SmartBot\Bot( __DIR__.'/../data/', $options );
        
        $this -> assertInstanceOf('\SmartBot\Bot', $bot );
        $this -> assertEquals( $bot -> getCaller(), 123 );
        $this -> assertEquals( $bot -> hasContext('Foo:Bar'), true );
        $this -> assertEquals( $bot -> hasContext('Foo:Boz'), false );
        
        // Learning capabilities
        $bot -> learn('Caller:birdthdate',  '1975-06-30');
        $bot -> learn('Caller:name',        'Bruno VIBERT');
        
        $this -> assertEquals( $bot -> getCallerProperty($callerId, 'name'), 'Bruno VIBERT' );
        
        $response = $bot -> getBrain() -> getMemory() -> searchSomeone('Bruno');
        $this -> assertInternalType('array', $response);
        $this -> assertCount(1, $response);
        $this -> assertInstanceOf('SmartBot\Bot\Brain\Memory\Item', $response[0] );
        
        $response = $bot -> getBrain() -> getMemory() -> searchSomeone('Bruno VIBERT');
        $this -> assertInstanceOf('SmartBot\Bot\Brain\Memory\Item', $response );
        
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
