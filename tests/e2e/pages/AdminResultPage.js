const {I} = inject();

// EasyAdmin eredmény CRUD (src/Controller/Admin/ResultCrudController.php, src/Form/ResultType.php).
// Az EasyAdmin aláírt URL-eket használ, ezért a menün és a listán keresztül navigálunk.
module.exports = {
    openIndex() {
        I.amOnPage('/admin');
        I.click('a[href*="crudAction=index"][href*="ResultCrudController"]');
        I.waitForElement('.action-new');
    },

    openNew() {
        this.openIndex();
        I.click('.action-new');
        I.waitForElement('#result_submit');
    },

    openEdit(resultId) {
        this.openIndex();
        // a sor műveletei egy lenyíló menüben vannak
        I.click(`tr[data-id="${resultId}"] .dropdown-toggle`);
        I.click(`tr[data-id="${resultId}"] a.action-edit`);
        I.waitForElement('#result_submit');
    },

    // codes: p1..pN pilótakódok; sc: 'Igen' | 'Nem' a futam safety car kérdéséhez (a pozíciók után következik)
    fill(codes, sc) {
        codes.forEach((code, i) => I.selectOption(`select[name="result[attributes][${i}][value]"]`, code));
        if (sc !== undefined) {
            I.selectOption(`select[name="result[attributes][${codes.length}][value]"]`, sc);
        }
    },

    /** az űrlap a megadott eseményhez tartozik, és pontosan ennyi eredmény mezője (pozíciók + safety car) van */
    async seeEventOffered(eventId, fieldCount) {
        I.seeElementInDOM(`input[name="result[event]"][value="${eventId}"]`);
        const count = await I.grabNumberOfVisibleElements('select[name^="result[attributes]"]');
        if (count !== fieldCount) {
            throw new Error(`az eredmény űrlapnak ${fieldCount} mezője kellene legyen, de ${count} van`);
        }
    },

    /** mentés után a szerkesztő oldalra visz: az URL entityId paramétere a mentett eredmény id-ja */
    async currentResultId() {
        const url = new URL(await I.grabCurrentUrl());
        return Number(url.searchParams.get('entityId'));
    },

    save() {
        I.click('#result_submit');
    },

    // flash: "Eredmények kiszámolva" (új) vagy "Eredmények újraszámolva" (szerkesztés)
    seeSaved() {
        I.waitForElement(locate('.alert-success').withText('Eredmények'));
    },
};
