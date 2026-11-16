const assert = require('assert');
const {PASSWORD, USERS, WEEKEND_NAME} = require('../data/expected');

// Az admin minden menüpontja: a szerkesztés/létrehozás után a változásnak a "front" oldalon is látszania kell.
// A pilóta / esemény / user / üzenet / információ / beállítás menük ROLE_SUPER_ADMIN-hoz kötöttek.
Feature('Admin menüpontok @admin');

// datetime-local mező értéke ("2026-11-16T21:06") eltolva – időzónától független, mert a mező saját értékéből számol
const shiftLocal = (value, hours) => {
    const [date, time] = value.split('T');
    const [y, m, d] = date.split('-').map(Number);
    const [hh, mm] = time.split(':').map(Number);
    const t = new Date(Date.UTC(y, m - 1, d, hh, mm) + hours * 3600 * 1000);
    const p = n => String(n).padStart(2, '0');
    return `${t.getUTCFullYear()}-${p(t.getUTCMonth() + 1)}-${p(t.getUTCDate())}T${p(t.getUTCHours())}:${p(t.getUTCMinutes())}`;
};

const pickerCodes = async I => I.executeScript(() => Array.from(document.querySelectorAll('.f1-select__panel .f1-select__code'))
    .map(el => el.textContent.trim()));

Scenario('event: renaming and moving the deadline shows up for the players', async ({I, adminPage, bettingPage, actualPage}) => {
    const info = await I.seedScenario('open-weekend');
    await I.loginAs('e2e_superadmin');

    adminPage.openEdit('Event', info.events.qualify.id);
    I.fillField('#Event_name', 'E2E Átnevezett Nagydíj');
    const current = await I.grabValueFrom('#Event_date_time');
    // a még nyitott időmérő határideje 3 órával korábbra kerül -> már lejárt
    adminPage.setDateTime('#Event_date_time', shiftLocal(current, -3));
    adminPage.saveAndReturn();
    adminPage.seeFlash('Esemény Cachek törölve');

    await I.loginAs('e2e_user');
    actualPage.open();
    I.seeElement(locate(actualPage.board('IDŐMÉRŐ')).withDescendant(
        './/span[contains(@class,"f1-board__title") and normalize-space()="E2E Átnevezett Nagydíj"]'));
    // a lejárt időmérő hátrébb kerül, a sprint maradt a legközelebbi
    assert.deepStrictEqual(await actualPage.boardTags(), ['SPRINT FUTAM', 'IDŐMÉRŐ', 'FUTAM']);

    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    I.waitForText('Sajnos lejárt a tippelési időszak', 10, '.f1-betting');
    bettingPage.seeNoForm();

    // a naptár alapból csak a hátralévő eseményeket mutatja: a már lejárt időmérő csak a szűrő kikapcsolása után látszik
    I.amOnPage('/#/calendar');
    I.waitForElement(locate('#main-component strong').withTextEquals(WEEKEND_NAME), 10);
    I.dontSeeElement(locate('#main-component strong').withText('E2E Átnevezett Nagydíj'));
    I.click(locate('#main-component a.btn').withText('Csak Hátralévők'));
    I.waitForElement(locate('#main-component strong').withText('E2E Átnevezett Nagydíj'), 10);
});

