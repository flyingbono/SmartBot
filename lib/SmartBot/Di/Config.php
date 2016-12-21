<?php

return [
    'Brain'                 => DI\object(SmartBot\Bot\Brain::class),
    'Conversation'          => DI\object(SmartBot\Bot\Conversation::class),
    'Responder'             => DI\object(SmartBot\Bot\Responder::class),
    'Brain\Memory'          => DI\object(SmartBot\Bot\Brain\Memory::class),
    'Responder\Acquire'     => DI\object(SmartBot\Bot\Responder\Acquire::class),
];
