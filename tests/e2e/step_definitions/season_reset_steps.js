const assert = require('assert');
const {ctx, seed, yearOf, loginAs} = require('./context');

// Szezonváltás: kiinduló állapotok (src/E2e/E2eScenarios.php) és az adatbázis állapotának összevetése
const {I, adminPage, seasonResetPage, trophiesPage} = inject();

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

// --- a nullázás beküldése

const SUPERADMIN = 'E2E Superadmin';

// a megnyitott oldal kódja, CSRF-tokenje és az űrlap címe (/admin?routeName=season_reset_execute)
When('megjegyzi a megerősítő kódot', async () => {
    ctx.reset = await seasonResetPage.grabForm();
    assert.ok(ctx.reset, 'a Szezonváltás oldalon nincs nullázó űrlap');
});

When('beírja a megerősítő kódot, és a Szezon nullázása gombra kattint', async () => {
    seasonResetPage.submitCode(await seasonResetPage.grabCode());
});

When('rossz kódot ír be, és a Szezon nullázása gombra kattint', async () => {
    seasonResetPage.submitCode(await seasonResetPage.grabCode() === '000000' ? '111111' : '000000');
});

/**
 * Közvetlen POST a böngészőből, a superadmin saját sessionjével (mintha a böngésző "vissza" gombjával vagy a
 * fejlesztői eszközökkel küldené újra). A szerver a Szezonváltás oldalra irányít vissza; a flash-t utána az oldal
 * megnyitásával nézzük meg. Az érvényes CSRF-token a nyitott oldalé, ha van rajta űrlap (a "mostani"), különben a
 * megjegyzett. Több kérés egymás után megy, köztük az oldal megnyitása (ami új kódot adna) nélkül.
 * @param {...{code: string, token?: string}} posts token nélkül az érvényes tokennel
 */
const postReset = async (...posts) => {
    const form = await seasonResetPage.grabForm();
    const {action, token: validToken} = form || ctx.reset;
    const responses = await I.executeScript(async ({action, posts}) => {
        const out = [];
        for (const {code, token} of posts) {
            const r = await fetch(action, {method: 'POST', body: new URLSearchParams({code, _token: token}), redirect: 'manual'});
            out.push({status: r.status, type: r.type});
        }
        return out;
    }, {action, posts: posts.map(({code, token}) => ({code, token: token !== undefined ? token : validToken}))});
    responses.forEach(r => assert.strictEqual(r.type, 'opaqueredirect', `a POST válasza nem átirányítás: ${JSON.stringify(r)}`));
    seasonResetPage.open();
};

When('a böngészőből elküldi a megjegyzett kódot', () => postReset({code: ctx.reset.code}));

When('a böngészőből hamis tokennel elküldi a megjegyzett kódot', () => postReset({code: ctx.reset.code, token: 'hamis'}));

When('a böngészőből a megjegyzett kódot előbb hamis, majd a jó tokennel küldi el', () =>
    postReset({code: ctx.reset.code, token: 'hamis'}, {code: ctx.reset.code}));

When('a böngészőből üres kóddal elküldi a szezon nullázását', () => postReset({code: ''}));

Given('a szuperadmin nullázza a szezont', async () => {
    await loginAs(SUPERADMIN);
    seasonResetPage.open();
    seasonResetPage.submitCode(await seasonResetPage.grabCode());
    seasonResetPage.seeSuccess('Szezon nullázva:');
});

// a seed tavalyi archív bejegyzése (slug = tavalyi év); az Információk listájában keresve, a sor menüjéből törölve
When('a szuperadmin törli a tavalyi archív Információt', async () => {
    await loginAs(SUPERADMIN);
    const year = String(yearOf(-1));
    adminPage.openIndex('Information', year);
    const id = await I.executeScript(year => {
        const row = Array.from(document.querySelectorAll('table.datagrid tbody tr[data-id]'))
            .find(tr => Array.from(tr.querySelectorAll('td')).some(td => td.textContent.trim() === year));
        return row ? row.dataset.id : null;
    }, year);
    assert.ok(id, `nincs "${year}" slugú Információ`);
    adminPage.deleteRow('Information', id, year);
});

// --- az eredmény

Then('megjelenik a hibaüzenet: {string}', text => seasonResetPage.seeError(text));

Then('megjelenik a siker-üzenet', () => seasonResetPage.seeSuccess('Szezon nullázva:'));

Then('megjelenik a siker-üzenet a megjegyzett darabszámokkal', () => {
    const t = ctx.dbBefore.tables;
    seasonResetPage.seeSuccess(`Szezon nullázva: ${t.bet} tipp, ${t.result} eredmény, ${t.trophy} kupa, `
        + `${t.alternative_championship} alternatív pont-sor törölve, ${t.user} felhasználó pontjai nullázva.`);
});

Then('az oldalon új, eltérő megerősítő kód van', async () => {
    const code = await seasonResetPage.grabCode();
    assert.match(code, /^\d{6}$/);
    assert.notStrictEqual(code, ctx.reset.code);
});

Then('az adatbázisban {int} tipp van', async count => {
    assert.strictEqual((await I.dbCounts()).tables.bet, count);
});

const DELETED = ['bet', 'bet_attribute', 'result', 'result_attribute', 'alternative_championship', 'trophy'];
const KEPT = ['event', 'user', 'driver', 'championship_point', 'prize_draw', 'prize', 'information', 'setting', 'message_wall'];
const pick = (object, keys) => Object.fromEntries(keys.map(k => [k, object[k]]));
const mapValues = (object, fn) => Object.fromEntries(Object.entries(object).map(([k, v]) => [k, fn(v)]));

Then('a tippek, eredmények, kupák és alternatív pontok törlődtek, a userek pontjai nullázódtak', async () => {
    const after = await I.dbCounts();
    assert.deepStrictEqual(pick(after.tables, DELETED), Object.fromEntries(DELETED.map(t => [t, 0])));
    assert.deepStrictEqual(
        mapValues(after.users, u => pick(u, ['point_summary', 'point_difference', 'alternative_point_summary', 'alternative_point_difference'])),
        mapValues(ctx.dbBefore.users, () => ({point_summary: 0, point_difference: null, alternative_point_summary: null, alternative_point_difference: null})));
});

Then('minden más adat változatlan', async () => {
    const after = await I.dbCounts();
    const before = ctx.dbBefore;
    // a seed nem üres: különben a változatlanság semmit nem bizonyítana
    KEPT.forEach(t => assert.ok(before.tables[t] > 0, `a seedben nincs ${t} sor`));
    assert.deepStrictEqual(pick(after.tables, KEPT), pick(before.tables, KEPT));
    assert.deepStrictEqual(after.events, before.events);
    assert.deepStrictEqual(after.drivers, before.drivers);
    assert.deepStrictEqual(mapValues(after.users, u => u.is_alter_champs), mapValues(before.users, u => u.is_alter_champs));
});

// --- a front a nullázás után

Then('a Trófeák oldal üres', async () => {
    trophiesPage.open();
    assert.deepStrictEqual(await trophiesPage.seasonTable(), []);
    I.see('Még nincsenek eredmények', '#main-component');
});

When('az Eredmények oldalon a tavalyi évre kattint', () => {
    const year = String(yearOf(-1));
    I.waitForElement(locate('#main-component button').withText(year));
    I.click(locate('#main-component button').withText(year));
});

Then('az oldalon látszik a tavalyi évre: {string}', text =>
    I.waitForElement(locate('#main-component').withText(withYear(text, yearOf(-1))), 15));
