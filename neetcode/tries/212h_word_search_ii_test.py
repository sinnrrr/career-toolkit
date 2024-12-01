class TrieNode:
    def __init__(self):
        self.children = {}


class Solution:
    def findWords(self, board: list[list[str]], words: list[str]) -> list[str]:
        ROWS, COLS = len(board), len(board[0])

        root = TrieNode()
        cache = {}

        def dfs(curr: TrieNode, row: int, col: int):
            if row < 0 or row >= ROWS or col < 0 or col >= COLS:
                return

            char = board[row][col]
            if (row, col) in cache:
                curr.children[char] = cache[(row, col)]
                return

            curr.children[char] = cache[(row, col)] = TrieNode()

            directions = [(1, 0), (0, 1), (-1, 0), (0, -1)]
            for drow, dcol in directions:
                dfs(curr.children[char], row + drow, col + dcol)

        for row in range(ROWS):
            for col in range(COLS):
                dfs(root, row, col)

        res = []
        for word in words:
            curr, i, n, max_steps = root, 0, len(word), ROWS * COLS
            while i < n and i < max_steps:
                if word[i] not in curr.children:
                    break
                curr, i = curr.children[word[i]], i + 1
            if i == n:
                res.append(word)

        return res


def test():
    assert Solution().findWords(
        [["a", "b", "c"], ["a", "e", "d"], ["a", "f", "g"]],
        ["abcdefg", "gfedcbaaa", "eaabcdgfa", "befa", "dgc", "ade"],
    ) == ["abcdefg", "befa", "eaabcdgfa", "gfedcbaa"]
