const assert = require('assert');
const {Q_RESULT, Q_SWAPPED, Q_OUTSIDERS, USERS} = require('../data/expected');

// Amíg a user nem tippelt egy eseményre (és a határidő sem járt le), a többiek tippjét nem láthatja,
// csak azt, hogy ki tippelt már (src/LegacyService/ResultTable/ResultTable.php: only_users -> only_bets -> full).
Feature('Mások tippjeinek láthatósága @visibility');

const OTHERS = `Tippesek: ${USERS.e2e_user2.name}, ${USERS.e2e_user3.name}`;
// a "Tippnélküliek" sor csak akkor jelenik meg, ha a tippelők több mint fele már tippelt (2/3)
const MISSING = `Tippnélküliek: ${USERS.e2e_user.name}`;

Scenario('before betting the user sees only who has bet, not the tips', async ({I, actualPage}) => {
    await I.seedScenario('others-bet');
    await I.loginAs('e2e_user');
    actualPage.open();

    assert.deepStrictEqual(await actualPage.notesOf('IDŐMÉRŐ'), [OTHERS, MISSING]);
    assert.deepStrictEqual(await actualPage.tipRowNames('IDŐMÉRŐ'), []);
    I.dontSeeElement(locate('#main-component .f1-chip').withText('TSU'));
    assert.deepStrictEqual(await actualPage.notesOf('FUTAM'), ['Még senki sem tippelt.']);
});

Scenario('guest sees only who has bet', async ({I, actualPage}) => {
    await I.seedScenario('others-bet');
    actualPage.open();

    assert.deepStrictEqual(await actualPage.notesOf('IDŐMÉRŐ'), [OTHERS, MISSING]);
    assert.deepStrictEqual(await actualPage.tipRowNames('IDŐMÉRŐ'), []);
});

Scenario('after betting the user sees everybody\'s tips for that event only', async ({I, actualPage, bettingPage}) => {
    await I.seedScenario('others-bet');
    await I.loginAs('e2e_user');
    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.submit();
    I.waitForText('Tipped sikeresen rögzítve!');

    actualPage.open();
    assert.deepStrictEqual(
        (await actualPage.tipRowNames('IDŐMÉRŐ')).sort(),
        [USERS.e2e_user.name, USERS.e2e_user2.name, USERS.e2e_user3.name].sort());
    assert.deepStrictEqual(await actualPage.chipsOf('IDŐMÉRŐ', USERS.e2e_user2.name), Q_SWAPPED);
    assert.deepStrictEqual(await actualPage.chipsOf('IDŐMÉRŐ', USERS.e2e_user3.name), Q_OUTSIDERS);
    // a futamra még nem tippelt senki: ott továbbra sincs tipp-sor
    assert.deepStrictEqual(await actualPage.tipRowNames('FUTAM'), []);
});

Scenario('another user who has not bet still cannot see the tips', async ({I, actualPage}) => {
    // a láthatóság userenként dől el: aki maga nem tippelt (itt az admin), az nem látja a tippeket
    await I.seedScenario('others-bet');
    await I.loginAs('e2e_admin');
    actualPage.open();
    assert.deepStrictEqual(await actualPage.tipRowNames('IDŐMÉRŐ'), []);
});

Scenario('after the deadline everybody\'s tips are visible even without own bet', async ({I, actualPage}) => {
    await I.seedScenario('others-bet-closed');
    await I.loginAs('e2e_user');
    actualPage.open();

    assert.deepStrictEqual(await actualPage.chipsOf('IDŐMÉRŐ', USERS.e2e_user2.name), Q_SWAPPED);
    assert.deepStrictEqual(await actualPage.chipsOf('IDŐMÉRŐ', USERS.e2e_user3.name), Q_OUTSIDERS);
    I.dontSeeElement(actualPage.row('IDŐMÉRŐ', USERS.e2e_user.name));
});
