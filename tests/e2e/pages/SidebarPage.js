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

    /**
     * A "Kupák" modul: az utolsó lezárt hétvége dobogója (templates/controller/module/trophies.html.twig).
     * @returns {Promise<{title: string, rows: Array<[string, string, number]>}>} [típus, nevek, pont]
     */
    async lastPodium() {
        I.waitForElement('.reload-able-module[data-module-route="trophies"] .f1-board');
        return I.executeScript(() => {
            const module = document.querySelector('.reload-able-module[data-module-route="trophies"]');
            const title = module.querySelector('.f1-board__title');
            return {
                title: title ? title.textContent.trim() : '',
                rows: Array.from(module.querySelectorAll('.f1-row')).map(row => [
                    (row.querySelector('.f1-trophy').className.match(/f1-trophy--(gold|silver|bronze)/) || [])[1],
                    row.querySelector('.f1-name').textContent.trim(),
                    Number(row.querySelector('.f1-num--main').textContent.trim()),
                ]),
            };
        });
    },

    /**
     * A "Pontok" modul (templates/extension/point_calculating_info.html.twig): helyezés => bajnoki pont, és az összpont sora.
     * @returns {Promise<{pairs: string, total: string}>} pl. {pairs: '1:15 2:11 …', total: 'Összpont = 60 pont'}
     */
    async pointTable() {
        I.waitForElement(locate('.f1-aside .f1-board__tag').withText('Pontok'));
        return I.executeScript(() => {
            const board = Array.from(document.querySelectorAll('.f1-aside .f1-board'))
                .find(b => b.querySelector('.f1-board__tag').textContent.trim() === 'Pontok');
            const places = Array.from(board.querySelectorAll('thead th')).slice(1).map(th => th.textContent.trim().replace('.', ''));
            const points = Array.from(board.querySelectorAll('tbody tr:first-child td')).slice(1).map(td => td.textContent.trim());
            return {
                pairs: places.map((place, i) => `${place}:${points[i]}`).join(' '),
                total: board.querySelector('tbody tr:nth-child(2)').textContent.trim().replace(/\s+/g, ' '),
            };
        });
    },

    /**
     * A "Nyeremény" modul (templates/extension/prize_draw.html.twig): helyezés => összeg, és a megjegyzés sora.
     * Nyitott nevezésnél nincs sor, a megjegyzés helyén a résztvevők száma.
     * @returns {Promise<{rows: string, note: string}>} pl. {rows: '1:24,000 2:14,400 3:9,600', note: '12 résztvevő X 5000 …'}
     */
    async prizeDraw() {
        I.waitForElement(locate('.f1-aside .f1-board__tag').withText('Nyeremény'));
        return I.executeScript(() => {
            const board = Array.from(document.querySelectorAll('.f1-aside .f1-board'))
                .find(b => b.querySelector('.f1-board__tag').textContent.trim() === 'Nyeremény');
            const note = board.querySelector('.f1-board__note');
            return {
                rows: Array.from(board.querySelectorAll('.f1-row'))
                    .map(row => `${row.querySelector('.f1-pos').textContent.trim()}:${row.querySelector('.f1-num--main').textContent.trim()}`)
                    .join(' '),
                note: note ? note.textContent.trim() : '',
            };
        });
    },

    /** a VB modul alatti második statisztika: "25 pont szerezhető még (1×25)" */
    async remainingPoints() {
        I.waitForElement('.f1-aside .f1-stats');
        return I.executeScript(() => document.querySelectorAll('.f1-aside .f1-stat')[1].textContent.trim().replace(/\s+/g, ' '));
    },
};
