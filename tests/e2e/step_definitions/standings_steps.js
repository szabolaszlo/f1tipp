const assert = require('assert');
const {TROPHIES, list, rowsOf, entityCounts} = require('./context');

// Eredmények oldal (hétvégénként összesítő + eseménytáblák), oldalsáv VB tabella + rekordok + Kupák modul,
// Trófeák oldal. A táblázatok oszlopnevei határozzák meg, mit ellenőrzünk.
const {I, actualPage, resultsPage, sidebarPage, trophiesPage} = inject();

const trophyList = text => list(text).map(t => {
    if (!TROPHIES[t]) {
        throw new Error(`ismeretlen kupa: "${t}" (lehet: ${Object.keys(TROPHIES).join(', ')})`);
    }
    return TROPHIES[t];
});
// "1 arany, 2 ezüst" -> {gold: 1, silver: 2}
const trophyCounts = text => Object.fromEntries(list(text).map(item => {
    const [count, type] = item.split(/\s+/);
    return [trophyList(type)[0], Number(count)];
}));
const summaryRows = table => rowsOf(table).map(r => [r['név'], Number(r['hétvégi pont']), Number(r['bajnoki pont'])]);

// --- Eredmények oldal

Then('a pontok az Eredmények oldalon a/az {int}. nagydíjon:', async (round, table) => {
    resultsPage.open();
    for (const row of rowsOf(table)) {
        for (const tag of Object.keys(row).filter(k => k !== 'név')) {
            assert.strictEqual(await resultsPage.eventPoints(round, tag, row['név']), Number(row[tag]),
                `Eredmények ${round}. nagydíj: ${row['név']} ${tag} pont`);
        }
    }
});

// táblázat: név | <táblacímke> | ... – az Aktuális oldalon és az Eredmények oldal 1. nagydíján is
Then('a pontok az Aktuális és az Eredmények oldalon:', async table => {
    const cells = rowsOf(table).flatMap(row => Object.keys(row).filter(k => k !== 'név')
        .map(tag => ({name: row['név'], tag, points: Number(row[tag])})));
    actualPage.open();
    for (const {name, tag, points} of cells) {
        assert.strictEqual(await actualPage.pointsOf(tag, name), points, `Aktuális: ${name} ${tag} pont`);
    }
    resultsPage.open();
    for (const {name, tag, points} of cells) {
        assert.strictEqual(await resultsPage.eventPoints(1, tag, name), points, `Eredmények: ${name} ${tag} pont`);
    }
});

Then('a hétvégi összesítő az Aktuális és az Eredmények oldalon:', async table => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.weekendSummary(), summaryRows(table), 'Aktuális hétvégi összesítő');
    resultsPage.open();
    assert.deepStrictEqual(await resultsPage.weekendSummary(1), summaryRows(table), 'Eredmények hétvégi összesítő');
});

Then('az Eredmények oldalon a/az {int}. nagydíj összesítője:', async (round, table) => {
    resultsPage.open();
    assert.deepStrictEqual(await resultsPage.weekendSummary(round), summaryRows(table), `${round}. nagydíj összesítője`);
});

Then('az Eredmények oldal nagydíjai:', async table => {
    resultsPage.open();
    assert.deepStrictEqual(await resultsPage.weekendTitles(), rowsOf(table).map(r => `${r['nagydíj']}. nagydíj ${r['név']}`));
});

When('az Eredmények oldalon a/az {string} évre kattint', year => {
    I.waitForElement(locate('#main-component button').withText(year));
    I.click(locate('#main-component button').withText(year));
});

// --- oldalsáv

// oszlopok: név, és tetszőlegesen pont | lemaradás | kupa ("arany", több is vesszővel) | kupák ("1 arany, 2 ezüst")
const seeStandings = async (table, ignoreOrder) => {
    I.amOnPage('/');
    const rows = rowsOf(table);
    const columns = Object.keys(rows[0] || {});
    const pick = r => {
        const out = {név: r.name};
        if (columns.includes('pont')) out.pont = r.points;
        if (columns.includes('lemaradás')) out.lemaradás = r.lag;
        if (columns.includes('kupa')) out.kupa = r.trophies;
        if (columns.includes('kupák')) out.kupák = r.trophyCounts;
        return out;
    };
    const expected = rows.map(r => {
        const out = {név: r['név']};
        if (columns.includes('pont')) out.pont = Number(r['pont']);
        if (columns.includes('lemaradás')) out.lemaradás = r['lemaradás'];
        if (columns.includes('kupa')) out.kupa = trophyList(r['kupa']);
        if (columns.includes('kupák')) out.kupák = trophyCounts(r['kupák']);
        return out;
    });
    const byName = (a, b) => a.név.localeCompare(b.név);
    const actual = (await sidebarPage.standings()).map(pick);
    assert.deepStrictEqual(ignoreOrder ? actual.sort(byName) : actual, ignoreOrder ? expected.sort(byName) : expected);
};

