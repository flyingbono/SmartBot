<?php 

return [
       
    SmartBot\Bot\Brain\Memory\Item::factory(array(
            'address'   => 'Myself:name',
            'value'     => 'John DOE'
    )),
    SmartBot\Bot\Brain\Memory\Item::factory(array(
            'address'   => 'Myself:birdthdate',
            'value'     => '1975-06-30'
    )),
    SmartBot\Bot\Brain\Memory\Item::factory(array(
            'address'   => 'Myself:country',
            'value'     => 'Internet'
    )),
];
