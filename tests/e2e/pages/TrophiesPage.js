const {I} = inject();

// Trófeák oldal (/#/trophies, templates/controller/module/season_trophies.html.twig)
module.exports = {
    open() {
        I.amOnPage('/#/trophies');
        I.waitForElement('#main-component table');
    },

    /** @returns {Promise<string|null>} 'gold' | 'silver' | 'bronze' – a user első kupája */
    async trophyOf(userName) {
        return I.executeScript(name => {
            const row = Array.from(document.querySelectorAll('#main-component tr'))
                .find(tr => Array.from(tr.querySelectorAll('strong')).some(s => s.textContent.trim() === name));
            if (!row) {
                return null;
            }
            const img = row.querySelector('img[src*="trophy_"]');
            const match = img && img.getAttribute('src').match(/trophy_(gold|silver|bronze)/);
            return match ? match[1] : null;
        }, userName);
    },
};
