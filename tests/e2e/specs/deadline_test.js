const assert = require('assert');
const {Q_RESULT, USERS} = require('../data/expected');

Feature('Határidő @deadline');

const TIME_OUT = 'Sajnos lejárt a tippelési időszak';

Scenario('past event shows time-out and no form', async ({I, bettingPage}) => {
    await I.seedScenario('deadline-passed');
    await I.loginAs('e2e_user');
    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    I.waitForText(TIME_OUT, 10, '.f1-betting');
    bettingPage.seeNoForm();

    bettingPage.selectTab('Futam');
    I.waitForElement('.f1-select__trigger');
    I.seeElement('.f1-betting__submit');
});

Scenario('deadline passing while filling rejects the bet', async ({I, bettingPage, actualPage}) => {
    const info = await I.seedScenario('about-to-close', {deadlineIn: 20});
    await I.loginAs('e2e_user');
    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    await I.waitUntilServerTime(info.events.qualify.deadline, info.serverNow);
    bettingPage.submit();
    I.waitForText(TIME_OUT, 10, '.f1-betting');

    actualPage.open();
    actualPage.seeBoard('IDŐMÉRŐ');
    I.dontSeeElement(actualPage.row('IDŐMÉRŐ', USERS.e2e_user.name));
});

// a sprintes (3 eseményes) hétvége fülei után a 2 eseményes hétvégéé kell látszódjanak
Scenario('stale weekend cache is not reused between scenarios', async ({I, bettingPage}) => {
    await I.seedScenario('sprint-open-weekend');
    await I.loginAs('e2e_user');
    bettingPage.open();
    assert.ok((await bettingPage.tabLabels()).includes('Sprint Futam'));

    await I.seedScenario('deadline-passed');
    bettingPage.open();
    I.refreshPage();
    I.waitForElement('.f1-betting__tabs');
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Időmérő', 'Futam']);
});
