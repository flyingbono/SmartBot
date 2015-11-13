<?php
namespace SmartBot\Bot\Brain;

use SmartBot\Bot\Brain\Memory\Item;
use SmartBot\Bot\Exception;

class Memory extends \SmartBot\Di\Injectable {
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
    
    public function __construct(){
        
    }
    
    public function load() {
        $ai     =   $this -> getDi() -> get('Bot');
        $path   =  $ai -> getPath().'/memory/';
        
        
        // loading innate memory
        if( file_exists($path.'innate.php') ) {
            foreach( include $path.'innate.php' as $item ){
                $item -> range  = self::RANGE_LONG;
                $item -> type   = self::TYPE_INNATE;
                
                // @todo Ensure item is valid in current contexts (Person) ?
                $this -> _addItem( $item );
            }
        }
        
        // loading acquired memory
        if( file_exists($path.'acquired.php') ) {
            foreach( include $path.'acquired.php' as $item ){
                
                $item -> type   = self::TYPE_ACQUIRED;
                
                // @todo Forgot item according to range, acquired date and current date ?
                // @todo Ensure item is valid in current contexts (Person) ?
                $this -> _addItem( $item );
            }
        }
        
        // @todo Sort item  : recently acquired first
//         var_dump($this->_items);
        return $this;
    }
    
    public function getAddress( $address ){
        $parts = explode(':', $address );
        if( $parts[0] == 'Caller' ) {
            unset($parts[0]);
            
            $address = join(':', array_merge(array('Caller', $this -> getDi() -> get('Bot')->getCaller() ), $parts ) );
        }
        
        return $address;
    }
    
    public function searchSomeone( $someone ){
      
        $someone = strtolower($someone);
        
        $results = array();
        
        foreach( $this -> _items as $item ){
            /** @var Item $item */
           
            
            
            if( false == $item -> isCaller() )
                continue;
            
            if( preg_match('/^Caller:([^:]+):(name)$/i', $item -> address ) )
            {
        
                
                if(strtolower( $item -> getValue() ) == $someone)
                    return $item;
                else if(false !== stripos($item -> getValue(), $someone) )
                    $results[] = $item;
            }

        }
        
        return $results;
    }
    
    public function search( $address, $strict = false ){
        
        if( ! $strict )
            $address = $this -> getAddress($address);
//          echo '> search address :'.$address.PHP_EOL;
            
        foreach( $this -> _items as $item ){
            /** @var Item $item */
//             echo 'compare *'.$item -> address .'* / *'. $address.'*'.PHP_EOL;
            if( $item -> address == $address ) {
                return $item;
            }
        }
        
        return Item::factory(); // return an empty item
    }
    
    public function acquire( $address, $value, $range ) {      
        $item = $this -> search($address);

        $item -> value      = trim($value);
        $item -> range      = $range;
        
        if( $item -> type == self::TYPE_NONE ) {
            $item -> acquired   = new \DateTime;
            $item -> address    = $this -> getAddress($address);
            $item -> type       = self::TYPE_ACQUIRED;
            
            $this -> _items[] = $item;
        }
        
        
        return $item;
    }
    
    public function parse( $str ){
        
        preg_match_all('/\{[^\}]+\}/', $str, $matches );
//         var_dump($matches);
        foreach($matches[0] as $variable ){
            $address = substr($variable, 1, -1 );
     
            $str = str_replace($variable, $this -> get($address) -> getValue(), $str);
        }
        
        return $str;
    }
    
    public function get($something){
        return $this ->search($something);
    }
    
    /**

     * @param string $something
     * @return boolean
     */
    public function knows( $something ){
        
        $item = $this ->search($something);
        
        return $item -> type != Memory::TYPE_NONE;
        
    }
    
    public function dontKnows( $something ){
        $item = $this ->_search($something);
        
        return $item -> type == Memory::TYPE_NONE;
    }
    
    private function _addItem( Item $item ){
        $this -> _items[] = $item;
    }
    
    private function _learn( ){
        
    }
    
    private function _dump(){
        $data  = '<?php'.PHP_EOL;
        $data .= 'return ['.PHP_EOL;
        
        foreach( $this -> _items as $item ) {
            if( $item -> type != self::TYPE_ACQUIRED )
                continue;

            // Do not rememer immediate items
            if( $item -> range == self::RANGE_IMMEDIATE )
                continue;
                
                
            $data .= sprintf("SmartBot\Bot\Brain\Memory\Item::factory(array(
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
    
    public function flush() {
        $ai     = $this -> getDi() -> get('Bot');
        $path   = $ai -> getPath().'/memory/';
        $file   = $path.'/acquired.php';
        $backup = dirname($file).'/'.substr(basename($file), 0, -4).'.'.date('Ymdh').'.php';
        $data   = $this -> _dump();
        
        
        if( file_exists($file) ){
            // backup memory file
            @rename( $file, $backup );
            if( false == file_exists($backup) )
                throw new Exception('Cannot flush memory');
        }
        
        file_put_contents($file, $data);
        
        return $this;
            
    }
    
}