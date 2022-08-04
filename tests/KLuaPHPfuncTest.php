<?php

use PHPUnit\Framework\TestCase;
use KLua\KLua;
use KLua\KLuaInternal;
use KLua\KLuaConfig;

require_once __DIR__ . '/testdata/Box.php';

class KLuaPHPfuncTest extends TestCase {
    public static function setUpBeforeClass(): void {
        if (KPHP_COMPILER_VERSION) { KLua::loadFFI(); }

        $config = new KLuaConfig();
        KLua::init($config);
        KLua::evalScript(__DIR__ . '/testdata/testlib.lua');

        $box = new Box();
        KLua::registerFunction0('box_get_value', function () use ($box) {
            return $box->value;
        });
        KLua::registerFunction1('box_set_value', function ($value) use ($box) {
            $box->value = $value;
            return null;
        });

        KLua::registerFunction0('phpversion', static function () {
            return '8.1';
        });

        KLua::registerFunction1('phpabs', static function ($x) {
            return abs($x);
        });

        KLua::registerFunction2('phpsprintf', static function ($format, $args) {
            return sprintf($format, ...$args);
        });

        KLua::registerFunction2('phpmin', static function ($x, $y) {
            return min($x, $y);
        });

        KLua::registerFunction2('phpconcat2', static function ($x, $y) {
            return $x . $y;
        });

        KLua::registerFunction2('phparraydiff', static function ($xs, $ys) {
            return array_diff($xs, $ys);
        });

        KLua::registerFunction3('phpconcat3', static function ($x, $y, $z) {
            return $x . $y . $z;
        });

        KLua::registerFunction4('phpconcat4', static function ($x1, $x2, $x3, $x4) {
            return $x1 . $x2 . $x3 . $x4;
        });
    }

    public static function tearDownAfterClass(): void {
        KLua::close();
    }

    public function testCall() {
        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals(10, KLua::eval('return phpmin(phpmin(10, 20), 100)'));

            KLua::call('box_set_value', 35.0);
            $this->assertEquals(35.0, KLua::call('box_get_value'));
            KLua::call('box_set_value', 'str');
            $this->assertEquals('str', KLua::call('box_get_value'));
            $this->assertTrue(KLuaInternal::checkStack());

            $this->assertEquals('50', KLua::eval('return phpsprintf("%d", {50})'));
            $this->assertEquals('1020', KLua::eval('return phpsprintf("%s%d", {"10", 20})'));
            $this->assertTrue(KLuaInternal::checkStack());

            $this->assertEquals([], KLua::eval('return phparraydiff({}, {})'));
            $this->assertEquals([], KLua::eval('return phparraydiff({a = 1}, {a = 1})'));
            $this->assertEquals([1], KLua::eval('return phparraydiff({1}, {2})'));
            $this->assertEquals([1], KLua::eval('return phparraydiff({1, 2}, {2})'));
            $this->assertEquals([0 => 1, 2 => 3], KLua::eval('return phparraydiff({1, 2, 3}, {2})'));
            $this->assertTrue(KLuaInternal::checkStack());

            $this->assertEquals('8.1', KLua::call('phpversion'));
            $this->assertEquals(31, KLua::call('phpabs', 31));
            $this->assertEquals(31, KLua::call('phpabs', -31));
            $this->assertEquals(10, KLua::call('phpmin', 20, 10));
            $this->assertEquals(10, KLua::call('phpmin', 10, 20));
            $this->assertEquals('ab', KLua::call('phpconcat2', 'a', 'b'));
            $this->assertEquals('abc', KLua::call('phpconcat3', 'a', 'b', 'c'));
            $this->assertEquals('abcd', KLua::call('phpconcat4', 'a', 'b', 'c', 'd'));
            $this->assertTrue(KLuaInternal::checkStack());
            
            $this->assertEquals('ab',
                KLua::callBuilder('phpconcat2')
                    ->pushString('a')
                    ->pushString('b')
                    ->call()
                    ->getValue());
            $this->assertTrue(KLuaInternal::checkStack());
        }
    }
}
