<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;
use KLua\KLuaException;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

// This is a very simplified example.
// The point is: you can do this using Lua solely.
// There is no need to have KLua API for that.

class ScriptRunner {
    public static function init() {
        KLua::eval('
            function set_time_limit(secs)
                local start = os.clock()
                local check_tick = function() 
                    if os.clock()-start > secs then 
                        debug.sethook() -- disable hooks
                        error("time limit reached")
                    end
                end
                debug.sethook(check_tick, "", 1000);
            end
        ');
    }

    public static function run($code, $time_limit = 1.0) {
        KLua::eval("
            set_time_limit($time_limit);
            do
                -- TODO: setup sandbox env, so user can't
                -- remove the time limit or break out of the
                -- constrained context.
                -- See plugin_sandbox.php example.
                $code
            end
        ");
    }
}

KLua::init(new KLuaConfig());
ScriptRunner::init();

ScriptRunner::run('
    print("This executes successfully")
');

try {
    // This eval fails during the execution.
    ScriptRunner::run('
        local function fib(n) 
            if n<2 then
                return n
            else
                return fib(n-2)+fib(n-1)
            end
        end
        local total=0
        for i=1, 1000000 do
            total = total + fib(i)
        end
        print(total)
    ', 0.2);
} catch (KLuaException $e) {
    // We can handle the error and continue normally.
    echo "eval error: " . $e->getMessage() . "\n";
}
