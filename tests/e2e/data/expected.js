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

module.exports = {
    PASSWORD, USERS, WEEKEND_NAME, Q_RESULT, Q_SWAPPED, Q_OUTSIDERS, BETS,
    EXPECTED, FIXED_QUALIFY_RESULT, EXPECTED_AFTER_FIX,
};
