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

use SmartBot\Di\Injectable;

/**
 * SmartBot Conversation Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Conversation extends Injectable
{
      
    const CONTEXT_NONE          = 'none';
    const CONTEXT_PERSON        = 'person';
    const CONTEXT_LOCATION      = 'location';
    const CONTEXT_SOMETHING     = 'something';
    
    /**
     * The acquired memory data file
     *
     * @var string
     */
    private $memoryFile;
    
    
    private $items;
    
    
    /**
     * Flush the actual conversation and load a new one
     *
     * @return \SmartBot\Bot\Brain\Memory Provide a fluent interface
     */
    public function load($entity)
    {
        $this -> flush();
        
        $bot     =   $this -> getDi() -> get('Bot');
        $this -> memoryFile   =  sprintf('%s/smart-bot-conversation-%s.php', $bot -> getDataPath(), md5($entity));
        
        
        // loading acquired memory
        if (file_exists($this -> memoryFile)) {
            echo $this -> memoryFile;
            foreach (include $this -> memoryFile as $item) {
                $this -> addItem($item);
            }
        }
        
        return $this;
    }
    
    /**
     * Add a memory item
     *
     * @param \SmartBot\Bot\Conversation\Item $item
     */
    private function addItem(Item $item)
    {
        $this -> items[] = $item;
    }
    
    /**
     * Class destructor. Flush data to memory file
     */
    public function __destruct()
    {
        $this -> flush();
    }
    
    private function dump()
    {
        $data  = '<?php'.PHP_EOL;
        $data .= 'return ['.PHP_EOL;
    
        foreach ($this -> items as $item) {
            $data .= sprintf(
                "SmartBot\Bot\Conversation\Item::factory(array(
    'context'   => '%s',
    )),",
                $item -> context
            ).PHP_EOL;
        }
    
        $data .= '];'.PHP_EOL;
    
        return $data;
    }
    
    /**
     * Flush memory dump the the acquired memory file
     *
     * @throws Exception
     * @return \SmartBot\Bot\Brain\Memory Provide a fluent interface
     */
    public function flush()
    {
        if (true === file_exists($this -> memoryFile) && false === is_writable($this -> memoryFile)) {
            throw new Exception('Conversation memory file is not writable');
        }
        
        if (count($this -> items)==0) {
            @unlink($this -> memoryFile);
            
            return;
        }
        
        $data   = $this -> dump();
        file_put_contents($this -> memoryFile, $data);

        return $this;
    }
}
