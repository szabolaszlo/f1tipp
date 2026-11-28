const {I} = inject();

// EasyAdmin általános CRUD lépések (src/Controller/Admin/*CrudController.php).
// Az EasyAdmin 4.29 URL-jei nincsenek aláírva, így a CRUD oldalak közvetlenül megnyithatók.
const crudUrl = (crud, action, entityId) => '/admin?crudAction=' + action
    + '&crudControllerFqcn=App%5CController%5CAdmin%5C' + crud + 'CrudController'
    + (entityId !== undefined ? '&entityId=' + entityId : '');

module.exports = {
    crudUrl,

    /** query: a lista keresőmezőjének szövege (lapozás nélkül megtalálni a sort) */
    openIndex(crud, query) {
        I.amOnPage(crudUrl(crud, 'index') + (query ? '&query=' + encodeURIComponent(query) : ''));
        I.waitForElement('table.datagrid, .datagrid-empty, .no-results');
    },

    openNew(crud) {
        I.amOnPage(crudUrl(crud, 'new'));
        I.waitForElement(`form#new-${crud}-form`);
    },

    openEdit(crud, entityId) {
        I.amOnPage(crudUrl(crud, 'edit', entityId));
        I.waitForElement(`form#edit-${crud}-form`);
    },

    /** "Mentés" (vissza a listára) – a listára érve a tábla megjelenését várjuk */
    saveAndReturn() {
        I.click('.action-saveAndReturn');
        I.waitForElement('table.datagrid');
    },

    /** index nézetből törlés a sor lenyíló menüjén és a megerősítő ablakon át */
    deleteRow(crud, entityId, query) {
        this.openIndex(crud, query);
        I.click(`tr[data-id="${entityId}"] .dropdown-toggle`);
        I.click(`tr[data-id="${entityId}"] a.action-delete`);
        I.waitForElement('#modal-action-confirmation-button', 5);
        I.click('#modal-action-confirmation-button');
        I.waitForElement('table.datagrid, .datagrid-empty, .no-results');
        I.dontSeeElement(`tr[data-id="${entityId}"]`);
    },

    /** a datetime-local mezőbe gépelés a böngésző dátumformátuma miatt szétesik: az értéket közvetlenül állítjuk */
    setDateTime(selector, value) {
        I.executeScript(({selector, value}) => {
            document.querySelector(selector).value = value;
        }, {selector, value});
    },

    seeFlash(text) {
        I.waitForElement(locate('.alert-success').withText(text));
    },
};
