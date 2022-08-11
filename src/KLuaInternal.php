<?php

namespace KLua;

use KFinalize\KFinalize;

class KLuaInternal {
    /** @var KLuaCallResult */
    public static $call_result = null;
    /** @var KLuaCallBuilder */
    public static $call_builder = null;

    /** @var KLuaMethodCallResult */
    public static $method_call_result = null;
    /** @var KLuaMethodCallBuilder */
    public static $method_call_builder = null;

    public static $lua_max_stack = 1000000;
    public static $pack_lua_tables = true;

    /** @var (callable():mixed)[] */
    public static $phpfuncs0 = [];
    /** @var (callable(mixed):mixed)[] */
    public static $phpfuncs1 = [];
    /** @var (callable(mixed,mixed):mixed)[] */
    public static $phpfuncs2 = [];
    /** @var (callable(mixed,mixed,mixed):mixed)[] */
    public static $phpfuncs3 = [];
    /** @var (callable(mixed,mixed,mixed,mixed):mixed)[] */
    public static $phpfuncs4 = [];

    /** @var ffi_scope<lua> */
    public static $lib;

    /** @var ffi_cdata<lua, struct lua_State*> */
    public static $state = null;

    /** @var KLuaStats */
    public static $stats;

    public static $callback_registered = false;

    public const MULTRET = -1;

    public const TNONE = -1;
    public const TNIL = 0;
    public const TBOOLEAN = 1;
    public const TLIGHTUSERDATA = 2;
    public const TNUMBER = 3;
    public const TSTRING = 4;
    public const TTABLE = 5;
    public const TFUNCTION = 6;
    public const TUSERDATA = 7;
    public const TTHREAD = 8;

    /**
     * @param KLuaConfig $config
     */
    public static function init($config) {
        self::$lua_max_stack = $config->lua_max_stack;
        self::$pack_lua_tables = $config->pack_lua_tables;
        self::$stats = new KLuaStats();
        self::$call_result = new KLuaCallResult();
        self::$call_builder = new KLuaCallBuilder();
        self::$method_call_result = new KLuaMethodCallResult();
        self::$method_call_builder = new KLuaMethodCallBuilder();
        self::$phpfuncs0 = [];
        self::$phpfuncs1 = [];
        self::$phpfuncs2 = [];
        self::$phpfuncs3 = [];
        self::$phpfuncs4 = [];

        if (!self::$lib) {
            self::$lib = \FFI::scope('lua');
        }

        if (self::$state !== null) {
            self::close();
        }

        if ($config->use_ffi_allocator) {
            // Use FFI allocator (PHP or KPHP memory manager).
            self::$state = self::$lib->lua_newstate(function ($ud, $ptr, $orig_size, $new_size) {
                if ($new_size === 0) {
                    if ($orig_size !== 0 && $ptr !== null) {
                        // free()
                        self::$stats->mem_usage -= $orig_size;
                        \FFI::free(\FFI::cast("uint8_t[$orig_size]", $ptr));
                    }
                    return null;
                }
                if ($ptr === null) {
                    // malloc()
                    self::$stats->mem_usage += $new_size;
                    self::$stats->mem_alloc_bytes_total += $new_size;
                    self::$stats->mem_allocs_total++;
                    $mem = \FFI::new("uint8_t[$new_size]", false);
                    return \FFI::cast('void*', \FFI::addr($mem));
                }
                // realloc()
                $copy_size = ($new_size > $orig_size) ? $orig_size : $new_size;
                self::$stats->mem_usage += ($new_size - $orig_size);
                self::$stats->mem_alloc_bytes_total += $new_size;
                self::$stats->mem_allocs_total++;
                $mem = \FFI::new("uint8_t[$new_size]", false);
                \FFI::memcpy($mem, $ptr, $copy_size);
                \FFI::free(\FFI::cast("uint8_t[$orig_size]", $ptr));
                return \FFI::cast('void*', \FFI::addr($mem));
            }, null);
        } else {
            // Use glibc free+realloc.
            self::$state = self::$lib->luaL_newstate();
        }

        if (!self::$callback_registered) {
            KFinalize::push(function() {
                if (self::$state !== null) {
                    self::close();
                }
            });
        }
      
        if ($config->preload_stdlib !== null) {
            foreach ($config->preload_stdlib as $lib_name) {
                self::openLib($lib_name);
            }
        } else {
            self::$lib->luaL_openlibs(self::$state);
        }
    }

