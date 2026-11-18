const assert = require('assert');
const {ctx, eventOf, codes, list, rowsOf, usernameOf, loginAs} = require('./context');

// Tippelek oldal (assets/js/components/Betting.vue + DriverSelect.vue), és a tippelő form kézi beküldése.
const {I, bettingPage} = inject();

const SAVED = 'Tipped sikeresen rögzítve!';

When('megnyitja a Tippelek oldalt', () => bettingPage.open());

When('vendégként megnyitja a Tippelek oldalt', () => I.amOnPage('/#/betting'));

When('a Tippelek oldalon a/az {string} fülre vált', label => {
    bettingPage.open();
    bettingPage.selectTab(label);
});

When('a/az {string} fülre vált', label => {
    I.waitForElement('.f1-betting__tabs');
    bettingPage.selectTab(label);
});

// megnyitja a Tippelek oldalt, és a fülek sorrendjét nézi
Then('a Tippelek oldal fülei sorban: {string}', async text => {
    bettingPage.open();
    assert.deepStrictEqual(await bettingPage.tabLabels(), list(text));
});

// az éppen nyitott Tippelek oldal fülei (újranyitás nélkül)
Then('a fülek sorban: {string}', async text => {
    I.waitForElement('.f1-betting__tabs');
    assert.deepStrictEqual(await bettingPage.tabLabels(), list(text));
});

Then('a/az {string} fül van kiválasztva', label => {
    I.seeElement(locate(bettingPage.tab(label)).withClassAttr('is-active'));
});

When('kitölti a helyezéseket: {string}', text => bettingPage.fillPositions(codes(text)));

When('a/az {int}. helyre kiválasztja: {string}', (position, code) => bettingPage.pickDriver(position, code));

When('megnyitja a/az {int}. hely választóját', position => bettingPage.openPicker(position));

When('törli a/az {int}. hely választását', position => bettingPage.clearPick(position));

// a választó megnyitása, bezárása Escape-pel, majd Backspace a még fókuszban lévő választón
When('a/az {int}. hely választóján Backspace-t nyom', position => {
    I.click(bettingPage.trigger(position));
    I.pressKey('Escape');
    I.pressKey('Backspace');
});

When('a safety car kérdésre ezt válaszolja: {string}', answer => bettingPage.answerSafetyCar(answer));

When('a választóban a/az {string} pilótára kattint', code => I.click(bettingPage.option(code)));

Then('a választóban nincs {string}', code => bettingPage.dontSeeInPicker(code));

Then('a választóban ott van {string}', code => bettingPage.seeInPicker(code));

Then('a választóban a/az {string} pilóta neve tartalmazza: {string}', (code, text) => {
    I.seeElement(locate(bettingPage.option(code)).withText(text));
});

Then('a/az {int}. helyen {string} van kiválasztva', (position, code) => bettingPage.seeSelected(position, code));

Then('a/az {int}. hely üres', position => bettingPage.seeEmpty(position));

Then('a/az {int}. hely választója le van tiltva', position => {
    I.seeElement(`${bettingPage.trigger(position)}[disabled]`);
});

Then('még {int} helyezés hiányzik', count => bettingPage.seeMissing(count));

Then('nincs safety car kérdés', () => I.dontSeeElement('.f1-betting__choice'));

Then('a tippküldés gomb le van tiltva', () => bettingPage.seeSubmitDisabled());

Then('a tippküldés gomb aktív', () => bettingPage.seeSubmitEnabled());

Then('nincs törlés gomb', () => I.dontSeeElement('.f1-select__clear'));

When('elküldi a tippet', () => bettingPage.submit());

// a host és a konténer órája közti eltérést az E2eHelper a seedeléskori serverNow alapján korrigálja
When('megvárja, amíg a szerver órája szerint lejár a/az {string} határideje', async label => {
    await I.waitUntilServerTime(ctx.events[eventOf(label).key].deadline, ctx.serverNow);
});

Then('a tipp rögzítve', () => I.waitForText(SAVED));

Then('átirányít az Aktuális oldalra', () => I.waitInUrl('#/actual', 6));

// a 3 mp-es átirányítás elmaradását csak várakozással lehet bizonyítani
Then('{int} másodperc múlva is a Tippelek oldalon marad', seconds => {
    I.wait(seconds);
    I.seeInCurrentUrl('#/betting');
});

Then('a tipp zárolva van, nem törölhető: {string}', text => {
    bettingPage.seeLocked();
    codes(text).forEach((code, i) => bettingPage.seeSelected(i + 1, code));
    I.dontSeeElement('.f1-select__clear');
});

Then('a tippelési időszak lejárt üzenet látszik', () => I.waitForText('Sajnos lejárt a tippelési időszak', 10, '.f1-betting'));

Then('nincs tippelő űrlap', () => bettingPage.seeNoForm());

Then('van tippelő űrlap', () => {
    I.waitForElement('.f1-select__trigger');
    I.waitForElement('.f1-betting__submit');
});

Then('a vendég nem tippelhet: {string}', text => {
    I.waitForText(text);
    I.dontSeeElement('.f1-betting__form');
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

// Külön böngésző-sessionben (a nyitott oldal közben marad) egy másik user leadja az időmérő tippjét.
When('egy másik böngészőben {string} leadja az időmérő tippjét: {string}', async (name, text) => {
    await session('masik-bongeszo', async () => {
        await I.loginAs(usernameOf(name));
        bettingPage.open();
        bettingPage.selectTab('Időmérő');
        bettingPage.fillPositions(codes(text));
        bettingPage.submit();
        I.waitForText(SAVED);
    });
});

// A tippelő formot a böngészőből, a user saját sessionjével küldjük be – mintha valaki a fejlesztői eszközökkel
// a zárolt mezőket megkerülve próbálná. A választ a következő lépés ellenőrzi.
When('a böngészőből közvetlenül beküldi a/az {string} tippet: {string}', async (label, text) => {
    const eventId = ctx.events[eventOf(label).key].id;
    ctx.response = await I.executeScript(async ({eventId, codes}) => {
        const form = await fetch(`/module/betting/form/${eventId}`).then(r => r.json());
        const body = new URLSearchParams();
        form.hidden.forEach(h => body.append(h.name, h.value));
        form.fields.forEach((f, i) => body.append(f.name, codes[i]));
        const response = await fetch(form.action, {method: 'POST', body});
        return {status: response.status, form: await response.json()};
    }, {eventId, codes: codes(text)});
});

Then('a szerver {int} státusszal elutasítja', status => assert.strictEqual(ctx.response.status, status));

Then('a szerver {int} státusszal elutasítja: {string}', (status, message) => {
    assert.strictEqual(ctx.response.status, status);
    assert.deepStrictEqual(ctx.response.form.errors, [message]);
});
