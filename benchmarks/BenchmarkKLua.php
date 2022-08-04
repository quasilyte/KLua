<?php

use KLua\KLua;
use KLua\KLuaConfig;

class BenchmarkKLua {
    public function __construct() {
        if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

        $config = new KLuaConfig();
        KLua::init($config);
        KLua::evalScript(__DIR__ . '/testdata/testlib.lua');

        KLua::eval('
            global_vec2 = Vector2:new(-15, 30)
        ');

        KLua::setVar('global_nil', null);
        KLua::setVar('global_number', 10);
        KLua::setVar('global_string', 'strvalue');
        KLua::setVar('global_array_table', [1, 2, 3]);
        KLua::setVar('global_map_table', ['foo' => 1, 'bar' => 2]);

        KLua::registerFunction0('phpfunc0', static function () {
            return null;
        });
        KLua::registerFunction1('phpfunc1', static function ($x) {
            return null;
        });
        KLua::registerFunction2('phpfunc2', static function ($x, $y) {
            return null;
        });
        KLua::registerFunction3('phpfunc3', static function ($x, $y, $z) {
            return null;
        });
        KLua::registerFunction4('phpfunc4', static function ($x1, $x2, $x3, $x4) {
            return null;
        });
    }

    public function benchmarkCallStaticMethod() {
        return KLua::callStaticMethod('math', 'abs', -5);
    }

    public function benchmarkEvalCallMethodAngle() {
        return KLua::eval('return global_vec2:angle()');
    }

    public function benchmarkEvalCallMethodAbs() {
        return KLua::eval('return global_vec2:abs()');
    }

    public function benchmarkCallMethodAngle() {
        return KLua::callMethod('global_vec2', 'angle');
    }

    public function benchmarkCallMethodAbs() {
        return KLua::callMethod('global_vec2', 'abs');
    }

    public function benchmarkEvalPHPCall0() {
        return KLua::eval('return phpfunc0()');
    }

    public function benchmarkEvalCall0() {
        return KLua::eval('return zero()');
    }

    public function benchmarkEvalCall1() {
        return KLua::eval('return add1(10)');
    }

    public function benchmarkEvalCall2() {
        return KLua::eval('return concat2("x", "y")');
    }

    public function benchmarkCall0() {
        return KLua::call('zero');
    }

    public function benchmarkCall1() {
        return KLua::call('add1', 10);
    }

    public function benchmarkCall2() {
        return KLua::call('concat2', 'x', 'y');
    }

    public function benchmarkPHPCall0() {
        return KLua::call('phpfunc0');
    }

    public function benchmarkPHPCall1() {
        return KLua::call('phpfunc1', null);
    }

    public function benchmarkPHPCall2() {
        return KLua::call('phpfunc2', null, null);
    }

    public function benchmarkPHPCall3() {
        return KLua::call('phpfunc3', null, null, null);
    }

    public function benchmarkPHPCall4() {
        return KLua::call('phpfunc4', null, null, null, null);
    }

    public function benchmarkCall0Builder() {
        return KLua::callBuilder('zero')->call()->getValue();
    }

    public function benchmarkCall0BuilderDiscard() {
        return KLua::callBuilder('zero')->call()->discardValue();
    }

    public function benchmarkCall1Builder() {
        return KLua::callBuilder('add1')->pushNumber(10)->call()->getValue();
    }

    public function benchmarkCall1BuilderDiscard() {
        return KLua::callBuilder('add1')->pushNumber(10)->call()->discardValue();
    }

    public function benchmarkCall2Builder() {
        return KLua::callBuilder('concat2')->pushString('x')->pushString('y')->call()->getValue();
    }

    public function benchmarkCall2BuilderDiscard() {
        return KLua::callBuilder('concat2')->pushString('x')->pushString('y')->call()->discardValue();
    }

    public function benchmarkSetVarNil() {
        return KLua::setVar('dummy', null);
    }

    public function benchmarkSetVarNumber() {
        return KLua::setVar('dummy', 10);
    }

    public function benchmarkSetVarString() {
        return KLua::setVar('dummy', 'strvalue');
    }

    public function benchmarkSetVarArrayTable() {
        return KLua::setVar('dummy', [1, 2, 3]);
    }

    public function benchmarkSetVarMapTable() {
        return KLua::setVar('dummy', ['foo' => 1, 'bar' => 2]);
    }

    public function benchmarkGetVarNil() {
        return KLua::getVar('global_nil');
    }

    public function benchmarkGetVarNumber() {
        return KLua::getVar('global_number');
    }

    public function benchmarkGetVarString() {
        return KLua::getVar('global_string');
    }

    public function benchmarkGetVarArrayTable() {
        return KLua::getVar('global_array_table');
    }

    public function benchmarkGetVarMapTable() {
        return KLua::getVar('global_map_table');
    }
}
