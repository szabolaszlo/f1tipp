const assert = require('assert');
const fs = require('fs');
const path = require('path');
const {ctx, seed, yearOf, currentSeasonYear, loginAs} = require('./context');

// Szezonváltás: kiinduló állapotok (src/E2e/E2eScenarios.php) és az adatbázis állapotának összevetése
const {I, actualPage, adminPage, seasonResetPage, trophiesPage} = inject();

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

// a tiltások listája pontosan ez az egy sor (a többi ok, pl. az archív- és évszám-ok, nem látszik)
Then('a tiltások között csak ez látszik: {string}', async text => {
    seasonResetPage.seeBlock(text);
    assert.deepStrictEqual(await seasonResetPage.grabBlocks(), [text]);
});

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

/**
 * Emberi dupla kattintás: az első POST-ot a szerver feldolgozza, de a válaszát visszatartjuk, amíg a második (valódi
 * egér-)kattintás meg nem történt; így a második kattintás még a régi oldalon éri a gombot. (A Playwright dblclick-je
 * ugyanabban a taszkban kattint kétszer, amit a Chrome egyetlen beküldéssé von össze, ezért az nem bizonyítana semmit.)
 * Hogy a második kattintás NEM küld kérést, csak korlátos várakozással látható: 2 mp-ig várunk a második kérésre.
 * A nullázó kérések metódusát a ctx.resetPosts gyűjti.
 */
When('beírja a megerősítő kódot, és duplán kattint a Szezon nullázása gombra', async () => {
    I.fillField('#season-reset-code-input', await seasonResetPage.grabCode());
    ctx.resetPosts = [];
    await I.usePlaywrightTo('dupla kattintás a nullázás gombra', async ({page}) => {
        let release;
        const gate = new Promise(resolve => { release = resolve; });
        const seen = [];
        const seenPromises = [0, 1].map(i => new Promise(resolve => { seen[i] = resolve; }));
        const isExecute = url => url.searchParams.get('routeName') === 'season_reset_execute'
            || url.pathname.endsWith('/season_reset/execute');
        const handler = async route => {
            const index = ctx.resetPosts.push(route.request().method()) - 1;
            const response = await route.fetch({maxRedirects: 0});
            (seen[index] || (() => {}))();
            await gate;
            // a második beküldés felváltja az első navigációt: annak a kérése már nem teljesíthető
            await route.fulfill({response}).catch(() => {});
        };
        await page.route(isExecute, handler);
        const box = await page.locator('#season-reset-submit').boundingBox();
        const [x, y] = [box.x + box.width / 2, box.y + box.height / 2];
        await page.mouse.click(x, y);
        await seenPromises[0];
        await page.mouse.click(x, y);
        await Promise.race([seenPromises[1], new Promise(resolve => setTimeout(resolve, 2000))]);
        release();
        await page.waitForSelector('#flash-messages .alert', {timeout: 10000});
        await page.unroute(isExecute, handler);
    });
});

Then('egyetlen nullázó kérés ment el', () => assert.deepStrictEqual(ctx.resetPosts, ['POST']));

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

// az Információ id-ja a slug alapján, az Információk listájában keresve
const informationIdOf = async slug => {
    adminPage.openIndex('Information', slug);
    const id = await I.executeScript(slug => {
        const row = Array.from(document.querySelectorAll('table.datagrid tbody tr[data-id]'))
            .find(tr => Array.from(tr.querySelectorAll('td')).some(td => td.textContent.trim() === slug));
        return row ? row.dataset.id : null;
    }, slug);
    assert.ok(id, `nincs "${slug}" slugú Információ`);
    return id;
};

// a seed tavalyi archív bejegyzése (slug = tavalyi év); a sor menüjéből törölve
When('a szuperadmin törli a tavalyi archív Információt', async () => {
    await loginAs(SUPERADMIN);
    const year = String(yearOf(-1));
    adminPage.deleteRow('Information', await informationIdOf(year), year);
});

