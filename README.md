![](docs/logo.png)

# KLua

KLua is a [FFI](https://www.php.net/manual/ru/class.ffi.php)-based [Lua5](https://www.lua.org) library that can be used in both PHP and [KPHP](github.com/VKCOM/kphp).

## Installation

Since this is a FFI library, it needs a dynamic library available during the run time.

Installation steps:

1. Install liblua5 in your system (if you don't have it already)
2. Locate the library file and place in under `./ffilibs/liblua5`
3. Install this composer package to use KLua classes inside your code

Depending on your system, you need to find `liblua.so`, `liblua.dylib`
or `liblua.dll` file. Then you can copy it to the application root `ffilibs` folder
under the `liblua5` name (note: no extension suffixes).

If you're having difficulties locating the library file, use a helper script:

```bash
$ php -f locate_lib.php
note: can't locate liblua5.4, maybe it's not installed
library candidate: /lib/x86_64-linux-gnu/liblua5.3.so.0
library candidate: /lib/x86_64-linux-gnu/liblua5.3.so

run something like this to make it discoverable (unix):
	mkdir -p ffilibs && sudo ln -s /lib/x86_64-linux-gnu/liblua5.3.so ./ffilibs/liblua5.3
```

Then install the composer library itself:

```bash
$ composer require quasilyte/klua
```

Notes:

* If you want to place library files/links globally, make `./ffilibs` a symlink
* You'll probably want to add `ffilibs/` to your gitignore

## Examples

* [simple.php](examples/1_simple.php) - a simple overview of the API basics
* [phpfunc.php](examples/2_phpfunc.php) - how to bind PHP functions to Lua
* [override_print.php](examples/3_override_print.php) - override Lua `print()` stdlib function
* [limited_stdlib.php](examples/4_limited_stdlib.php) - how to limit the stdlib access to Lua scripts
* [plugin_sandbox.php](examples/5_plugin_sandbox.php) - how to load several plugins without conflicts
* [phpfunc_table.php](examples/6_phpfunc_table.php) - how to create module-like native libraries
* [userdata.php](examples/7_userdata.php) - how to use Lua [light userdata](https://www.lua.org/pil/28.5.html)

Running examples with PHP:

```bash
$ php -d opcache.enable_cli=1\
      -d opcache.preload=preload.php\
      -f ./examples/1_simple.php
```

Running examples with KPHP:

```bash
# Step 1: compile the example:
$ kphp --mode cli --composer-root $(pwd) ./examples/simple.php
# Step 2: run the binary:
$ ./kphp_out/cli
```

## Quick start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

// For PHP, loadFFI() is called from preload script.
// For KPHP, loadFFI() can be called in the beginning of the script.
if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

KLua::init(new KLuaConfig());

KLua::eval('
    function example(x)
        return x + 1
    end
');

var_dump(KLua::call('example', 10)); // => 11
```

Running with PHP:

```bash
$ php -d opcache.enable_cli=1 -d opcache.preload=preload.php -f example.php
float(11)
```

Running with KPHP:

```bash
# Compile
$ kphp --mode cli --composer-root $(pwd) example.php
# Execute
$ ./kphp_out/cli
float(11)
```

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

If some value can't be converted properly, a special error-like value is produced instead.

```
['_error' => 'error message']
```

The [light userdata](https://www.lua.org/pil/28.5.html) is a special case. It can't be auto-converted from a PHP value, but there are `KLua::setVarUserData` and call builder API `pushUserData` functions to pass user data from PHP to Lua. When Lua->PHP conversion is performed, the user data address is stored as PHP `int` value. You can convert that `int` addr to the CData `void*` by using `KLua::userDataPtr`. See [userdata.php](examples/7_userdata.php) for the complete example.

## API reference

See [api_reference.md](docs/api_reference.md) for full documentation.

All `KLua` class methods are static.

`KLua` initialization methods:

* [`loadFFI()`](docs/api_reference.md#klualoadffi)
* [`init($config)`](docs/api_reference.md#kluainit)
* [`close()`](docs/api_reference.md#kluaclose)

`KLua` main methods:

* [`eval($code)`](docs/api_reference.md#kluaeval)
* [`evalScript($filename)`](docs/api_reference.md#kluaevalscript)
* [`setVar($var_name, $value)`](docs/api_reference.md#kluasetvar)
* [`setVarUserData($var_name, $ptr)`](docs/api_reference.md#kluasetvaruserdata)
* [`getVar($var_name)`](docs/api_reference.md#kluagetvar)
* [`registerFunction($func_name, $php_func)`](docs/api_reference.md#kluaregisterfunction)
* [`userDataPtr($addr)`](docs/api_reference.md#kluauserdataptr)
* [`call($func_name, ...$args)`](docs/api_reference.md#kluacall)
* [`callStaticMethod($table_name, $table_key, ...$args)`](docs/api_reference.md#kluacallstaticmethod)
* [`callMethod($table_name, $table_key, ...$args)`](docs/api_reference.md#kluacallmethod)

`KLua` call builder methods:

* [`callBuilder($func_name)`](docs/api_reference.md#kluacallbuilder)
* [`staticMethodCallBuilder($table_name, $table_key)`](docs/api_reference.md#kluastaticmethodcallbuilder)
* [`methodCallBuilder($table_name, $table_key)`](docs/api_reference.md#kluamethodcallbuilder)

`KLua` utility methods:

* [`getStats()`](docs/api_reference.md#kluagetstate)
