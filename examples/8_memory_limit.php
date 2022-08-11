<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;
use KLua\KLuaException;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

// Note: it's not advised to set the memory limit that is too low.
// Lua may not be prepared to get a null pointer during it's
// code parsing and other low-level routines.
// The limit we're using here is really low, you should
// use something bigger in the real-world use cases.
const LUA_MAX_MEM_BYTES = 1024 * 30;

$config = new KLuaConfig();
$config->alloc_hook = function ($alloc_size) {
    // To learn how much memory Lua is using right now
    // we need to use KLua::getStats().
    $stats = KLua::getStats();
    $mem_free = LUA_MAX_MEM_BYTES - $stats->mem_usage;
    return $mem_free >= $alloc_size;
};
KLua::init($config);

// This eval runs successfully.
KLua::eval('print("hello!")');

try {
    // This eval fails during the execution.
    KLua::eval('
        local s = ""
        for i=1, 1000000 do
            s = s .. "x"
        end
        print(#s)
    ');
} catch (KLuaException $e) {
    // We can handle the error and continue normally.
    echo "eval error: " . $e->getMessage() . "\n";
}
