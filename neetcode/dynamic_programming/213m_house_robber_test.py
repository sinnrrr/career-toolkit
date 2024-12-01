class Solution:
    def _helper(self, arr: list[int]):
        rob1, rob2 = 0, 0
        for n in arr:
            temp = max(rob1 + n, rob2)
            rob1 = rob2
            rob2 = temp
        return rob2

    def rob(self, nums: list[int]) -> int:
        return max(
            nums[0],
            self._helper(nums[:-1]),
            self._helper(nums[1:]),
        )
