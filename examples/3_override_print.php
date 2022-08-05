<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

KLua::init(new KLuaConfig());

class LuaLogger {
    public $messages = [];

    public function doPrint($arg) {
        $this->messages[] = $arg;
        return null;
    }
}

$logger = new LuaLogger();

// Let's save all print arguments instead of letting Lua to
// print to stdout freely.
KLua::registerFunction1('print', [$logger, 'doPrint']);

KLua::eval('
    print(1)
    print("hello")
');

// Now we can process the print() arguments here, outside
// of the Lua context.
foreach ($logger->messages as $msg) {
    var_dump(['message' => $msg]);
}
