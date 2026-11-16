const {I} = inject();

// EasyAdmin általános CRUD lépések (src/Controller/Admin/*CrudController.php).
// Az EasyAdmin 4.29 URL-jei nincsenek aláírva, így a CRUD oldalak közvetlenül megnyithatók.
const crudUrl = (crud, action, entityId) => '/admin?crudAction=' + action
    + '&crudControllerFqcn=App%5CController%5CAdmin%5C' + crud + 'CrudController'
    + (entityId !== undefined ? '&entityId=' + entityId : '');

module.exports = {
    crudUrl,

    openIndex(crud) {
        I.amOnPage(crudUrl(crud, 'index'));
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
    deleteRow(crud, entityId) {
        this.openIndex(crud);
        I.click(`tr[data-id="${entityId}"] .dropdown-toggle`);
        I.click(`tr[data-id="${entityId}"] a.action-delete`);
        I.waitForElement('#modal-delete-button', 5);
        I.click('#modal-delete-button');
        I.waitForElement('table.datagrid, .datagrid-empty, .no-results');
        I.dontSeeElement(`tr[data-id="${entityId}"]`);
    },

    seeFlash(text) {
        I.waitForElement(locate('.alert-success').withText(text));
    },
};
