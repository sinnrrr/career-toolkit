class WordDictionary:
    def __init__(self):
        self.root = {}

    def addWord(self, word: str) -> None:
        curr = self.root
        for char in word:
            if char not in curr:
                curr[char] = {}
            curr = curr[char]
        curr["*"] = ""

    def search(self, word: str) -> bool:
        def dfs(node: dict, substr: str):
            if len(substr) == 0:
                return "*" in node

            char = substr[0]
            if not node or (char != "." and char not in node):
                return False

            if char == ".":
                for children in node:
                    if dfs(node[children], substr[1:]):
                        return True
                return False

            else:
                return dfs(node[char], substr[1:])

        return dfs(self.root, word)


def test():
    wordDictionary = WordDictionary()
    wordDictionary.addWord("a")
    wordDictionary.addWord("a")
    assert wordDictionary.search(".") is True
    assert wordDictionary.search("a") is True
    assert wordDictionary.search("aa") is False
    assert wordDictionary.search("a") is True
    assert wordDictionary.search(".a") is False
    assert wordDictionary.search("a.") is False
