<?php

use PHPUnit\Framework\TestCase;
use KLua\KLua;
use KLua\KLuaConfig;

class KLuaTest extends TestCase {
    public static function setUpBeforeClass(): void {
        if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

        $config = new KLuaConfig();
        KLua::init($config);
        KLua::evalScript(__DIR__ . '/testdata/testlib.lua');
    }

    public static function tearDownAfterClass(): void {
        KLua::close();
    }

    public function testPHP2Lua() {
        $type_tests = [
            [1, 'number'],
            [1.2, 'number'],

            ['', 'string'],
            ['example', 'string'],

            [null, 'nil'],

            [true, 'boolean'],
            [false, 'boolean'],

            [[], 'table'],
            [[1], 'table'],
            [[1, 2], 'table'],
            [['foo' => 40], 'table'],
            [[10 => 'ok', 'foo' => 'bar'], 'table'],
        ];
        foreach ($type_tests as $test) {
            [$value, $expected_type] = $test;
            $this->assertTrue(KLua::call('check_type', $value, $expected_type));
        }
    }

    public function testLua2PHPError() {
        $res = KLua::eval('return print');
        $this->assertEquals(['_error' => 'unsupported Lua->PHP type: function'], $res);
    }

    public function testLua2PHP() {
        $num = KLua::eval('return 15');
        $this->assertTrue(is_float($num));
        $this->assertEquals($num, 15.0);

        $str = KLua::eval('return "foo"');
        $this->assertTrue(is_string($str));
        $this->assertEquals($str, 'foo');

        $nil = KLua::eval('return nil');
        $this->assertTrue(is_null($nil));

        $f = KLua::eval('return false');
        $t = KLua::eval('return true');
        $this->assertTrue(is_bool($f));
        $this->assertTrue(is_bool($t));
        $this->assertEquals($f, false);
        $this->assertEquals($t, true);

        $array_table_tests = [
            ['{}', []],
            ['{1}', [1]],
            ['{[1] = "foo"}', ['foo']],
            ['{1, 2}', [1, 2]],
            ['{1, "foo"}', [1, "foo"]],
            ['{{}}', [[]]],
            ['{{1}}', [[1]]],
            ['{{1, 2}}', [[1, 2]]],
            ['{{1, 2}, "x", "y"}', [[1, 2], 'x', 'y']],
            ['{"x", {1, 2}, "y"}', ['x', [1, 2], 'y']],
            ['{[0] = 10, [1] = 20}', [10, 20]],
        ];
        foreach ($array_table_tests as $i => $test) {
            [$lua_literal, $expected_result] = $test;
            $result = KLua::eval('return ' . $lua_literal);
            $this->assertEquals($result, $expected_result);
        }

        $map_table_tests = [
            ['{[2] = 10, ["foo"] = 20}', [2 => 10, 'foo' => 20]],
            ['{["foo"] = 20, [2] = 10}', [2 => 10, 'foo' => 20]],
            ['{[1] = 10, [3] = 30}', [1 => 10, 3 => 30]],
            ['{[2] = {1, 2}}', [2 => [1, 2]]],
            ['{["a"] = {["b"] = {c = 1}}}', ['a' => ['b' => ['c' => 1]]]],
            ['{["a"] = {["b"] = {c = 1}}, [1] = 2, [2] = 3}', ['a' => ['b' => ['c' => 1]], 1 => 2, 2 => 3]],
            ['{[1] = 2, [2] = 3, ["a"] = {["b"] = {c = 1}}}', ['a' => ['b' => ['c' => 1]], 1 => 2, 2 => 3]],
        ];
        foreach ($map_table_tests as $i => $test) {
            [$lua_literal, $expected_result] = $test;
            $result = KLua::eval('return ' . $lua_literal);
            $this->assertEquals($result, $expected_result);
        }
    }

    public function testCallStaticMethodByIndex() {
        $this->assertEquals(10, KLua::callStaticMethod('FuncArray', 1, 2, 5));
        $this->assertEquals(100, KLua::callStaticMethod('FuncArray', 1, 20, 5));

        $this->assertEquals(7, KLua::callStaticMethod('FuncArray', 2, 2, 5));
        $this->assertEquals(25, KLua::callStaticMethod('FuncArray', 2, 20, 5));
    }
}
