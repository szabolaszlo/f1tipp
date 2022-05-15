export default class CalendarEventType {
    id
    translatedName
    state

    constructor(id, translatedName, state) {
        this.id = id;
        this.translatedName = translatedName;
        this.state = state;
    }
}
