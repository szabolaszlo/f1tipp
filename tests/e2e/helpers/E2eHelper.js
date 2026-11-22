const Helper = require('@codeceptjs/helper');
const {execFileSync} = require('child_process');
const path = require('path');
const {PASSWORD} = require('../data/users');

const REPO_ROOT = path.resolve(__dirname, '../../..');
const CHART_JS_STUB = 'window.Chart = function Chart() { this.destroy = function () {}; this.update = function () {}; }; window.Chart.defaults = {};';
const CONSOLE = ['compose', 'exec', '-T', '-u', 'www-data', '-w', '/var/www/f1tipp_sf4', 'php', 'php', 'bin/console'];

/**
 * E2E segédlépések: adatbázis seedelése a Symfony e2e parancsokkal, gyors belépés,
 * külső kérések blokkolása és a böngészőhibák gyűjtése.
 */
class E2eHelper extends Helper {
    constructor(config) {
        super(config);
        this.baseUrl = new URL(config.baseUrl);
        this.problems = [];
    }

    get pw() {
        return this.helpers.Playwright;
    }

    async _before() {
        this.problems = [];
        const context = this.pw.browserContext;

        // csak az e2e app hostja érhető el: Google fontok/ikon, gtag, rapidapi stb. tiltva.
        // Kivétel: a CDN-ről töltött Chart.js (pontgrafikon) helyett csonk, hogy ne legyen "Chart is not defined".
        await context.route('**/*', route => {
            const url = new URL(route.request().url());
            if (url.host === this.baseUrl.host) {
                return route.continue();
            }
            if (url.host === 'cdn.jsdelivr.net' && url.pathname.startsWith('/npm/chart.js')) {
                return route.fulfill({contentType: 'application/javascript', body: CHART_JS_STUB});
            }
            return route.abort();
        });

        const watch = page => {
            page.on('console', msg => {
                if (msg.type() === 'error' && !this._isExpectedNoise(msg.text(), (msg.location() || {}).url)) {
                    this.problems.push(`console.error: ${msg.text()}`);
                }
            });
            page.on('pageerror', err => this.problems.push(`pageerror: ${err.message}`));
            page.on('response', response => {
                const url = new URL(response.url());
                if (url.host === this.baseUrl.host && response.status() >= 500) {
                    this.problems.push(`HTTP ${response.status()}: ${response.url()}`);
                }
            });
        };
        context.pages().forEach(watch);
        context.on('page', watch);
    }

    // "Failed to load resource" zaj, ami nem hiba: a szándékosan blokkolt külső kérések (net::ERR_FAILED),
    // és a vendégként várt 401 az /is_logged-ra (Navigation.vue így dönti el, ki van-e lépve).
    // Minden más 4xx/5xx saját erőforrás hibának számít.
    _isExpectedNoise(text, url) {
        if (text.includes('net::ERR_FAILED') || text.includes('ERR_BLOCKED')) {
            return true;
        }
        if (!text.includes('Failed to load resource') || !url) {
            return false;
        }
        const {host, pathname} = new URL(url);
        return host === this.baseUrl.host
            && pathname === '/is_logged'
            && text.includes('status of 401');
    }

    _console(args) {
        const out = execFileSync('docker', [...CONSOLE, ...args, '--env=e2e'], {
            cwd: REPO_ROOT,
            encoding: 'utf8',
            stdio: ['ignore', 'pipe', 'ignore'],
            timeout: 120000,
        });
        const lines = out.trim().split('\n');
        return JSON.parse(lines[lines.length - 1]);
    }

    /**
     * Adatbázis nulláról + nevesített szenárió (lásd App\E2e\E2eScenarios).
     * @returns {{serverNow: number, events: Object<string, {id: number, deadline: number}>, results?: Object<string, number>}}
     */
    async seedScenario(name, opts = {}) {
        const args = ['app:e2e:scenario', name];
        if (opts.deadlineIn) {
            args.push(`--deadline-in=${opts.deadlineIn}`);
        }
        this.lastScenario = this._console(args);
        this.lastScenario.hostNow = Math.floor(Date.now() / 1000);
        return this.lastScenario;
    }

    async resetDb() {
        this._console(['app:e2e:reset']);
    }

    /**
     * Belépés a /login JSON végponton (a Login UI-t a login_test nyomkodja végig).
     *
     * Felhasználóváltás előtt elnavigálunk a nyitott oldalról: a pollingjai (app.js: /get_online_user,
     * module_reload.js) különben a régi session cookie-val és a régi REMEMBERME cookie-val futhatnak a
     * login alatt. A session-zár miatt ezek a login után futnak le, a migrált (már nem létező) régi session
     * helyett a remember-me visszalépteti a régi usert, és a válasz Set-Cookie-ja felülírja az új belépést.
     * Az about:blank-re navigálás leállítja az oldal JS-ét, és a még úton lévő kérések válaszát eldobatja.
     */
    async loginAs(username) {
        await this.pw.page.goto('about:blank');
        const response = await this.pw.page.request.post(new URL('/login', this.baseUrl).href, {data: {username, password: PASSWORD}});
        if (!response.ok()) {
            throw new Error(`loginAs(${username}) failed: HTTP ${response.status()}`);
        }
        await this.pw.amOnPage('/');
    }

    /**
     * Megvárja, hogy a szerver órája biztosan túllépje az epoch-ot. A host és a konténer órája közti
     * eltérést a seedeléskori serverNow / hostNow párból korrigálja.
     */
    async waitUntilServerTime(epoch, serverNow) {
        const scenario = this.lastScenario || {};
        const skew = (scenario.hostNow !== undefined && scenario.serverNow === serverNow)
            ? scenario.hostNow - serverNow
            : 0;
        const targetHostMs = (epoch + 1 + skew) * 1000;
        const waitMs = targetHostMs - Date.now();
        if (waitMs > 0) {
            await new Promise(resolve => setTimeout(resolve, waitMs));
        }
    }

    async seeNoConsoleErrors() {
        if (this.problems.length) {
            throw new Error(`Browser problems:\n  ${this.problems.join('\n  ')}`);
        }
    }
}

module.exports = E2eHelper;
