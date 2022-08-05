<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

KLua::init(new KLuaConfig());

// Suppose that you want to provide a PCRE regexp library
// to your Lua scripts. To make it more idiomatic, you would
// want to put those regexp functions in some table named "pcre".

// KLua doesn't provide a way to register a table-bound function,
// but it's easy to achieve our goal anyway.

// We use "php_" prefix to avoid accidental name clashes.
KLua::registerFunction2('php_preg_match', function ($pat, $s) {
    return preg_match($pat, $s) === 1;
});

// Now let's create a neat wrapper.
KLua::eval('
    pcre = {}

    function pcre.match(pat, s)
        return php_preg_match(pat, s)
    end
');

// The rest of the Lua code can now use pcre library.
KLua::eval('
    print(pcre.match("/[0-9]+/", "abc"))
    print(pcre.match("/[0-9]+/", "435"))
');
