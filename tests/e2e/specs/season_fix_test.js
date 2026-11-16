const assert = require('assert');
const {SEASON_ROUNDS, SEASON, SEASON_AFTER_FIX, FIXED_QUALIFY_RESULT, USERS} = require('../data/expected');

// Késői javítás: az 1. hétvége időmérő eredménye két másik hétvége után derül ki rosszul. Az admin átírja,
// és a pontoknak, a hétvégi összesítőnek, a bajnoki tabellának és a kupáknak újra kell számolódniuk –
// a többi hétvégét viszont nem szabad érintenie.
Feature('Késői eredményjavítás szezon közben @season');

const PLAYERS = ['e2e_user', 'e2e_user2', 'e2e_user3'];

const summaries = async resultsPage => {
    const out = {};
    for (let round = 1; round <= 3; round++) {
        out[round] = await resultsPage.weekendSummary(round);
    }
    return out;
};

const expectedSummaries = expected => {
    const out = {};
    for (let round = 1; round <= 3; round++) {
        out[round] = PLAYERS
            .map(u => [USERS[u].name, ...expected.weekends[u][round - 1]])
            .sort((a, b) => b[1] - a[1]);
    }
    return out;
};

const standings = async sidebarPage => (await sidebarPage.standings())
    .map(r => [r.name, r.points, r.lag, r.trophyCounts]);

Scenario('season state is calculated per weekend', async ({I, resultsPage, sidebarPage, trophiesPage}) => {
    await I.seedScenario('season');
    await I.loginAs('e2e_user');

    resultsPage.open();
    assert.deepStrictEqual(await resultsPage.weekendTitles(),
        SEASON_ROUNDS.map((name, i) => `${i + 1}. forduló ${name}`));
    assert.deepStrictEqual(await summaries(resultsPage), expectedSummaries(SEASON));

    I.amOnPage('/');
    assert.deepStrictEqual(await standings(sidebarPage), SEASON.standings);
    // a "Kupák" modul a legutóbbi lezárt (3.) hétvége dobogóját mutatja
    assert.deepStrictEqual(await sidebarPage.lastPodium(), {
        title: SEASON_ROUNDS[2],
        rows: [['gold', 'E2E User3', 73], ['silver', 'E2E User1', 64], ['bronze', 'E2E User2', 12]],
    });

    trophiesPage.open();
    assert.deepStrictEqual((await trophiesPage.seasonTable()).map(r => [r.name, r.point]), SEASON.trophyTable);
});

Scenario('correcting a result two weekends later recalculates the whole season', async ({I, adminResultPage, resultsPage, sidebarPage, trophiesPage}) => {
    const info = await I.seedScenario('season');

    await I.loginAs('e2e_admin');
    adminResultPage.openEdit(info.results.w1_qualify);
    adminResultPage.fill(FIXED_QUALIFY_RESULT);
    adminResultPage.save();
    adminResultPage.seeSaved();

    await I.loginAs('e2e_user');
    resultsPage.open();
    assert.strictEqual(await resultsPage.eventPoints(1, 'IDŐMÉRŐ', USERS.e2e_user.name), 34);
    assert.strictEqual(await resultsPage.eventPoints(1, 'IDŐMÉRŐ', USERS.e2e_user2.name), 40);
    assert.deepStrictEqual(await summaries(resultsPage), expectedSummaries(SEASON_AFTER_FIX),
        'az 1. hétvége változik, a 2. és 3. nem');

    I.amOnPage('/');
    assert.deepStrictEqual(await standings(sidebarPage), SEASON_AFTER_FIX.standings);
    // az 1. hétvége javítása nem írja felül a "legutóbbi hétvége" dobogóját
    assert.deepStrictEqual((await sidebarPage.lastPodium()).title, SEASON_ROUNDS[2]);

    trophiesPage.open();
    assert.deepStrictEqual((await trophiesPage.seasonTable()).map(r => [r.name, r.point]), SEASON_AFTER_FIX.trophyTable);
});

Scenario('recalculating all points from the admin keeps the season unchanged', async ({I, adminPage, sidebarPage, resultsPage}) => {
    await I.seedScenario('season');
    await I.loginAs('e2e_admin');
    I.amOnPage('/admin/maintenance/re_calculate_points');
    adminPage.seeFlash('Sikeresen újraszámolva a pontok');

    I.amOnPage('/');
    assert.deepStrictEqual(await standings(sidebarPage), SEASON.standings);
    resultsPage.open();
    assert.deepStrictEqual(await summaries(resultsPage), expectedSummaries(SEASON));
});
