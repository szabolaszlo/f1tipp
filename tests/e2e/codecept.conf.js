// F1Tipp E2E tesztek – az e2e Symfony környezetet (külön f1tipp_e2e adatbázis) nyomkodják.
// Futtatás: a repó gyökeréből `npm run e2e`, vagy itt `npm test` / `npm run test:headed`.
const BASE_URL = process.env.E2E_BASE_URL || 'http://127.0.0.1:8001';

exports.config = {
    // minden teszt magyar nyelvű Gherkin forgatókönyv (features/), a lépések a step_definitions/ alatt
    gherkin: {
        features: './features/*.feature',
        steps: [
            './step_definitions/common_steps.js',
            './step_definitions/betting_steps.js',
            './step_definitions/actual_steps.js',
            './step_definitions/standings_steps.js',
            './step_definitions/admin_steps.js',
            './step_definitions/pages_steps.js',
            './step_definitions/feed_steps.js',
        ],
    },
    output: './output',
    helpers: {
        Playwright: {
            url: BASE_URL,
            browser: 'chromium',
            show: !!process.env.E2E_SHOW,
            restart: 'context',
            waitForTimeout: 10000,
            waitForNavigation: 'load',
            locale: 'hu-HU',
            windowSize: '1280x900',
        },
        E2eHelper: {
            require: './helpers/E2eHelper.js',
            baseUrl: BASE_URL,
        },
    },
    include: {
        I: './steps_file.js',
        loginPage: './pages/LoginPage.js',
        navigationPage: './pages/NavigationPage.js',
        bettingPage: './pages/BettingPage.js',
        actualPage: './pages/ActualPage.js',
        adminResultPage: './pages/AdminResultPage.js',
        sidebarPage: './pages/SidebarPage.js',
        trophiesPage: './pages/TrophiesPage.js',
        resultsPage: './pages/ResultsPage.js',
        adminPage: './pages/AdminPage.js',
    },
    plugins: {
        screenshotOnFail: {enabled: true},
    },
    name: 'f1tipp-e2e',
};
