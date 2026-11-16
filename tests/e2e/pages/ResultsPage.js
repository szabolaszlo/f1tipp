const {I} = inject();

// Eredmények oldal (/#/results, templates/controller/module/results.html.twig): hétvégénként (forduló)
// az összesítő tábla (.f1-board--summary), majd az eseménytáblák.
module.exports = {
    open() {
        I.amOnPage('/#/results');
        I.waitForElement('#results .f1-weekend');
    },

    async weekendTitles() {
        return I.executeScript(() => Array.from(document.querySelectorAll('#results .f1-weekend__title'))
            .map(el => el.textContent.replace(/\s+/g, ' ').trim()));
    },

    /**
     * Egy hétvége összesítője: [név, hétvégi (gyűjtött) pont, bajnoki pont] sorok, a megjelenés sorrendjében.
     * @returns {Promise<Array<[string, number, number]>>}
     */
    async weekendSummary(round) {
        return I.executeScript(round => {
            const section = Array.from(document.querySelectorAll('#results .f1-weekend'))
                .find(s => s.querySelector('.f1-weekend__round').textContent.trim() === `${round}. forduló`);
            if (!section) {
                return null;
            }
            return Array.from(section.querySelectorAll('.f1-board--summary .f1-row')).map(row => [
                row.querySelector('.f1-name').textContent.trim(),
                Number(row.querySelector('.f1-num--dim').textContent.trim()),
                Number(row.querySelector('.f1-num--main').textContent.trim().replace('+', '')),
            ]);
        }, round);
    },

    /** egy hétvége egy eseménytáblájában a user pontja (tag: IDŐMÉRŐ / FUTAM / SPRINT FUTAM) */
    async eventPoints(round, tag, userName) {
        return I.executeScript(({round, tag, userName}) => {
            const section = Array.from(document.querySelectorAll('#results .f1-weekend'))
                .find(s => s.querySelector('.f1-weekend__round').textContent.trim() === `${round}. forduló`);
            const board = Array.from(section.querySelectorAll('.f1-board--event'))
                .find(b => b.querySelector('.f1-board__tag').textContent.trim() === tag);
            const row = Array.from(board.querySelectorAll('.f1-tiprow'))
                .find(r => (r.querySelector('.f1-name') || {}).textContent.trim() === userName);
            return row ? Number(row.querySelector('.f1-tiprow__pts').textContent.trim()) : null;
        }, {round, tag, userName});
    },
};
