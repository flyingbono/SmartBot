<?php
namespace SmartBot\Bot\Responder;

class Response
{
    
    /**
     * The rules to check
     * @var string
     */
    protected $_rule = null;
    
    /**
     * The response
     * @var string
     */
    protected $_message;
    
    
    public function __construct( $message, $rule = null )
    {
        $this -> _rule     = $rule;
        $this -> _message  = $message;
        
    }
    
    public function getRule()
    {
        return $this -> _rule;
    }
    
    public function getMessage()
    {
        return $this -> _message;
    }
}
