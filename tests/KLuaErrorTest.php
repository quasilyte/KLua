<?php

use PHPUnit\Framework\TestCase;
use KLua\KLua;
use KLua\KLuaConfig;

class KLuaErrorTest extends TestCase {
    public static function setUpBeforeClass(): void {
        if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

        $config = new KLuaConfig();
        KLua::init($config);
        KLua::evalScript(__DIR__ . '/testdata/testlib.lua');
    }

    public static function tearDownAfterClass(): void {
        KLua::close();
    }

    public function testLua2PHPError() {
        $res = KLua::eval('return print');
        $this->assertEquals(['_error' => 'unsupported Lua->PHP type: function'], $res);
    }
}
