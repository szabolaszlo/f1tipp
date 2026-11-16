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
        I.click(`a.action-edit[href*="entityId=${resultId}&"]`);
        I.waitForElement('#result_submit');
    },

    // codes: p1..pN pilótakódok; sc: 'Igen' | 'Nem' a futam safety car kérdéséhez (a pozíciók után következik)
    fill(codes, sc) {
        codes.forEach((code, i) => I.selectOption(`select[name="result[attributes][${i}][value]"]`, code));
        if (sc !== undefined) {
            I.selectOption(`select[name="result[attributes][${codes.length}][value]"]`, sc);
        }
    },

    save() {
        I.click('#result_submit');
    },

    // flash: "Eredmények kiszámolva" (új) vagy "Eredmények újraszámolva" (szerkesztés)
    seeSaved() {
        I.waitForElement(locate('.alert-success').withText('Eredmények'));
    },
};
