const calendar = () => {
    return {
        calendar: null,
        config: {},
        id: null,
        calendarId: null,
        calendars: [],
        activeCalendars: [],
        calendarEvent: {},
        dispatchCalendarEvents(eventName, params) {
            const eventNameKebap = eventName.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase();
            this.$dispatch(`calendar-${eventNameKebap}`, params);
        },
        getCalendarEventSources() {
            this.calendars.forEach((calendar) => {
                if (this.calendarId === null) {
                    this.calendarId = calendar.id;
                }
               calendar.events = (info) => this.$wire.getEvents(info, calendar.id);
               this.activeCalendars.push(calendar.id);
            });

            return this.calendars;
        },
        toggleEventSource(calendar) {
            const calendarEventSource = this.calendar.getEventSourceById(calendar.id);
            if (calendarEventSource) {
                calendarEventSource.remove();
            } else {
                this.calendar.addEventSource(calendar);
            }
        },
        init() {
            this.id = this.$id('calendar');
            this.$wire.getCalendars().then((calendars) => {
                this.calendars = calendars;
            });
            this.$wire.getConfig().then((config) => {
                this.config = config;
                this.initCalendar();
            })
        },
        initCalendar() {
            let calendarEl = document.getElementById(this.id);

            let defaultConfig = {
                plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                initialDate: new Date(),
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                eventSources: this.getCalendarEventSources(),
                select: selectionInfo => this.dispatchCalendarEvents('select', selectionInfo),
                unselect: (jsEvent, view) => this.dispatchCalendarEvents('unselect', {jsEvent, view}),
                dateClick: dateClickInfo => this.dispatchCalendarEvents('dateClick', dateClickInfo),
                eventDidMount: eventDidMountInfo => this.dispatchCalendarEvents('eventDidMount', eventDidMountInfo),
                eventClick: eventClickInfo => this.dispatchCalendarEvents('eventClick', eventClickInfo),
                eventMouseEnter: eventMouseEnterInfo => this.dispatchCalendarEvents('eventMouseEnter', eventMouseEnterInfo),
                eventMouseLeave: eventMouseLeaveInfo => this.dispatchCalendarEvents('eventMouseLeave', eventMouseLeaveInfo),
                eventDragStart: eventDragStartInfo => this.dispatchCalendarEvents('eventDragStart', eventDragStartInfo),
                eventDragStop: eventDragStopInfo => this.dispatchCalendarEvents('eventDragStop', eventDragStopInfo),
                eventDrop: eventDropInfo => this.dispatchCalendarEvents('eventDrop', eventDropInfo),
                eventResizeStart: eventResizeStartInfo => this.dispatchCalendarEvents('eventResizeStart', eventResizeStartInfo),
                eventResizeStop: eventResizeStopInfo => this.dispatchCalendarEvents('eventResizeStop', eventResizeStopInfo),
                eventResize: eventResizeInfo => this.dispatchCalendarEvents('eventResize', eventResizeInfo),
                drop: dropInfo => this.dispatchCalendarEvents('drop', dropInfo),
                eventReceive: eventReceiveInfo => this.dispatchCalendarEvents('eventReceive', eventReceiveInfo),
                eventLeave: eventLeaveInfo => this.dispatchCalendarEvents('eventLeave', eventLeaveInfo),
                eventAdd: eventAddInfo => this.dispatchCalendarEvents('eventAdd', eventAddInfo),
                eventChange: eventChangeInfo => this.dispatchCalendarEvents('eventChange', eventChangeInfo),
                eventRemove: eventRemoveInfo => this.dispatchCalendarEvents('eventRemove', eventRemoveInfo),
                eventsSet: eventsSetInfo => this.dispatchCalendarEvents('eventsSet', eventsSetInfo),
            };

            this.calendar = new Calendar(calendarEl, {...defaultConfig, ...this.config});

            this.$wire.getCalendarEventsBeingListenedFor().then(
                (listeners) => {
                    listeners.forEach((listener) => {
                        this.calendar.on(listener, (info) => {
                            this.$wire.emit(listener, info);
                        });
                    });
                }
            );

            this.calendar.render();
        },
    }
}

export default calendar;
