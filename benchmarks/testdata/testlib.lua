function add1(x)
    return x + 1
end

function zero()
    return 0
end

function concat2(x, y)
    return x .. y
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

function Vector2:abs()
    return Vector2:new(math.abs(self.x), math.abs(self.y))
end

function Vector2:angle()
    return math.atan2(self.y, self.x)
end
