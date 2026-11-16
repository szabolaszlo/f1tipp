const {I} = inject();

// Belépés oldal (assets/js/components/Login.vue)
module.exports = {
    open() {
        I.amOnPage('/#/login');
        I.waitForElement('#signin');
    },

    login(username, password) {
        I.fillField('#text', username);
        I.fillField('#password', password);
        I.click('#login-submit');
    },

    // a hibaüzenet a form feletti piros <strong>-ban jelenik meg (pl. "Invalid credentials.")
    seeError() {
        I.waitForFunction(() => {
            const el = document.querySelector('strong.text-red');
            return el && el.textContent.trim().length > 0;
        });
    },
};
