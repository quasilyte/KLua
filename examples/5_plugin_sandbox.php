<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

KLua::init(new KLuaConfig());

// The KLua instance is shared for the entire request.
// So if your application needs to load several plugins,
// there is always a chance that there will be some
// name conflict at some point.
//
// To avoid that, we'll load every plugin inside isolated environment.
// In the real life, you may want to do something more complicated.
// This is just an example.

// Every plugin defined main() function.
// That's part of our plugin contract.
//
// Also, we define helper_func in both of them on purpose.

$plugin1 = '
function main()
    print("plugin1 called")
    print(helper_func())
end

function helper_func()
    return 10
end
';

$plugin2 = '
function main()
    print("plugin2 called")
    print(helper_func())
end

function helper_func()
    return 20
end
';

class PluginManager {
    /** @var string[] */
    private $plugins = [];

    public function __construct() {
        KLua::eval('
            _app_plugins = {}
        ');
    }

    public function load($name, $code) {
        $this->plugins[] = $name;
        KLua::eval("
            local _main_func = nil;
            do
                local new_env = {}
                setmetatable(new_env, {__index = _ENV})
                local _ENV = new_env

                $code

                _main_func = main
            end
            _app_plugins['$name'] = _main_func
        ");
    }

    public function run() {
        foreach ($this->plugins as $p) {
            KLua::callStaticMethod('_app_plugins', $p);
        }
    }
}

$plugin_manager = new PluginManager();
$plugin_manager->load('a', $plugin1);
$plugin_manager->load('b', $plugin2);
$plugin_manager->run();
