const assert = require('assert');
const {
    USERS, WEEKEND_NAME, Q_RESULT, Q_SWAPPED, RECORD_QUALIFY, RECORD_WEEKEND, TROPHY_POINTS,
    SPRINT_RESULT, SPRINT_FIXED_RESULT, SPRINT_BETS, SPRINT_EXPECTED, SPRINT_EXPECTED_AFTER_SPRINT_FIX,
    SPRINT_EXPECTED_AFTER_QUALIFY_FIX, SPRINT_EXPECTED_WITHOUT_SPRINT,
} = require('../data/expected');

// Sprint hétvége: 3 esemény egy hétvégén (sprint, időmérő, futam). A 2 eseményes hétvégét (időmérő + futam)
// a betting / scoring / result_fix / weekend_order specek fedik; itt a teljes flow fut végig 3 eseménnyel:
// tippleadás több userrel, eredmények eseményenként, hétvégi összesítő, bajnoki tabella, kupák, rekordok, javítás.
Feature('Sprint hétvége (3 esemény) @sprint');

const PLAYERS = ['e2e_user', 'e2e_user2', 'e2e_user3'];
const EVENTS = ['sprint', 'qualify', 'race'];
const TAGS = {sprint: 'SPRINT FUTAM', qualify: 'IDŐMÉRŐ', race: 'FUTAM'};
const TABS = {sprint: 'Sprint Futam', qualify: 'Időmérő', race: 'Futam'};
const SAVED = 'Tipped sikeresen rögzítve!';

const byDesc = (expected, key) => [...PLAYERS].sort((a, b) => expected[b][key] - expected[a][key]);
const summaryRows = expected => byDesc(expected, 'weekend').map(u => [USERS[u].name, expected[u].weekend, expected[u].alt]);
const standingRows = expected => byDesc(expected, 'alt').map(u => [USERS[u].name, expected[u].alt, expected[u].lag, [expected[u].trophy]]);
const recordRows = (expected, key) => byDesc(expected, key).map(u => [USERS[u].name, expected[u][key]]);
const podiumOrder = ['gold', 'silver', 'bronze'];

/** a user mindhárom eseményre tippel a Tippelek oldalon; mentés után marad, és fület vált */
const betOnWholeWeekend = (I, bettingPage, bets) => {
    bettingPage.open();
    for (const key of EVENTS) {
        bettingPage.selectTab(TABS[key]);
        bettingPage.fillPositions(bets[key]);
        if (key === 'race') {
            bettingPage.answerSafetyCar(bets.sc);
        }
        bettingPage.seeSubmitEnabled();
        bettingPage.submit();
        I.waitForText(SAVED);
        I.click('Maradok');
        bettingPage.seeLocked();
    }
};

const entityCounts = I => I.executeScript(() => fetch('/module/entity_counts').then(r => r.json()));

/** az új eredmény űrlap a következő eredmény nélküli eseményt kínálja; mentés után a szerkesztő oldalra visz */
const enterResult = async (I, adminResultPage, eventId, codes, sc) => {
    adminResultPage.openNew();
    await adminResultPage.seeEventOffered(eventId, codes.length + (sc === undefined ? 0 : 1));
    adminResultPage.fill(codes, sc);
    adminResultPage.save();
    adminResultPage.seeSaved();
    return adminResultPage.currentResultId();
};

const seeEventPoints = async (actualPage, key, expected, label) => {
    for (const user of PLAYERS) {
        assert.strictEqual(await actualPage.pointsOf(TAGS[key], USERS[user].name), expected[user][key],
            `${label}: ${user} ${TAGS[key]} pont`);
    }
};

/**
 * A lezárt sprint hétvége minden kimenete: eseménypontok és összesítő az Aktuális és az Eredmények oldalon,
 * oldalsáv tabella + rekordok + utolsó dobogó, Trófeák oldal.
 */
