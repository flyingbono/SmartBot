# SmartBot

Multilinguage bot responder implemented in PHP, with learning capabilities

### Installation :
```
composer.phar require flyingbono/smart-bot
```

### Basic usage :

```php
<?php
require_once '../vendor/autoload.php';

$bot = new \SmartBot\Bot( 
				// Data path (innate memory and acquired memory storage)
				'/tmp/smartbot-data/', 
				
				// Default listener
				'SmartBot\Bot\Listener\EnUSListener' );

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
