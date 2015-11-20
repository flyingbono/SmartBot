# SmartBot
[![Build Status](https://img.shields.io/travis/flyingbono/SmartBot/master.svg?@SHA)](https://travis-ci.org/flyingbono/SmartBot)
[![Code Climate](https://codeclimate.com/github/flyingbono/SmartBot/badges/gpa.svg?@SHA)](https://codeclimate.com/github/flyingbono/SmartBot)
[![Test Coverage](https://codeclimate.com/github/flyingbono/SmartBot/badges/coverage.svg?@SHA)](https://codeclimate.com/github/flyingbono/SmartBot/coverage)
[![Issue Count](https://codeclimate.com/github/flyingbono/SmartBot/badges/issue_count.svg?@SHA)](https://codeclimate.com/github/flyingbono/SmartBot)
[![Translations](https://img.shields.io/badge/translations-1-red.svg?@SHA)](https://github.com/flyingbono/SmartBot/tree/master/lib/SmartBot/Bot/Listener)


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
	'caller'	=> 'CallerUID', // ID of the person talking to the bot (default: null)
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
$bot -> addListener( new MyCustomLister );

// Add custom contexts
$bot -> addContext('Time:morning');
$bot -> addContext(array('Humor:Funny','...'));

// Sets the user who's talking to the bot (required)
// It may be the logged user in your app, 
// or simply the PHP Session ID
$bot -> setCaller('Bruno VIBERT');

// Talk to the bot (optionnal, but recommanded ;))
$response = $bot -> talk('Hello !'); // Hello, Hi...
$response = $bot -> talk('My name is John'); // Ok, John, I will remember that !
$response = $bot -> talk('Hi'); // Hello, John
```
