const assert = require('assert');
const {ctx, seed, yearOf} = require('./context');

// Szezonváltás: kiinduló állapotok (src/E2e/E2eScenarios.php) és az adatbázis állapotának összevetése
const {I, seasonResetPage} = inject();

// --- kiinduló állapotok

Given('a tavalyi szezon lezárult, az archív bejegyzés megvan', () => seed('previous-season'));
Given('a tavalyi szezon lezárult, de nincs archív bejegyzés', () => seed('previous-season-no-archive'));
Given('a tavalyi szezon lezárult, de egy eredményes eseményt már jövőbe tettek', () => seed('previous-season-moved'));
Given('idei eredmény van egy lezajlott eseményen', () => seed('current-season'));

// --- az adatbázis állapota

When('megjegyzi az adatbázis állapotát', async () => {
    ctx.dbBefore = await I.dbCounts();
});

Then('az adatbázis nem változott', async () => {
    assert.deepStrictEqual(await I.dbCounts(), ctx.dbBefore);
});

// --- az oldal elérése

When('vendégként megnyitja a Szezonváltás oldalt', () => I.amOnPage(seasonResetPage.url));

When('megnyitja a Szezonváltás oldalt', () => seasonResetPage.open());

// a böngésző a user saját sessionjével kéri le / küldi be, a választ a "a szerver {int} státusszal elutasítja" lépés ellenőrzi
When('a böngészőből lekéri a Szezonváltás oldalt', async () => {
    ctx.response = await I.executeScript(url => fetch(url).then(r => ({status: r.status})), seasonResetPage.url);
});

When('a böngészőből közvetlenül elküldi a szezon nullázását', async () => {
    ctx.response = await I.executeScript(async () => {
        const body = new URLSearchParams({code: '000000', _token: 'x'});
        const response = await fetch('/admin/maintenance/season_reset/execute', {method: 'POST', body});
        return {status: response.status};
    });
});

When('a Karbantartás menüben a Szezonváltásra kattint', () => {
    seasonResetPage.openMaintenanceMenu();
    I.click(seasonResetPage.maintenanceMenuItem());
});

Then('nem látszik a Szezonváltás oldal', () => {
    I.dontSeeElement('#season-reset-counts');
    I.dontSeeElement('#season-reset-submit');
    I.dontSee('Szezonváltás');
});

Then('a Karbantartás menüben nincs Szezonváltás', () => {
    seasonResetPage.openMaintenanceMenu();
    I.seeElement(locate('#main-menu a').withText('Cache Törlés'));
    I.dontSeeElement(seasonResetPage.maintenanceMenuItem());
});

Then('megnyílik a Szezonváltás oldal', () => {
    seasonResetPage.seeTitle();
    I.seeInCurrentUrl('routeName=season_reset');
    I.seeElement('#season-reset-counts, #season-reset-blocks');
});

// --- a törlődő sorok

Then('a törlődő darabszámok megegyeznek az adatbázis sorszámaival', async () => {
    const {tables} = await I.dbCounts();
    assert.deepStrictEqual(await seasonResetPage.grabCounts(), {
        bet: tables.bet,
        result: tables.result,
        trophy: tables.trophy,
        alternative: tables.alternative_championship,
        user: tables.user,
    });
});

Then('látszik, hogy mi marad meg', () => seasonResetPage.seeKept());

// --- tiltások, figyelmeztetés, gomb

// a szövegben {Y} a tavalyi, illetve az idei év, {Y+1} a rákövetkező
const withYear = (text, year) => text.replace(/\{Y\+1\}/g, year + 1).replace(/\{Y\}/g, year);

Then('a tiltások között látszik: {string}', text => seasonResetPage.seeBlock(text));

Then('a tiltások között látszik a tavalyi évre: {string}', text => seasonResetPage.seeBlock(withYear(text, yearOf(-1))));

Then('a tiltások között látszik az idei évre: {string}', text => seasonResetPage.seeBlock(withYear(text, yearOf())));

Then('nincs tiltás', () => seasonResetPage.dontSeeBlocks());

Then('látszik a figyelmeztetés: {string}', text => seasonResetPage.seeWarning(text));

Then('van nullázás gomb', () => seasonResetPage.seeSubmit());

Then('nincs nullázás gomb', () => seasonResetPage.dontSeeSubmit());

Then('látszik a 6 jegyű megerősítő kód', async () => {
    assert.match(await seasonResetPage.grabCode(), /^\d{6}$/);
});
