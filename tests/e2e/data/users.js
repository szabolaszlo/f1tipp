// A seedelt felhasználók (src/E2e/E2eFixtures.php: USERS, PASSWORD_HASH). A feature fájlok a megjelenített
// nevükkel hivatkoznak rájuk ("E2E User1"), a belépéshez a felhasználónév kell.
// A tesztadat (tippek, eredmények) és a várt pontok a features/*.feature fájlokban vannak.

const PASSWORD = 'e2e-pass';

/** felhasználónév => megjelenített név, a seed sorrendjében (id 1-től) */
const USERS = {
    e2e_user: {name: 'E2E User1'},
    e2e_user2: {name: 'E2E User2'},
    e2e_user3: {name: 'E2E User3'},
    e2e_admin: {name: 'E2E Admin'},
    e2e_superadmin: {name: 'E2E Superadmin'},
};

module.exports = {PASSWORD, USERS};
