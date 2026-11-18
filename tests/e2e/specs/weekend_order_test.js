const assert = require('assert');
const {WEEKEND_NAME} = require('../data/expected');

// Az aktuális hétvége eseményei mindig a "most"-hoz legközelebbi elöl: ha a futam már közelebb van, mint
// a (lezajlott) időmérő, a futam kerül előre – az Aktuális oldalon és a Tippelek fülein is.
// Eredmény után a hétvégi összesítő áll legelöl. A következő hétvégére a futam után 3 nappal vált az oldal.
Feature('Hétvégi sorrend @order');

const NEXT_WEEKEND_NAME = 'E2E Második Nagydíj';

Scenario('before qualifying the nearest event comes first', async ({I, actualPage, bettingPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), ['IDŐMÉRŐ', 'FUTAM']);

    bettingPage.open();
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Időmérő', 'Futam']);
    I.seeElement(locate(bettingPage.tab('Időmérő')).withClassAttr('is-active'));
});

Scenario('when the race is nearer than the past qualifying, the race comes first', async ({I, actualPage, bettingPage}) => {
    await I.seedScenario('race-closer');
    await I.loginAs('e2e_user');
    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), ['FUTAM', 'IDŐMÉRŐ']);

    bettingPage.open();
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Futam', 'Időmérő']);
    // a nyitott futam fül az alapértelmezett, kitölthető formmal
    I.seeElement(locate(bettingPage.tab('Futam')).withClassAttr('is-active'));
    I.waitForElement('.f1-betting__submit');
});

Scenario('after the race result the weekend summary comes first', async ({I, actualPage}) => {
    await I.seedScenario('results-entered');
    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), ['HÉTVÉGE', 'FUTAM', 'IDŐMÉRŐ']);
});

Scenario('two days after the race the finished weekend is still shown', async ({I, actualPage}) => {
    await I.seedScenario('recent-weekend');
    actualPage.open();
    assert.strictEqual(await actualPage.heroTitle(), WEEKEND_NAME);
    assert.deepStrictEqual(await actualPage.boardTags(), ['HÉTVÉGE', 'FUTAM', 'IDŐMÉRŐ']);
});

Scenario('more than three days after the race the next weekend is shown', async ({I, actualPage, bettingPage}) => {
    await I.seedScenario('finished-weekend');
    await I.loginAs('e2e_user');
    actualPage.open();
    assert.strictEqual(await actualPage.heroTitle(), NEXT_WEEKEND_NAME);
    assert.deepStrictEqual(await actualPage.boardTags(), ['IDŐMÉRŐ', 'FUTAM']);

    bettingPage.open();
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Időmérő', 'Futam']);
    I.waitForElement('.f1-betting__submit');
});
