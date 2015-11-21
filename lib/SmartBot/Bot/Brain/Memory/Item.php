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
namespace SmartBot\Bot\Brain\Memory;

use SmartBot\Bot\Brain\Memory;

/**
 * SmartBot Brain Memory Item Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Item
{
    
    const DATE_REGEX = '/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/';
    
    /**
     * The item address
     * 
     * @var string
     */
    public $address;
    
    /**
     * The item acquisition date
     * 
     * @var string|Datetime
     */
    public $acquired;
    
    /**
     * The item value
     * 
     * @var string|Datetime
     */
    public $value;  
    
    /**
     * The item memory range
     * 
     * @var string
     */
    public $range;
    
    /**
     * The item memory storage type
     * 
     * @var string
     */
    public $type = Memory::TYPE_NONE;
    
    /**
     * Check if the current item is a Entity item
     * 
     * @return boolean
     */
    public function isEntity()
    {
        return (substr($this -> address, 0, 7) == 'Entity:');
    }
    
    /**
     * Memory item factory
     * 
     * @param array $data
     * @return \SmartBot\Bot\Brain\Memory\Item
     */
    public static function factory(array $data = array())
    {
        $item = new self;
        foreach ($data as $key => $value) {
            if (property_exists($item, $key)) {
                if (preg_match(self::DATE_REGEX, $value) ) {
                    $value = new \DateTime($value); 
                }
                
                $item -> $key = $value;
            }
        }
        
        return $item;
    }
    
    /**
     * Get the item value
     * @return string|\Datetime
     */
    public function getValue()
    {
        if ($this -> value instanceof \DateTime) {
            return $this -> value -> format('d/m/Y'); 
        }
        
        return $this -> value;
    }
}
