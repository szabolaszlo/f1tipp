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
};
