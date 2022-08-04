<?php

namespace KLua;

class KLuaStats {
    /** How much memory Lua runtime is using right now. */
    public $mem_usage = 0;

    /** How many bytes Lua runtime allocated up to this point. */
    public $mem_alloc_bytes_total = 0;

    /** How many memory allocations Lua runtime performed up to this point. */
    public $mem_allocs_total = 0;
}
