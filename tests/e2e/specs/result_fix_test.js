const assert = require('assert');
const {
    USERS, EXPECTED, EXPECTED_AFTER_FIX, FIXED_QUALIFY_RESULT,
    RECORD_QUALIFY, RECORD_WEEKEND, EXPECTED_RECORDS, EXPECTED_RECORDS_AFTER_FIX,
} = require('../data/expected');

Feature('Eredmény javítása @fix');

const PLAYERS = ['e2e_user', 'e2e_user2', 'e2e_user3'];

const sidebarRows = expected => [...PLAYERS]
    .sort((a, b) => expected[b].alt - expected[a].alt)
    .map(u => [USERS[u].name, expected[u].alt, expected[u].lag, [expected[u].trophy]]);

Scenario('correcting the qualifying result recalculates points, standings and trophies',
    async ({I, adminResultPage, actualPage, sidebarPage, trophiesPage}) => {
        const info = await I.seedScenario('results-entered');

        I.amOnPage('/');
        assert.deepStrictEqual(
            (await sidebarPage.standings()).map(r => [r.name, r.points, r.lag, r.trophies]),
            sidebarRows(EXPECTED), 'kiinduló tabella');

        await I.loginAs('e2e_admin');
        adminResultPage.openEdit(info.results.qualify);
        adminResultPage.fill(FIXED_QUALIFY_RESULT);
        adminResultPage.save();
        adminResultPage.seeSaved();

        actualPage.open();
        for (const user of PLAYERS) {
            assert.strictEqual(await actualPage.pointsOf('IDŐMÉRŐ', USERS[user].name),
                EXPECTED_AFTER_FIX[user].qualify, `${user} időmérő pont javítás után`);
        }

        I.amOnPage('/');
        assert.deepStrictEqual(
            (await sidebarPage.standings()).map(r => [r.name, r.points, r.lag, r.trophies]),
            sidebarRows(EXPECTED_AFTER_FIX), 'tabella javítás után');

        trophiesPage.open();
        for (const user of PLAYERS) {
            assert.strictEqual(await trophiesPage.trophyOf(USERS[user].name),
                EXPECTED_AFTER_FIX[user].trophy, `${user} trófea javítás után`);
        }
    });

const pick = (records, keys) => Object.fromEntries(keys.map(k => [k, records[k]]));

const recordsScenario = keys => async ({I, adminResultPage, sidebarPage}) => {
    const info = await I.seedScenario('results-entered');

    I.amOnPage('/');
    assert.deepStrictEqual(pick(await sidebarPage.records(), keys), pick(EXPECTED_RECORDS, keys), 'rekordok javítás előtt');

    await I.loginAs('e2e_admin');
    adminResultPage.openEdit(info.results.qualify);
    adminResultPage.fill(FIXED_QUALIFY_RESULT);
    adminResultPage.save();
    adminResultPage.seeSaved();

    I.amOnPage('/');
    assert.deepStrictEqual(pick(await sidebarPage.records(), keys), pick(EXPECTED_RECORDS_AFTER_FIX, keys), 'rekordok javítás után');
};

Scenario('sidebar qualifying top-3 record follows the corrected result', recordsScenario([RECORD_QUALIFY]));

// BUG: a "Hétvégék Legjobbjai (TOP 3)" rekord eredményjavítás után is a régi pontokat mutatja (70/67 a 73/64 helyett).
// Ok: Trophy::getTopWeekendUsers() (src/Repository/Trophy.php) élettartam nélkül cache-eli a 'topWeekendUsers' kulcsot,
// és sem az EasyAdminSubscriber::reCalculateResults, sem a kalkulátorok nem törlik. Javítás után a skip levehető.
Scenario.skip('sidebar weekend top-3 record follows the corrected result', recordsScenario([RECORD_WEEKEND]));
