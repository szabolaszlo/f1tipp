const assert = require('assert');
const {Q_RESULT} = require('../data/expected');

Feature('Tippleadás @betting');

Before(async ({I, bettingPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    bettingPage.open();
});

Scenario('shows weekend events as tabs in time order', async ({bettingPage}) => {
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Sprint Futam', 'Időmérő', 'Futam']);
});

Scenario('submit disabled until all positions filled', ({bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.seeMissing(10);
    bettingPage.seeSubmitDisabled();
    bettingPage.fillPositions(Q_RESULT.slice(0, 9));
    bettingPage.seeMissing(1);
    bettingPage.seeSubmitDisabled();
});

Scenario('same driver cannot be picked twice', ({I, bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.pickDriver(1, 'VER');
    bettingPage.openPicker(2);
    I.seeElement(bettingPage.option('VER').withAttr({'aria-disabled': 'true'}));
    I.seeElement(bettingPage.option('VER').withClassAttr('is-taken'));
    // aria-disabled: a Playwright magától nem kattintana rá, ezért kikényszerítjük – így sem választható
    I.forceClick(bettingPage.option('VER'));
    bettingPage.seeEmpty(2);
});

Scenario('race requires safety car answer', ({bettingPage}) => {
    bettingPage.selectTab('Futam');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.seeMissing(1);
    bettingPage.seeSubmitDisabled();
    bettingPage.answerSafetyCar('Igen');
    bettingPage.seeSubmitEnabled();
});

Scenario('saves bet and redirects to actual', ({I, bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.submit();
    I.waitForText('Tipped sikeresen rögzítve!');
    I.waitInUrl('#/actual', 6);
});

Scenario('stay button cancels redirect', async ({I, bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.submit();
    I.waitForText('Tipped sikeresen rögzítve!');
    I.click('Maradok');
    // az egyetlen idő-alapú ellenőrzés: a 3 mp-es átirányítás elmaradását csak várakozással lehet bizonyítani
    I.wait(4);
    I.seeInCurrentUrl('#/betting');
});