Then('a VB tabella:', table => seeStandings(table, false));

// teljes holtversenynél (azonos pont és kupák) a tabella sorrendje nincs meghatározva
Then('a VB tabella, sorrendtől függetlenül:', table => seeStandings(table, true));

// "Pontok" modul: "helyezés:pont" párok, és az összpont sora
Then('a pontszámítás táblázata: {string}, összesen {int} pont', async (pairs, total) => {
    I.amOnPage('/');
    assert.deepStrictEqual(await sidebarPage.pointTable(), {pairs, total: `Összpont = ${total} pont`});
});

// Nyeremény modul: "helyezés:összeg" párok és a megjegyzés sora (lezárt nevezés)
Then('a Nyeremény modul: {string}, megjegyzés: {string}', async (rows, note) => {
    I.amOnPage('/');
    assert.deepStrictEqual(await sidebarPage.prizeDraw(), {rows, note});
});

// nyitott nevezés: nincs helyezés-sor, csak a résztvevők száma
Then('a Nyeremény modulban csak ez látszik: {string}', async note => {
    I.amOnPage('/');
    assert.deepStrictEqual(await sidebarPage.prizeDraw(), {rows: '', note});
});

Then('a VB modulban a hátralévő pontok: {string}', async text => {
    I.amOnPage('/');
    assert.strictEqual(await sidebarPage.remainingPoints(), text);
});

Then('a VB tabellában szerepel: {string}', async name => {
    I.amOnPage('/');
    const names = (await sidebarPage.standings()).map(r => r.name);
    assert.ok(names.includes(name), `${name} a tabellában: ${names}`);
});

Then('a VB tabella nevei: {string}', async names => {
    I.amOnPage('/');
    assert.deepStrictEqual((await sidebarPage.standings()).map(r => r.name), list(names));
});

// a főoldali oldalsáv, újranyitás nélkül (pl. vendégként a főoldalon)
Then('az oldalsávban látszik a VB tabella, benne: {string}', name => {
    I.seeElement(locate('.f1-aside .f1-name').withText(name));
});

Then('a/az {string} rekord:', async (title, table) => {
    I.amOnPage('/');
    const records = await sidebarPage.records();
    assert.deepStrictEqual(records[title], rowsOf(table).map(r => [r['név'], Number(r['pont'])]),
        `${title} (van: ${Object.keys(records).join(', ')})`);
});

// a "Kupák" modul: a legutóbbi lezárt hétvége dobogója
Then('a Kupák modulban a/az {string} dobogója:', async (title, table) => {
    I.amOnPage('/');
    assert.deepStrictEqual(await sidebarPage.lastPodium(), {
        title,
        rows: rowsOf(table).map(r => [trophyList(r['kupa'])[0], r['név'], Number(r['pont'])]),
    });
});

Then('a Kupák modul a/az {string} dobogóját mutatja', async title => {
    I.amOnPage('/');
    assert.strictEqual((await sidebarPage.lastPodium()).title, title);
});

// "E2E User1" online: zöld pötty a neve mellett, a többieknél nincs (assets/js/app.js, /get_online_user)
Then('a VB tabellában csak {string} van online jelöléssel', async name => {
    // a CodeceptJS a paramétereket tömbként adja át a böngészőben futó függvénynek
    I.waitForFunction(([name]) => {
        const row = Array.from(document.querySelectorAll('.f1-aside .f1-row--user'))
            .find(r => r.querySelector('.f1-name').textContent.trim() === name);
        const dot = row && row.querySelector('.online-dot');
        return dot && getComputedStyle(dot).backgroundColor === 'rgb(0, 128, 0)';
    }, [name], 10);
    const online = await I.executeScript(() => Array.from(document.querySelectorAll('.f1-aside .f1-row--user'))
        .filter(r => getComputedStyle(r.querySelector('.online-dot')).backgroundColor === 'rgb(0, 128, 0)')
        .map(r => r.querySelector('.f1-name').textContent.trim()));
    assert.deepStrictEqual(online, [name]);
});

// --- Trófeák oldal

// oszlopok: név, és tetszőlegesen kupa (a user első kupája) | pont (a szezon kupatáblája, sorrendben)
Then('a Trófeák oldal kupatáblája:', async table => {
    trophiesPage.open();
    const rows = rowsOf(table);
    if (rows.length && 'kupa' in rows[0]) {
        for (const row of rows) {
            assert.strictEqual(await trophiesPage.trophyOf(row['név']), trophyList(row['kupa'])[0], `${row['név']} kupája`);
        }
    }
    if (rows.length && 'pont' in rows[0]) {
        assert.deepStrictEqual((await trophiesPage.seasonTable()).map(r => [r.name, r.point]),
            rows.map(r => [r['név'], Number(r['pont'])]));
    }
});

Then('a hétvégén pontosan {int} kupát osztottak ki', async count => {
    assert.strictEqual((await entityCounts()).trophies, count);
});
