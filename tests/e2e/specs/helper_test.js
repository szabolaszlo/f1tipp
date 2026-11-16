const assert = require('assert');
const {tryTo} = require('codeceptjs/effects');

// Az E2eHelper hibagyűjtésének önellenőrzése: a smoke tesztek csak akkor érnek valamit,
// ha a seeNoConsoleErrors tényleg elbukik a váratlan hibákon.
Feature('E2E helper @helper');

Scenario('own-host 404 resource counts as a browser problem', async ({I}) => {
    I.amOnPage('/is_logged');
    I.executeScript(() => fetch('/build/nincs-ilyen-fajl.js'));
    I.wait(1);
    assert.strictEqual(await tryTo(() => I.seeNoConsoleErrors()), false);
});

Scenario('guest 401 on /is_logged is expected noise', async ({I}) => {
    await I.seedScenario('open-weekend');
    I.amOnPage('/');
    I.waitForElement('.f1-user__login');
    assert.strictEqual(await tryTo(() => I.seeNoConsoleErrors()), true);
});
