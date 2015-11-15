<?php
/*
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

use SmartBot\Di\Injectable;
use SmartBot\Bot\Responder\Response;

/**
 * SmartBot Bot Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Responder extends Injectable
{
    
    /**
     * Available responder responses
     * 
     * @var array
     */
    protected $_responses = [];
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        
    }
    
    /**
     * Add a responder response
     * 
     * @param  string|array $responses
     * @param  string       $rule
     * @return \SmartBot\Bot\Responder Provide a fluent interface
     */
    final public function add( $responses, $rule = null )
    {
        
        if(false == is_array($responses) ) {
            $responses = array($responses); 
        }
           
        foreach( $responses as $response ){
            $this -> _responses[] = new Response($response, $rule);
        }
        
        return $this;
    }
    
    /**
     * Get responder responses
     * 
     * @return array
     */
    public function getResponses()
    {
        return $this -> _responses;
    }
    
    /**
     * Handle an input message and returns the responses that satify rules
     * 
     * @param  string $message
     * @param  array  $args    Regex-captured arguments
     * @return array
     */
    public function handle( $message, $args = array() )
    {
        $results = array();
        foreach( $this -> _responses as $response ){
            if($this -> getDi() -> get('Brain') -> isRuleSatisfied($response -> getRule()) ) {
                $results[] = $this -> _parseArgs($response -> getMessage(), $args);
            } else {
            }
        }
        
        return $results;
    }
    
    /**
     * Parse a response strings and replace $[0-9] by the corresponding value in $args
     * 
     * @param  string $message
     * @param  array  $args
     * @return string
     */
    protected function _parseArgs( $message, $args )
    {
        preg_match_all('/\$([0-9])/', $message, $matches);
        foreach( $matches[0] as $index => $param ){
            $key   = (int) $matches[1][$index];
            $value = array_key_exists($key, $args)? $args[$key]:'';
            
            $message = str_replace($param, $value, $message);
        }
        
        return $message;
    }
    
    
    
    
}
