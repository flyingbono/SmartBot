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

use SmartBot\Bot;

/**
 * SmartBot ListenerAbstract Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
abstract class ListenerAbstract
{
    /**
     * The SmartBot instance
     *
     * @var \SmartBot\Bot
     */
    protected $_smartBot;
    
    /**
     * Listener configuration (loaded from INI file)
     *
     * @var array
     */
    protected $_config = array();
    
    /**
     * Listener's responders
     *
     * @var array
     */
    protected $_responders = array();
    
    /**
     * Class constructor
     *
     * @param \SmartBot\Bot $smartBot
     */
    final public function __construct(\SmartBot\Bot $smartBot)
    {
        $this -> _smartBot = $smartBot;
        
        $this -> _loadConfig();
    }
    
    /**
     * Get the responder singleton instance and load listen strings
     *
     * @param  string $name
     * @return \SmartBot\Bot\Responder
     */
    protected function responder($name)
    {
        $responder = $this -> _smartBot -> responder($name);
        
        if (false == array_key_exists($name, $this -> _responders)) {
            // append config to responder
            foreach ($this -> _config as $section => $data) {
                if (preg_match(sprintf('/^responder:%s$/i', $name), $section)) {
                    $responder -> add($data['msg']);
                }
                
                if (preg_match(sprintf('/^responder:%s:(.*)$/i', $name), $section, $matches)) {
                    $responder -> add($data['msg'], $matches[1]);
                }

            }

            $this -> _responders[$name] = $responder;
        }
        
        return $responder;
    }
    
    /**
     * Find and return the listener config file
     *
     * @return string
     */
    public function getConfigFile()
    {
        
        $ref    = new \ReflectionClass(get_class($this));
        $config = dirname($ref -> getFileName()).'/Config/'.array_slice(explode('\\', get_class($this)), -1)[0].'.ini';
        
        unset($ref);
        
        return $config;
    }
    
    
    /**
     * Load internal listen string stored in the INI file
     *
     * @throws Exception
     * @return \SmartBot\Bot\Responder
     */
    final private function _loadConfig()
    {
        $iniFile = $this -> getConfigFile();

        if (false == file_exists($iniFile)) {
            throw new Exception(sprintf('Config file not found for listener : %s', get_class($this)));
        }
        
            
        $this -> _config = parse_ini_file($iniFile, true);
            
        return $this;
    }
}
