<?php

namespace KLua;

/**
 * KLua provides a minimal liblua interface for PHP and KPHP.
 * It can be used to integrate the Lua scripting language into your application.
 * 
 * The documentation is available at the project repository:
 * https://github.com/quasilyte/KLua
 */
class KLua {
    public static function loadFFI(): bool {
        return \FFI::load(__DIR__ . '/lua.h') !== null;
    }

    /**
     * @param KLuaConfig $config
     */
    public static function init($config) {
        KLuaInternal::init($config);
    }

    public static function close() {
        KLuaInternal::close();
    }

    /**
     * @return KLuaStats
     */
    public static function getStats() {
        return KLuaInternal::$stats;
    }

    /**
     * @param string $func_name
     * @param callable():mixed $fn
     */
    public static function registerFunction0($func_name, $fn) {
        KLuaInternal::registerFunction0($func_name, $fn);
    }

    /**
     * @param string $func_name
     * @param callable(mixed):mixed $fn
     */
    public static function registerFunction1($func_name, $fn) {
        KLuaInternal::registerFunction1($func_name, $fn);
    }

    /**
     * @param string $func_name
     * @param callable(mixed,mixed):mixed $fn
     */
    public static function registerFunction2($func_name, $fn) {
        KLuaInternal::registerFunction2($func_name, $fn);
    }

    /**
     * @param string $func_name
     * @param callable(mixed,mixed,mixed):mixed $fn
     */
    public static function registerFunction3($func_name, $fn) {
        KLuaInternal::registerFunction3($func_name, $fn);
    }

    /**
     * @param string $func_name
     * @param callable(mixed,mixed,mixed,mixed):mixed $fn
     */
    public static function registerFunction4($func_name, $fn) {
        KLuaInternal::registerFunction4($func_name, $fn);
    }

    /**
     * @param string $var_name
     * @param mixed $value
     */
    public static function setVar($var_name, $value) {
        KLuaInternal::setVar($var_name, $value);
    }

    /**
     * @return mixed
     */
    public static function getVar($var_name) {
        return KLuaInternal::getVar($var_name);
    }

    /**
     * @param string $code
     * @return mixed
     * @throws KLuaException
     */
    public static function eval($code) {
        return KLuaInternal::eval($code);
    }

    /**
     * @param string $filename
     * @return mixed
     * @throws KLuaException
     */
    public static function evalScript($filename) {
        $code = file_get_contents($filename);
        if (!$code) {
            throw new KLuaException("can't read $filename");
        }
        return self::eval($code);
    }

    /**
     * @param string $func_name
     * @return KLuaCallBuilder
     */
    public static function callBuilder($func_name) {
        return KLuaInternal::callBuilder($func_name);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @return KLuaMethodCallBuilder
     */
    public static function staticMethodCallBuilder($table_name, $table_key) {
        return KLuaInternal::staticMethodCallBuilder($table_name, $table_key);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @return KLuaMethodCallBuilder
     */
    public static function methodCallBuilder($table_name, $table_key) {
        return KLuaInternal::methodCallBuilder($table_name, $table_key);
    }

    /**
     * @param string $func_name
     * @param mixed[] $args
     */
    public static function call($func_name, ...$args) {
        return KLuaInternal::call($func_name, $args);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @param mixed[] $args
     * @return mixed
     */
    public static function callStaticMethod($table_name, $table_key, ...$args) {
        return KLuaInternal::callStaticMethod($table_name, $table_key, $args);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @param mixed[] $args
     * @return mixed
     */
    public static function callMethod($table_name, $table_key, ...$args) {
        return KLuaInternal::callMethod($table_name, $table_key, $args);
    }
}
