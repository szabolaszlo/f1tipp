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
            // típus => darab (a kupaikon sarkában lévő szám)
            trophyCounts: Object.fromEntries(Array.from(row.querySelectorAll('.f1-trophy')).map(t => [
                (t.className.match(/f1-trophy--(gold|silver|bronze)/) || [])[1],
                Number((t.querySelector('b') || {textContent: '1'}).textContent.trim()),
            ])),
            flag: !!row.querySelector('.f1-flag'),
        })));
    },

    /**
     * A tabella alatti TOP 3 rekordok (időmérő / futam / hétvége), címük szerint.
     * @returns {Promise<Object<string, Array<[string, number]>>>} pl. {'Hétvégék Legjobbjai (TOP 3):': [['E2E User1', 70], ...]}
     */
    async records() {
        I.waitForElement('.f1-aside .f1-row--user');
        return I.executeScript(() => {
            const board = document.querySelector('.f1-aside .f1-row--user').closest('.f1-board');
            const out = {};
            let current = null;
            board.querySelectorAll('.f1-board__subhead, .f1-row--compact').forEach(el => {
                if (el.classList.contains('f1-board__subhead')) {
                    current = el.textContent.trim();
                    out[current] = [];
                } else if (current) {
                    out[current].push([
                        el.querySelector('.f1-name').textContent.trim(),
                        Number(el.querySelector('.f1-num--main').textContent.trim()),
                    ]);
                }
            });
            return out;
        });
    },
};