Scenario('driver: deactivated, renamed and new drivers in the betting picker', async ({I, adminPage, bettingPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_superadmin');

    // VER (id 1) inaktív lesz, NOR (id 2) új nevet kap, és felveszünk egy új pilótát
    adminPage.openEdit('Driver', 1);
    I.uncheckOption('#Driver_status');
    adminPage.saveAndReturn();

    adminPage.openEdit('Driver', 2);
    I.fillField('#Driver_name', 'Lando Átnevezett');
    adminPage.saveAndReturn();

    adminPage.openNew('Driver');
    I.fillField('#Driver_name', 'Franco Colapinto');
    I.fillField('#Driver_short', 'COL');
    I.fillField('#Driver_point', '0');
    I.checkOption('#Driver_status');
    adminPage.saveAndReturn();
    I.see('COL', 'table.datagrid');

    await I.loginAs('e2e_user');
    bettingPage.open();
    bettingPage.selectTab('Időmérő');
    bettingPage.openPicker(1);
    const codes = await pickerCodes(I);
    assert.ok(!codes.includes('VER'), `VER nem választható: ${codes}`);
    assert.ok(codes.includes('COL'), `COL választható: ${codes}`);
    // a felület CSS-sel nagybetűsít: textContent alapján
    I.seeElement(locate(bettingPage.option('NOR')).withText('Átnevezett'));
    I.click(bettingPage.option('COL'));
    bettingPage.seeSelected(1, 'COL');
});

Scenario('user: a user created in the admin can log in and joins the championship', async ({I, adminPage, loginPage, navigationPage, sidebarPage}) => {
    await I.seedScenario('results-entered');
    await I.loginAs('e2e_superadmin');

    adminPage.openNew('User');
    I.fillField('#User_name', 'E2E Újonc');
    I.fillField('#User_username', 'e2e_newbie');
    I.fillField('#User_roles_0', 'ROLE_USER');
    I.fillField('#User_password', 'ujonc-jelszo');
    I.checkOption('#User_isAlterChamps');
    adminPage.saveAndReturn();
    I.see('e2e_newbie', 'table.datagrid');

    I.amOnPage('/');
    const names = (await sidebarPage.standings()).map(r => r.name);
    assert.ok(names.includes('E2E Újonc'), `az új bajnoksági tag a tabellában: ${names}`);

    I.clearCookie();
    loginPage.open();
    loginPage.login('e2e_newbie', 'ujonc-jelszo');
    I.waitInUrl('/#/');
    navigationPage.seeLoggedInAs('e2e_newbie');
});

Scenario('user: editing a user keeps the password and removes them from the championship', async ({I, adminPage, loginPage, navigationPage, sidebarPage}) => {
    await I.seedScenario('results-entered');
    await I.loginAs('e2e_superadmin');

    // e2e_user3 (id 3): kikerül a bajnokságból, a jelszó mezőhöz (hash) nem nyúlunk
    adminPage.openEdit('User', 3);
    I.fillField('#User_name', 'E2E User3 Átnevezve');
    I.uncheckOption('#User_isAlterChamps');
    adminPage.saveAndReturn();

    I.amOnPage('/');
    const names = (await sidebarPage.standings()).map(r => r.name);
    assert.deepStrictEqual(names, [USERS.e2e_user.name, USERS.e2e_user2.name]);

    I.clearCookie();
    loginPage.open();
    loginPage.login('e2e_user3', PASSWORD);
    I.waitInUrl('/#/');
    navigationPage.seeLoggedInAs('e2e_user3');
});

Scenario('message: edited and deleted messages on the wall', async ({I, adminPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_user');
    I.waitForElement('#message-input');
    I.fillField('#message-input', 'Eredeti üzenet');
    I.click('#message-submit');
    I.waitForElement(locate('#messages-list').withText('Eredeti üzenet'), 10);

    await I.loginAs('e2e_superadmin');
    adminPage.openEdit('Message', 1);
    I.fillField('#Message_content', 'Moderált üzenet');
    adminPage.saveAndReturn();

    await I.loginAs('e2e_user');
    I.waitForElement(locate('#messages-list').withText('Moderált üzenet'), 10);
    I.dontSeeElement(locate('#messages-list').withText('Eredeti üzenet'));

    await I.loginAs('e2e_superadmin');
    adminPage.deleteRow('Message', 1);

    await I.loginAs('e2e_user');
    I.waitForElement('#messages-list');
    I.dontSeeElement(locate('#messages-list').withText('Moderált üzenet'));
});

const setEditor = (I, html) => I.executeScript(html => {
    window.CKEDITOR.instances.Information_content.setData(html);
}, html);

Scenario('information: edited rules and a new past-season page', async ({I, adminPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_superadmin');

    adminPage.openEdit('Information', 23);
    I.waitForFunction(() => window.CKEDITOR && window.CKEDITOR.instances.Information_content
        && window.CKEDITOR.instances.Information_content.status === 'ready', 15);
    await setEditor(I, '<p>Módosított E2E szabályok</p>');
    adminPage.saveAndReturn();

    // az Eredmények oldal korábbi évei az információ slug-ja alapján töltődnek (/information_content/{év})
    adminPage.openNew('Information');
    I.fillField('#Information_title', '2015-ös szezon');
    I.fillField('#Information_slug', '2015');
    I.waitForFunction(() => window.CKEDITOR && window.CKEDITOR.instances.Information_content
        && window.CKEDITOR.instances.Information_content.status === 'ready', 15);
    await setEditor(I, '<p>E2E archív eredmények 2015</p>');
    adminPage.saveAndReturn();

    await I.loginAs('e2e_user');
    I.amOnPage('/#/rules');
    I.waitForElement(locate('#main-component').withText('Módosított E2E szabályok'), 15);

    I.amOnPage('/#/results');
    I.waitForElement(locate('#main-component button').withText('2015'));
    I.click(locate('#main-component button').withText('2015'));
    I.waitForElement(locate('#main-component').withText('E2E archív eredmények 2015'), 15);
});

Scenario('setting: the analytics id is written into the page', async ({I, adminPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_superadmin');

    adminPage.openNew('Setting');
    I.fillField('#Setting_key', 'ga4MeasurementId');
    I.fillField('#Setting_value', 'G-E2ETEST');
    adminPage.saveAndReturn();

    I.amOnPage('/');
    I.waitForElement('.f1-header');
    const html = await I.executeScript(() => document.head.innerHTML);
    assert.ok(html.includes("gtag('config', 'G-E2ETEST')"), 'a beállított GA4 azonosító a gtag configban');
});

Scenario('maintenance: cache clear', async ({I, adminPage}) => {
    await I.seedScenario('open-weekend');
    await I.loginAs('e2e_admin');
    I.amOnPage('/admin/maintenance/cache_clear');
    adminPage.seeFlash('Sikeres cache törlés');
    I.seeElement('.action-new');
});
