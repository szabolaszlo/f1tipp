const {PASSWORD} = require('../data/expected');

Feature('Belépés @login');

Before(async ({I}) => {
    await I.seedScenario('open-weekend');
});

Scenario('logs in with valid credentials', ({I, loginPage, navigationPage}) => {
    loginPage.open();
    loginPage.login('e2e_user', PASSWORD);
    I.waitInUrl('/#/');
    navigationPage.seeLoggedInAs('e2e_user');
});

Scenario('rejects wrong password', ({I, loginPage, navigationPage}) => {
    loginPage.open();
    loginPage.login('e2e_user', 'rossz-jelszo');
    loginPage.seeError();
    navigationPage.seeLoggedOut();
});

Scenario('logs out', async ({I, navigationPage}) => {
    await I.loginAs('e2e_user');
    navigationPage.seeLoggedInAs('e2e_user');
    navigationPage.logout();
    navigationPage.seeLoggedOut();
});

Scenario('guest cannot bet', ({I}) => {
    I.amOnPage('/#/betting');
    I.waitForText('Nem vagy bejelentkezve!');
    I.dontSeeElement('.f1-betting__form');
});

Scenario('stays logged in after reload', ({I, loginPage, navigationPage}) => {
    loginPage.open();
    loginPage.login('e2e_user', PASSWORD);
    navigationPage.seeLoggedInAs('e2e_user');
    I.refreshPage();
    navigationPage.seeLoggedInAs('e2e_user');
});
