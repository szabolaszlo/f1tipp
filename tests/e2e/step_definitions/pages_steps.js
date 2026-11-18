const assert = require('assert');

// Statisztika oldal, főoldali hírek és a csak tagoknak látszó modulok.
const {I} = inject();

// a főoldal a belépett usernek több modult mutat: üzenőfal, nyeremény, pontszámítás
const MEMBER_ONLY = () => ['#message-input', locate('.f1-board__tag').withText('Nyeremény'), locate('.f1-board__tag').withText('Pontok')];

Then('látszik a hír: {string}', title => I.waitForElement(locate('.f1-news__title').withText(title), 10));

Then('nem látszanak a csak tagoknak szóló modulok', () => MEMBER_ONLY().forEach(el => I.dontSeeElement(el)));

Then('látszanak a csak tagoknak szóló modulok', () => MEMBER_ONLY().forEach(el => I.waitForElement(el, 10)));

When('megnyitja a Statisztika oldalt', () => {
    I.amOnPage('/#/statistics');
    I.waitForElement(locate('#main-component .panel-heading').withText('Statisztikája'), 15);
});

// Statisztika: pozíciónként hány %-ban tippelték / lett az adott pilóta (src/LegacyService/Statistics).
// táblázat: pilóta | arány
Then('a/az {string} panel {string} sora:', async (panelTitle, position, table) => {
    const row = await I.executeScript(({panelTitle, position}) => {
        const panel = Array.from(document.querySelectorAll('#main-component .panel'))
            .find(p => p.querySelector('.panel-heading').textContent.trim() === panelTitle);
        if (!panel) {
            return null;
        }
        const tr = Array.from(panel.querySelectorAll('tr'))
            .find(r => r.querySelector('th strong').textContent.trim() === position);
        return Object.fromEntries(Array.from(tr.querySelectorAll('th'))
            .filter(th => th.querySelector('.color-one'))
            .map(th => [th.querySelector('.color-one').textContent.trim(), th.querySelector('.color-two').textContent.trim()]));
    }, {panelTitle, position});
    assert.deepStrictEqual(row, Object.fromEntries(table.parse().raw()));
});