const seeWholeWeekend = async ({I, actualPage, resultsPage, sidebarPage, trophiesPage}, expected, label) => {
    actualPage.open();
    for (const key of EVENTS) {
        await seeEventPoints(actualPage, key, expected, label);
    }
    assert.deepStrictEqual(await actualPage.weekendSummary(), summaryRows(expected), `${label}: Aktuális hétvégi összesítő`);

    resultsPage.open();
    assert.deepStrictEqual(await resultsPage.weekendSummary(1), summaryRows(expected), `${label}: Eredmények hétvégi összesítő`);
    for (const key of EVENTS) {
        for (const user of PLAYERS) {
            assert.strictEqual(await resultsPage.eventPoints(1, TAGS[key], USERS[user].name), expected[user][key],
                `${label}: Eredmények ${user} ${TAGS[key]} pont`);
        }
    }

    I.amOnPage('/');
    assert.deepStrictEqual((await sidebarPage.standings()).map(r => [r.name, r.points, r.lag, r.trophies]),
        standingRows(expected), `${label}: VB tabella és kupák`);
    const records = await sidebarPage.records();
    assert.deepStrictEqual(records[RECORD_QUALIFY], recordRows(expected, 'qualify'), `${label}: időmérő rekordok`);
    assert.deepStrictEqual(records[RECORD_WEEKEND], recordRows(expected, 'weekend'), `${label}: hétvégi rekordok`);
    assert.deepStrictEqual(await sidebarPage.lastPodium(), {
        title: WEEKEND_NAME,
        rows: podiumOrder.map(trophy => {
            const user = PLAYERS.find(u => expected[u].trophy === trophy);
            return [trophy, USERS[user].name, expected[user].weekend];
        }),
    }, `${label}: utolsó hétvége dobogója`);

    trophiesPage.open();
    for (const user of PLAYERS) {
        assert.strictEqual(await trophiesPage.trophyOf(USERS[user].name), expected[user].trophy, `${label}: ${user} trófea`);
    }
    assert.deepStrictEqual(
        (await trophiesPage.seasonTable()).map(r => [r.name, r.point]),
        podiumOrder.map(trophy => [USERS[PLAYERS.find(u => expected[u].trophy === trophy)].name, TROPHY_POINTS[trophy]]),
        `${label}: szezon kupatábla`);

    const counts = await entityCounts(I);
    assert.strictEqual(counts.trophies, 3, `${label}: hétvégénként pontosan 3 kupa`);
};

Scenario('the three weekend events are offered in time order', async ({I, actualPage, bettingPage}) => {
    await I.seedScenario('sprint-open-weekend');
    await I.loginAs('e2e_user');

    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), ['SPRINT FUTAM', 'IDŐMÉRŐ', 'FUTAM']);

    bettingPage.open();
    assert.deepStrictEqual(await bettingPage.tabLabels(), ['Sprint Futam', 'Időmérő', 'Futam']);
    // a legközelebbi (sprint) fül az alapértelmezett
    I.seeElement(locate(bettingPage.tab('Sprint Futam')).withClassAttr('is-active'));
});

// A sprint tipp 8 helyezés, safety car kérdés nélkül; a hétvége eseményeire külön-külön kell tippelni.
Scenario('sprint bet has 8 positions and is saved separately from the other events', async ({I, bettingPage}) => {
    await I.seedScenario('sprint-open-weekend');
    await I.loginAs('e2e_user');
    bettingPage.open();

    bettingPage.selectTab('Sprint Futam');
    bettingPage.seeMissing(8);
    I.dontSeeElement(bettingPage.trigger(9));
    I.dontSeeElement('.f1-betting__choice');
    bettingPage.fillPositions(SPRINT_RESULT);
    bettingPage.seeSubmitEnabled();
    bettingPage.submit();
    I.waitForText(SAVED);
    I.click('Maradok');

    bettingPage.selectTab('Időmérő');
    bettingPage.seeMissing(10);
    bettingPage.seeEmpty(1);

    bettingPage.selectTab('Futam');
    bettingPage.seeMissing(11);

    bettingPage.selectTab('Sprint Futam');
    bettingPage.seeLocked();
    SPRINT_RESULT.forEach((code, i) => bettingPage.seeSelected(i + 1, code));
    // a rögzített tipp nem törölhető
    I.dontSeeElement('.f1-select__clear');
});

