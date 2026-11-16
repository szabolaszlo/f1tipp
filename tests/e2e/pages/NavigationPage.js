const {I} = inject();

// Fejléc / felhasználó blokk (assets/js/components/Navigation.vue)
module.exports = {
    seeLoggedInAs(username) {
        I.waitForElement(locate('.f1-user__name').withText(username));
    },

    seeLoggedOut() {
        I.waitForElement('.f1-user__login');
        I.dontSeeElement('.f1-user__name');
    },

    logout() {
        I.click('.f1-user__logout');
    },
};
