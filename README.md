# KLua

KLua is a [FFI](https://www.php.net/manual/ru/class.ffi.php)-based [Lua5](https://www.lua.org) library that can be used in both PHP and [KPHP](github.com/VKCOM/kphp).

## Installation

TODO

## Value conversion

| PHP Type | Lua Type | Operation Cost |
|---|---|---|
| bool | boolean | free |
| int | number | free |
| float | number | free |
| string | string | string data is copied |
| map-like array | table | expensive conversion |
| list-like array | sequence table | expensive conversion |

All conversions are symmetrical, except for the Lua->PHP case of sequence tables.

If `KLuaConfig::pack_lua_tables` is set to `false`, Lua tables will be returned "as is".
If that option is set to `true` (the default), then KLua will try to return Lua sequence
tables as list-like PHP arrays.

Not every PHP value can be converted to a Lua value and vice versa.
Both languages should communicate to each other using the simpler protocols.

## API reference

All `KLua` class methods are static.

`KLua` initialization methods:

* `loadFFI()`
* `init($config)`
* `close()`

`KLua` utility methods:

* `getStats()`

`KLua` main methods:

* `eval($code)`
* `evalScript($filename)`
* `setVar($var_name, $value)`
* `getVar($var_name)`
* `registerFunction($func_name, $php_func)`
* `call($func_name, ...$args)`
* `callStaticMethod($table_name, $method_name, ...$args)`
* `callMethod($table_name, ...$args)`
* `callBuilder($func_name)`
* `staticMethodCallBuilder($table_name, $method_name)`
* `methodCallBuilder($table_name, $method_name)`

### KLua::loadFFI

```php
function loadFFI();
```

Perform the `FFI::load()`.

For PHP, call this function from the opcache preload script.
For KPHP, call this function once under the `if (kphp)` condition.

### KLua::init

```php
/**
 * @param KLuaConfig $config - KLua library configuration options
 */
function init($config);
```

`init()` prepares the Lua state and binds it to this class static members.

It's usually only necessary to call this method exactly once.

See the `KLuaConfig` fields to learn the configuration details.

### KLua::close

```php
function close();
```

`close()` performs the current Lua state de-initialization.

All memory that is still retained by Lua will be reclaimed.

### KLua::eval

```php
/**
 * @param string $code - Lua code to be executed
 * @return mixed - evaluation result (null for void)
 * @throws KLuaException
 */
function eval($code);
```

`eval()` executes a given Lua code and returns its results (if any).

The code being evaluated is not sandboxed, it's up to the caller to
properly prepare this code chunk for being safely executed.

While flexible, `eval()` may not be the most efficient way to do whay you want.
There are some specialized alternatives that should be used in those cases:

* To call a function, consider `call()`, `callStaticMethod`, `callMethod()`
* To get/set a variable, use `getVar()` and `setVar()`

If error occurs during the script parse or execution, an exception is throwed.

`eval()` will return a non-null result for scripts that have a `return` statement.

### KLua::evalScript

```php
/**
 * @param string $filename - Lua script filename
 * @return mixed - evaluation result (null for void)
 * @throws KLuaException
 */
function evalScript($filename);
```

`evalScript()` is a convenience wrapper useful for debugging.

It basically forwards the `file_get_content()` results to `eval()`.

If the specified file can't be read, an exception is thrown.

### KLua::setVar

```php
/**
 * @param string $var_name - Lua global variable name
 * @param mixed $value - a PHP value to be assigned to the specified Lua variable
 * @throws KLuaException
 */
function setVar($var_name, $value);
```

`setVar()` binds a given value to a global Lua variable.

If no such variable exists, it will be created.
If variable already exists, its value will be updated.

`setVar()` will throw an exception if `$value` can't be converted to Lua value.

### KLua::getVar

```php
/**
 * @param string $var_name - Lua global variable name
 * @return mixed - a Lua variable converted to PHP value
 * @throws KLuaException
 */
function getVar($var_name);
```

`getVar()` returns the global Lua variable value.

If variable doesn't exist, null is returned.

`getVar()` will throw an exception if `$var_name` value can't be converted to PHP value.

### KLua::registerFunction

```php
/**
 * @param string $func_name - a name that can be used in Lua to reference $fn
 * @param callable():mixed - a PHP function that can be called from Lua
 */
function registerFunction0($func_name, $fn);

/**
 * @param string $func_name - a name that can be used in Lua to reference $fn
 * @param callable(mixed):mixed - a PHP function that can be called from Lua
 */
function registerFunction1($func_name, $fn);

/**
 * @param string $func_name - a name that can be used in Lua to reference $fn
 * @param callable(mixed,mixed):mixed - a PHP function that can be called from Lua
 */
function registerFunction2($func_name, $fn);

// ...etc, up to registerFunction5
```

The bound PHP functions should respect these rules:

* Depending on the params count, appropriate register function should be used
* A function should return a Lua-convertible value

When PHP function is called from Lua, all arguments are converted following
the conversion rules described previously. Registered PHP function receives
the values that are already converted.

### KLua::call

```php
/**
 * @param string $func_name - Lua global function name
 * @param mixed[] $args - PHP values to be passed as Lua function arguments
 * @return mixed - a Lua function call result converted to PHP value
 * @throws KLuaException
 */
function call($func_name, ...$args);
```

Every function call argument should be a Lua-convertible value.

The Lua function results are returned as follow:

* For 0 results, null is returned
* For 1 result, this result is returned
* For more than 1 results, an array of results is returned

`call()` throws an exception if something goes wrong.

### KLua::callStaticMethod

```php
/**
 * @param string $table_name - Lua global variable name (should be table-typed)
 * @param string $method_name - a function-typed field name inside selected table
 * @param mixed[] $args - PHP values to be passed as Lua function arguments
 * @return mixed - a Lua function call result converted to PHP value
 * @throws KLuaException
 */
function callStaticMethod($table_name, $method_name, ...$args);
```

Like `call()`, but the function is searched inside a table.

The table itself is not passed as an argument to that method.
In other words, it behaves like `$table_name.$method_name($args...)`.

### KLua::callMethod

```php
/**
 * @param string $table_name - Lua global variable name (should be table-typed)
 * @param string $method_name - a function-typed field name inside selected table
 * @param mixed[] $args - PHP values to be passed as Lua function arguments
 * @return mixed - a Lua function call result converted to PHP value
 * @throws KLuaException
 */
function callMethod($table_name, $method_name, ...$args);
```

Like `call()`, but the function is searched inside a table.

Passes the table as a first parameter of the method.
In other words, it behaves like `$table_name:$method_name($args...)`.

### KLua::callBuilder

```php
/**
 * @param string $func_name - Lua global function name
 * @return ?KLuaCallBuilder - a Lua call builder
 * @throws KLuaException
 */
function callBuilder($func_name);
```

`callBuilder()` returns a Lua call builder that can be used to construct and execute
a complex function call expression.

Using the builder, you can avoid some PHP->Lua and Lua->PHP conversion.
This is not a purely optimization matter. Some values can't be converted
between the two languages, so there should be an alternative API that makes
it possible to use raw Lua values while calling Lua functions.

If `$func_name` doesn't exist, null is returned.

### KLua::staticMethodCallBuilder

```php
/**
 * @param string $table_name - Lua global variable name (should be table-typed)
 * @param string $method_name - a function-typed field name inside selected table
 * @return KLuaMethodCallBuilder - a Lua call builder
 * @throws KLuaException
 */
function staticMethodCallBuilder($table_name, $method_name, ...$args);
```

Like `callBuilder()`, but for static method calls.

If `$table_name` or `$table_name->$method_name` doesn't exist, null is returned.

### KLua::methodCallBuilder

```php
/**
 * @param string $table_name - Lua global variable name (should be table-typed)
 * @param string $method_name - a function-typed field name inside selected table
 * @return KLuaMethodCallBuilder - a Lua call builder
 * @throws KLuaException
 */
function methodCallBuilder($table_name, $method_name, ...$args);
```

Like `callBuilder()`, but for instance method calls.

If `$table_name` or `$table_name->$method_name` doesn't exist, null is returned.

### Call builder API

To make a call using a builder:

1. Get a suitable call builder
    * For functions, use `KLua::callBuilder()`
    * For static methods, use `KLua::staticMethodCallBuilder()`
    * For instance methods, use `KLua::methodCallBuilder()`
2. Push function arguments (if there are any)
3. Use `call()` method to invoke the function
4. Consume or discard the call results (if there are any)

Here is a list of argument pushing routines:

* `pushVar(string $var_name)` adds a Lua variable named `$var_name` to the list
* `pushNil()` adds a `nil` to the list
* `pushBool(bool $v)` adds bool-typed value to the list
* `pushNumber(float $v)` adds a float-typed value to the list
* `pushString(string $v)` adds a string-typed value to the list
* `pushTable(array $v)` adds an array-typed value to the list

If function returns zero results, you may omit the step `(4)`.

Otherwise, the results should be explicitely consumed or discarded.

* `getValue()` returns a single Lua result converted to PHP value
* `getValues(int $n)` returns `$n` Lua results converted to an array of PHP values
* `discardValue()` pops a single Lua result
* `discardValues(int $v)` pops `$n` Lua results
* `assignVar(string $var_name)` assigns a signle Lua result to a Lua variable named `$var_name`

Here is a simple example:

```php
// $result = math.abs($x)
$result = KLua::staticCallBuilder('math', 'abs')
    ->pushNumber($x)
    ->call()
    ->getValue();
```

You should not retain the builder object reference.
Both builder and result objects are re-used internally, to avoid allocations.
KLua provides a builder pattern API to make it more convenient,
but you can't really use it beyond a single call construction.
