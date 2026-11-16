// A tesztadat egyetlen helyen. Egyezik a src/E2e/E2eFixtures.php és src/E2e/E2eScenarios.php
// seed adataival; a várt pontokat a design27 szabályaiból kézzel számoltuk (nem a kalkulátorból):
//   időmérő: pontos 4 / top10-ben 1, futam: pontos 3 / top10-ben 1, safety car 3,
//   alternatív (oldalsáv) helyezési pont: 14/10/8/7/6/4/3/2/1/0.
// Ha a 2026_rules branch bekerül (15/11/9/...), az ALT_POINTS-ot és az alt értékeket itt kell frissíteni.

const PASSWORD = 'e2e-pass';

const USERS = {
    e2e_user: {name: 'E2E User1'},
    e2e_user2: {name: 'E2E User2'},
    e2e_user3: {name: 'E2E User3'},
    e2e_admin: {name: 'E2E Admin'},
};

const WEEKEND_NAME = 'E2E Nagydíj';

const Q_RESULT = ['VER', 'NOR', 'LEC', 'PIA', 'HAM', 'RUS', 'SAI', 'ALO', 'GAS', 'ALB'];
const Q_SWAPPED = ['NOR', 'VER', 'LEC', 'PIA', 'HAM', 'RUS', 'SAI', 'ALO', 'GAS', 'ALB'];
const Q_OUTSIDERS = ['TSU', 'STR', 'OCO', 'HUL', 'VER', 'NOR', 'LEC', 'PIA', 'HAM', 'RUS'];

const BETS = {
    e2e_user: {qualify: Q_RESULT, race: Q_RESULT, sc: 'Nem'},
    e2e_user2: {qualify: Q_SWAPPED, race: Q_RESULT, sc: 'Igen'},
    e2e_user3: {qualify: Q_OUTSIDERS, race: Q_OUTSIDERS, sc: 'Igen'},
};

// eredmények: időmérő = Q_RESULT, futam = Q_RESULT + SC Igen
const EXPECTED = {
    e2e_user: {qualify: 40, race: 30, weekend: 70, alt: 14, lag: '', trophy: 'gold'},
    e2e_user2: {qualify: 34, race: 33, weekend: 67, alt: 10, lag: '+ 4', trophy: 'silver'},
    e2e_user3: {qualify: 6, race: 9, weekend: 15, alt: 8, lag: '+ 6', trophy: 'bronze'},
};

// javítás: az időmérő eredménye Q_SWAPPED lesz
const FIXED_QUALIFY_RESULT = Q_SWAPPED;
const EXPECTED_AFTER_FIX = {
    e2e_user: {qualify: 34, race: 30, weekend: 64, alt: 10, lag: '+ 4', trophy: 'silver'},
    e2e_user2: {qualify: 40, race: 33, weekend: 73, alt: 14, lag: '', trophy: 'gold'},
    e2e_user3: {qualify: 6, race: 9, weekend: 15, alt: 8, lag: '+ 6', trophy: 'bronze'},
};

// az oldalsáv TOP 3 rekordjai (pont szerint csoportosítva, csökkenő sorrendben)
const RECORD_QUALIFY = 'Időmérők Legjobbjai (TOP 3):';
const RECORD_WEEKEND = 'Hétvégék Legjobbjai (TOP 3):';
const EXPECTED_RECORDS = {
    [RECORD_QUALIFY]: [['E2E User1', 40], ['E2E User2', 34], ['E2E User3', 6]],
    [RECORD_WEEKEND]: [['E2E User1', 70], ['E2E User2', 67], ['E2E User3', 15]],
};
const EXPECTED_RECORDS_AFTER_FIX = {
    [RECORD_QUALIFY]: [['E2E User2', 40], ['E2E User1', 34], ['E2E User3', 6]],
    [RECORD_WEEKEND]: [['E2E User2', 73], ['E2E User1', 64], ['E2E User3', 15]],
};

// --- szezon közbeni késői javítás (src/E2e/E2eScenarios.php: season) ---
// 3 lezajlott hétvége (eredmény mindig: időmérő Q_RESULT, futam Q_RESULT + SC Igen), a 4. jövő héten.
//   1. hétvége: User1 Q/Q/Nem = 40+30 = 70, User2 QS/Q/Igen = 34+33 = 67, User3 QO/QO/Igen = 6+9 = 15
//   2. hétvége: User1 QS/Q/Nem = 34+30 = 64, User2 Q/Q/Igen = 40+33 = 73, User3 QO/QO/Igen = 15
//   3. hétvége: User1 QS/Q/Nem = 64,        User2 QO/QO/Nem = 6+6 = 12,  User3 Q/Q/Igen = 73
// A javítás az 1. hétvége időmérőjét Q_SWAPPED-re írja (két hétvégével később): User1 34+30 = 64, User2 40+33 = 73.
const SEASON_ROUNDS = ['E2E 1. Nagydíj', 'E2E 2. Nagydíj', 'E2E 3. Nagydíj'];
const SEASON = {
    // hétvégénként [gyűjtött, bajnoki pont]
    weekends: {
        e2e_user: [[70, 14], [64, 10], [64, 10]],
        e2e_user2: [[67, 10], [73, 14], [12, 8]],
        e2e_user3: [[15, 8], [15, 8], [73, 14]],
    },
    // oldalsáv: sorrend, pont, lemaradás, kupák darabra
    standings: [
        ['E2E User1', 34, '', {gold: 1, silver: 2}],
        ['E2E User2', 32, '+ 2', {gold: 1, silver: 1, bronze: 1}],
        ['E2E User3', 30, '+ 4', {gold: 1, bronze: 2}],
    ],
    // szezon kupatábla (arany 25, ezüst 18, bronz 15)
    trophyTable: [['E2E User1', 61], ['E2E User2', 58], ['E2E User3', 55]],
};
const SEASON_AFTER_FIX = {
    weekends: {
        e2e_user: [[64, 10], [64, 10], [64, 10]],
        e2e_user2: [[73, 14], [73, 14], [12, 8]],
        e2e_user3: [[15, 8], [15, 8], [73, 14]],
    },
    // User1 és User3 30-30 ponton: a több arany (User3: 1, User1: 0) dönt
    standings: [
        ['E2E User2', 36, '', {gold: 2, bronze: 1}],
        ['E2E User3', 30, '+ 6', {gold: 1, bronze: 2}],
        ['E2E User1', 30, '+ 6', {silver: 3}],
    ],
    trophyTable: [['E2E User2', 65], ['E2E User3', 55], ['E2E User1', 54]],
};

module.exports = {
    SEASON_ROUNDS, SEASON, SEASON_AFTER_FIX,
    RECORD_QUALIFY, RECORD_WEEKEND, EXPECTED_RECORDS, EXPECTED_RECORDS_AFTER_FIX,
    PASSWORD, USERS, WEEKEND_NAME, Q_RESULT, Q_SWAPPED, Q_OUTSIDERS, BETS,
    EXPECTED, FIXED_QUALIFY_RESULT, EXPECTED_AFTER_FIX,
};
