const assert = require('assert');
const {tryTo} = require('codeceptjs/effects');
const {ctx, usernameOf, loginAs, seed} = require('./context');

// Közös lépések: a kiinduló állapot (seed), belépés / kilépés, oldalak megnyitása, üzenőfal, böngészőhibák.
const {I, loginPage, navigationPage} = inject();

// --- kiinduló állapotok (src/E2e/E2eScenarios.php), minden időpont a futás pillanatához relatív

Given('nyitott hétvége van', () => seed('open-weekend'));
Given('nyitott sprint hétvége van', () => seed('sprint-open-weekend'));
Given('az időmérő határideje lejárt, a futam még nyitott', () => seed('deadline-passed'));
Given('az időmérő határideje {int} másodperc múlva lejár', seconds => seed('about-to-close', {deadlineIn: seconds}));
Given('mindhárom játékos tippelt, de még nincs eredmény', () => seed('bets-placed'));
Given('lezajlott hétvége van eredményekkel', () => seed('results-entered'));
Given('lezajlott hétvége van eredményekkel, de E2E User3 nem tippelt', () => seed('results-without-user3'));
Given('lezajlott hétvége van eredményekkel, E2E User1 és E2E User2 holtversenyben', () => seed('results-tie'));
Given('lezajlott sprint hétvége van eredményekkel', () => seed('sprint-results-entered'));
Given('E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem', () => seed('others-bet'));
Given('E2E User2 és E2E User3 tippelt az időmérőre, E2E User1 nem, és a határidő lejárt', () => seed('others-bet-closed'));
Given('vasárnap délelőtt van: az időmérő 20 órája volt, a futam 2 óra múlva lesz', () => seed('race-closer'));
Given('a hétvége futama 2 napja volt', () => seed('recent-weekend'));
Given('a hétvége futama 4 napja volt', () => seed('finished-weekend'));
Given('szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, a 4. jövő héten', () => seed('season'));
Given('szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, az 1. sprintes, a 4. jövő héten', () => seed('sprint-season'));

// --- belépés, kilépés

Given('{string} belép', name => loginAs(name));

When('a belépés oldalon {string} felhasználónévvel és {string} jelszóval belép', (username, password) => {
    loginPage.open();
    loginPage.login(username, password);
    ctx.loggedIn = null;
});

When('kilép', () => {
    navigationPage.logout();
    ctx.loggedIn = null;
});

When('kitörli a sütiket', () => {
    I.clearCookie();
    ctx.loggedIn = null;
});

Then('a főoldalra kerül', () => I.waitInUrl('/#/'));

Then('a fejlécben {string} felhasználónév látszik', username => navigationPage.seeLoggedInAs(username));

Then('nincs belépve senki', () => navigationPage.seeLoggedOut());

Then('a belépésnél hibaüzenet jelenik meg', () => loginPage.seeError());

Then('van Admin gomb a fejlécben', () => navigationPage.seeAdminLink());

Then('nincs Admin gomb a fejlécben', () => navigationPage.dontSeeAdminLink());

When('az Admin gombra kattint', () => navigationPage.openAdmin());

// --- oldalak

When('megnyitja a főoldalt', () => {
    I.amOnPage('/');
    I.waitForElement('.f1-header');
});

When('megnyitja a/az {string} oldalt', path => I.amOnPage(path));

When('újratölti az oldalt', () => I.refreshPage());

When('megnyitja a Szabályok oldalt', () => I.amOnPage('/#/rules'));

When('megnyitja az Eredmények oldalt', () => I.amOnPage('/#/results'));

When('megnyitja a Naptár oldalt', () => I.amOnPage('/#/calendar'));

Then('az oldalon látszik: {string}', text => I.waitForElement(locate('#main-component').withText(text), 15));

Then('megjelenik: {string}', text => I.waitForText(text, 10));

When('a/az {string} gombra kattint', label => I.click(label));

// a menüs oldalak: betöltés után van tartalom, nincs töltésjelző és hibaüzenet, és nincs böngészőhiba
Then('a/az {string} oldal hiba nélkül betöltődik', async path => {
    I.amOnPage(path);
    I.waitForFunction(() => {
        const main = document.querySelector('#main-component');
        return main && main.textContent.trim().length > 0 && !main.querySelector('.v-spinner');
    }, 15);
    I.dontSeeElement(locate('#main-component').withText('Hiba történt a betöltésekor'));
    await I.seeNoConsoleErrors();
});

Then('nincs böngészőhiba', () => I.seeNoConsoleErrors());

// az E2eHelper hibagyűjtésének önellenőrzése
When('az oldal lekér egy nem létező saját fájlt: {string}', path => {
    I.executeScript(path => fetch(path), path);
    I.wait(1);
});

Then('a hibagyűjtő böngészőhibát jelez', async () => {
    assert.strictEqual(await tryTo(() => I.seeNoConsoleErrors()), false);
});

// --- üzenőfal

When('üzenetet ír az üzenőfalra: {string}', text => {
    I.waitForElement('#message-input');
    I.fillField('#message-input', text);
    I.click('#message-submit');
});

Then('megjelenik az üzenőfalon: {string}', text => I.waitForElement(locate('#messages-list').withText(text), 10));

Then('nem látszik az üzenőfalon: {string}', text => {
    I.waitForElement('#messages-list');
    I.dontSeeElement(locate('#messages-list').withText(text));
});

Then('az üzenőfalon nincs HTML elem: {string}', tags => {
    tags.split(',').map(t => t.trim()).forEach(tag => I.dontSeeElement(`#messages-list ${tag}`));
});

Then('az üzenőfalon link mutat ide: {string}', href => I.seeElement(`#messages-list a[href="${href}"]`));

Then('az üzenetbe írt szkript nem futott le', async () => {
    assert.strictEqual(await I.executeScript(() => window.__xss), undefined);
});

Then('nincs üzenet beviteli mező', () => I.dontSeeElement('#message-input'));

// élő frissítés: külön böngésző-sessionben (a nyitott oldal közben marad) egy másik user ír
When('egy másik böngészőben {string} üzenetet ír az üzenőfalra: {string}', async (name, text) => {
    await session('masik-bongeszo', async () => {
        await I.loginAs(usernameOf(name));
        I.waitForElement('#message-input');
        I.fillField('#message-input', text);
        I.click('#message-submit');
        I.waitForElement(locate('#messages-list').withText(text), 10);
    });
});

Then('újratöltés nélkül megjelenik az üzenőfalon: {string}', text => I.waitForElement(locate('#messages-list').withText(text), 25));

// --- naptár (alapból csak a hátralévő eseményeket mutatja)

Then('a naptárban látszik: {string}', name => I.waitForElement(locate('#main-component strong').withTextEquals(name), 10));

Then('a naptárban nem látszik: {string}', name => {
    I.waitForElement('#main-component strong');
    I.dontSeeElement(locate('#main-component strong').withText(name));
});

When('a naptárban kikapcsolja a/az {string} szűrőt', label => I.click(locate('#main-component a.btn').withText(label)));
