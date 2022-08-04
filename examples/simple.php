<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

// For PHP, loadFFI() is called from preload script.
// For KPHP, loadFFI() can be called in the beginning of the script.
if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

// Before using KLua library, a single init() call is required.
// We're using the default config for simplicity.
KLua::init(new KLuaConfig());

KLua::eval('
    function example(x)
        return x + 1
    end
');

var_dump(KLua::call('example', 10)); // => 11
