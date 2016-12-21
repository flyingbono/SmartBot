<?php
namespace SmartBot\Tests;


use SmartBot\Bot\Utils;

/**
 * Bot Utils test
 *
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testBoolComp(){
        $tests = array(
//             'true&&(false||false)'                  => false,
            'true&&(false||false||false||true)'     => true,
//             'true&&(true|false)'                    => true,
//             'true||(false&false)'                   => true,
        );
        
        foreach( $tests as $expression => $attemptedResult ){
            $result = Utils::validateExpression($expression);
            $this -> assertEquals( $attemptedResult, $result );
        }
        
    }
}