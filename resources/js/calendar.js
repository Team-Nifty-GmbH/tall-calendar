const calendar = () => {
    return {
        showModal() {
            this.calendarId = this.calendarEvent.calendar_id;
            Alpine.$data(document.getElementById(this.id + '-calendar-event-edit').querySelector('[wireui-modal]')).open();
        },
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
            Alpine.$data(document.getElementById(this.id + '-calendar-edit').querySelector('[wireui-modal]')).open();
        },
        saveCalendar() {
            this.$wire.saveCalendar(this.calendarItem).then(calendar => {
                this.calendars.splice(this.calendars.findIndex(c => c.id === calendar.id), 1, calendar);
                this.calendarId = calendar.id;
                this.activeCalendars.push(calendar.id);

                this.close();
            });
        },
        deleteCalendar() {
            this.$wire.deleteCalendar(this.calendarItem).then(success => {
                if(success) {
                    this.close();
                }

                this.calendar.getEventSourceById(this.calendarItem.id).remove();
                this.calendars.splice(this.calendars.findIndex(c => c.id === this.calendarItem.id), 1);
            });
        },
        saveEvent() {
            this.$wire.saveEvent(this.calendarEvent).then(event => {
                if (event === false) {
                    return false;
                }

                if (! this.calendarEventItemProxy?.id) {
                    this.calendar.addEvent(event, this.calendar.getEventSourceById(event.calendar_id));
                }

                this.syncProxy();

                // check if this.close exists
                if (typeof this.close === 'function') {
                    this.close();
                }
            });
        },
        setDateTime(type, event) {
            const date = event.target.parentNode.parentNode.parentNode.querySelector('input[type="date"]').value;
            let time = event.target.parentNode.parentNode.parentNode.querySelector('input[type="time"]').value;

            if (this.calendarEvent.allDay) {
                time = '00:00:00';
            }

            let dateTime = dayjs(date + ' ' + time);

            if (type === 'start') {
                this.calendarEvent.start = dateTime.format(); // Use the default ISO 8601 format
            } else {
                this.calendarEvent.end = dateTime.format(); // Use the default ISO 8601 format
            }
        },
        deleteEvent() {
            this.$wire.deleteEvent(this.calendarEvent.id).then(success => {
                if(success) {
                    this.close();
                }

                this.calendarEventItemProxy.remove();
            });
        },
        dateClick(day) {
            if (this.calendarItem.resourceEditable === false) {
                this.calendarClick(this.calendars.find(c => c.resourceEditable === true));
            }

            const date = dayjs(day.dateStr + '09:00');
            this.eventClick({
                event: {
                    start: date,
                    end: date.add(1, 'hour'),
                    allDay: false,
                    invited: [],
                    calendar_id: this.calendarId,
                    is_editable: true,
                }
            }, false);
            this.showModal();
        },
        eventClick(event, isProxy = true) {
            let eventObject;
            if (isProxy) {
                this.calendarEventItemProxy = event.event;
                eventObject = event.event.toPlainObject({collapseExtendedProps: true});
            } else {
                this.calendarEventItemProxy = null;
                eventObject = event.event;
                eventObject.start = eventObject.start.toISOString().slice(0, -1);
                eventObject.end = eventObject.end?.toISOString().slice(0, -1);
            }

            this.calendarEvent = eventObject;
        },
        syncProxy() {
            const value = this.calendarEvent;
            if (this.calendarEventItemProxy) {
                this.calendarEventItemProxy.setExtendedProp('calendar_id', value.calendar_id);
                this.calendarEventItemProxy.setProp('title', value.title);
                this.calendarEventItemProxy.setStart(value.start);
                this.calendarEventItemProxy.setEnd(value.end);
                this.calendarEventItemProxy.setAllDay(value.allDay);
            }
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
            this.$dispatch(`calendar-${eventNameKebap}`, params);
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
