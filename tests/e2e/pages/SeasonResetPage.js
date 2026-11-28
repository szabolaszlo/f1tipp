const {I} = inject();

// Szezonváltás admin oldal (src/Controller/Admin/SeasonResetController.php, templates/admin/season_reset.html.twig)
const URL = '/admin?routeName=season_reset';
const COUNTS = '#season-reset-counts';

module.exports = {
    url: URL,

    open() {
        I.amOnPage(URL);
        I.waitForElement(COUNTS + ', #season-reset-blocks', 10);
    },

    /** az EasyAdmin menü "Karbantartás" almenüje: a lenyitás után látszik a menüpont */
    maintenanceMenuItem() {
        return locate('#main-menu a').withText('Szezonváltás');
    },

    openMaintenanceMenu() {
        I.click(locate('#main-menu a.submenu-toggle').withText('Karbantartás'));
    },

    seeTitle() {
        I.waitForElement(locate('.content-header-title h1').withText('Szezonváltás'), 10);
    },

    /** a "törlődik" lista soronkénti darabszáma (a sor szövege a számmal kezdődik): {bet, result, trophy, alternative, user} */
    async grabCounts() {
        const keys = ['bet', 'result', 'trophy', 'alternative', 'user'];
        const counts = {};
        for (const key of keys) {
            counts[key] = parseInt(await I.grabTextFrom(`${COUNTS} [data-count="${key}"]`), 10);
        }
        return counts;
    },

    seeBlock(text) {
        I.waitForElement(locate('#season-reset-blocks li').withText(text), 10);
    },

    /** a tiltások listájának sorai, sorrendben */
    async grabBlocks() {
        return I.executeScript(() => Array.from(document.querySelectorAll('#season-reset-blocks li'))
            .map(li => li.textContent.replace(/\s+/g, ' ').trim()));
    },

    dontSeeBlocks() {
        I.dontSeeElement('#season-reset-blocks');
    },

    seeKept() {
        I.see('Megmarad:', '#season-reset-keeps');
    },

    seeWarning(text) {
        I.waitForElement(locate('#season-reset-warnings li').withText(text), 10);
    },

    seeSubmit() {
        I.seeElement('#season-reset-submit');
    },

    dontSeeSubmit() {
        I.dontSeeElement('#season-reset-submit');
    },

    async grabCode() {
        return (await I.grabTextFrom('#season-reset-code')).replace(/\s+/g, '');
    },

    /** a nyitott oldal nullázó űrlapja: {code, token, action}, vagy null, ha nincs (tiltás, illetve nem ez az oldal) */
    async grabForm() {
        return I.executeScript(() => {
            const form = document.querySelector('#season-reset form');
            return form ? {
                code: document.querySelector('#season-reset-code').textContent.replace(/\s+/g, ''),
                token: form.querySelector('input[name="_token"]').value,
                action: form.action,
            } : null;
        });
    },

    /** a kód beírása és a gomb megnyomása; a válasz oldalát a flash-ellenőrzés várja meg */
    submitCode(code) {
        I.fillField('#season-reset-code-input', code);
        I.click('#season-reset-submit');
    },

    seeError(text) {
        I.waitForElement(locate('#flash-messages .alert-danger').withText(text), 10);
        I.dontSeeElement('#flash-messages .alert-success');
    },

    seeSuccess(text) {
        I.waitForElement(locate('#flash-messages .alert-success').withText(text), 10);
        I.dontSeeElement('#flash-messages .alert-danger');
    },

    // --- az élő checklist: sorok a [data-check="<kulcs>"], állapotuk a [data-status]

    checkSelector(key, status) {
        return `[data-check="${key}"]` + (status ? `[data-status="${status}"]` : '');
    },

    /** a pont állapota, és ha van megadva szöveg, az is a soron belül (a link és a részlet is beleszámít) */
    seeCheck(key, status, text) {
        const row = locate(this.checkSelector(key, status));
        I.waitForElement(text ? row.withText(text) : row, 10);
    },

    clickCheckLink(key) {
        I.click(locate(this.checkSelector(key)).find('a'));
    },
};
