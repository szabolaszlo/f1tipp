const assert = require('assert');
const {Q_RESULT, USERS} = require('../data/expected');

// Élő frissítés (assets/js/module_reload.js): a nyitott oldal 10 mp-enként lekéri az entitásszámokat, és ha
// változott, újratölti az érintett modult. Egy másik user (külön böngésző-session) ír / tippel,
// nekünk oldal-újratöltés nélkül kell látnunk.
Feature('Élő frissítés @live');

Scenario('a message from another user appears without reload', async ({I}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    I.waitForElement('#messages-list');

    await session('user2', async () => {
        await I.loginAs('e2e_user2');
        I.waitForElement('#message-input');
        I.fillField('#message-input', 'Üzenet a másik böngészőből');
        I.click('#message-submit');
        I.waitForElement(locate('#messages-list').withText('Üzenet a másik böngészőből'), 10);
    });

    I.waitForElement(locate('#messages-list').withText('Üzenet a másik böngészőből'), 25);
});

Scenario('another user\'s bet shows up on the open actual page', async ({I, actualPage, bettingPage}) => {
    await I.seedScenario('open-weekend');
    actualPage.open();
    assert.deepStrictEqual(await actualPage.notesOf('IDŐMÉRŐ'), ['Még senki sem tippelt.']);
    // az Aktuális modul -1 számlálóval indul, az első lekérdezés (<= 10 mp) állítja be a kiinduló értéket:
    // ami addig változik, azt nem veszi észre – ezért megvárjuk
    I.waitForElement('.reload-able-module[data-module-route="actual_events_result"]:not([data-module-count="-1"])', 15);

    await session('user2', async () => {
        await I.loginAs('e2e_user2');
        bettingPage.open();
        bettingPage.selectTab('Időmérő');
        bettingPage.fillPositions(Q_RESULT);
        bettingPage.submit();
        I.waitForText('Tipped sikeresen rögzítve!');
    });

    I.waitForElement(locate(actualPage.board('IDŐMÉRŐ')).withDescendant(
        `.//*[contains(@class,"f1-board__note")][contains(., "Tippesek: ${USERS.e2e_user2.name}")]`), 35);
});
