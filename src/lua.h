#define FFI_LIB "./ffilibs/liblua5"
#define FFI_SCOPE "lua"

typedef struct lua_State lua_State;

typedef int64_t lua_Integer;
typedef uint64_t lua_Unsigned;
typedef double lua_Number;

typedef uint64_t lua_KContext;

typedef int (*lua_CFunction) (lua_State *L);

typedef void* (*lua_Alloc) (void *ud, void *ptr, size_t osize, size_t nsize);

lua_State *lua_newstate(lua_Alloc f, void *ud);
lua_State *luaL_newstate();
void lua_close(lua_State *L);

void luaL_openlibs(lua_State *L);
int luaopen_base(lua_State *L);
int luaopen_package(lua_State *L);
int luaopen_coroutine(lua_State *L);
int luaopen_table(lua_State *L);
int luaopen_io(lua_State *L);
int luaopen_os(lua_State *L);
int luaopen_string(lua_State *L);
int luaopen_math(lua_State *L);
int luaopen_utf8(lua_State *L);
int luaopen_debug(lua_State *L);
void luaL_requiref(lua_State *L, const char *modname, lua_CFunction openf, int glb);

int luaL_loadstring(lua_State *L, const char *s);
int luaL_loadbufferx(lua_State *L, const char *buff, size_t size, const char *name, const char *mode);

int lua_pcallk(lua_State *L, int nargs, int nresults, int errfunc, lua_KContext ctx, void *k);

// @kphp-ffi-signalsafe
int lua_gettop(lua_State *L);
// @kphp-ffi-signalsafe
void lua_settop(lua_State *L, int index);
void lua_pushvalue(lua_State *L, int index);

// @kphp-ffi-signalsafe
int lua_type(lua_State *L, int index);

void lua_createtable(lua_State *L, int narr, int nrec);
int lua_getfield(lua_State *L, int index, const char *k);
int lua_gettable(lua_State *L, int index);
int lua_rawgeti(lua_State *L, int index, lua_Integer n);
void lua_rawset(lua_State *L, int index);
void lua_rawseti(lua_State *L, int index, lua_Integer i);
// @kphp-ffi-signalsafe
lua_Unsigned lua_rawlen(lua_State *L, int index);
void lua_len(lua_State *L, int index);
int lua_next(lua_State *L, int index);

// @kphp-ffi-signalsafe
void lua_pushnil(lua_State *L);
// @kphp-ffi-signalsafe
void lua_pushboolean(lua_State *L, int b);
// @kphp-ffi-signalsafe
void lua_pushnumber(lua_State *L, lua_Number n);
const char *lua_pushlstring(lua_State *L, const char *s, size_t len);
void lua_pushcclosure(lua_State *L, lua_CFunction fn, int n);

void lua_setglobal(lua_State *L, const char *name);
int lua_getglobal(lua_State *L, const char *name);

const char *lua_tolstring(lua_State *L, int index, size_t *len);
// @kphp-ffi-signalsafe
int lua_toboolean(lua_State *L, int index);
// @kphp-ffi-signalsafe
lua_Number lua_tonumberx(lua_State *L, int idx, int *isnum);
