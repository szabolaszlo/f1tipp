const assert = require('assert');
const {Q_RESULT, USERS} = require('../data/expected');

Feature('Tipp megjelenítése @display');

Before(async ({I, bettingPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.submit();
    I.waitForText('Tipped sikeresen rögzítve!');
});

Scenario('saved bet is locked on reopen', ({I, bettingPage}) => {
    I.refreshPage();
    I.waitForElement('.f1-betting__tabs');
    bettingPage.selectTab('Időmérő');
    bettingPage.seeLocked();
    Q_RESULT.forEach((code, i) => bettingPage.seeSelected(i + 1, code));
    I.seeElement('button.f1-select__trigger[aria-label="1. helyezett"][disabled]');
});

Scenario('saved bet appears on actual page', async ({actualPage}) => {
    actualPage.open();
    assert.deepStrictEqual(await actualPage.chipsOf('IDŐMÉRŐ', USERS.e2e_user.name), Q_RESULT);
});
