const assert = require('assert');

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

// Statisztika: pozíciónként, hány %-ban tippelték / lett az adott pilóta (src/LegacyService/Statistics).
//   időmérő p1 tippek: VER (User1), NOR (User2), TSU (User3) -> 33.33% ; eredmény: VER 100%
const statRow = (I, panelTitle, position) => I.executeScript(({panelTitle, position}) => {
    const panel = Array.from(document.querySelectorAll('#main-component .panel'))
        .find(p => p.querySelector('.panel-heading').textContent.trim() === panelTitle);
    if (!panel) {
        return null;
    }
    const row = Array.from(panel.querySelectorAll('tr'))
        .find(tr => tr.querySelector('th strong').textContent.trim() === position);
    return Object.fromEntries(Array.from(row.querySelectorAll('th'))
        .filter(th => th.querySelector('.color-one'))
        .map(th => [th.querySelector('.color-one').textContent.trim(), th.querySelector('.color-two').textContent.trim()]));
}, {panelTitle, position});

Scenario('statistics show bet and result distributions', async ({I}) => {
    await I.loginAs('e2e_user');
    I.amOnPage('/#/statistics');
    I.waitForElement(locate('#main-component .panel-heading').withText('Időmérő Tippek Statisztikája'), 15);
    assert.deepStrictEqual(await statRow(I, 'Időmérő Tippek Statisztikája', '1.'),
        {VER: '33.33%', NOR: '33.33%', TSU: '33.33%'});
    assert.deepStrictEqual(await statRow(I, 'Időmérő Eredmények Statisztikája', '1.'), {VER: '100%'});
});

// Online jelzés: a belépett user neve mellett zöld pötty jelenik meg a VB tabellában (assets/js/app.js, /get_online_user).
Scenario('logged in user is shown online in the standings', async ({I}) => {
    await I.loginAs('e2e_user');
    I.waitForFunction(() => {
        const row = Array.from(document.querySelectorAll('.f1-aside .f1-row--user'))
            .find(r => r.querySelector('.f1-name').textContent.trim() === 'E2E User1');
        const dot = row && row.querySelector('.online-dot');
        return dot && getComputedStyle(dot).backgroundColor === 'rgb(0, 128, 0)';
    }, 10);
    const others = await I.executeScript(() => Array.from(document.querySelectorAll('.f1-aside .f1-row--user'))
        .filter(r => getComputedStyle(r.querySelector('.online-dot')).backgroundColor === 'rgb(0, 128, 0)')
        .map(r => r.querySelector('.f1-name').textContent.trim()));
    assert.deepStrictEqual(others, ['E2E User1']);
});
