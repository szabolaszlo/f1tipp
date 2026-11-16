const {I} = inject();

// Tippelés oldal (assets/js/components/Betting.vue + DriverSelect.vue).
// A felület CSS-sel nagybetűsít, ezért a szövegeket textContent alapján (withText/withTextEquals) keressük.
const xp = s => `'${s}'`;
// osztály + pontos (whitespace-normalizált) szöveg; a withTextEquals nem normalizál
const exact = (cls, text) => ({
    xpath: `//*[contains(concat(' ', normalize-space(@class), ' '), ' ${cls} ')][normalize-space(.)=${xp(text)}]`,
});

module.exports = {
    open() {
        I.amOnPage('/#/betting');
        I.waitForElement('.f1-betting__tabs');
    },

    tab(label) {
        return exact('f1-betting__tab', label);
    },

    async tabLabels() {
        // a grabTextFrom innerText-et ad (CSS nagybetűs lehet) – a textContent kell
        return I.executeScript(() =>
            Array.from(document.querySelectorAll('.f1-betting__tab')).map(el => el.textContent.trim()));
    },

    selectTab(label) {
        I.click(this.tab(label));
        I.waitForElement(locate(this.tab(label)).withClassAttr('is-active'));
        I.waitForElement('.f1-betting__form');
    },

    trigger(position) {
        return `button.f1-select__trigger[aria-label="${position}. helyezett"]`;
    },

    option(code) {
        return locate('li.f1-select__option')
            .withDescendant(`.//span[contains(@class,"f1-select__code") and normalize-space()=${xp(code)}]`);
    },

    openPicker(position) {
        I.click(this.trigger(position));
        I.waitForElement('.f1-select__panel');
    },

    pickDriver(position, code) {
        this.openPicker(position);
        I.click(this.option(code));
        this.seeSelected(position, code);
    },

    fillPositions(codes) {
        codes.forEach((code, i) => this.pickDriver(i + 1, code));
    },

    seeSelected(position, code) {
        I.waitForElement(locate(this.trigger(position))
            .withDescendant(`.//span[contains(@class,"f1-select__code") and normalize-space()=${xp(code)}]`));
    },

    seeEmpty(position) {
        I.seeElement(locate(this.trigger(position)).withDescendant('.//span[contains(@class,"f1-select__placeholder")]'));
    },

    answerSafetyCar(answer) {
        I.click(exact('f1-betting__choice', answer));
        I.waitForElement(locate(exact('f1-betting__choice', answer)).withClassAttr('is-active'));
    },

    seeMissing(count) {
        I.waitForElement(locate('.f1-betting__hint').withText(`Még ${count} helyezés hiányzik`));
    },

    seeSubmitDisabled() {
        I.seeElement('.f1-betting__submit[disabled]');
    },

    seeSubmitEnabled() {
        I.waitForElement('.f1-betting__submit:not([disabled])');
    },

    submit() {
        I.click('.f1-betting__submit');
    },

    seeLocked() {
        I.waitForElement(locate('.f1-betting__note').withText('A tipped rögzítve, már nem módosítható.'));
        I.dontSeeElement('.f1-betting__submit');
    },

    seeNoForm() {
        I.dontSeeElement('.f1-select__trigger');
        I.dontSeeElement('.f1-betting__submit');
    },
};
