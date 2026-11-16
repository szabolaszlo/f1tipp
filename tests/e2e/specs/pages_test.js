Feature('Üzenőfal és oldalak @pages');

Before(async ({I}) => {
    await I.seedScenario('results-entered');
});

Scenario('logged in user posts to message wall', async ({I}) => {
    await I.loginAs('e2e_user');
    I.waitForElement('#message-input');
    I.fillField('#message-input', 'Hajrá E2E!');
    I.click('#message-submit');
    I.waitForElement(locate('#messages-list').withText('Hajrá E2E!'), 10);
});

const ROUTES = new DataTable(['route']);
['/#/statistics', '/#/calendar', '/#/rules', '/#/trophies', '/#/results'].forEach(r => ROUTES.add([r]));

Data(ROUTES).Scenario('page loads without errors', async ({I, current}) => {
    await I.loginAs('e2e_user');
    I.amOnPage(current.route);
    I.waitForFunction(() => {
        const main = document.querySelector('#main-component');
        return main && main.textContent.trim().length > 0 && !main.querySelector('.v-spinner');
    }, 15);
    I.dontSeeElement(locate('#main-component').withText('Hiba történt a betöltésekor'));
    await I.seeNoConsoleErrors();
});

Scenario('rules page shows the rules information', async ({I}) => {
    I.amOnPage('/#/rules');
    I.waitForElement(locate('#main-component').withText('E2E szabályok'), 15);
});
