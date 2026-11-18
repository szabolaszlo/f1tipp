const assert = require('assert');
const {Q_RESULT} = require('../data/expected');

Feature('Tippleadás @betting');

Before(async ({I, bettingPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    bettingPage.open();
});

Scenario('shows weekend events as tabs in time order', async ({bettingPage}) => {
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Időmérő', 'Futam']);
});

Scenario('submit disabled until all positions filled', ({bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.seeMissing(10);
    bettingPage.seeSubmitDisabled();
    bettingPage.fillPositions(Q_RESULT.slice(0, 9));
    bettingPage.seeMissing(1);
    bettingPage.seeSubmitDisabled();
});

// a más helyre már kiválasztott pilóta nem is szerepel a listában, így az rövidebb
Scenario('already picked driver is left out of the other pickers', ({bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.pickDriver(1, 'VER');
    bettingPage.openPicker(2);
    bettingPage.dontSeeInPicker('VER');
    bettingPage.seeInPicker('NOR');
});

Scenario('clearing a pick frees the driver for other positions', ({I, bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    I.dontSeeElement('.f1-select__clear');
    bettingPage.pickDriver(1, 'VER');
    bettingPage.seeMissing(9);
    bettingPage.clearPick(1);
    bettingPage.seeMissing(10);
    I.dontSeeElement('.f1-select__clear');
    bettingPage.openPicker(2);
    bettingPage.seeInPicker('VER');
});

// tippleadás előtt két pilóta helyet cserélhet: az egyiket töröljük, majd átválasztjuk
Scenario('two picks can be swapped before submitting', ({bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.seeSubmitEnabled();

    bettingPage.clearPick(2);
    bettingPage.seeSubmitDisabled();
    bettingPage.pickDriver(1, Q_RESULT[1]);
    bettingPage.pickDriver(2, Q_RESULT[0]);

    bettingPage.seeSelected(1, Q_RESULT[1]);
    bettingPage.seeSelected(2, Q_RESULT[0]);
    bettingPage.seeSubmitEnabled();
});

Scenario('backspace on a picker clears the pick', ({I, bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.pickDriver(1, 'VER');
    I.click(bettingPage.trigger(1));
    I.pressKey('Escape');
    I.pressKey('Backspace');
    bettingPage.seeEmpty(1);
});

// tabváltás után a be nem küldött választások megmaradnak
Scenario('unsent picks survive switching tabs', ({bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.pickDriver(1, 'VER');
    bettingPage.selectTab('Futam');
    bettingPage.seeEmpty(1);
    bettingPage.selectTab('Időmérő');
    bettingPage.seeSelected(1, 'VER');
    bettingPage.seeMissing(9);
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

// A hétvége eseményeire külön-külön kell tippelni: az időmérő tipp mentése után a futam még nyitott.
// (3 eseményes, sprintes hétvége: sprint_weekend_test.js)
Scenario('each weekend event is bet separately', ({I, bettingPage}) => {
    bettingPage.selectTab('Időmérő');
    bettingPage.fillPositions(Q_RESULT);
    bettingPage.submit();
    I.waitForText('Tipped sikeresen rögzítve!');
    I.click('Maradok');

    bettingPage.selectTab('Futam');
    bettingPage.seeMissing(11);
    bettingPage.seeEmpty(1);

    bettingPage.selectTab('Időmérő');
    bettingPage.seeLocked();
    bettingPage.seeSelected(10, Q_RESULT[9]);
    // a rögzített tipp nem törölhető
    I.dontSeeElement('.f1-select__clear');
});
