<?php

namespace KLua;

class KLuaCallResult {
    /**
     * @param string $var_name
     */
    public function assignVar($var_name) {
        KLuaInternal::luaSetGlobal($var_name);
    }

    public function discardValue() {
        KLuaInternal::stackDiscard(1);
    }

    /**
     * @param int $n
     */
    public function discardValues($n) {
        KLuaInternal::stackDiscard($n);
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return KLuaInternal::stackPop();
    }

    /**
     * @param int $n
     * @return mixed[]
     */
    public function getValues($n) {
        return KLuaInternal::stackPopList($n);
    }
}
