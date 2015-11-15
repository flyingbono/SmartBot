<?php

return [
    'Brain' => DI\object(SmartBot\Bot\Brain::class),
    'Brain\Memory' => DI\object(SmartBot\Bot\Brain\Memory::class),
    'Responder' => DI\object(SmartBot\Bot\Responder::class), 
    'Responder\Acquire' => DI\object(SmartBot\Bot\Responder\Acquire::class),
];
