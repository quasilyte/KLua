<?php

use PHPUnit\Framework\TestCase;
use KLua\KLua;
use KLua\KLuaInternal;
use KLua\KLuaConfig;

class KLuaCallBuilderTest extends TestCase {
    public static function setUpBeforeClass(): void {
        if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

        $config = new KLuaConfig();
        KLua::init($config);
        KLua::evalScript(__DIR__ . '/testdata/testlib.lua');

        KLua::eval('
            tmp_var = nil
            global_ten = 10
            global_vec = Vector2:new(1, 2)
            stdlib = Stdlib:new()
        ');
    }

    public static function tearDownAfterClass(): void {
        KLua::close();
    }

    public function testPushArgs() {
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals('boolean', KLua::callBuilder('type')->pushBool(true)->call()->getValue());
            $this->assertEquals('boolean', KLua::callBuilder('type')->pushBool(false)->call()->getValue());
            $this->assertEquals('number', KLua::callBuilder('type')->pushVar('global_ten')->call()->getValue());
            $this->assertEquals('number', KLua::callBuilder('type')->pushNumber(0)->call()->getValue());
            $this->assertEquals('number', KLua::callBuilder('type')->pushNumber(3.5)->call()->getValue());
            $this->assertEquals('string', KLua::callBuilder('type')->pushString('')->call()->getValue());
            $this->assertEquals('string', KLua::callBuilder('type')->pushString('hello')->call()->getValue());
            $this->assertEquals('nil', KLua::callBuilder('type')->pushNil()->call()->getValue());
            $this->assertEquals('table', KLua::callBuilder('type')->pushTable([])->call()->getValue());
            $this->assertEquals('table', KLua::callBuilder('type')->pushTable([1])->call()->getValue());
            $this->assertEquals('table', KLua::callBuilder('type')->pushTable([1, 2])->call()->getValue());
            $this->assertEquals('table', KLua::callBuilder('type')->pushTable(['key' => 'val'])->call()->getValue());
            $this->assertTrue(KLuaInternal::checkStack());

            $this->assertEquals('boolean', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushBool(true)->call()->getValue());
            $this->assertEquals('boolean', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushBool(false)->call()->getValue());
            $this->assertEquals('number', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushVar('global_ten')->call()->getValue());
            $this->assertEquals('number', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushNumber(0)->call()->getValue());
            $this->assertEquals('number', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushNumber(3.5)->call()->getValue());
            $this->assertEquals('string', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushString('')->call()->getValue());
            $this->assertEquals('string', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushString('hello')->call()->getValue());
            $this->assertEquals('nil', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushNil()->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushTable([])->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushTable([1])->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushTable([1, 2])->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('stdlib', 'static_type')->pushTable(['key' => 'val'])->call()->getValue());
            $this->assertTrue(KLuaInternal::checkStack());

            $this->assertEquals('boolean', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushBool(true)->call()->getValue());
            $this->assertEquals('boolean', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushBool(false)->call()->getValue());
            $this->assertEquals('number', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushVar('global_ten')->call()->getValue());
            $this->assertEquals('number', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushNumber(0)->call()->getValue());
            $this->assertEquals('number', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushNumber(3.5)->call()->getValue());
            $this->assertEquals('string', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushString('')->call()->getValue());
            $this->assertEquals('string', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushString('hello')->call()->getValue());
            $this->assertEquals('nil', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushNil()->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushTable([])->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushTable([1])->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushTable([1, 2])->call()->getValue());
            $this->assertEquals('table', KLua::staticMethodCallBuilder('Stdlib', 'static_type')->pushTable(['key' => 'val'])->call()->getValue());
            $this->assertTrue(KLuaInternal::checkStack());

            $this->assertEquals('boolean', KLua::methodCallBuilder('stdlib', 'type')->pushBool(true)->call()->getValue());
            $this->assertEquals('boolean', KLua::methodCallBuilder('stdlib', 'type')->pushBool(false)->call()->getValue());
            $this->assertEquals('number', KLua::methodCallBuilder('stdlib', 'type')->pushVar('global_ten')->call()->getValue());
            $this->assertEquals('number', KLua::methodCallBuilder('stdlib', 'type')->pushNumber(0)->call()->getValue());
            $this->assertEquals('number', KLua::methodCallBuilder('stdlib', 'type')->pushNumber(3.5)->call()->getValue());
            $this->assertEquals('string', KLua::methodCallBuilder('stdlib', 'type')->pushString('')->call()->getValue());
            $this->assertEquals('string', KLua::methodCallBuilder('stdlib', 'type')->pushString('hello')->call()->getValue());
            $this->assertEquals('nil', KLua::methodCallBuilder('stdlib', 'type')->pushNil()->call()->getValue());
            $this->assertEquals('table', KLua::methodCallBuilder('stdlib', 'type')->pushTable([])->call()->getValue());
            $this->assertEquals('table', KLua::methodCallBuilder('stdlib', 'type')->pushTable([1])->call()->getValue());
            $this->assertEquals('table', KLua::methodCallBuilder('stdlib', 'type')->pushTable([1, 2])->call()->getValue());
            $this->assertEquals('table', KLua::methodCallBuilder('stdlib', 'type')->pushTable(['key' => 'val'])->call()->getValue());
            $this->assertTrue(KLuaInternal::checkStack());
        }
    }

