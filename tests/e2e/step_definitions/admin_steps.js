const assert = require('assert');
const {ctx, DRIVER_IDS, eventOf, codes, rowsOf, userIdOf, loginAs} = require('./context');

// EasyAdmin (/admin): eredmények rögzítése / javítása / törlése (ROLE_ADMIN), és a ROLE_SUPER_ADMIN menüpontjai.
// A "megnyitja az adminban ..." lépés megjegyzi a szerkesztett entitást, a következő mező-lépések arra vonatkoznak.
const {I, adminPage, adminResultPage} = inject();

const ADMIN = 'E2E Admin';
const SUPERADMIN = 'E2E Superadmin';
const RULES_INFORMATION_ID = 23;

// datetime-local mező értéke ("2026-11-16T21:06") eltolva – időzónától független, mert a mező saját értékéből számol
const shiftLocal = (value, hours) => {
    const [date, time] = value.split('T');
    const [y, m, d] = date.split('-').map(Number);
    const [hh, mm] = time.split(':').map(Number);
    const t = new Date(Date.UTC(y, m - 1, d, hh, mm) + hours * 3600 * 1000);
    const p = n => String(n).padStart(2, '0');
    return `${t.getUTCFullYear()}-${p(t.getUTCMonth() + 1)}-${p(t.getUTCDate())}T${p(t.getUTCHours())}:${p(t.getUTCMinutes())}`;
};

const shiftEventTime = async hours => {
    const current = await I.grabValueFrom('#Event_date_time');
    adminPage.setDateTime('#Event_date_time', shiftLocal(current, hours));
};

const waitForEditor = () => I.waitForFunction(() => window.CKEDITOR && window.CKEDITOR.instances.Information_content
    && window.CKEDITOR.instances.Information_content.status === 'ready', 15);
const setEditor = html => I.executeScript(html => window.CKEDITOR.instances.Information_content.setData(html), html);

const openEdit = async (entity, id) => {
    await loginAs(SUPERADMIN);
    adminPage.openEdit(entity, id);
    ctx.adminEntity = entity;
};

// --- eredmények (ROLE_ADMIN)

// az új eredmény űrlap a következő eredmény nélküli eseményt kínálja: ellenőrizzük, hogy a várt eseményét, a várt mezőszámmal
When(/^az admin rögzíti (?:a|az) "([^"]+)" eredményét: "([^"]+)"(?:, safety car: "([^"]+)")?$/, async (label, text, sc) => {
    const {key} = eventOf(label);
    const positions = codes(text);
    await loginAs(ADMIN);
    adminResultPage.openNew();
    await adminResultPage.seeEventOffered(ctx.events[key].id, positions.length + (sc ? 1 : 0));
    adminResultPage.fill(positions, sc || undefined);
    adminResultPage.save();
    adminResultPage.seeSaved();
    ctx.results[key] = await adminResultPage.currentResultId();
});

// sc: a futam safety car válaszának javítása ('Igen' | 'Nem'); ha nincs megadva, marad a korábbi
const fixResult = async (resultKey, text, sc) => {
    await loginAs(ADMIN);
    adminResultPage.openEdit(ctx.results[resultKey]);
    adminResultPage.fill(codes(text), sc || undefined);
    adminResultPage.save();
    adminResultPage.seeSaved();
};

When(/^az admin (?:a|az) "([^"]+)" eredményét erre javítja: "([^"]+)"(?:, safety car: "([^"]+)")?$/, (label, text, sc) =>
    fixResult(eventOf(label).key, text, sc));

// a szezon szenárióban hétvégénként: w1_sprint_qualify, w1_qualify, w1_race, ...
When(/^az admin (?:a|az) (\d+)\. hétvége "([^"]+)" eredményét erre javítja: "([^"]+)"(?:, safety car: "([^"]+)")?$/, (weekend, label, text, sc) =>
    fixResult(`w${weekend}_${eventOf(label).key}`, text, sc));

When('az admin törli a/az {string} eredményét', async label => {
    await loginAs(ADMIN);
    adminPage.deleteRow('Result', ctx.results[eventOf(label).key]);
});

When('az admin újraszámolja az összes pontot', async () => {
    await loginAs(ADMIN);
    I.amOnPage('/admin/maintenance/re_calculate_points');
});

When('az admin üríti a cache-t', async () => {
    await loginAs(ADMIN);
    I.amOnPage('/admin/maintenance/cache_clear');
});

When('megnyitja az admin eredmény listát', () => adminPage.openIndex('Result'));

Then('látszik az új eredmény gomb', () => I.seeElement('.action-new'));

Then('megjelenik az admin üzenet: {string}', text => adminPage.seeFlash(text));

Then('megnyílik az admin felület', () => {
    I.waitInUrl('/admin');
    I.waitForElement('table.datagrid, .datagrid-empty, .no-results');
});

Then('az admin listában látszik: {string}', text => I.see(text, 'table.datagrid'));

// --- szuperadmin: szerkesztés

When('a szuperadmin megnyitja az adminban a/az {string} eseményt', label => openEdit('Event', ctx.events[eventOf(label).key].id));

When('a szuperadmin megnyitja az adminban a/az {string} pilótát', code => openEdit('Driver', DRIVER_IDS[code]));

When('a szuperadmin megnyitja az adminban a/az {string} felhasználót', name => openEdit('User', userIdOf(name)));

When('a szuperadmin megnyitja az adminban a/az {int}. üzenetet', id => openEdit('Message', id));

When('a szuperadmin megnyitja az adminban a szabályok oldalt', async () => {
    await openEdit('Information', RULES_INFORMATION_ID);
    waitForEditor();
});

When('a nevét átírja erre: {string}', name => I.fillField(`#${ctx.adminEntity}_name`, name));

