const assert = require('assert');
const {ctx, seed} = require('./context');

// Szezonváltás: kiinduló állapotok (src/E2e/E2eScenarios.php) és az adatbázis állapotának összevetése
const {I} = inject();

// --- kiinduló állapotok

Given('a tavalyi szezon lezárult, az archív bejegyzés megvan', () => seed('previous-season'));
Given('a tavalyi szezon lezárult, de nincs archív bejegyzés', () => seed('previous-season-no-archive'));
Given('a tavalyi szezon lezárult, de egy eredményes eseményt már jövőbe tettek', () => seed('previous-season-moved'));
Given('idei eredmény van egy lezajlott eseményen', () => seed('current-season'));

// --- az adatbázis állapota

When('megjegyzi az adatbázis állapotát', async () => {
    ctx.dbBefore = await I.dbCounts();
});

Then('az adatbázis nem változott', async () => {
    assert.deepStrictEqual(await I.dbCounts(), ctx.dbBefore);
});
