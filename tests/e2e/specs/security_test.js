const assert = require('assert');
const {Q_RESULT, Q_SWAPPED, USERS} = require('../data/expected');

Feature('Jogosultságok és visszaélések @security');

// A tippelő formot a böngészőből, a felhasználó saját sessionjével küldjük be – mintha valaki a
// fejlesztői eszközökkel a zárolt mezőket megkerülve próbálná.
const postBet = (I, eventId, codes, sc) => I.executeScript(async ({eventId, codes, sc}) => {
    const form = await fetch(`/module/betting/form/${eventId}`).then(r => r.json());
    const body = new URLSearchParams();
    form.hidden.forEach(h => body.append(h.name, h.value));
    form.fields.forEach((f, i) => body.append(f.name, f.type === 'driver' ? codes[i] : sc));
    const response = await fetch(form.action, {method: 'POST', body});
    return {status: response.status, form: await response.json()};
}, {eventId, codes, sc});

Scenario('a saved bet cannot be overwritten by re-posting the form', async ({I, bettingPage, actualPage}) => {
    const info = await I.seedScenario('others-bet');
    await I.loginAs('e2e_user');
    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.submit();
    I.waitForText('Tipped sikeresen rögzítve!');

    // mentés után már látszanak a többiek tippjei – ezek alapján próbálná átírni a sajátját
    const response = await postBet(I, info.events.qualify.id, Q_SWAPPED);
    assert.strictEqual(response.status, 409);
    assert.deepStrictEqual(response.form.errors, ['A tipped már rögzítve van, nem módosítható.']);

    actualPage.open();
    assert.deepStrictEqual(await actualPage.chipsOf('IDŐMÉRŐ', USERS.e2e_user.name), Q_RESULT);
});

Scenario('the server rejects the same driver on two positions', async ({I, actualPage}) => {
    const info = await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    const duplicated = ['VER', 'VER', ...Q_RESULT.slice(2)];
    const response = await postBet(I, info.events.qualify.id, duplicated);
    assert.strictEqual(response.status, 422);

    actualPage.open();
    I.dontSeeElement(actualPage.row('IDŐMÉRŐ', USERS.e2e_user.name));
});

Scenario('guest cannot post a bet or read the message wall', async ({I}) => {
    const info = await I.seedScenario('open-weekend');
    I.amOnPage('/');
    const statuses = await I.executeScript(async eventId => ({
        form: (await fetch(`/module/betting/form/${eventId}`)).status,
        post: (await fetch(`/module/betting/form/${eventId}`, {method: 'POST'})).status,
        messages: (await fetch('/module/messages/list')).status,
    }), info.events.qualify.id);
    assert.deepStrictEqual(statuses, {form: 401, post: 401, messages: 401});
    I.dontSeeElement('#message-input');
});

Scenario('message wall shows HTML from users as plain text', async ({I}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    I.waitForElement('#message-input');
    const payload = '<img src=x onerror="window.__xss=1"><b>vastag</b> www.example.com';
    I.fillField('#message-input', payload);
    I.click('#message-submit');
    I.waitForElement(locate('#messages-list').withText('<b>vastag</b>'), 10);

    I.dontSeeElement('#messages-list img');
    I.dontSeeElement('#messages-list b');
    assert.strictEqual(await I.executeScript(() => window.__xss), undefined);
    // a linkesítés továbbra is működik
    I.seeElement('#messages-list a[href="http://www.example.com"]');

    // újratöltés után (szerveroldali renderelés) is szövegként jelenik meg
    I.refreshPage();
    I.waitForElement(locate('#messages-list').withText('<b>vastag</b>'), 10);
    I.dontSeeElement('#messages-list img');
    assert.strictEqual(await I.executeScript(() => window.__xss), undefined);
});

Scenario('guest cannot open the admin', async ({I}) => {
    await I.seedScenario('open-weekend');
    I.amOnPage('/admin');
    I.dontSeeElement('.action-new');
    I.dontSeeElement('table.datagrid');
});

Scenario('a regular user cannot open the admin or its maintenance actions', async ({I}) => {
    await I.seedScenario('results-entered');
    await I.loginAs('e2e_user');
    const statuses = await I.executeScript(async () => ({
        admin: (await fetch('/admin')).status,
        recalculate: (await fetch('/admin/maintenance/re_calculate_points')).status,
        cacheClear: (await fetch('/admin/maintenance/cache_clear')).status,
    }));
    assert.deepStrictEqual(statuses, {admin: 403, recalculate: 403, cacheClear: 403});
});

Scenario('an admin can enter results but cannot edit events, drivers or users', async ({I, adminPage}) => {
    const info = await I.seedScenario('open-weekend');
    await I.loginAs('e2e_admin');
    adminPage.openIndex('Result');

    const statuses = await I.executeScript(async urls => {
        const out = {};
        for (const [name, url] of Object.entries(urls)) {
            out[name] = (await fetch(url)).status;
        }
        return out;
    }, {
        event: adminPage.crudUrl('Event', 'edit', info.events.qualify.id),
        driver: adminPage.crudUrl('Driver', 'edit', 1),
        user: adminPage.crudUrl('User', 'edit', 1),
    });
    assert.deepStrictEqual(statuses, {event: 403, driver: 403, user: 403});
});
