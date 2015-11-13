<?php
namespace SmartBot\Di;

class Injectable {
    
    /**
     * @Inject("DI")
     * @var \DI\Container
     */
    protected $_di;
    
    
    /**
     * Get the dependency injector
     * 
     * @return \DI\Container
     */
    public function getDi(){
        return $this -> _di;
    }
    
    protected function setDi(\DI\Container $di){
        $this -> _di = $di;
        return $this;
    }
}