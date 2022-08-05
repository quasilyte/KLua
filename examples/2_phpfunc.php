<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

KLua::init(new KLuaConfig());

// Use KLua::registerFunction to make PHP code callable from Lua.
// The number suffix specifies how many parameters your PHP function has.
KLua::registerFunction1('php_json_encode', function ($value) {
    return json_encode($value);
});

// You can use conventional "function references"
// and even static/instance method references here.
KLua::registerFunction1('php_json_decode', 'php_json_decode');

/** @kphp-required */
function php_json_decode($s) {
    return json_decode($s, true);
}

// Registered PHP functions are now accessible via Lua code.
KLua::eval('
    table_encoded = php_json_encode({1, 2, 3})
    table_decoded = php_json_decode(table_encoded)
');

// => "[1,2,3]"
var_dump(KLua::getVar('table_encoded'));

// => [1, 2, 3]
var_dump(KLua::getVar('table_decoded'));
