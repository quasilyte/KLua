<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

// print() function is not available in Lua by default,
// it's provided by the "base" library that binds symbols to _G.
// When we use the default KLua config, all libraries are preloaded,
// so we don't need to worry about this.
// If we want to hand-pick what parts of stdlib we want to preload,
// $config->preload_stdlib array should be used.
$config = new KLuaConfig();
$config->preload_stdlib = ['base'];
KLua::init($config);

// Now we can use the print() function.
KLua::eval('
    print("Hello, World")
');
