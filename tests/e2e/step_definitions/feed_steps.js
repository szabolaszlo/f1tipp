const assert = require('assert');
const {ctx} = require('./context');

// Hírgyűjtés (/cron/collect_feeds) és a főoldali hírek képe, bevezetője. A feed és a WordPress REST API
// e2e-ben fájlból jön (src/E2e/E2eFeed.php, E2eFeedFetcher). A külső képek betöltését a helper blokkolja,
// ezért a kép src attribútumát nézzük.
const {I} = inject();

// élesben a Nethely cronja hívja 15 percenként; itt a böngészővel nyitjuk meg, mint egy kézi futtatásnál
When('lefut a hírgyűjtés', async () => {
    I.amOnPage('/cron/collect_feeds');
    ctx.feedResponse = (await I.grabTextFrom('body')).trim();
});

Then('a hírgyűjtés válasza: {string}', expected => assert.strictEqual(ctx.feedResponse, expected));

// a hírek lustán töltődnek (News.vue: IntersectionObserver), ezért előbb oda kell görgetni
const newsItem = async title => {
    I.waitForElement('.f1-news', 10);
    I.scrollTo('.f1-news');
    I.waitForElement(locate('.f1-news__title').withText(title), 10);
    const item = await I.executeScript(title => {
        const article = Array.from(document.querySelectorAll('.f1-news__item'))
            .find(a => a.querySelector('.f1-news__title').textContent.trim() === title);
        if (!article) {
            return null;
        }
        const img = article.querySelector('img.f1-news__img');
        return {
            image: img ? img.getAttribute('src') : null,
            placeholder: !!article.querySelector('div.f1-news__img'),
            lead: article.querySelector('.f1-news__desc').textContent.trim(),
        };
    }, title);
    assert.ok(item, `nincs ilyen hír: "${title}"`);
    return item;
};

Then('a/az {string} hír képe: {string}', async (title, image) => {
    assert.strictEqual((await newsItem(title)).image, image);
});

Then('a/az {string} hír kép nélkül jelenik meg', async title => {
    const item = await newsItem(title);
    assert.strictEqual(item.image, null);
    assert.ok(item.placeholder, 'a kép helyén nincs helykitöltő');
});

Then('a/az {string} hír bevezetője: {string}', async (title, lead) => {
    assert.strictEqual((await newsItem(title)).lead, lead);
});
