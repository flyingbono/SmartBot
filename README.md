# SmartBot
[![Build Status](http://62.210.124.92/badge/?type=build)](https://travis-ci.org/flyingbono/SmartBot)
[![Code Climate](http://62.210.124.92/badge/?type=cc_gpa)](https://codeclimate.com/github/flyingbono/SmartBot)
[![Test Coverage](http://62.210.124.92/badge/?type=cc_coverage)](https://codeclimate.com/github/flyingbono/SmartBot/coverage)
[![Issue Count](http://62.210.124.92/badge/?type=cc_issues)](https://codeclimate.com/github/flyingbono/SmartBot/issues)
[![Issue Count](http://62.210.124.92/badge/?type=issues)](https://github.com/flyingbono/SmartBot/issues)
[![Listeners](http://62.210.124.92/badge/?type=listeners)](https://github.com/flyingbono/SmartBot/tree/master/lib/SmartBot/Bot/Listener)

Multilinguage bot responder implemented in PHP, with learning capabilities

### Installation :
``` sh
$ composer.phar require flyingbono/smart-bot
```

### Basic usage :

```php
<?php
require_once '../vendor/autoload.php';

// Options : all are optionals
$options = array(
	'listener' 	=> 'SmartBot\Bot\Listener\EnUSListener', // default listener (default: EnUSListener)
	'innate'	=> 'file.php', // Innate memory data (default: null)
	'entity'	=> 'CallerUID', // ID of the person talking to the bot (default: null)
	'context'	=> ['Humor:Funny'], // List of the bot contexts (users-defined)
);

$bot = new \SmartBot\Bot( 
	// Data path for acquired memory storage)
	'/tmp/smartbot-data/', 
	
	// Bot options
	$options );

// Load innate memory (optionnal)
// @see (documentation : todo)
$bot -> setInnateMemory( $memoryFilePath );

// Learn some data (optional)
$bot  -> learn('Weather', 'Sun shine');
$bot  -> learn('Myself:name', 'Smart BOT');
// ...

// Add custom listener (optionnal)
$bot -> addListener( 'SmartBot\Bot\Listener\CustomListener' );

// Add custom contexts
$bot -> addContext('Time:morning');
$bot -> addContext(array('Humor:Funny','...'));

// Sets the user who's talking to the bot (required)
// It may be the logged user in your app, 
// or simply the PHP Session ID
$bot -> setEntity('Bruno VIBERT');

// Talk to the bot (optionnal, but recommanded ;))
$response = $bot -> talk('Hello !'); // Hello, Hi...
$response = $bot -> talk('My name is John'); // Ok, John, I will remember that !
$response = $bot -> talk('Hi'); // Hello, John
```
