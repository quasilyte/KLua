<?php

namespace KLua;

class KLuaMethodCallResult {
    /**
     * @param string $var_name
     */
    public function assignVar($var_name) {
        KLuaInternal::luaSetGlobal($var_name);
        KLuaInternal::stackDiscard(1); // the table we initially pushed
    }

    public function discardValue() {
        // The actual value + the table we initially pushed.
        KLuaInternal::stackDiscard(2);
    }

    /**
     * @param int $n
     */
    public function discardValues($n) {
        // The actual values + the table we initially pushed.
        KLuaInternal::stackDiscard($n + 1);
    }

    /**
     * @return mixed
     */
    public function getValue() {
        $result = KLuaInternal::stackPop();
        KLuaInternal::stackDiscard(1); // the table we initially pushed
        return $result;
    }

    /**
     * @param int $n
     * @return mixed[]
     */
    public function getValues($n) {
        $result = KLuaInternal::stackPopList($n);
        KLuaInternal::stackDiscard(1); // the table we initially pushed
        return $result;
    }
}
