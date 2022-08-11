<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KLua\KLua;
use KLua\KLuaConfig;

if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

// KLua supports "light userdata" (in terms of Lua).
// Since it's the only userdata type that works for 5.2, 5.3 and 5.4 versions.
// Please refer to Lua manual to learn more about the light userdata.
// KLua API spells it as just "UserData" for brevity.
// https://www.lua.org/pil/28.5.html

class UserData {
    /** @var ffi_scope<lua_userdata> */
    public static $lib;

    public static function init() {
        self::$lib = FFI::cdef('
            #define FFI_SCOPE "lua_userdata"
            struct Vec2 {
                double x;
                double y;
            };
        ');
    }

    public static function newVec2($x, $y) {
        $vec = self::$lib->new('struct Vec2');
        $vec->x = $x;
        $vec->y = $y;
        return $vec;
    }
}

KLua::init(new KLuaConfig());
UserData::init();

// One of the simplest way to make light userdata object
// available to Lua is to assign it to a global variable.
$vec2 = UserData::newVec2(10, 20);
KLua::setVarUserData('my_vec', FFI::addr($vec2));

KLua::registerFunction1('vec2_x', function ($vec_addr) {
    // userdata arguments are passed as PHP int values.
    // These values hold an address that can be converted back
    // to a pointer using KLua::userDataPtr().
    $ptr = KLua::userDataPtr((int)$vec_addr);
    // Since $ptr is void*, you'll need to cast it to a
    // properly-typed pointer before using it.
    $vec2 = UserData::$lib->cast('struct Vec2*', $ptr);
    return $vec2->x;
});

KLua::registerFunction1('vec2_y', function ($vec_addr) {
    $ptr = KLua::userDataPtr((int)$vec_addr);
    $vec2 = UserData::$lib->cast('struct Vec2*', $ptr);
    return $vec2->y;
});


KLua::eval('
    print(vec2_x(my_vec)) -- 10.0
    print(vec2_y(my_vec)) -- 20.0

    function test_userdata(v)
        t = {x = vec2_x(v), y = vec2_y(v)}
        print(t.x, t.y)
    end
');

// Another way is to pass it as a function call argument.
// Note that you'll need to use a builder API to do that.
$other_vec = UserData::newVec2(-1.5, 1.5);
KLua::callBuilder('test_userdata')->
    pushUserData(FFI::addr($other_vec))->
    call();

// Since Lua->PHP conversion of light userdata involves
// a conversion to int, you'll need to perform a series of
// extra steps to make that value useful.
$addr = (int)KLua::getVar('my_vec');
$void_ptr = KLua::userDataPtr($addr);
$vec_ptr = UserData::$lib->cast('struct Vec2*', $void_ptr);
var_dump([$vec_ptr->x, $vec_ptr->y]);
