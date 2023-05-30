# ReTry mechanism for any php application

[![ReTry](https://github.com/apacheborys/re-try-php/actions/workflows/php.yml/badge.svg)](https://github.com/apacheborys/re-try-php/actions/workflows/php.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

That library still in development phase. Please don't use it until first release

With this library you will be able to introduce re-try approach in simple way:
1. Create configuration
2. When your application start, please add next 2 rows in the begining:
```php
$factory = new \ApacheBorys\Retry\HandlerFactory($config);
$retry = $factory->createExceptionHandler($yourContainer);
```
3. Start by another process code like that:
```php
$factory = new \ApacheBorys\Retry\HandlerFactory($config);
$worker = $factory->createMessageHandler($yourContainer);
while (true) {
    $worker->processRetries();
    sleep(1);
}
```

Config example:

```json
{
    /**
      * here we can define declarator what should register exception handling callback function, if you are plan to use 
      * standard php function set_exception_handler - you can ignore that section. StandardHandlerExceptionDeclarator is default
     **/
    "handlerExceptionDeclarator": {
      "class": "ApacheBorys\\Retry\\HandlerExceptionDefiner\\StandardHandlerExceptionDeclarator",
      "arguments": []
    },
    "items": {
      /* name of retry */
      "test": {
        /* what type of Exception we would like to retry */
        "exception": "ApacheBorys\\Retry\\Tests\\Functional\\Exceptions\\Mock",
        /* how many tries we should do */
        "maxRetries": 4,
        /* here we are describing formula, how next execution time should be calculated. Calculated amount will be added to current time */
        "formula": [
          {
            /* here available *, -, + and / operators */
            "operator": "+",
            /* you can use QTY_TRIES operator or any integer value */
            "argument": "QTY_TRIES"
          },
          {
            "operator": "*",
            "argument": "5"
          }
        ],
        /* here you should define, what kind of transport you would use to deliver re-try messages to worker. Please pay your attention to https://github.com/apacheborys/re-try-php-basics-lib */
        "transport": {
          "class": "ApacheBorys\\Retry\\Tests\\Functional\\Transport\\FileTransportForTests",
          /* each specific transport could have own arguments in constructor. Here you should define it */
          "arguments": [
            "tests\/transport.data"
          ]
        },
        /* here you should define, what kind of executor you would use to perform re-try action */
        "executor": {
          "class": "ApacheBorys\\Retry\\Tests\\Functional\\Executor\\Runtime",
          /* each specific executor could have own arguments in constructor. Here you should define it */
          "arguments": []
        }
      }
    }
}
```

The notice about `handlerExceptionDeclarator`, `transport` and `executor`:

As second argument for constructor of `ApacheBorys\Retry\ExceptionHandler` and `ApacheBorys\Retry\MessageHandler` you can send ContainerInterface. In this case, you can define arguments for `handlerExceptionDeclarator`, `transport` and `executor` as instances from runtime. It will be fetched from this injected Container.

For example:
```json
...
    "transport": {
        "class": "ApacheBorys\\Retry\\BasicTransport\\PdoTransport,
        "arguments": [
            "@pdoInstanceFromYourContainer"
        ]
    },
...
```

Leading `@` indicates - you are trying to inject some instance from your container.

Also, if you don't want to use Container to inject some instances from runtime. But you still need to create some instances to ensure proper execution for  `handlerExceptionDeclarator`, `transport` and `executor`, you can use next construction:

```json
...
    "transport": {
        "class": "ApacheBorys\\Retry\\BasicTransport\\PdoTransport,
        "arguments": [
            [
                    "class": "\\PDO",
                    "arguments": [
                        "sqlite:/app/storage/retry-db.data'"
                    ]
            ]
        ]
    },
...
```

In this case, handler/declarator will try to instantiate described class with arguments. In these arguments you can use same tricks with leading `@`; and `class`, `arguments` constructions.

Leading `@` works with `class` too.