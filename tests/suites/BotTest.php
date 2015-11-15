<?php
namespace SmartBot\Tests;


/**
 * Bot test
 *
 */
class BotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Result::wasSuccessFul
     */
    public function testBotInstanciate()
    {
        $options = array(
                'listener'  => 'SmartBot\Bot\Listener\EnUSListener',
                'innate'    => __DIR__.'/../data/smart-bot-memory-innate.php',
        );
        
        $bot = new \SmartBot\Bot( __DIR__.'/../data/', $options );
    }
}
