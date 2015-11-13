<?php
namespace SmartBot\Bot;

interface ListenerInterface {
    /**
     * Add listeners
     * @return SmartBot\Bot\Listener[]
     */
    public function initialize(); 
    //public function handle( array $args = array() );
}