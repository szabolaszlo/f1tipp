const {event} = require('codeceptjs');
const {USERS} = require('../data/users');

// A step definíciók közös állapota és segédfüggvényei. Nem step fájl: a *_steps.js fájlok húzzák be.
const {I} = inject();

/**
 * A forgatókönyv állapota: a seed válasza (esemény és eredmény id-k), ki van belépve (API-s belépésnél
 * nem lépünk be újra ugyanazzal a userrel), és az utolsó kézi kérés válasza. Minden forgatókönyv előtt nullázódik.
 */
const ctx = {};
const reset = () => {
    Object.keys(ctx).forEach(k => delete ctx[k]);
    Object.assign(ctx, {events: {}, results: {}, loggedIn: null, response: null});
};
reset();
event.dispatcher.on(event.test.before, reset);

// a Tippelek fül neve -> az Aktuális oldal táblacímkéje és a seed (src/E2e/E2eScenarios.php) eseménykulcsa
const EVENTS = {
    'Sprint Futam': {tag: 'SPRINT FUTAM', key: 'sprint_qualify'},
    'Időmérő': {tag: 'IDŐMÉRŐ', key: 'qualify'},
    'Futam': {tag: 'FUTAM', key: 'race'},
};
const TROPHIES = {arany: 'gold', ezüst: 'silver', bronz: 'bronze'};

// pilóta id-k a seed sorrendjében (src/E2e/E2eFixtures.php: DRIVERS)
const DRIVER_IDS = ['VER', 'NOR', 'LEC', 'PIA', 'HAM', 'RUS', 'SAI', 'ALO', 'GAS', 'ALB', 'TSU', 'STR', 'OCO', 'HUL']
    .reduce((ids, code, i) => ({...ids, [code]: i + 1}), {});

const eventOf = label => {
    if (!EVENTS[label]) {
        throw new Error(`ismeretlen esemény: "${label}" (lehet: ${Object.keys(EVENTS).join(', ')})`);
    }
    return EVENTS[label];
};
const codes = text => text.trim().split(/\s+/);
const list = text => (text.trim() ? text.split(',').map(s => s.trim()) : []);
const rowsOf = table => table.parse().hashes();
const usernameOf = name => {
    const username = Object.keys(USERS).find(u => USERS[u].name === name);
    if (!username) {
        throw new Error(`ismeretlen felhasználó: "${name}" (lehet: ${Object.values(USERS).map(u => u.name).join(', ')})`);
    }
    return username;
};
// felhasználó id-k a seed sorrendjében (src/E2e/E2eFixtures.php: USERS)
const userIdOf = name => Object.keys(USERS).indexOf(usernameOf(name)) + 1;

/** belépés a /login JSON végponton; ha már ő van belépve, nem lép be újra */
const loginAs = async name => {
    if (ctx.loggedIn !== name) {
        await I.loginAs(usernameOf(name));
        ctx.loggedIn = name;
    }
};

const seed = async (scenario, opts) => {
    const info = await I.seedScenario(scenario, opts);
    ctx.serverNow = info.serverNow;
    ctx.year = info.year;
    ctx.events = info.events;
    ctx.results = {...(info.results || {})};
    ctx.loggedIn = null;
    return info;
};

/**
 * a seed szerinti "most" éve, ahogy a PHP látja (a seed adja vissza; a host időzónája szilveszterkor mást adhatna),
 * eltolva: yearOf(-1) = tavaly
 */
const yearOf = (offset = 0) => {
    if (!Number.isInteger(ctx.year)) {
        throw new Error('a seed nem adta vissza az évet (year)');
    }
    return ctx.year + offset;
};

/** az új szezon éve: az események legkésőbbi dátumának éve (a SeasonFactsProvider currentYear-je) */
const currentSeasonYear = async () => {
    const {events} = await I.dbCounts();
    return Number(Object.values(events).sort().pop().slice(0, 4));
};

const entityCounts = () => I.executeScript(() => fetch('/module/entity_counts').then(r => r.json()));

module.exports = {ctx, EVENTS, TROPHIES, DRIVER_IDS, eventOf, codes, list, rowsOf, usernameOf, userIdOf, loginAs, seed, yearOf, currentSeasonYear, entityCounts};