// A teljes flow: 3 user tippel mindhárom eseményre a felületen, az admin eseményenként rögzíti az eredményt,
// végül a sprint eredményét javítja. A sprint nélkül User1 nyerne, a sprinttel User2, a javítás után megint User1.
Scenario('full sprint weekend: bets of three players, results event by event, trophies, then a sprint result fix',
    async ({I, actualPage, bettingPage, adminResultPage, resultsPage, sidebarPage, trophiesPage}) => {
        const info = await I.seedScenario('sprint-open-weekend');

        // --- tippleadás: User1 mindhárom eseményre
        await I.loginAs('e2e_user');
        betOnWholeWeekend(I, bettingPage, SPRINT_BETS.e2e_user);

        // User2 a saját tippje előtt csak azt látja, hogy User1 tippelt a sprintre, a tippjét nem
        await I.loginAs('e2e_user2');
        actualPage.open();
        assert.deepStrictEqual(await actualPage.notesOf(TAGS.sprint), [`Tippesek: ${USERS.e2e_user.name}`]);
        assert.deepStrictEqual(await actualPage.tipRowNames(TAGS.sprint), []);
        betOnWholeWeekend(I, bettingPage, SPRINT_BETS.e2e_user2);
        actualPage.open();
        assert.deepStrictEqual(await actualPage.chipsOf(TAGS.sprint, USERS.e2e_user.name), SPRINT_BETS.e2e_user.sprint);

        await I.loginAs('e2e_user3');
        betOnWholeWeekend(I, bettingPage, SPRINT_BETS.e2e_user3);
        actualPage.open();
        for (const key of EVENTS) {
            assert.deepStrictEqual((await actualPage.tipRowNames(TAGS[key])).sort(),
                PLAYERS.map(u => USERS[u].name).sort(), `${TAGS[key]}: mindhárom tipp látszik`);
        }
        assert.deepStrictEqual(await actualPage.chipsOf(TAGS.sprint, USERS.e2e_user3.name), SPRINT_BETS.e2e_user3.sprint);

        // --- eredmények időrendben: sprint, időmérő, futam
        await I.loginAs('e2e_admin');
        const sprintResultId = await enterResult(I, adminResultPage, info.events.sprint_qualify.id, SPRINT_RESULT);

        actualPage.open();
        await seeEventPoints(actualPage, 'sprint', SPRINT_EXPECTED, 'sprint után');
        // a sprintben nincs "jó pilóta, rossz hely" pont: csak a pontos helyek színeződnek
        const user1Sprint = await actualPage.chipClassesOf(TAGS.sprint, USERS.e2e_user.name);
        user1Sprint.forEach((cls, i) => assert.ok(i < 4
            ? !cls.includes('f1-chip--exact') && !cls.includes('f1-chip--near')
            : cls.includes('f1-chip--exact'), `e2e_user sprint p${i + 1}: ${cls}`));
        const user3Sprint = await actualPage.chipClassesOf(TAGS.sprint, USERS.e2e_user3.name);
        user3Sprint.forEach((cls, i) => assert.ok(!cls.includes('f1-chip--exact') && !cls.includes('f1-chip--near'),
            `e2e_user3 sprint p${i + 1}: ${cls}`));
        // a futam előtt még nincs hétvégi összesítő és kupa
        I.dontSeeElement('#main-component .f1-board--summary');
        assert.strictEqual((await entityCounts(I)).trophies, 0, 'sprint után még nincs kupa');

        await enterResult(I, adminResultPage, info.events.qualify.id, Q_RESULT);
        actualPage.open();
        await seeEventPoints(actualPage, 'qualify', SPRINT_EXPECTED, 'időmérő után');
        await seeEventPoints(actualPage, 'sprint', SPRINT_EXPECTED, 'időmérő után');
        I.dontSeeElement('#main-component .f1-board--summary');
        assert.strictEqual((await entityCounts(I)).trophies, 0, 'időmérő után még nincs kupa');

        await enterResult(I, adminResultPage, info.events.race.id, Q_RESULT, 'Igen');
        await seeWholeWeekend({I, actualPage, resultsPage, sidebarPage, trophiesPage}, SPRINT_EXPECTED, 'futam után');

        // --- a sprint eredmény javítása: a hétvége, a tabella, a kupák és a rekordok újraszámolódnak
        adminResultPage.openEdit(sprintResultId);
        adminResultPage.fill(SPRINT_FIXED_RESULT);
        adminResultPage.save();
        adminResultPage.seeSaved();
        await seeWholeWeekend({I, actualPage, resultsPage, sidebarPage, trophiesPage}, SPRINT_EXPECTED_AFTER_SPRINT_FIX, 'sprint javítás után');
    });

