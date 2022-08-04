<?php

use PHPUnit\Framework\TestCase;
use KLua\KLua;
use KLua\KLuaConfig;

class KLuaNoConvTest extends TestCase {
    public static function setUpBeforeClass(): void {
        if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

        $config = new KLuaConfig();
        $config->pack_lua_tables = false;
        KLua::init($config);
    }

    public static function tearDownAfterClass(): void {
        KLua::close();
    }

    public function testLua2PHP() {
        $array_table_tests = [
            ['{}', []],
            ['{1}', [1 => 1]],
            ['{[1] = "foo"}', [1 => 'foo']],
            ['{1, 2}', [1 => 1, 2 => 2]],
            ['{1, "foo"}', [1 => 1, 2 => "foo"]],
            ['{{}}', [1 => []]],
            ['{{1}}', [1 => [1 => 1]]],
            ['{{1, 2}}', [1 => [1 => 1, 2 => 2]]],
            ['{{1, 2}, "x", "y"}', [1 => [1 => 1, 2 => 2], 2 => 'x', 3 => 'y']],
            ['{"x", {1, 2}, "y"}', [1 => 'x', 2 => [1 => 1, 2 => 2], 3 => 'y']],
            ['{[0] = 10, [1] = 20}', [10, 20]],
        ];
        foreach ($array_table_tests as $i => $test) {
            [$lua_literal, $expected_result] = $test;
            $result = KLua::eval('return ' . $lua_literal);
            $this->assertEquals($result, $expected_result);
        }
    }
}
