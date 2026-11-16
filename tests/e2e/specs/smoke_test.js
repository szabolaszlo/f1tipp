Feature('Smoke @smoke');

Scenario('home loads after reset', async ({I}) => {
    await I.seedScenario('open-weekend');
    I.amOnPage('/');
    I.waitForElement('.f1-header');
    // a sidebar CSS-sel nagybetűsít: textContent alapú withText kell, nem innerText alapú see
    I.seeElement(locate('.f1-aside .f1-name').withText('E2E User1'));
    await I.seeNoConsoleErrors();
});
