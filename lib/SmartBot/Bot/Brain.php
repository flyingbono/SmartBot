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

use SmartBot\Bot\Exception;
use SmartBot\Bot\Brain\Output;
use SmartBot\Bot\Brain\Memory;

/**
 * SmartBot Brain Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Brain extends \SmartBot\Di\Injectable
{
    
    /**
     * Bot instance
     *
     * @Inject("Bot")
     * @var           \SmartBot\Bot
     */
    protected $_smartBot;
    
    /**
     * My own memory
     *
     * @var \SmartBot\Bot\Brain\Memory
     */
    protected $_memory;
    
    /**
     * Brain class constructor
     */
    public function __construct()
    {
        // injectable properties are not available yet !
        
    }
    
    /**
     * Brain initialisation
     *
     * @return \SmartBot\Bot\Brain Provide a fluent interface
     */
    public function initialize()
    {
        
        $this -> _memory = $this -> _di -> get('Brain\Memory');

        return $this;
    }
    
    /**
     * Loads brain memory acquired data
     *
     * @return \SmartBot\Bot\Brain Provide a fluent interface
     */
    public function load()
    {
        $this -> _memory -> load();
        
        $this -> learn('Time:hour', date('H:i'), Memory::RANGE_IMMEDIATE);
        $this -> learn('Time:date', date('d/m/Y'), Memory::RANGE_IMMEDIATE);
        
        return $this;
    }
    
    
    /**
     * Get my memory instance
     *
     * @return \SmartBot\Bot\Brain\Memory
     */
    public function getMemory()
    {
        return $this -> _memory;
    }
    
    /**
     * Learn something
     *
     * @param  string $what  Item address of what to learn
     * @param  string $value Item value
     * @param  string $range Item date-range accessibility
     * @return \SmartBot\Bot\Brain Provide a fluent interface
     */
    public function learn($what, $value, $range = Memory::RANGE_LONG)
    {
        $this -> _memory -> acquire($what, $value, $range);
        
        return $this;
    }
    
    public function isRuleSatisfied($rule)
    {
        
        if (true == is_null($rule)) {
            return true;
        }
        
        $conditions = trim($rule);
        preg_match_all('/([a-z]+)\(([^\&|\(\)]+)\)/i', $rule, $matches);
        
        foreach ($matches[1] as $index => $method) {
            $str    = $matches[0][$index];
            $value  = trim($matches[2][$index]);
            
            switch (strtolower(trim($method))) {
                case 'hascontext':
                    $value      = ($this->_smartBot -> hasContext($value))? ' true ':' false ';
                    $conditions = str_replace($str, $value, $conditions);
                    break;
                        
                case 'isknown':
                    $value      = ($this-> _memory -> knows($value))? ' true ':' false ';
                    $conditions = str_replace($str, $value, $conditions);
                    break;
                        
                case 'isnotknown':
                    $value      = (! $this-> _memory -> knows($value))? ' true ':' false ';
                    $conditions = str_replace($str, $value, $conditions);
                    break;
            }
            
        }

        $result = Utils::validateExpression($conditions);

        return $result;
    }

    /**
     * Send something to the brain
     *
     * @return string
     */
    public function input($message)
    {
        $output = new Output;
                
        $results = array();
        foreach ($this -> _smartBot -> getListeners() as $config) {
            if (preg_match($config['regex'], $message, $matches)) {
                if ($config['responder'] instanceof Responder) {
                    $response = $config['responder'] -> handle($message, array_slice($matches, 1));
                } else {
                    $response = $config['responder']($message, array_slice($matches, 1));
                }
                
                switch (gettype($response)) {
                    case 'string':
                        $results[] = $response;
                        break;
                    case 'array':
                        $results = array_merge($results, $response);
                        break;
                }
            }
        }
        $output -> setResults($results);
        return $this -> _memory -> parse((string) $output);
    }
    
    /**
     * Class destructor : flush memory
     */
    public function __destruct()
    {
        $this -> flush();
    }
   
    /**
     * Flush brain acquired memory
     *
     * @return \SmartBot\Bot\Brain Provide a fluent interface
     */
    public function flush()
    {
        
        $this -> _memory -> flush();
        return $this;
    }
}
