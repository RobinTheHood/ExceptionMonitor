# Exception Monitor


![alt text](https://raw.githubusercontent.com/RobinTheHood/ExceptionMonitor/master/docs/Example-Image.png)


## About
ExceptionMonitor gives you nice looking PHP Error Messages. If you wish more features feel free to contribute.

## Installation


If Composer is installed globally, run

```bash
composer require robinthehood/exception-monitor
```

## How to use

### Code
```php
error_reporting(E_ALL);
require '../vendor/autoload.php';
ExceptionMonitor\ExceptionMonitor::register();
```

### Settings
| Index  | Description                                           | Example value    |
|--------|-------------------------------------------------------|------------------|
| ip     | Shows the ExceptionMonitor only if the ip matches     | 127.0.0.1        |
| domain | Shows the ExceptionMonitor only if the domain matches | www.example.com  |
| mail   | Send Mail to this address if ip/domain not match      | info@example.com |

If an ip and a domain are specified, both must match in order to display the ExceptionMonitor.

# License
Copyright (c) 2017 Robin Wieschendorf

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
