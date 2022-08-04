function check_type(v, expected)
    return type(v) == expected
end

function concat2(x, y)
    return x .. y
end

function add1(x)
    return x + 1
end

function multiret(n)
    if n == 0 then
        return
    elseif n == 1 then
        return 1
    elseif n == 2 then
        return 1, 2
    elseif n == 3 then
        return 1, 2, 3
    else
        return 1, 2, 3, 4
    end
end

Stdlib = {}

function Stdlib.static_type(x)
    return type(x)
end

function Stdlib:type(x)
    return type(x)
end

function Stdlib:new()
    local obj = {}
    setmetatable(obj, self)
    self.__index = self
    return obj
end

Vector2 = {}

function Vector2:new(x, y)
    local obj = {
        x = x,
        y = y,
    }
    setmetatable(obj, self)
    self.__index = self
    return obj
end

function Vector2.create(x, y)
    return Vector2:new(x, y)
end

function Vector2:get_components()
    return self.x, self.y
end

function Vector2:abs()
    return Vector2:new(math.abs(self.x), math.abs(self.y))
end

function Vector2:angle()
    return math.atan2(self.y, self.x)
end

FuncArray = {
    function (x, y)
        return x * y
    end,
    function (x, y)
        return x + y
    end,
}
