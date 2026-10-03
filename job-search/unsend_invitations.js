const sleep = ms => new Promise(r => setTimeout(r, ms));
let arr = Array.from(document.querySelectorAll(`button[aria-label*="Withdraw"]`))
for (var i = 0; i < arr.length; i++) {
    let btn = arr.pop()
    btn.click()
    await sleep(300)
    document.querySelector('[data-test-dialog-primary-btn]').click()
    await sleep(300)
    document.querySelector('button[aria-label^="Dismiss"]').click()
}
