<?php

namespace KLua;

class KLuaConfig {
    /**
     * Whether to use FFI-based allocator (PHP/KPHP memory managers)
     * instead of the default liblua allocator.
     * It's recommended to use FFI-based allocators,
     * but you may need to test what works better for you.
     *
     * @var bool
     **/   
    public $use_ffi_allocator = true;

    /**
     * Whether this library needs to try converting from Lua
     * conventional array-like tables into PHP list-like arrays.
     * Otherwise it will return arrays as is with 1-based indexing.
     * 
     * @var bool
     */
    public $pack_lua_tables = true;

    /**
     * A value of LUAI_MAXSTACK that was used during the liblua compilation.
     *
     * @var int
     */
    public $lua_max_stack = 1000000;

    /**
     * A list of stdlib library names to preload.
     *
     * A null value (default) means "all libraries".
     * It will result in KLua calling luaL_openlibs.
     * 
     * An empty array means "preload nothing".
     * 
     * Otherwise it's interpreted as a list of libraries to preload.
     * 
     * Recognized names:
     *
     * - "base" (populates _G)
     * - "package"
     * - "coroutine"
     * - "table"
     * - "io"
     * - "os"
     * - "string"
     * - "math"
     * - "utf8"
     * - "debug"
     * 
     * Any other name will result in an error.
     *
     * @var ?(string[])
     */
    public $preload_stdlib = null;
}
