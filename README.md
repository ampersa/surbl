This package enables you to query multi.surbl.org with a URL and determine if the domain is listed.

## Installation
Composer
```
$ composer require ampersa\surbl
```

## Usage
**Basic usage**  
```php
use Ampers\SURBL\SURBL;
...

$surbl = new SURBL;
$result = $surbl->listed('http://ampersa.co.uk');
// Returns: (bool) false

$result = $surbl->listed('http://surbl-org-permanent-test-point.com/');
// Returns: (bool) true
```

**Specify lists to query**  
By default, all lists (phishing (PH), malware (MW), AbuseButler (ABUSE) and cracked (CR)) are queried.

To specify lists to use, pass a bitmask of options to the constructor
```php
$surbl = new SURBL(SURBL::LIST_PH | SURBL::LIST_MW);
$result = $surbl->listed('http://surbl-org-permanent-test-point.com/');
// Returns: (bool) false
```

**Call statically**  
A static accessor has been included to provide shorthand access to the listed() function. The second argument may be used to pass the bitmask of options.
```php
$result = SURBL::isListed('http://surbl-org-permanent-test-point.com/');
// Returns: (bool) true

$result = SURBL::isListed('http://surbl-org-permanent-test-point.com/', SURBL::LIST_PH | SURBL::LIST_MW);
// Returns: (bool) false
```

## Contributing
1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request
