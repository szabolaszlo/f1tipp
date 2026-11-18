const assert = require('assert');
const {USERS, WEEKEND_NAME} = require('../data/expected');

// Gherkin lépések a hétvégi flow-hoz (features/*.feature). A lépések a pages/ oldal-objektumaira épülnek,
// a várt értékek a feature fájl táblázataiban vannak.
const {I, actualPage, bettingPage, adminPage, adminResultPage, resultsPage, sidebarPage, trophiesPage} = inject();

const SAVED = 'Tipped sikeresen rögzítve!';

// a Tippelek fül neve -> az Aktuális oldal táblacímkéje és a seed (E2eScenarios) eseménykulcsa
const EVENTS = {
    'Sprint Futam': {tag: 'SPRINT FUTAM', key: 'sprint_qualify'},
    'Időmérő': {tag: 'IDŐMÉRŐ', key: 'qualify'},
    'Futam': {tag: 'FUTAM', key: 'race'},
};
const TROPHIES = {arany: 'gold', ezüst: 'silver', bronz: 'bronze'};

/** a forgatókönyv állapota: a seed válasza (esemény és eredmény id-k), és ki van belépve */
let ctx = {};

const event = label => {
    if (!EVENTS[label]) {
        throw new Error(`ismeretlen esemény: "${label}" (lehet: ${Object.keys(EVENTS).join(', ')})`);
    }
    return EVENTS[label];
};
const codes = text => text.trim().split(/\s+/);
const list = text => text.split(',').map(s => s.trim());
const rowsOf = table => table.parse().hashes();
const usernameOf = name => {
    const username = Object.keys(USERS).find(u => USERS[u].name === name);
    if (!username) {
        throw new Error(`ismeretlen játékos: "${name}"`);
    }
    return username;
};

const loginAs = async name => {
    if (ctx.loggedIn !== name) {
        await I.loginAs(usernameOf(name));
        ctx.loggedIn = name;
    }
};

const seed = async scenario => {
    const info = await I.seedScenario(scenario);
    ctx = {events: info.events, results: {...(info.results || {})}, loggedIn: null};
};

const entityCounts = () => I.executeScript(() => fetch('/module/entity_counts').then(r => r.json()));

// --- előfeltételek

Given('nyitott sprint hétvége van', () => seed('sprint-open-weekend'));

Given('lezajlott sprint hétvége van eredményekkel', () => seed('sprint-results-entered'));

Given('{string} belép', name => loginAs(name));

// --- tippelés

When('a Tippelek oldalon a/az {string} fülre vált', label => {
    bettingPage.open();
    bettingPage.selectTab(label);
});

When('a/az {string} fülre vált', label => bettingPage.selectTab(label));

When('kitölti a helyezéseket: {string}', text => bettingPage.fillPositions(codes(text)));

When('elküldi a tippet', () => {
    bettingPage.seeSubmitEnabled();
    bettingPage.submit();
});

Then('a tipp rögzítve', () => {
    I.waitForText(SAVED);
    I.click('Maradok');
});

Then('még {int} helyezés hiányzik', count => bettingPage.seeMissing(count));

Then('nincs safety car kérdés', () => I.dontSeeElement('.f1-betting__choice'));

Then('a tipp zárolva van, nem törölhető: {string}', text => {
    bettingPage.seeLocked();
    codes(text).forEach((code, i) => bettingPage.seeSelected(i + 1, code));
    I.dontSeeElement('.f1-select__clear');
});

// táblázat: esemény | helyezések | safety car – eseményenként kitölti, elküldi, marad az oldalon és ellenőrzi a zárolást
When('{string} leadja a tippjeit:', async (name, table) => {
    await loginAs(name);
    bettingPage.open();
    for (const row of rowsOf(table)) {
        bettingPage.selectTab(row['esemény']);
        bettingPage.fillPositions(codes(row['helyezések']));
        if (row['safety car']) {
            bettingPage.answerSafetyCar(row['safety car']);
        }
        bettingPage.seeSubmitEnabled();
        bettingPage.submit();
        I.waitForText(SAVED);
        I.click('Maradok');
        bettingPage.seeLocked();
    }
});

