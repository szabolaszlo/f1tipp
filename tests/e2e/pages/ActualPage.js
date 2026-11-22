const {I} = inject();

// Aktuális oldal (/#/actual): a hétvége eseménytáblái (templates/extension/result_table/type/*.twig).
// Egy tábla: .f1-board--event, címke: .f1-board__tag (szerveroldalon nagybetűsítve: IDŐMÉRŐ, FUTAM, SPRINT FUTAM).
const lit = s => `'${s}'`;

module.exports = {
    open() {
        I.amOnPage('/#/actual');
        I.waitForElement('.f1-board--event');
    },

    board(tag) {
        return `//div[contains(concat(' ', normalize-space(@class), ' '), ' f1-board--event ')]`
            + `[.//span[contains(@class,'f1-board__tag') and normalize-space()=${lit(tag)}]]`;
    },

    seeBoard(tag) {
        I.waitForElement({xpath: this.board(tag)});
    },

    row(tag, userName) {
        return {
            xpath: `${this.board(tag)}//div[contains(concat(' ', normalize-space(@class), ' '), ' f1-tiprow ')]`
                + `[./div[contains(@class,'f1-name') and normalize-space()=${lit(userName)}]]`,
        };
    },

    async _rowData(tag, userName) {
        this.seeBoard(tag);
        I.waitForElement(this.row(tag, userName));
        return I.executeScript(xpath => {
            const row = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
            const chips = Array.from(row.querySelectorAll('.f1-chip'));
            const pts = row.querySelector('.f1-tiprow__pts');
            return {
                chips: chips.map(c => c.textContent.trim()),
                classes: chips.map(c => c.className),
                points: pts ? pts.textContent.trim() : null,
            };
        }, this.row(tag, userName).xpath);
    },

    async chipsOf(tag, userName) {
        return (await this._rowData(tag, userName)).chips;
    },

    async chipClassesOf(tag, userName) {
        return (await this._rowData(tag, userName)).classes;
    },

    async pointsOf(tag, userName) {
        return Number((await this._rowData(tag, userName)).points);
    },

    /** a táblák címkéi megjelenési sorrendben (összesítő: HÉTVÉGE) */
    async boardTags() {
        I.waitForElement('.f1-board__tag');
        return I.executeScript(() => Array.from(document.querySelectorAll('#main-component .f1-board__tag'))
            .map(el => el.textContent.trim()));
    },

    /**
     * A HÉTVÉGE összesítő tábla (templates/extension/result_table/type/summary.html.twig):
     * [név, hétvégi (gyűjtött) pont, bajnoki pont] sorok, a megjelenés sorrendjében.
     * @returns {Promise<Array<[string, number, number]>>}
     */
    async weekendSummary() {
        I.waitForElement('#main-component .f1-board--summary .f1-row');
        return I.executeScript(() => Array.from(document.querySelectorAll('#main-component .f1-board--summary .f1-row')).map(row => [
            row.querySelector('.f1-name').textContent.trim(),
            Number(row.querySelector('.f1-num--dim').textContent.trim()),
            Number(row.querySelector('.f1-num--main').textContent.trim().replace('+', '')),
        ]));
    },

    async heroTitle() {
        I.waitForElement('.f1-hero__title');
        return I.executeScript(() => document.querySelector('.f1-hero__title').textContent.trim());
    },

    /** egy tábla tipp-sorainak user nevei (fejléc- és eredménysor nélkül) */
    async tipRowNames(tag) {
        this.seeBoard(tag);
        return I.executeScript(xpath => {
            const board = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
            return Array.from(board.querySelectorAll('.f1-tiprow:not(.f1-tiprow--head):not(.f1-tiprow--result) .f1-name'))
                .map(el => el.textContent.trim());
        }, this.board(tag));
    },

    /** a tábla megjegyzés-sorai (pl. "Tippesek: ...", "Tippnélküliek: ...", "Még senki sem tippelt."), whitespace-normalizálva */
    async notesOf(tag) {
        this.seeBoard(tag);
        return I.executeScript(xpath => {
            const board = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
            return Array.from(board.querySelectorAll('.f1-board__note'))
                .map(el => el.textContent.replace(/\s+/g, ' ').trim());
        }, this.board(tag));
    },
};
