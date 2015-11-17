<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/flyingbono/SmartBot>.
 */
namespace SmartBot\Bot;

/**
 * SmartBot Utils Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Utils
{
    
    /**
     * Camelize a string
     * 
     * @param  string $value
     * @return string
     */
    public static function camelize($value) 
    {
        return strtr(ucwords(strtr($value, array('_' => ' ','-' => ' ', '.' => '_ ', '\\' => '_ '))), array(' ' => ''));
    }
    
    
    public static function validateExpression($exp, $l = 1) 
    {
        $exp = trim(strtolower($exp));
        $exp = str_replace(' ', '', $exp);
        $exp = str_replace(' ', '', $exp);
        
//         echo sprintf('%s Evaluate expression : %s', str_repeat('>',$l), $exp).PHP_EOL;
        
        preg_match_all('/\(([^\)]+)\)/ui', $exp, $matches);
        foreach ($matches[1] as $index => $subExpression ) {
            $value      = self::validateExpression($subExpression, $l+1)? 'true':'false';
            $exp = str_replace($matches[0][$index], $value, $exp);
        }
        
        $map = array(
            'true&&true'    => (bool) (true && true),
            'true&&false'   => (bool) (true && false),
            'false&&true'   => (bool) (false && true),
            'false&&false'  => (bool) (false && false),
            'true&true'     => (bool) (true & true),
            'true&false'    => (bool) (true & false),
            'false&true'    => (bool) (false & true),
            'false&false'   => (bool) (false & false),
            'true||true'    => (bool) (true || true),
            'true||false'   => (bool) (true || false),
            'false||true'   => (bool) (false || true),
            'false||false'  => (bool) (false || false),
            'true|true'     => (bool) (true | true),
            'true|false'    => (bool) (true | false),
            'false|true'    => (bool) (false | true),
            'false|false'   => (bool) (false | false),
        );
        
        if ( true === array_key_exists($exp, $map) ) {
           $exp = str_replace($exp, ($map[$exp])? 'true':'false', $exp); 
           
        } else {
            while ( preg_match('/(true|false)([&\|]{1,2})(true|false)/', $exp, $matches) ) {
               
                if ( true == array_key_exists($matches[0], $map) ) {
                    $value = $map[$matches[0]]? 'true':'false';
//                     echo sprintf('%s Replacing "%s" by "%s" in %s', str_repeat('>',$l+1), $matches[0], $value, $exp);
//                     echo PHP_EOL;
                    $exp = str_replace($matches[0], $value, $exp);
                
                } else {
                    throw new \Exception(
                        sprintf(
                            'Cannot found a valid boolean result for expression "%s" in "%s"', 
                            $matches[0], $exp 
                        ) 
                    );
                }

            }
        }
        
//         echo sprintf('%s %s', str_repeat('<',$l), $exp).PHP_EOL;
        
        return ($exp === 'true');
    }
    
}
