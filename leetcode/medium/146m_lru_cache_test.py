class LRUCache:
    def __init__(self, capacity: int):
        self.cache = {}
        self.free_space = capacity

    def get(self, key: int):
        if key not in self.cache:
            return -1

        self.cache[key] = self.cache.pop(key)
        return self.cache[key]

    def put(self, key: int, val: int):
        if key in self.cache:
            self.cache.pop(key)
        else:
            if self.free_space:
                self.free_space -= 1
            else:
                self.cache.pop(next(iter(self.cache)))

        self.cache[key] = val