// --- Aktuális oldal

Then('az Aktuális oldal táblái sorban: {string}', async text => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), list(text));
});

Then('a Tippelek oldal fülei sorban: {string}', async text => {
    bettingPage.open();
    assert.deepStrictEqual(await bettingPage.tabLabels(), list(text));
});

Then('a/az {string} fül van kiválasztva', label => {
    I.seeElement(locate(bettingPage.tab(label)).withClassAttr('is-active'));
});

Then('a/az {string} táblán csak a tippelők neve látszik: {string}', async (tag, note) => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.notesOf(tag), [note]);
    assert.deepStrictEqual(await actualPage.tipRowNames(tag), [], `${tag}: tippek nem látszanak`);
});

Then('a/az {string} táblán {string} tippje: {string}', async (tag, name, text) => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.chipsOf(tag, name), codes(text));
});

Then('a/az {string} táblán {string} tippje pont és jelölés nélkül látszik', async (tag, name) => {
    actualPage.open();
    const classes = await actualPage.chipClassesOf(tag, name);
    assert.ok(classes.length > 0, `${tag}: ${name} tippje látszik`);
    assert.ok(classes.every(c => !c.includes('f1-chip--exact') && !c.includes('f1-chip--near')), classes.join(' | '));
    I.dontSeeElement(locate(actualPage.row(tag, name)).withDescendant('.//*[contains(@class,"f1-tiprow__pts")]'));
});

Then('minden eseménytáblán látszik a tippje: {string}', async text => {
    actualPage.open();
    for (const {tag} of Object.values(EVENTS)) {
        assert.deepStrictEqual((await actualPage.tipRowNames(tag)).sort(), list(text).sort(), `${tag} tipp-sorai`);
    }
});

// pl. 'a "SPRINT FUTAM" táblán "E2E User1" tippjében csak az 5, 6, 7, 8. hely pontos, a többi jelöletlen'
Then(/^(?:a|az) "([^"]+)" táblán "([^"]+)" tippjében (?:csak az? ([\d, ]+)\. hely pontos|egyik hely sem pontos), a többi jelöletlen$/,
    async (tag, name, exactText) => {
        actualPage.open();
        const exact = exactText ? list(exactText).map(Number) : [];
        const classes = await actualPage.chipClassesOf(tag, name);
        classes.forEach((cls, i) => {
            const position = i + 1;
            assert.ok(!cls.includes('f1-chip--near'), `${name} ${position}. hely nem lehet "közeli": ${cls}`);
            assert.strictEqual(cls.includes('f1-chip--exact'), exact.includes(position), `${name} ${position}. hely: ${cls}`);
        });
    });

// táblázat: név | <táblacímke> | ... – az oszlopok az Aktuális oldal tábláinak címkéi (SPRINT FUTAM, IDŐMÉRŐ, FUTAM)
const seePointsOnActual = async table => {
    actualPage.open();
    for (const row of rowsOf(table)) {
        for (const tag of Object.keys(row).filter(k => k !== 'név')) {
            assert.strictEqual(await actualPage.pointsOf(tag, row['név']), Number(row[tag]), `Aktuális: ${row['név']} ${tag} pont`);
        }
    }
};

Then('a pontok az Aktuális oldalon:', seePointsOnActual);

Then('a pontok az Aktuális és az Eredmények oldalon:', async table => {
    await seePointsOnActual(table);
    resultsPage.open();
    for (const row of rowsOf(table)) {
        for (const tag of Object.keys(row).filter(k => k !== 'név')) {
            assert.strictEqual(await resultsPage.eventPoints(1, tag, row['név']), Number(row[tag]), `Eredmények: ${row['név']} ${tag} pont`);
        }
    }
});

Then('még nincs hétvégi összesítő és kupa', async () => {
    actualPage.open();
    I.dontSeeElement('#main-component .f1-board--summary');
    assert.strictEqual((await entityCounts()).trophies, 0, 'még nincs kupa');
});

// --- hétvégi összesítő, tabella, rekordok, kupák

const summaryRows = table => rowsOf(table).map(r => [r['név'], Number(r['hétvégi pont']), Number(r['bajnoki pont'])]);

Then('a hétvégi összesítő az Aktuális oldalon:', async table => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.weekendSummary(), summaryRows(table), 'Aktuális hétvégi összesítő');
});

