class Node:
    def __init__(self, val: int):
        self.next = self.prev = None
        self.val = val


class LFUCache:
    def __init__(self, capacity: int):
        self.cap = capacity
        self.cache = {}

    def get(self, key: int) -> int:
        pass

    def put(self, key: int, value: int) -> None:
        pass
