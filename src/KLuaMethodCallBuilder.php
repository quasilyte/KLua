<?php

namespace KLua;

class KLuaMethodCallBuilder {
    /** @var string|int */
    public $func_name = '';

    /** @var int */
    private $num_args = 0;

    /**
     * @param string $table_name
     * @param string|int $table_key
     */
    public function initStaticMethodCall($table_name, $table_key) {
        return $this->initMethodCallImpl($table_name, $table_key, false);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     */
    public function initMethodCall($table_name, $table_key) {
        return $this->initMethodCallImpl($table_name, $table_key, true);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @param bool $is_instance_method
     */
    private function initMethodCallImpl($table_name, $table_key, $is_instance_method) {
        $this->func_name = $table_key;
        $this->num_args = 0;
        if (!KLuaInternal::luaGetGlobalTable($table_name)) {
            return false;
        }
        if (!KLuaInternal::lookupTableFunc(-1, $table_key)) {
            KLuaInternal::stackDiscard(1); // table
            return false;
        }
        if ($is_instance_method) {
            $this->num_args++;
            KLuaInternal::$lib->lua_pushvalue(KLuaInternal::$state, -2); // table as receiver
        }
        return true;
    }

    /**
     * @param string $var_name
     */
    public function pushVar($var_name) {
        $this->num_args++;
        KLuaInternal::$lib->lua_getglobal(KLuaInternal::$state, $var_name);
        return $this;
    }

    public function pushNil() {
        $this->num_args++;
        KLuaInternal::$lib->lua_pushnil(KLuaInternal::$state);
        return $this;
    }

    /**
     * @param bool $value
     */
    public function pushBool($value) {
        $this->num_args++;
        KLuaInternal::$lib->lua_pushboolean(KLuaInternal::$state, (int)$value);
        return $this;
    }

    /**
     * @param float $value
     */
    public function pushNumber($value) {
        $this->num_args++;
        KLuaInternal::$lib->lua_pushnumber(KLuaInternal::$state, $value);
        return $this;
    }

    
    /**
     * @param string $value
     */
    public function pushString($value) {
        $this->num_args++;
        KLuaInternal::$lib->lua_pushlstring(KLuaInternal::$state, $value, strlen($value));
        return $this;
    }

    /**
     * @param mixed[] $value
     */
    public function pushTable($value) {
        $this->num_args++;
        KLuaInternal::stackPushArray($value);
        return $this;
    }

    /**
     * @param ffi_cdata<C, void*> $value
     */
    public function pushUserData($value) {
        $this->num_args++;
        KLuaInternal::$lib->lua_pushlightuserdata(KLuaInternal::$state, $value);
        return $this;
    }

    /**
     * @return KLuaMethodCallResult
     * @throws KLuaException
     */
    public function call() {
        if ($err = KLuaInternal::luaPcall($this->num_args)) {
            throw new KLuaException("call $this->func_name: $err");
        }
        return KLuaInternal::$method_call_result;
    }
}
