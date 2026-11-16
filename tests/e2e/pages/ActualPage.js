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
};
