# ReTry mechanism for any php application

[![ReTry](https://github.com/apacheborys/re-try-php/actions/workflows/php.yml/badge.svg)](https://github.com/apacheborys/re-try-php/actions/workflows/php.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

With this library you will be able to introduce re-try approach in simple way:
1. Create configuration
2. When your application start, please add next 2 rows in the begining:
```php
$retry = new ApacheBorys\Retry\ExceptionHandler($config);
$retry->initHandler();
```
3. Start by another process code like that:
```php
$worker = new ApacheBorys\Retry\MessageHandler($config);
while (true) {
    $worker->processRetries();
    sleep(1);
}
```

Config example:

```json
{
    "test": {       /* name of retry */
        "exception": "ApacheBorys\\Retry\\Tests\\Functional\\Exceptions\\Mock", /* what type of Exception we would like to retry */
        "maxRetries": 4, /* how many tries we should do */
        /* here we are describing formula, how next execution time should be calculated. Calculated amount will be added to current time */
        "formula": [ 
            {
                "operator": "+", /* here available *, -, + and / operators */
                "argument": "QTY_TRIES" /* you can use QTY_TRIES operator or any integer value */
            },
            {
                "operator": "*",
                "argument": "5"
            }
        ],
        "transport": { /* here you should define, what kind of transport you would use to deliver re-try messages to worker */
            "class": "ApacheBorys\\Retry\\Tests\\Functional\\Transport\\FileTransportForTests",
            "arguments": [ /* each specific transport could have own arguments in constructor. Here you should define it */
                "tests\/transport.data"
            ]
        },
        "executor": { /* here you should define, what kind of executor you would use to perform re-try action */
            "class": "ApacheBorys\\Retry\\Tests\\Functional\\Executor\\Runtime",
            "arguments": [] /* each specific executor could have own arguments in constructor. Here you should define it */
        }
    }
}
```