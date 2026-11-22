/**
 * Időzített frissítés, ami csak látható lapon fut.
 *
 * Háttérbe tett lapon (másik fül, lezárt mobil, háttérbe tett böngésző-app) a böngészők
 * lassítják vagy teljesen leállítják a timereket (iOS Safari: semmi nem fut; Chrome: legfeljebb
 * percenként egyszer, később még ritkábban), így a setInterval-ra nem lehet építeni. Ezért:
 * - rejtett lapon leállítjuk a timert (nem is kérdezgetjük feleslegesen a szervert),
 * - amikor a lap újra látható lesz (visibilitychange, illetve bfcache-ből visszaállított lapnál
 *   pageshow), és az utolsó frissítés óta eltelt legalább egy intervallum, azonnal frissítünk,
 * - ha a hálózat visszajön (online), szintén frissítünk; mobilon ébredés után az első kérés
 *   gyakran még kapcsolat nélkül megy ki.
 *
 * @param {Function} callback
 * @param {number} intervalMs
 * @returns {Function} leiratkozás (timer és eseményfigyelők törlése)
 */
export default function visibleInterval(callback, intervalMs) {
    let timer = null;
    let lastRun = Date.now();

    const isVisible = () => document.visibilityState !== 'hidden';

    const run = () => {
        lastRun = Date.now();
        callback();
    };

    const stop = () => {
        if (timer !== null) {
            clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        stop();
        timer = setInterval(run, intervalMs);
    };

    const onVisibilityChange = () => {
        if (!isVisible()) {
            stop();
            return;
        }
        if (Date.now() - lastRun >= intervalMs) {
            run();
        }
        start();
    };

    const onPageShow = (event) => {
        if (event.persisted) {
            onVisibilityChange();
        }
    };

    const onOnline = () => {
        if (isVisible()) {
            run();
            start();
        }
    };

    document.addEventListener('visibilitychange', onVisibilityChange);
    window.addEventListener('pageshow', onPageShow);
    window.addEventListener('online', onOnline);

    if (isVisible()) {
        start();
    }

    return () => {
        stop();
        document.removeEventListener('visibilitychange', onVisibilityChange);
        window.removeEventListener('pageshow', onPageShow);
        window.removeEventListener('online', onOnline);
    };
}
