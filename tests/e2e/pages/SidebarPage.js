const {I} = inject();

// Oldalsáv "VB" tabella (templates/extension/user_championship.html.twig): alternatív bajnokság pontjai.
module.exports = {
    /** @returns {Promise<Array<{pos: number, name: string, lag: string, points: number, trophies: string[]}>>} */
    async standings() {
        I.waitForElement('.f1-aside .f1-row--user');
        return I.executeScript(() => Array.from(document.querySelectorAll('.f1-aside .f1-row--user')).map(row => ({
            pos: Number(row.querySelector('.f1-pos').textContent.trim()),
            name: row.querySelector('.f1-name').textContent.trim(),
            lag: row.querySelector('.f1-num--dim').textContent.trim().replace(/\s+/g, ' '),
            points: Number(row.querySelector('.f1-num--main').textContent.trim()),
            trophies: Array.from(row.querySelectorAll('.f1-trophy'))
                .map(t => (t.className.match(/f1-trophy--(gold|silver|bronze)/) || [])[1])
                .filter(Boolean),
        })));
    },
};