When('a szuperadmin a tavalyi archív Információ tartalmát átírja erre: {string}', async html => {
    await loginAs(SUPERADMIN);
    adminPage.openEdit('Information', await informationIdOf(String(yearOf(-1))));
    I.waitForFunction(() => window.CKEDITOR && window.CKEDITOR.instances.Information_content
        && window.CKEDITOR.instances.Information_content.status === 'ready', 15);
    await I.executeScript(html => window.CKEDITOR.instances.Information_content.setData(html), html);
    adminPage.saveAndReturn();
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

Then('nincs kupa és alternatív pont az adatbázisban', async () => {
    const {tables} = await I.dbCounts();
    assert.deepStrictEqual([tables.trophy, tables.alternative_championship], [0, 0]);
});

// a nullázás előtti állapot: a front ezt betölti (és cache-eli), a nullázás után ennek el kell tűnnie
Then('a Trófeák oldal nem üres', async () => {
    trophiesPage.open();
    const table = await trophiesPage.seasonTable();
    assert.ok(table.some(row => row.point > 0 && row.trophies.length > 0), `a Trófeák oldalon nincs kupa: ${JSON.stringify(table)}`);
});

// az Aktuális oldal hétvégéjének egyetlen tábláján sincs tipp-sor, eredménysor vagy pilóta-chip
Then('az Aktuális oldalon nem látszik tipp és eredmény', async () => {
    actualPage.open();
    const found = await I.executeScript(() => ({
        rows: Array.from(document.querySelectorAll('#main-component .f1-tiprow:not(.f1-tiprow--head)')).map(r => r.textContent.trim()),
        chips: Array.from(document.querySelectorAll('#main-component .f1-chip')).map(c => c.textContent.trim()),
    }));
    assert.deepStrictEqual(found, {rows: [], chips: []});
});

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

// --- az élő checklist

// a szövegben {Y} a lezárt (tavalyi) év, {C} az új szezon éve (az események legkésőbbi dátumának éve), {C-1} az előző
const withCheckYears = async text => {
    const current = await currentSeasonYear();
    return withYear(text, yearOf(-1)).replace(/\{C-1\}/g, current - 1).replace(/\{C\}/g, current);
};

Then('az {string} ellenőrzés állapota: {string}', (key, status) => seasonResetPage.seeCheck(key, status));

Then('az {string} ellenőrzés állapota: {string}, szövege: {string}', async (key, status, text) =>
    seasonResetPage.seeCheck(key, status, await withCheckYears(text)));

// a csapat nélküli aktív pilóták: a seedelt (és az adminban változtatott) aktív pilóták és a config/packages/f1tipp_teams.yaml
// csapat-pilótalistái különbsége, így a yaml évi frissítése nem töri el a tesztet
const teamDriverCodes = () => {
    const yaml = fs.readFileSync(path.resolve(__dirname, '../../../config/packages/f1tipp_teams.yaml'), 'utf8')
        .split('\n').filter(line => !line.trim().startsWith('#')).join('\n');
    return Array.from(yaml.matchAll(/drivers:\s*\[([^\]]*)\]/g)).flatMap(m => m[1].match(/[A-Z]{3}/g) || []);
};

Then('a {string} ellenőrzés a yaml szerint a hiányzó aktív pilótákat jelzi', async key => {
    const {drivers} = await I.dbCounts();
    const listed = teamDriverCodes();
    assert.ok(listed.length > 0, 'a f1tipp_teams.yaml-ben nem találtam pilótalistát');
    const missing = Object.entries(drivers).filter(([code, d]) => d.status === true && !listed.includes(code))
        .map(([code]) => code).sort();
    if (missing.length) {
        seasonResetPage.seeCheck(key, 'fail', `Aktív pilóta csapat nélkül a f1tipp_teams.yaml-ben: ${missing.join(', ')}`);
    } else {
        seasonResetPage.seeCheck(key, 'ok');
    }
});

Then('a kézi emlékeztetők láthatók: {}', list => {
    list.split(',').map(k => k.trim()).forEach(key => seasonResetPage.seeCheck(key, 'manual'));
});

Then('a checklistben nincs jelölőnégyzet', () => I.dontSeeElement('[data-check] input'));

// a link az EasyAdmin CRUD URL-jére (crudControllerFqcn, crudAction, szerkesztésnél entityId) visz, és megjelenik
// a lista, a szerkesztő vagy az új űrlap
const seeCheckLink = async (key, crud, action, entityId) => {
    seasonResetPage.open();
    seasonResetPage.clickCheckLink(key);
    I.waitForElement(action === 'index' ? 'table.datagrid, .datagrid-empty, .no-results' : `form#${action}-${crud}-form`, 10);
    const url = new URL(await I.grabCurrentUrl());
    assert.strictEqual(url.searchParams.get('crudControllerFqcn'), `App\\Controller\\Admin\\${crud}CrudController`, `rossz vezérlő: ${url}`);
    assert.strictEqual(url.searchParams.get('crudAction'), action, `rossz művelet: ${url}`);
    if (entityId !== undefined) {
        assert.strictEqual(url.searchParams.get('entityId'), String(entityId), `rossz entitás: ${url}`);
    }
};

Then('az {string} ellenőrzés linkje ide visz: {string} {string}', (key, crud, action) => seeCheckLink(key, crud, action));

Then('az {string} ellenőrzés linkje ide visz: {string} {string} #{int}', (key, crud, action, id) => seeCheckLink(key, crud, action, id));
