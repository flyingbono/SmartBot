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
namespace SmartBot\Bot\Brain;

use SmartBot\Bot\Brain\Memory\Item;
use SmartBot\Bot\Exception;

/**
 * SmartBot Memory Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Memory extends \SmartBot\Di\Injectable
{
    const TYPE_INNATE       = 'INNATE';
    const TYPE_ACQUIRED     = 'ACQUIRED';
    const TYPE_NONE         = 'NONE';
    
    const RANGE_IMMEDIATE   = 'IMMEDIATE';  // Forget in 1h
    const RANGE_SHORT       = 'SHORT';      // Forget in 1d
    const RANGE_MEDIUM      = 'MEDIUM';     // Forget in 1m
    const RANGE_LONG        = 'LONG';       // Never forget
    
    /**
     * My memory items
     * 
     * @var MemoryItem[]
     */
    private $_items  = array();   
    
    /**
     * The acquired memory data file
     * 
     * @var string
     */
    private $_acquiredMemoryFile;
    
    /**
     * Add innate memory items
     * @param array $items
     * @return \SmartBot\Bot\Brain\Memory Provide a fluent interface
     */
    public function addInnateItems( array $items )
    {
        foreach ($items as $item) {
            if ($item instanceof Item ) {
                $item -> range  = self::RANGE_LONG;
                $item -> type   = self::TYPE_INNATE;
                
                $this -> _addItem($item);
            }
        }
        
        return $this;
    }
    
    /**
     * Load acquired memory items
     * 
     * @return \SmartBot\Bot\Brain\Memory Provide a fluent interface
     */
    public function load() 
    {
        $bot     =   $this -> getDi() -> get('Bot');
        $this -> _acquiredMemoryFile   =  $bot -> getDataPath().'/smart-bot-memory-acquired.php';
        
        // loading acquired memory
        if (file_exists($this -> _acquiredMemoryFile)) {
            
            // Sort item  : recently acquired first ?
            foreach (include $this -> _acquiredMemoryFile as $item) {
            
                
                
                $item -> type   = self::TYPE_ACQUIRED;
                
                // Forgot item according to range, acquired date and current date ?
                // Ensure item is valid in current contexts (Entity) ?
                $this -> _addItem($item);
            }
        }
        
        return $this;
    }
    
    /**
     * Get a well formatted memory address
     * 
     * @param  string $address
     * @return string
     */
    public function getAddress( $address )
    {
        $parts = explode(':', $address);
        if ($parts[0] == 'Entity') {
            unset($parts[0]);
            
            $address = join(':', array_merge(array('Entity', $this -> getDi() -> get('Bot')->getEntity() ), $parts));
        }
        
        return $address;
    }
    
    /**
     * Search a entity in memory items
     * 
     * @param  string $someone
     * @return \SmartBot\Bot\Brain\Memory\Item|\SmartBot\Bot\Brain\Memory\Item[]
     */
    public function searchSomeone( $someone )
    {
        $someone = strtolower($someone);
        $results = array();
        
        foreach ( $this -> _items as $item ) {
            /**
            * @var Item $item 
            */
           
            if (false == $item -> isEntity()) {
                continue; 
            }
            
            if (preg_match('/^Entity:([^:]+):(name)$/i', $item -> address)) {
                if (strtolower($item -> getValue()) == $someone) {
                    return $item; 
                } else if (false !== stripos($item -> getValue(), $someone) ) {
                    $results[] = $item; 
                }
            }

        }
        
        return $results;
    }
    
    /**
     * Search a memory item by address
     * @param string  $address
     * @param boolean $strict
     * @return \SmartBot\Bot\Brain\Memory\Item
     */
    public function search( $address, $strict = false )
    {
        
        if (! $strict ) {
            $address = $this -> getAddress($address); 
        }
            
        foreach ( $this -> _items as $item ) {
            /**
            * @var Item $item 
            */
            if ($item -> address == $address ) {
                return $item;
            }
        }
        
        return Item::factory(); // return an empty item
    }
    
    /**
     * Integrate a new memory item
     * 
     * @param  string $address
     * @param  string $value
     * @param  string $range
     * @return \SmartBot\Bot\Brain\Memory\Item
     */
    public function acquire( $address, $value, $range ) 
    {      
        $item = $this -> search($address);

        $item -> value      = trim($value);
        $item -> range      = $range;
        
        if ($item -> type == self::TYPE_NONE ) {
            $item -> acquired   = new \DateTime;
            $item -> address    = $this -> getAddress($address);
            $item -> type       = self::TYPE_ACQUIRED;
            
            $this -> _items[] = $item;
        }
        
        return $item;
    }
    
    /**
     * I don't remember...
     * 
     * @param  string $str
     * @return string
     */
    public function parse( $str )
    {
        
        preg_match_all('/\{[^\}]+\}/', $str, $matches);
        foreach ($matches[0] as $variable ) {
            $address = substr($variable, 1, -1);
            $str = str_replace($variable, $this -> get($address) -> getValue(), $str);
        }
        
        return $str;
    }
    
    /**
     * Alias of search
     * 
     * @deprecated
     * @param      string $something
     */
    public function get($something)
    {
        return $this ->search($something);
    }
    
    /**
     * Check if there is a memory item @ this address
     * 
     * @param  string $something The address
     * @return boolean
     */
    public function knows( $something )
    {
        
        $item = $this ->search($something);
        
        return $item -> type != Memory::TYPE_NONE;
    }
    
    /**
     * Check if there is *not* a memory item @ this address
     * 
     * @param  string $something The address
     * @return boolean
     */
    public function dontKnows( $something )
    {
        $item = $this ->_search($something);
        
        return $item -> type == Memory::TYPE_NONE;
    }
    
    /**
     * Add a memory item
     * 
     * @param \SmartBot\Bot\Brain\Memory\Item $item
     */
    private function _addItem( Item $item )
    {
        $this -> _items[] = $item;
    }
    
    /**
     * Get a memory dump (acquired items only, excluding immediate range items)
     * 
     * @return string
     */
    private function _dump()
    {
        $data  = '<?php'.PHP_EOL;
        $data .= 'return ['.PHP_EOL;
        
        foreach ($this -> _items as $item) {
            /**
             * @var Item $item
             */
            
            if ($item -> type != self::TYPE_ACQUIRED) {
                continue; 
            }

            // Do not rememer immediate items
            if ($item -> range == self::RANGE_IMMEDIATE) {
                continue; 
            }
                
                
            $data .= sprintf(
                "SmartBot\Bot\Brain\Memory\Item::factory(array(
    'address'   => '%s', 
    'range'     => SmartBot\Bot\Brain\Memory::RANGE_%s,	
    'acquired'  => '%s', 
    'value'     => '%s'
    )),",
                $item -> address,
                $item -> range,
                $item -> acquired ->  format('Y-m-d H:i:s'),
                $item -> getValue()
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

        $backup = dirname($this -> _acquiredMemoryFile).'/'.
                    substr(basename($this -> _acquiredMemoryFile), 0, -4).
                    '.'.date('Ymdh').'.php';
        $data   = $this -> _dump();
        
        if (file_exists($this -> _acquiredMemoryFile)) {
            // backup memory file
            @rename($this -> _acquiredMemoryFile, $backup);
            if (false == file_exists($backup)) {
                throw new Exception('Cannot flush memory'); 
            }
        }
        
        file_put_contents($this -> _acquiredMemoryFile, $data);
        
        return $this;
    }
    
}
