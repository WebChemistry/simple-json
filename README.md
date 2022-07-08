## Simple json parser

JSON which is not so strict and very similar to JS object syntax.


`{ key: value, }` = ` ['key' => 'value'] ` \
`{ 'key': "value", }` = ` ['key' => 'value'] ` \
`{ { foo }, { bar } }` = ` [['foo'], ['bar']] ` \
`{ foo, 15: bar, foo }` = ` [0 => 'foo', 15 => 'bar', 16 => 'foo'] ` \
`{ 15 }` = ` 15 ` \
`{ 42.42 }` = ` 42.42 ` \
`[ 1, 2 ]` = ` [1, 2] ` 


### Usage

```php
WebChemistry\SimpleJson\SimpleJsonParser::parse($string);
```