Scenario('after the race result the weekend summary comes first, then the events nearest first', async ({I, actualPage}) => {
    await I.seedScenario('sprint-results-entered');
    actualPage.open();
    assert.deepStrictEqual(await actualPage.boardTags(), ['HÉTVÉGE', 'FUTAM', 'IDŐMÉRŐ', 'SPRINT FUTAM']);
    assert.deepStrictEqual(await actualPage.weekendSummary(), summaryRows(SPRINT_EXPECTED));
});

// Az időmérő javítása csak az időmérő pontjait számolja újra: a sprint pontjai megmaradnak, és a hétvégébe is beszámítanak.
Scenario('correcting the qualifying result keeps the sprint points in the weekend',
    async ({I, actualPage, adminResultPage, resultsPage, sidebarPage, trophiesPage}) => {
        const info = await I.seedScenario('sprint-results-entered');
        await I.loginAs('e2e_admin');
        adminResultPage.openEdit(info.results.qualify);
        adminResultPage.fill(Q_SWAPPED);
        adminResultPage.save();
        adminResultPage.seeSaved();

        await seeWholeWeekend({I, actualPage, resultsPage, sidebarPage, trophiesPage}, SPRINT_EXPECTED_AFTER_QUALIFY_FIX, 'időmérő javítás után');
    });

// Tévesen rögzített sprint eredmény törlése: a hétvége csak az időmérő és a futam alapján számol – User1 nyer.
Scenario('deleting the sprint result recalculates the weekend without the sprint',
    async ({I, actualPage, adminPage, resultsPage, sidebarPage, trophiesPage}) => {
        const info = await I.seedScenario('sprint-results-entered');
        await I.loginAs('e2e_admin');
        adminPage.deleteRow('Result', info.results.sprint_qualify);

        actualPage.open();
        // a sprint tippek látszanak (lejárt a határidő), de pont és jelölés nélkül
        const classes = await actualPage.chipClassesOf(TAGS.sprint, USERS.e2e_user2.name);
        assert.ok(classes.every(c => !c.includes('f1-chip--exact') && !c.includes('f1-chip--near')), classes.join(' | '));
        I.dontSeeElement(locate(actualPage.row(TAGS.sprint, USERS.e2e_user2.name))
            .withDescendant('.//*[contains(@class,"f1-tiprow__pts")]'));
        for (const key of ['qualify', 'race']) {
            await seeEventPoints(actualPage, key, SPRINT_EXPECTED_WITHOUT_SPRINT, 'sprint törlés után');
        }
        assert.deepStrictEqual(await actualPage.weekendSummary(), summaryRows(SPRINT_EXPECTED_WITHOUT_SPRINT));

        resultsPage.open();
        assert.deepStrictEqual(await resultsPage.weekendSummary(1), summaryRows(SPRINT_EXPECTED_WITHOUT_SPRINT));

        I.amOnPage('/');
        assert.deepStrictEqual((await sidebarPage.standings()).map(r => [r.name, r.points, r.lag, r.trophies]),
            standingRows(SPRINT_EXPECTED_WITHOUT_SPRINT));

        trophiesPage.open();
        for (const user of PLAYERS) {
            assert.strictEqual(await trophiesPage.trophyOf(USERS[user].name), SPRINT_EXPECTED_WITHOUT_SPRINT[user].trophy, `${user} trófea`);
        }
    });