    public function testResultValue() {
        for ($i = 0; $i < 3; $i++) {
            $result = KLua::callBuilder('add1')->pushNumber(0)->call()->getValue();
            $this->assertEquals(KLua::call('add1', 0), $result);

            $result = KLua::staticMethodCallBuilder('FuncArray', 1)
                ->pushNumber(10)
                ->pushNumber(20)
                ->call()
                ->getValue();
            $this->assertEquals(10*20, $result);
            $this->assertTrue(KLuaInternal::checkStack());
            $result = KLua::staticMethodCallBuilder('FuncArray', 2)
                ->pushNumber(10)
                ->pushNumber(20)
                ->call()
                ->getValue();
            $this->assertEquals(10+20, $result);
            $this->assertTrue(KLuaInternal::checkStack());

            $expected = [];
            for ($j = 1; $j <= 4; $j++) {
                $expected[] = $j;
                $values = KLua::callBuilder('multiret')
                    ->pushNumber($j)
                    ->call()
                    ->getValues($j);
                $this->assertTrue(KLuaInternal::checkStack());
                $this->assertEquals($expected, $values);
            }

            $this->assertTrue(KLuaInternal::checkStack());

            [$x, $y] = KLua::methodCallBuilder('global_vec', 'get_components')
                ->call()
                ->getValues(2);
            $this->assertEquals(1.0, $x);
            $this->assertEquals(2.0, $y);

            $this->assertTrue(KLuaInternal::checkStack());

            $v = KLua::staticMethodCallBuilder('Vector2', 'create')
                ->pushNumber(10)
                ->pushNumber(20)
                ->call()
                ->getValue();
            $this->assertEquals(['x' => 10.0, 'y' => 20.0], $v);

            $this->assertTrue(KLuaInternal::checkStack());
        }
    }

    public function testResultDiscard() {
        for ($i = 0; $i < 3; $i++) {
            KLua::callBuilder('add1')->pushNumber(0)->call()->discardValue();
            $this->assertEquals(1, KLua::call('add1', 0));

            KLua::callBuilder('multiret')->pushNumber(0)->call();
            $this->assertEquals(1, KLua::call('add1', 0));
            for ($j = 1; $j <= 3; $j++) {
                KLua::callBuilder('multiret')->pushNumber($j)->call()->discardValues($j);
                $this->assertTrue(KLuaInternal::checkStack());
                $this->assertEquals(1, KLua::call('add1', 0));
            }

            $this->assertTrue(KLuaInternal::checkStack());

            KLua::methodCallBuilder('global_vec', 'get_components')
                ->call()
                ->discardValues(2);

            $this->assertTrue(KLuaInternal::checkStack());

            KLua::staticMethodCallBuilder('Vector2', 'create')
                ->pushNumber(10)
                ->pushNumber(20)
                ->call()
                ->discardValue();
            
            $this->assertTrue(KLuaInternal::checkStack());
        }
    }

    public function testResultAssignVar() {
        for ($i = 0; $i < 3; $i++) {
            KLua::setVar('tmp_var', 0);

            KLua::callBuilder('concat2')->
                pushString('aaa')->
                pushString('bbb')->
                call()->
                assignVar('tmp_var');
            $this->assertEquals('aaabbb', KLua::getVar('tmp_var'));

            KLua::callBuilder('add1')->
                pushNumber(100)->
                call()->
                assignVar('tmp_var');
            $this->assertEquals(101, KLua::getVar('tmp_var'));

            $this->assertTrue(KLuaInternal::checkStack());
        }
    }
}
