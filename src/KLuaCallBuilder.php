<?php

namespace KLua;

class KLuaCallBuilder {
    /** @var string */
    public $func_name = '';

    /** @var int */
    private $num_args = 0;

    /**
     * @param string $func_name
     * @return bool
     */
    public function initCall($func_name) {
        $this->func_name = $func_name;
        $this->num_args = 0;
        return KLuaInternal::luaGetGlobalFunction($func_name);
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
     * @return KLuaCallResult
     * @throws KLuaException
     */
    public function call() {
        if ($err = KLuaInternal::luaPcall($this->num_args)) {
            throw new KLuaException("call $this->func_name: $err");
        }
        return KLuaInternal::$call_result;
    }
}