Then('a hétvégi összesítő az Aktuális és az Eredmények oldalon:', async table => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.weekendSummary(), summaryRows(table), 'Aktuális hétvégi összesítő');
    resultsPage.open();
    assert.deepStrictEqual(await resultsPage.weekendSummary(1), summaryRows(table), 'Eredmények hétvégi összesítő');
});

Then('a VB tabella:', async table => {
    I.amOnPage('/');
    assert.deepStrictEqual(
        (await sidebarPage.standings()).map(r => [r.name, r.points, r.lag, r.trophies]),
        rowsOf(table).map(r => [r['név'], Number(r['pont']), r['lemaradás'], r['kupa'] ? [TROPHIES[r['kupa']]] : []]));
});

Then('a/az {string} rekord:', async (title, table) => {
    I.amOnPage('/');
    const records = await sidebarPage.records();
    assert.deepStrictEqual(records[title], rowsOf(table).map(r => [r['név'], Number(r['pont'])]), `${title} (van: ${Object.keys(records).join(', ')})`);
});

Then('az utolsó hétvége dobogója:', async table => {
    I.amOnPage('/');
    assert.deepStrictEqual(await sidebarPage.lastPodium(), {
        title: WEEKEND_NAME,
        rows: rowsOf(table).map(r => [TROPHIES[r['kupa']], r['név'], Number(r['pont'])]),
    });
});

Then('a Trófeák oldal kupatáblája:', async table => {
    trophiesPage.open();
    const rows = rowsOf(table);
    for (const row of rows) {
        assert.strictEqual(await trophiesPage.trophyOf(row['név']), TROPHIES[row['kupa']], `${row['név']} kupája`);
    }
    assert.deepStrictEqual((await trophiesPage.seasonTable()).map(r => [r.name, r.point]), rows.map(r => [r['név'], Number(r['pont'])]));
});

Then('a hétvégén pontosan {int} kupát osztottak ki', async count => {
    assert.strictEqual((await entityCounts()).trophies, count);
});

// --- admin: eredmények

// az új eredmény űrlap a következő eredmény nélküli eseményt kínálja: ellenőrizzük, hogy a várt eseményét, a várt mezőszámmal
When(/^az admin rögzíti (?:a|az) "([^"]+)" eredményét: "([^"]+)"(?:, safety car: "([^"]+)")?$/, async (label, text, sc) => {
    const {key} = event(label);
    const positions = codes(text);
    await loginAs(USERS.e2e_admin.name);
    adminResultPage.openNew();
    await adminResultPage.seeEventOffered(ctx.events[key].id, positions.length + (sc ? 1 : 0));
    adminResultPage.fill(positions, sc || undefined);
    adminResultPage.save();
    adminResultPage.seeSaved();
    ctx.results[key] = await adminResultPage.currentResultId();
});

When('az admin a/az {string} eredményét erre javítja: {string}', async (label, text) => {
    const {key} = event(label);
    await loginAs(USERS.e2e_admin.name);
    adminResultPage.openEdit(ctx.results[key]);
    adminResultPage.fill(codes(text));
    adminResultPage.save();
    adminResultPage.seeSaved();
});

When('az admin törli a/az {string} eredményét', async label => {
    const {key} = event(label);
    await loginAs(USERS.e2e_admin.name);
    adminPage.deleteRow('Result', ctx.results[key]);
});
