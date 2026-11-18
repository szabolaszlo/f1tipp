const assert = require('assert');
const {EVENTS, codes, list, rowsOf, entityCounts} = require('./context');

// Aktuális oldal (/#/actual): a hétvége eseménytáblái és összesítője. Az ellenőrző lépések maguk nyitják meg
// az oldalt, kivéve az élő frissítésé, ahol épp az a lényeg, hogy újratöltés nélkül változzon.
const {I, actualPage} = inject();

const MARKS = {pontos: 'f1-chip--exact', közeli: 'f1-chip--near', nincs: null};

When('megnyitja az Aktuális oldalt', () => actualPage.open());

When('vendégként megnyitja az Aktuális oldalt', () => actualPage.open());

Then('az Aktuális oldal táblái sorban: {string}', async text => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), list(text));
});

Then('az Aktuális oldal címe: {string}', async title => {
    actualPage.open();
    assert.strictEqual(await actualPage.heroTitle(), title);
});

Then('a/az {string} tábla címe: {string}', (tag, title) => {
    actualPage.open();
    I.seeElement(locate(actualPage.board(tag)).withDescendant(
        `.//span[contains(@class,"f1-board__title") and normalize-space()=${JSON.stringify(title)}]`));
});

// amíg a user nem tippelt (és a határidő sem járt le), csak azt látja, ki tippelt már
Then('a/az {string} táblán nincs tipp, csak ez a megjegyzés: {string}', async (tag, note) => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.notesOf(tag), [note]);
    assert.deepStrictEqual(await actualPage.tipRowNames(tag), [], `${tag}: nem látszik tipp`);
});

Then('a/az {string} táblán nincs tipp, csak ezek a megjegyzések:', async (tag, table) => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.notesOf(tag), table.parse().raw().map(r => r[0]));
    assert.deepStrictEqual(await actualPage.tipRowNames(tag), [], `${tag}: nem látszik tipp`);
});

Then('az oldalon sehol nem látszik a/az {string} pilóta tippje', code => {
    I.dontSeeElement(locate('#main-component .f1-chip').withText(code));
});

Then('a/az {string} táblán ezek tippje látszik: {string}', async (tag, names) => {
    actualPage.open();
    assert.deepStrictEqual((await actualPage.tipRowNames(tag)).sort(), list(names).sort());
});

Then('minden eseménytáblán látszik a tippje: {string}', async names => {
    actualPage.open();
    for (const {tag} of Object.values(EVENTS)) {
        assert.deepStrictEqual((await actualPage.tipRowNames(tag)).sort(), list(names).sort(), `${tag} tipp-sorai`);
    }
});

Then('a/az {string} táblán {string} tippje: {string}', async (tag, name, text) => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.chipsOf(tag, name), codes(text));
});

Then('a/az {string} táblán nincs {string} tipp-sora', (tag, name) => {
    actualPage.open();
    actualPage.seeBoard(tag);
    I.dontSeeElement(actualPage.row(tag, name));
});

// van eredmény nélküli, de lejárt határidejű tipp: látszik, de pont és pontos / közeli jelölés nélkül
Then('a/az {string} táblán {string} tippje pont és jelölés nélkül látszik', async (tag, name) => {
    actualPage.open();
    const classes = await actualPage.chipClassesOf(tag, name);
    assert.ok(classes.length > 0, `${tag}: ${name} tippje látszik`);
    assert.ok(classes.every(c => !c.includes(MARKS.pontos) && !c.includes(MARKS.közeli)), classes.join(' | '));
    I.dontSeeElement(locate(actualPage.row(tag, name)).withDescendant('.//*[contains(@class,"f1-tiprow__pts")]'));
});

// helyenként: pontos (pontos hely) / közeli (bent van, de rossz helyen) / nincs (nem ért pontot)
Then('a/az {string} táblán {string} tippjének jelölése: {string}', async (tag, name, text) => {
    actualPage.open();
    const expected = list(text);
    const classes = await actualPage.chipClassesOf(tag, name);
    assert.strictEqual(classes.length, expected.length, `${name}: ${classes.length} hely`);
    expected.forEach((mark, i) => {
        if (!(mark in MARKS)) {
            throw new Error(`ismeretlen jelölés: "${mark}" (lehet: ${Object.keys(MARKS).join(', ')})`);
        }
        const actual = Object.keys(MARKS).find(m => MARKS[m] && classes[i].includes(MARKS[m])) || 'nincs';
        assert.strictEqual(actual, mark, `${tag} ${name} ${i + 1}. hely: ${classes[i]}`);
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

Then('még nincs hétvégi összesítő és kupa', async () => {
    actualPage.open();
    I.dontSeeElement('#main-component .f1-board--summary');
    assert.strictEqual((await entityCounts()).trophies, 0, 'még nincs kupa');
});

Then('a hétvégi összesítő az Aktuális oldalon:', async table => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.weekendSummary(),
        rowsOf(table).map(r => [r['név'], Number(r['hétvégi pont']), Number(r['bajnoki pont'])]));
});

// --- élő frissítés (assets/js/module_reload.js): a nyitott oldal 10 mp-enként lekéri az entitásszámokat

// az Aktuális modul -1 számlálóval indul, az első lekérdezés (<= 10 mp) állítja be a kiinduló értéket:
// ami addig változik, azt nem veszi észre – ezért megvárjuk
When('megvárja, hogy az élő frissítés elinduljon', () => {
    I.waitForElement('.reload-able-module[data-module-route="actual_events_result"]:not([data-module-count="-1"])', 15);
});

Then('újratöltés nélkül megjelenik a/az {string} táblán: {string}', (tag, text) => {
    I.waitForElement(locate(actualPage.board(tag)).withDescendant(
        `.//*[contains(@class,"f1-board__note")][contains(., ${JSON.stringify(text)})]`), 35);
});
