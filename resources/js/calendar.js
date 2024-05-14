const calendar = () => {
    return {
        calendarItem: {},
        calendarEventItemProxy: {},
        parseDateTime(event, locale, property) {
            const dateTime = new Date(event.start);
            let config = null;
            if (event.is_all_day === true) {
                config = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                }
            } else {
                config = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }
            }

            return dateTime.toLocaleString(locale, config);
        },
        inviteStatus(calendarEvent, status) {
            calendarEvent.status = status;
            if (this.calendarItem.resourceEditable === false) {
                this.calendarClick(this.calendars.find(c => c.resourceEditable === true));
            }

            const existingEvent = this.calendar.getEventById(calendarEvent.calendar_event.id);

            if ((status === 'accepted' || status === 'maybe') && ! existingEvent) {
                this.calendar.addEvent(calendarEvent.calendar_event, this.calendar.getEventSourceById(this.calendarId));
            } else if(status === 'declined' && existingEvent) {
                existingEvent.remove();
            }

            this.$wire.inviteStatus(calendarEvent.id, status, this.calendarId);
        },
        calendarClick(calendar) {
            this.calendarId = calendar.id;
            this.calendarItem = calendar;
        },
        editCalendar(calendar) {
            this.calendarItem = calendar;
            this.$wire.editCalendar(calendar);
        },
        saveCalendar() {
            this.$wire.saveCalendar(this.calendarItem).then(calendar => {
                if (calendar === false) {
                    return false;
                }

                let index = this.calendars.findIndex(c => c.id === calendar.id);
                this.calendars.splice(index, index !== -1 ? 1 : 0, calendar);
                this.calendarId = calendar.id;
                this.activeCalendars.push(calendar.id);

                // check if this.close exists
                if (typeof this.close === 'function') {
                    this.close();
                }
            });
        },
        deleteCalendar() {
            this.$wire.deleteCalendar(this.calendarItem).then(success => {
                if (success) {
                    this.close();
                }

                this.calendar.getEventSourceById(this.calendarItem.id).remove();
                this.calendars.splice(this.calendars.findIndex(c => c.id === this.calendarItem.id), 1);
            });
        },
        saveEvent() {
            this.$wire.saveEvent(this.$wire.calendarEvent).then(event => {
                if (event === false) {
                    return false;
                }

                if (! this.$wire.calendarEvent?.id) {
                    this.calendar.addEvent(event, this.calendar.getEventSourceById(event.calendar_id));
                }

                // check if this.close exists
                if (typeof this.close === 'function') {
                    this.close();
                }
            });
        },
        setDateTime(type, event) {
            const date = event.target.parentNode.parentNode.parentNode.querySelector('input[type="date"]').value;
            let time = event.target.parentNode.parentNode.parentNode.querySelector('input[type="time"]').value;

            if (this.$wire.calendarEvent.allDay) {
                time = '00:00:00';
            }

            let dateTime = dayjs(date + ' ' + time);

            if (type === 'start') {
                this.$wire.calendarEvent.start = dateTime.format(); // Use the default ISO 8601 format
            } else {
                this.$wire.calendarEvent.end = dateTime.format(); // Use the default ISO 8601 format
            }
        },
        deleteEvent() {
            this.$wire.deleteEvent(this.$wire.calendarEvent).then(success => {
                if (success) {
                    this.close();
                }

                this.calendarEventItemProxy.remove();
            });
        },
        calendar: null,
        config: {},
        id: null,
        calendarId: null,
        calendars: [],
        invites: [],
        activeCalendars: [],
        calendarEvent: {},
        dispatchCalendarEvents(eventName, params) {
            const eventNameKebap = eventName.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase();
            this.$wire.dispatch(`calendar-${eventNameKebap}`, params);
        },
        getCalendarEventSources() {
            this.calendars.forEach((calendar) => {
                if (this.calendarId === null) {
                    this.calendarId = calendar.id;
                }
               calendar.events = (info) => this.$wire.getEvents(info, calendar);
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
            });
            this.$wire.getInvites().then((invites) => {
                this.invites = invites;
            });
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
                select: selectionInfo => {
                    this.dispatchCalendarEvents('select', selectionInfo);
                },
                unselect: (jsEvent, view) => {
                    this.dispatchCalendarEvents('unselect', {jsEvent, view});
                },
                dateClick: dateClickInfo => {
                    this.$wire.onDateClick(dateClickInfo);
                    this.dispatchCalendarEvents('dateClick', dateClickInfo);
                },
                eventDidMount: eventDidMountInfo => {
                    this.dispatchCalendarEvents('eventDidMount', eventDidMountInfo);
                },
                eventClick: eventClickInfo => {
                    this.$wire.onEventClick(eventClickInfo);
                    this.dispatchCalendarEvents('eventClick', eventClickInfo);
                },
                eventMouseEnter: eventMouseEnterInfo => {
                    this.dispatchCalendarEvents('eventMouseEnter', eventMouseEnterInfo);
                },
                eventMouseLeave: eventMouseLeaveInfo => {
                    this.dispatchCalendarEvents('eventMouseLeave', eventMouseLeaveInfo);
                },
                eventDragStart: eventDragStartInfo => {
                    this.$wire.onEventDragStart(eventDragStartInfo);
                    this.dispatchCalendarEvents('eventDragStart', eventDragStartInfo);
                },
                eventDragStop: eventDragStopInfo => {
                    this.$wire.onEventDragStop(eventDragStopInfo);
                    this.dispatchCalendarEvents('eventDragStop', eventDragStopInfo);
                },
                eventDrop: eventDropInfo => {
                    this.$wire.onEventDrop(eventDropInfo);
                    this.dispatchCalendarEvents('eventDrop', eventDropInfo);
                },
                eventResizeStart: eventResizeStartInfo => {
                    this.dispatchCalendarEvents('eventResizeStart', eventResizeStartInfo);
                },
                eventResizeStop: eventResizeStopInfo => {
                    this.dispatchCalendarEvents('eventResizeStop', eventResizeStopInfo);
                },
                eventResize: eventResizeInfo => {
                    this.dispatchCalendarEvents('eventResize', eventResizeInfo);
                },
                drop: dropInfo => {
                    this.dispatchCalendarEvents('drop', dropInfo);
                },
                eventReceive: eventReceiveInfo => {
                    this.dispatchCalendarEvents('eventReceive', eventReceiveInfo);
                },
                eventLeave: eventLeaveInfo => {
                    this.dispatchCalendarEvents('eventLeave', eventLeaveInfo);
                },
                eventAdd: eventAddInfo => {
                    this.dispatchCalendarEvents('eventAdd', eventAddInfo);
                },
                eventChange: eventChangeInfo => {
                    this.dispatchCalendarEvents('eventChange', eventChangeInfo);
                },
                eventRemove: eventRemoveInfo => {
                    this.dispatchCalendarEvents('eventRemove', eventRemoveInfo);
                },
                eventsSet: eventsSetInfo => {
                    this.dispatchCalendarEvents('eventsSet', eventsSetInfo);
                },
            };

            this.calendar = new Calendar(calendarEl, {...defaultConfig, ...this.config});
            this.calendar.render();
        },
    }
}

export default calendar;
