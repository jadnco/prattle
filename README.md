# Prattle

An online web chatting app

After cloning this repo to your local machine, navigate to `includes/init.php` and make sure to update the following constants with relevant data:

```php
// Get ROOT_PATH by calling __DIR__ (without /public)
define("ROOT_PATH", "/Applications/XAMPP/xamppfiles/htdocs/prattle");
define("INC_PATH", ROOT_PATH . "/includes/");

// Base url (public folder); eg. http://example.com/public
define("BASE_URL", "http://localhost/prattle/public");

// Add the home url; eg. http://example.com
define("HOME", "http://localhost/prattle");
```

All user and conversation data is stored within `JSON` files, which are automatically created. These files are given a `CHMOD` flag of `0600` for security measures.

Check out the [Dribbble](https://dribbble.com/jaden/projects/228211-Prattle) project if you want to see some screenshots of *Prattle* or [jadn.co](http://jadn.co/prattle) for more information.