    /**
     * @param string $lib_name
     */
    private static function openLib($lib_name) {
        switch ($lib_name) {
            case "base":
                self::$lib->luaL_requiref(self::$state, "_G", static function ($caller_state) {
                    return self::$lib->luaopen_base($caller_state);
                }, 1);
                break;
            case "package":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_package($caller_state);
                }, 1);
                break;
            case "coroutine":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_coroutine($caller_state);
                }, 1);
                break;
            case "table":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_table($caller_state);
                }, 1);
                break;
            case "io":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_io($caller_state);
                }, 1);
                break;
            case "os":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_os($caller_state);
                }, 1);
                break;
            case "string":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_string($caller_state);
                }, 1);
                break;
            case "math":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_math($caller_state);
                }, 1);
                break;
            case "utf8":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_utf8($caller_state);
                }, 1);
                break;
            case "debug":
                self::$lib->luaL_requiref(self::$state, $lib_name, static function ($caller_state) {
                    return self::$lib->luaopen_debug($caller_state);
                }, 1);
                break;
            default:
                throw new KLuaException("config->preload_stdlib: unrecognized stdlib name requested: $lib_name");
        }
        self::stackDiscard(1); // lib
    }

    public static function close() {
        self::$lib->lua_close(self::$state);
        self::$state = null;
    }

    /**
     * @param int $i
     */
    public static function upvalueIndex($i) {
        $registry_index = (-self::$lua_max_stack - 1000);
        return $registry_index - $i;
    }

    /**
     * @param string $func_name
     * @param callable():mixed $fn
     */
    public static function registerFunction0($func_name, $fn) {
        $id = count(self::$phpfuncs0);
        self::$phpfuncs0[] = $fn;

        self::$lib->lua_pushnumber(self::$state, (float)$id);
        self::$lib->lua_pushcclosure(self::$state, static function ($caller_state) {
            $upvalue_index = self::upvalueIndex(1);
            $func_id = (int)self::$lib->lua_tonumberx($caller_state, $upvalue_index, null);
            $phpfunc = self::$phpfuncs0[$func_id];
            $result = $phpfunc();
            self::stackPush($result);
            return 1;
        }, 1);
        self::$lib->lua_setglobal(self::$state, $func_name);
    }

    /**
     * @param string $func_name
     * @param callable(mixed):mixed $fn
     */
    public static function registerFunction1($func_name, $fn) {
        $id = count(self::$phpfuncs1);
        self::$phpfuncs1[] = $fn;

        self::$lib->lua_pushnumber(self::$state, (float)$id);
        self::$lib->lua_pushcclosure(self::$state, static function ($caller_state) {
            $upvalue_index = self::upvalueIndex(1);
            $func_id = (int)self::$lib->lua_tonumberx($caller_state, $upvalue_index, null);
            $phpfunc = self::$phpfuncs1[$func_id];
            $arg1 = self::stackGet(1);
            $result = $phpfunc($arg1);
            self::stackPush($result);
            return 1;
        }, 1);
        self::$lib->lua_setglobal(self::$state, $func_name);
    }

    /**
     * @param string $func_name
     * @param callable(mixed,mixed):mixed $fn
     */
    public static function registerFunction2($func_name, $fn) {
        $id = count(self::$phpfuncs2);
        self::$phpfuncs2[] = $fn;

        self::$lib->lua_pushnumber(self::$state, (float)$id);
        self::$lib->lua_pushcclosure(self::$state, static function ($caller_state) {
            $upvalue_index = self::upvalueIndex(1);
            $func_id = (int)self::$lib->lua_tonumberx($caller_state, $upvalue_index, null);
            $phpfunc = self::$phpfuncs2[$func_id];
            $arg1 = self::stackGet(1);
            $arg2 = self::stackGet(2);
            $result = $phpfunc($arg1, $arg2);
            self::stackPush($result);
            return 1;
        }, 1);
        self::$lib->lua_setglobal(self::$state, $func_name);
    }

    /**
     * @param string $func_name
     * @param callable(mixed,mixed,mixed):mixed $fn
     */
    public static function registerFunction3($func_name, $fn) {
        $id = count(self::$phpfuncs3);
        self::$phpfuncs3[] = $fn;

        self::$lib->lua_pushnumber(self::$state, (float)$id);
        self::$lib->lua_pushcclosure(self::$state, static function ($caller_state) {
            $upvalue_index = self::upvalueIndex(1);
            $func_id = (int)self::$lib->lua_tonumberx($caller_state, $upvalue_index, null);
            $phpfunc = self::$phpfuncs3[$func_id];
            $arg1 = self::stackGet(1);
            $arg2 = self::stackGet(2);
            $arg3 = self::stackGet(3);
            $result = $phpfunc($arg1, $arg2, $arg3);
            self::stackPush($result);
            return 1;
        }, 1);
        self::$lib->lua_setglobal(self::$state, $func_name);
    }

    /**
     * @param string $func_name
     * @param callable(mixed,mixed,mixed,mixed):mixed $fn
     */
    public static function registerFunction4($func_name, $fn) {
        $id = count(self::$phpfuncs4);
        self::$phpfuncs4[] = $fn;

        self::$lib->lua_pushnumber(self::$state, (float)$id);
        self::$lib->lua_pushcclosure(self::$state, static function ($caller_state) {
            $upvalue_index = self::upvalueIndex(1);
            $func_id = (int)self::$lib->lua_tonumberx($caller_state, $upvalue_index, null);
            $phpfunc = self::$phpfuncs4[$func_id];
            $arg1 = self::stackGet(1);
            $arg2 = self::stackGet(2);
            $arg3 = self::stackGet(3);
            $arg4 = self::stackGet(4);
            $result = $phpfunc($arg1, $arg2, $arg3, $arg4);
            self::stackPush($result);
            return 1;
        }, 1);
        self::$lib->lua_setglobal(self::$state, $func_name);
    }

    /**
     * @param string $var_name
     * @param mixed $value
     */
    public static function setVar($var_name, $value) {
        self::stackPush($value);
        self::luaSetGlobal($var_name);
    }

    /**
     * @param string $var_name
     * @param ffi_cdata<C, void*> $value
     */
    public static function setVarUserData($var_name, $value) {
        self::$lib->lua_pushlightuserdata(self::$state, $value);
        self::luaSetGlobal($var_name);
    }

    /**
     * @param string $var_name
     */
    public static function luaSetGlobal($var_name) {
        self::$lib->lua_setglobal(self::$state, $var_name);
    } 

    /**
     * @return mixed
     */
    public static function getVar($var_name) {
        self::$lib->lua_getglobal(self::$state, $var_name);
        return self::stackPop();
    }

    /**
     * @param string $code
     * @return mixed
     * @throws KLuaException
     */
    public static function eval($code) {
        $stack_top = self::$lib->lua_gettop(self::$state);
        $status = self::$lib->luaL_loadbufferx(self::$state, $code, strlen($code), $code, null);
        if ($status) {
            $error_message = (string)self::stackPop();
            throw new KLuaException("eval: load code: $error_message");
        }
        if ($err = self::luaPcall(0)) {
            throw new KLuaException("eval: run code: $err");
        }
        $results = self::collectCallResults($stack_top);
        return $results;
    }

    /**
     * @param string $func_name
     * @return ?KLuaCallBuilder
     */
    public static function callBuilder($func_name) {
        if (!self::$call_builder->initCall($func_name)) {
            return null;
        }
        return self::$call_builder;
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @return ?KLuaMethodCallBuilder
     */
    public static function staticMethodCallBuilder($table_name, $table_key) {
        if (!self::$method_call_builder->initStaticMethodCall($table_name, $table_key)) {
            return null;
        }
        return self::$method_call_builder;
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @return ?KLuaMethodCallBuilder
     */
    public static function methodCallBuilder($table_name, $table_key) {
        if (!self::$method_call_builder->initMethodCall($table_name, $table_key)) {
            return null;
        }
        return self::$method_call_builder;
    }

    /**
     * @param string $func_name
     * @param mixed[] $args
     */
    public static function call($func_name, $args) {
        $stack_top = self::$lib->lua_gettop(self::$state);
        if (!self::luaGetGlobalFunction($func_name)) {
            throw new KLuaException("can't find $func_name function");  
        }
        self::pushCallArgs($args);
        if ($err = self::luaPcall(count($args))) {
            throw new KLuaException("call $func_name: $err");
        }
        return self::collectCallResults($stack_top);
    }

    /**
     * @param string $table_name
     * @return bool
     */
    public static function luaGetGlobalTable($table_name) {
        $type = self::$lib->lua_getglobal(self::$state, $table_name);
        if ($type !== self::TTABLE) {
            self::stackDiscard(1);
            return false;
        }
        return true;
    }

    /**
     * @param string $func_name
     * @return bool
     */
    public static function luaGetGlobalFunction($func_name) {
        $type = self::$lib->lua_getglobal(self::$state, $func_name);
        if ($type !== self::TFUNCTION) {
            self::stackDiscard(1);
            return false;
        }
        return true;
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @param mixed[] $args
     * @return mixed
     */
    public static function callStaticMethod($table_name, $table_key, $args) {
        return self::callMethodImpl(false, $table_name, $table_key, $args);
    }

    /**
     * @param string $table_name
     * @param string|int $table_key
     * @param mixed[] $args
     * @return mixed
     */
    public static function callMethod($table_name, $table_key, $args) {
        return self::callMethodImpl(true, $table_name, $table_key, $args);
    }

    /**
     * @param int $table_index
     * @param int $k
     * @param int $type
     */
    public static function luaRawGeti($table_index, $k, $type) {
        $field_type = self::$lib->lua_rawgeti(self::$state, $table_index, $k);
        if ($field_type !== $type) {
            self::stackDiscard(1);
            return false;
        }
        return true;
    }

    /**
     * @param int $index
     * @param string $name
     * @param int $type
     */
    public static function luaGetField($index, $name, $type) {
        $field_type = self::$lib->lua_getfield(self::$state, $index, $name);
        if ($field_type !== $type) {
            self::stackDiscard(1);
            return false;
        }
        return true;
    }

    /**
     * @param int $table_index
     * @param string|int $k
     */
    public static function lookupTableFunc($table_index, $k) {
        if (is_string($k)) {
            return self::luaGetField($table_index, $k, self::TFUNCTION);
        }
        return self::luaRawGeti(-1, (int)$k, self::TFUNCTION);
    }

    /**
     * @param bool $is_instance
     * @param string $table_name
     * @param string|int $table_key
     * @param mixed[] $args
     */
    public static function callMethodImpl($is_instance, $table_name, $table_key, $args) {
        if (!self::luaGetGlobalTable($table_name)) {
            throw new KLuaException("can't find $table_name table");
        }
        $stack_top = self::$lib->lua_gettop(self::$state);
        if (!self::lookupTableFunc(-1, $table_key)) {
            self::stackDiscard(1); // table
            throw new KLuaException("can't find $table_name [$table_key] function");
        }
        
        $num_args = count($args);
        if ($is_instance) {
            $num_args++;
            self::$lib->lua_pushvalue(self::$state, -2); // table as receiver
        }
        self::pushCallArgs($args);
        if ($err = self::luaPcall($num_args)) {
            throw new KLuaException("call $table_name [$table_key]: $err");
        }
        $results = self::collectCallResults($stack_top);
        self::stackDiscard(1); // the table we initially pushed
        return $results;
    }

    /**
     * @param int $num_args
     * @return string
     */
    public static function luaPcall($num_args) {
        $status = self::$lib->lua_pcallk(self::$state, $num_args, self::MULTRET, 0, 0, null);
        if ($status) {
            return (string)self::stackPop();
        }
        return '';
    }

    /**
     * @param mixed $value
     */
    public static function stackPushArray($value) {
        if (false) {
            self::$lib->lua_createtable(self::$state, count($value), 0);
            $table_index = 1; // Lua array-like tables indexes start from 1
            foreach ($value as $elem) {
                self::stackPush($elem);
                self::$lib->lua_rawseti(self::$state, -2, $table_index);
                $table_index++;
            }
        } else {
            // Can't really predict how many which keys we have there.
            self::$lib->lua_createtable(self::$state, 0, 0);
            foreach ($value as $key => $elem) {
                self::stackPush($key);
                self::stackPush($elem);
                self::$lib->lua_rawset(self::$state, -3);
            }
        }
    }

    /**
     * @param mixed $value
     */
    public static function stackPush($value) {
        if ($value === null) {
            self::$lib->lua_pushnil(self::$state);
        } else if (is_string($value)) {
            self::$lib->lua_pushlstring(self::$state, $value, strlen($value));
        } else if (is_int($value) || is_float($value)) {
            self::$lib->lua_pushnumber(self::$state, (float)$value);
        } else if (is_array($value)) {
            self::stackPushArray($value);
        } else if (is_bool($value)) {
            self::$lib->lua_pushboolean(self::$state, (int)$value);
        } 
    }

    public static function stackPop() {
        $top = self::stackTop();
        self::stackDiscard(1);
        return $top;
    }

    public static function stackPopList($n) {
        $results = [];
        $top = self::$lib->lua_gettop(self::$state);
        for ($i = 0; $i < $n; $i++) {
            $index = $top - (($n - $i) - 1);
            $results[] = self::stackGet($index);
        }
        self::stackDiscard($n);
        return $results;
    }

    public static function stackTop() {
        return self::stackGet(-1);
    }

    public static function stackGet($index) {
        switch (self::$lib->lua_type(self::$state, $index)) {
            case self::TNIL:
                return null;
            case self::TBOOLEAN:
                return (bool)self::$lib->lua_toboolean(self::$state, $index);
            case self::TLIGHTUSERDATA:
                return self::ptr2addr(self::$lib->lua_touserdata(self::$state, $index));
            case self::TNUMBER:
                return self::$lib->lua_tonumberx(self::$state, $index, null);
            case self::TSTRING:
                return self::$lib->lua_tolstring(self::$state, $index, null);
            case self::TTABLE:
                return self::stackGetTable($index);
            case self::TFUNCTION:
                return ['_error' => "unsupported Lua->PHP type: function"];
            case self::TUSERDATA:
                return ['_error' => "unsupported Lua->PHP type: user data"];
            default:
                return ['_error' => "unsupported Lua->PHP type"];
        }
    }

    /**
     * @param ffi_cdata<C, void*> $ptr
     * @return int
     */
    public static function ptr2addr($ptr) {
        return ffi_cast_ptr2addr($ptr);
    }

    /**
     * @param int $addr
     * @return ffi_cdata<C, void*>
     */
    public static function addr2ptr($addr) {
        return ffi_cast_addr2ptr($addr);
    }

    /**
     * @param int $index
     */
    public static function stackGetTable($index) {
        if ($index === -1) {
            $index = self::$lib->lua_gettop(self::$state);
        }
        self::$lib->lua_pushnil(self::$state);
        if (self::$lib->lua_next(self::$state, $index) === 0) {
            return [];
        } else {
            self::stackDiscard(2); // key+value
        }
        if (!self::$pack_lua_tables) {
            return self::stackGetTableAsMap($index);
        }

        $type = (int)self::$lib->lua_rawgeti(self::$state, $index, 1);
        self::stackDiscard(1); // pop the [1] lookup result
        if ($type === self::TNIL) {
            // Don't have [1], can't be a vector-like table.
            return self::stackGetTableAsMap($index);
        }

        // Let's assume that people don't often mix numerical and other keys.
        // If [1] exists, we try to collect all values as array.
        // If we fail (find a hole, etc.), we fallback to table-as-map approach.
        $result = [];
        self::$lib->lua_pushnil(self::$state);
        $i = 1;
        $ok = true;
        while (self::$lib->lua_next(self::$state, $index) !== 0) {
            if (self::$lib->lua_type(self::$state, -2) !== self::TNUMBER) {
                $ok = false;
            break;
            }
            $key = (int)self::$lib->lua_tonumberx(self::$state, -2, null);
            if ($key !== $i) {
                $ok = false;
                break;
            }
            $i++;
            // Discard only the value, key remains for the next iteration.
            $result[] = self::stackPop();
        }
        if (!$ok) {
            self::stackDiscard(2); // key+value
            return self::stackGetTableAsMap($index);
        }
        return $result;
    }

    /**
     * @param int $index
     */
    public static function stackGetTableAsMap($index) {
        $result = [];
        self::$lib->lua_pushnil(self::$state);
        while (self::$lib->lua_next(self::$state, $index) !== 0) {
            $value = self::stackPop();
            $result[self::stackTop()] = $value;
        }
        return $result;
    }

    /**
     * @param int $n
     */
    public static function stackDiscard($n) {
        self::$lib->lua_settop(self::$state, -($n) - 1);
    }

    /**
     * @param mixed[] $args
     */
    public static function pushCallArgs($args) {
        foreach ($args as $arg) {
            self::stackPush($arg);
        }
    }

    public static function collectCallResults($stack_top) {
        $num_results = self::$lib->lua_gettop(self::$state) - $stack_top;
        switch ($num_results) {
            case 0:
                return null;
            case 1:
                return self::stackPop();
            default:
                return self::stackPopList($num_results);
        }
    }

    public static function checkStack() {
        return self::$lib->lua_gettop(self::$state) === 0;
    }
}
