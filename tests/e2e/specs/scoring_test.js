const assert = require('assert');
const {Q_RESULT, USERS, EXPECTED, WEEKEND_NAME} = require('../data/expected');

Feature('Eredmény és pontszámítás @scoring');

const PLAYERS = ['e2e_user', 'e2e_user2', 'e2e_user3'];

// Egyetlen, sorban haladó szenárió: az admin "új eredmény" űrlapja mindig a következő eredmény nélküli
// eseményt adja, így az időmérőt és a futamot ebben a sorrendben kell rögzíteni.
Scenario('admin enters results and points, standings and trophies are calculated',
    async ({I, adminResultPage, actualPage, sidebarPage, trophiesPage}) => {
        await I.seedScenario('bets-placed');
        await I.loginAs('e2e_admin');

        // --- időmérő eredmény
        adminResultPage.openNew();
        I.see(WEEKEND_NAME);
        adminResultPage.fill(Q_RESULT);
        adminResultPage.save();
        adminResultPage.seeSaved();

        actualPage.open();
        for (const user of PLAYERS) {
            assert.strictEqual(await actualPage.pointsOf('IDŐMÉRŐ', USERS[user].name), EXPECTED[user].qualify, `${user} időmérő pont`);
        }
        const user2Classes = await actualPage.chipClassesOf('IDŐMÉRŐ', USERS.e2e_user2.name);
        user2Classes.forEach((cls, i) => assert.ok(
            cls.includes(i < 2 ? 'f1-chip--near' : 'f1-chip--exact'), `e2e_user2 p${i + 1}: ${cls}`));
        const user3Classes = await actualPage.chipClassesOf('IDŐMÉRŐ', USERS.e2e_user3.name);
        user3Classes.forEach((cls, i) => {
            if (i < 4) {
                assert.ok(!cls.includes('f1-chip--near') && !cls.includes('f1-chip--exact'), `e2e_user3 p${i + 1}: ${cls}`);
            } else {
                assert.ok(cls.includes('f1-chip--near'), `e2e_user3 p${i + 1}: ${cls}`);
            }
        });

        // --- futam eredmény
        adminResultPage.openNew();
        adminResultPage.fill(Q_RESULT, 'Igen');
        adminResultPage.save();
        adminResultPage.seeSaved();

        actualPage.open();
        for (const user of PLAYERS) {
            assert.strictEqual(await actualPage.pointsOf('FUTAM', USERS[user].name), EXPECTED[user].race, `${user} futam pont`);
        }

        // --- oldalsáv VB tabella + kupák
        I.amOnPage('/');
        const standings = await sidebarPage.standings();
        assert.deepStrictEqual(
            standings.map(r => [r.name, r.points, r.lag, r.trophies]),
            PLAYERS.map(u => [USERS[u].name, EXPECTED[u].alt, EXPECTED[u].lag, [EXPECTED[u].trophy]]),
        );
        assert.ok(!standings.some(r => r.name === USERS.e2e_admin.name), 'admin nincs a tabellában');

        // --- Trófeák oldal
        trophiesPage.open();
        for (const user of PLAYERS) {
            assert.strictEqual(await trophiesPage.trophyOf(USERS[user].name), EXPECTED[user].trophy, `${user} trófea`);
        }
    });