When('a szövegét átírja erre: {string}', text => I.fillField('#Message_content', text));

When('a tartalmát átírja erre: {string}', html => setEditor(html));

When('az időpontját {int} órával korábbra teszi', hours => shiftEventTime(-hours));

When('kikapcsolja az aktív jelölést', () => I.uncheckOption('#Driver_status'));

When('kiveszi a bajnokságból', () => I.uncheckOption('#User_isAlterChamps'));

When('elmenti', () => adminPage.saveAndReturn());

// elhalasztott hétvége: minden eseménye későbbre kerül
When('a szuperadmin a hétvége minden eseményét {int} nappal későbbre teszi', async days => {
    for (const {id} of Object.values(ctx.events)) {
        await openEdit('Event', id);
        await shiftEventTime(days * 24);
        adminPage.saveAndReturn();
    }
});

When('a szuperadmin törli a/az {int}. üzenetet', async id => {
    await loginAs(SUPERADMIN);
    adminPage.deleteRow('Message', id);
});

// --- szuperadmin: új elemek (táblázat: mező | érték)

When('a szuperadmin felvesz egy új pilótát:', async table => {
    const f = table.parse().rowsHash();
    await loginAs(SUPERADMIN);
    adminPage.openNew('Driver');
    I.fillField('#Driver_name', f['név']);
    I.fillField('#Driver_short', f['rövidítés']);
    I.fillField('#Driver_point', f['pont']);
    if (f['aktív'] === 'igen') {
        I.checkOption('#Driver_status');
    }
    adminPage.saveAndReturn();
});

When('a szuperadmin felvesz egy új felhasználót:', async table => {
    const f = table.parse().rowsHash();
    await loginAs(SUPERADMIN);
    adminPage.openNew('User');
    I.fillField('#User_name', f['név']);
    I.fillField('#User_username', f['felhasználónév']);
    I.fillField('#User_roles_0', f['szerepkör']);
    I.fillField('#User_password', f['jelszó']);
    if (f['bajnokság tagja'] === 'igen') {
        I.checkOption('#User_isAlterChamps');
    }
    adminPage.saveAndReturn();
});

When('a szuperadmin felvesz egy új információs oldalt:', async table => {
    const f = table.parse().rowsHash();
    await loginAs(SUPERADMIN);
    adminPage.openNew('Information');
    I.fillField('#Information_title', f['cím']);
    I.fillField('#Information_slug', f['slug']);
    waitForEditor();
    await setEditor(f['tartalom']);
    adminPage.saveAndReturn();
});

When('a szuperadmin felvesz egy új beállítást: {string} = {string}', async (key, value) => {
    await loginAs(SUPERADMIN);
    adminPage.openNew('Setting');
    I.fillField('#Setting_key', key);
    I.fillField('#Setting_value', value);
    adminPage.saveAndReturn();
});

// --- szuperadmin: bajnoki pontozási tábla (a seed sorainak id-ja a helyezés, src/E2e/E2eFixtures.php: POINT_TABLE)

When('a szuperadmin átírja a bajnoki pontokat:', async table => {
    for (const row of rowsOf(table)) {
        await openEdit('ChampionshipPoint', Number(row['helyezés']));
        I.fillField('#ChampionshipPoint_points', row['pont']);
        adminPage.saveAndReturn();
    }
});

When('a szuperadmin törli a/az {int}. helyezés bajnoki pontját', async place => {
    await loginAs(SUPERADMIN);
    adminPage.deleteRow('ChampionshipPoint', place);
});

// mentés várakozás nélkül: a következő lépés a validációs hibát keresi
When('a szuperadmin felvesz egy bajnoki pontot: {int}. helyezés, {int} pont', async (place, points) => {
    await loginAs(SUPERADMIN);
    adminPage.openNew('ChampionshipPoint');
    I.fillField('#ChampionshipPoint_place', place);
    I.fillField('#ChampionshipPoint_points', points);
    I.click('.action-saveAndReturn');
});

Then('a mentés hibát jelez: {string}', text => {
    I.waitForText(text, 10, 'form');
    I.dontSeeElement('table.datagrid');
});

Then('az oldal fejlécében benne van: {string}', async text => {
    const html = await I.executeScript(() => document.head.innerHTML);
    assert.ok(html.includes(text), `a <head>-ben: ${text}`);
});

// --- jogosultságok

When('vendégként megnyitja az admin felületet', () => I.amOnPage('/admin'));

Then('nem látszik az admin felület', () => {
    I.dontSeeElement('.action-new');
    I.dontSeeElement('table.datagrid');
});

// táblázat: metódus | útvonal | státusz. Az útvonalban {időmérő} az időmérő esemény id-ja;
// "admin <Entitás> <művelet> <id>" alakban az EasyAdmin CRUD URL-je.
Then('a kérések válasza:', async table => {
    const rows = rowsOf(table).map(r => {
        const path = r['útvonal'].replace('{időmérő}', ctx.events.qualify && ctx.events.qualify.id);
        const crud = path.match(/^admin (\w+) (\w+) (\d+)$/);
        return {method: r['metódus'], url: crud ? adminPage.crudUrl(crud[1], crud[2], crud[3]) : path, status: Number(r['státusz'])};
    });
    const statuses = await I.executeScript(async rows => {
        const out = [];
        for (const {method, url} of rows) {
            out.push((await fetch(url, {method})).status);
        }
        return out;
    }, rows);
    assert.deepStrictEqual(
        rowsOf(table).map((r, i) => `${r['metódus']} ${r['útvonal']} -> ${statuses[i]}`),
        rowsOf(table).map((r, i) => `${r['metódus']} ${r['útvonal']} -> ${rows[i].status}`));
});
