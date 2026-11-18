// Fejléc "Admin" gombja (assets/js/components/Navigation.vue): csak ROLE_ADMIN (és a hierarchia miatt
// ROLE_SUPER_ADMIN) jogú felhasználónak jelenik meg, és az EasyAdmin felületre visz.
Feature('Admin gomb a fejlécben @admin-link');

Before(async ({I}) => {
    await I.seedScenario('open-weekend');
});

Scenario('guest does not see the admin button', ({I, navigationPage}) => {
    I.amOnPage('/');
    navigationPage.seeLoggedOut();
    navigationPage.dontSeeAdminLink();
});

Scenario('a regular user does not see the admin button', async ({I, navigationPage}) => {
    await I.loginAs('e2e_user');
    navigationPage.seeLoggedInAs('e2e_user');
    navigationPage.dontSeeAdminLink();
});

Scenario('an admin sees the admin button and it opens the admin', async ({I, navigationPage}) => {
    await I.loginAs('e2e_admin');
    navigationPage.seeLoggedInAs('e2e_admin');
    navigationPage.openAdmin();
    // az /admin a Result CRUD listára irányít át
    I.waitInUrl('/admin');
    I.waitForElement('table.datagrid, .datagrid-empty, .no-results');
    I.see('F1Tipp Admin');
});

Scenario('a super admin sees the admin button and it opens the admin', async ({I, navigationPage}) => {
    await I.loginAs('e2e_superadmin');
    navigationPage.seeLoggedInAs('e2e_superadmin');
    navigationPage.openAdmin();
    I.waitInUrl('/admin');
    I.waitForElement('table.datagrid, .datagrid-empty, .no-results');
});

Scenario('the admin button disappears after an admin logs out', async ({I, navigationPage}) => {
    await I.loginAs('e2e_admin');
    navigationPage.seeAdminLink();
    navigationPage.logout();
    navigationPage.seeLoggedOut();
    navigationPage.dontSeeAdminLink();
});
