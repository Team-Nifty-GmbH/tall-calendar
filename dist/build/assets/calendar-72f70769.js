const d=()=>({showModal(){this.calendarId=this.calendarEvent.calendar_id,Alpine.$data(document.getElementById(this.id+"-calendar-event-edit").querySelector("[wireui-modal]")).open()},calendarItem:{},calendarEventItemProxy:{},parseDateTime(t,a,e){const n=new Date(t.start);let i=null;return t.is_all_day===!0?i={year:"numeric",month:"2-digit",day:"2-digit"}:i={year:"numeric",month:"2-digit",day:"2-digit",hour:"2-digit",minute:"2-digit"},n.toLocaleString(a,i)},inviteStatus(t,a){t.status=a,this.calendarItem.resourceEditable===!1&&this.calendarClick(this.calendars.find(n=>n.resourceEditable===!0));const e=this.calendar.getEventById(t.calendar_event.id);(a==="accepted"||a==="maybe")&&!e?this.calendar.addEvent(t.calendar_event,this.calendar.getEventSourceById(this.calendarId)):a==="declined"&&e&&e.remove(),this.$wire.inviteStatus(t.id,a,this.calendarId)},calendarClick(t){this.calendarId=t.id,this.calendarItem=t},editCalendar(t){this.calendarItem=t,Alpine.$data(document.getElementById(this.id+"-calendar-edit").querySelector("[wireui-modal]")).open()},saveCalendar(){this.$wire.saveCalendar(this.calendarItem).then(t=>{this.calendars.splice(this.calendars.findIndex(a=>a.id===t.id),1,t),this.calendarId=t.id,this.activeCalendars.push(t.id),this.close()})},deleteCalendar(){this.$wire.deleteCalendar(this.calendarItem).then(t=>{t&&this.close(),this.calendar.getEventSourceById(this.calendarItem.id).remove(),this.calendars.splice(this.calendars.findIndex(a=>a.id===this.calendarItem.id),1)})},saveEvent(){this.$wire.saveEvent(this.calendarEvent).then(t=>{var a;if(t===!1)return!1;(a=this.calendarEventItemProxy)!=null&&a.id||this.calendar.addEvent(t,this.calendar.getEventSourceById(t.calendar_id)),this.syncProxy(),typeof this.close=="function"&&this.close()})},setDateTime(t,a){const e=a.target.parentNode.parentNode.parentNode.querySelector('input[type="date"]').value;let n=a.target.parentNode.parentNode.parentNode.querySelector('input[type="time"]').value;this.calendarEvent.allDay&&(n="00:00:00");let i=dayjs(e+" "+n);t==="start"?this.calendarEvent.start=i.format():this.calendarEvent.end=i.format()},deleteEvent(){this.$wire.deleteEvent(this.calendarEvent.id).then(t=>{t&&this.close(),this.calendarEventItemProxy.remove()})},dateClick(t){this.calendarItem.resourceEditable===!1&&this.calendarClick(this.calendars.find(e=>e.resourceEditable===!0));const a=dayjs(t.dateStr+"09:00");this.eventClick({event:{start:a,end:a.add(1,"hour"),allDay:!1,invited:[],calendar_id:this.calendarId,is_editable:!0}},!1),this.showModal()},eventClick(t,a=!0){var n;let e;a?(this.calendarEventItemProxy=t.event,e=t.event.toPlainObject({collapseExtendedProps:!0})):(this.calendarEventItemProxy=null,e=t.event,e.start=e.start.toISOString().slice(0,-1),e.end=(n=e.end)==null?void 0:n.toISOString().slice(0,-1)),this.calendarEvent=e,this.$dispatch("calendar-event-click",this.calendarEvent)},syncProxy(){const t=this.calendarEvent;this.calendarEventItemProxy&&(this.calendarEventItemProxy.setExtendedProp("calendar_id",t.calendar_id),this.calendarEventItemProxy.setProp("title",t.title),this.calendarEventItemProxy.setStart(t.start),this.calendarEventItemProxy.setEnd(t.end),this.calendarEventItemProxy.setAllDay(t.allDay))},calendar:null,config:{},id:null,calendarId:null,calendars:[],invites:[],activeCalendars:[],calendarEvent:{},dispatchCalendarEvents(t,a){const e=t.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g,"$1-$2").toLowerCase();this.$dispatch(`calendar-${e}`,a)},getCalendarEventSources(){return this.calendars.forEach(t=>{this.calendarId===null&&(this.calendarId=t.id),t.events=a=>this.$wire.getEvents(a,t),this.activeCalendars.push(t.id)}),this.calendars},toggleEventSource(t){const a=this.calendar.getEventSourceById(t.id);a?a.remove():this.calendar.addEventSource(t)},init(){this.id=this.$id("calendar"),this.$wire.getCalendars().then(t=>{this.calendars=t}),this.$wire.getConfig().then(t=>{this.config=t,this.initCalendar()}),this.$wire.getInvites().then(t=>{this.invites=t})},initCalendar(){let t=document.getElementById(this.id),a={plugins:[dayGridPlugin,timeGridPlugin,listPlugin,interactionPlugin],initialView:"dayGridMonth",initialDate:new Date,editable:!0,selectable:!0,selectMirror:!0,dayMaxEvents:!0,eventSources:this.getCalendarEventSources(),select:e=>this.dispatchCalendarEvents("select",e),unselect:(e,n)=>this.dispatchCalendarEvents("unselect",{jsEvent:e,view:n}),dateClick:e=>this.dispatchCalendarEvents("dateClick",e),eventDidMount:e=>this.dispatchCalendarEvents("eventDidMount",e),eventClick:e=>this.dispatchCalendarEvents("eventClick",e),eventMouseEnter:e=>this.dispatchCalendarEvents("eventMouseEnter",e),eventMouseLeave:e=>this.dispatchCalendarEvents("eventMouseLeave",e),eventDragStart:e=>this.dispatchCalendarEvents("eventDragStart",e),eventDragStop:e=>this.dispatchCalendarEvents("eventDragStop",e),eventDrop:e=>this.dispatchCalendarEvents("eventDrop",e),eventResizeStart:e=>this.dispatchCalendarEvents("eventResizeStart",e),eventResizeStop:e=>this.dispatchCalendarEvents("eventResizeStop",e),eventResize:e=>this.dispatchCalendarEvents("eventResize",e),drop:e=>this.dispatchCalendarEvents("drop",e),eventReceive:e=>this.dispatchCalendarEvents("eventReceive",e),eventLeave:e=>this.dispatchCalendarEvents("eventLeave",e),eventAdd:e=>this.dispatchCalendarEvents("eventAdd",e),eventChange:e=>this.dispatchCalendarEvents("eventChange",e),eventRemove:e=>this.dispatchCalendarEvents("eventRemove",e),eventsSet:e=>this.dispatchCalendarEvents("eventsSet",e)};this.calendar=new Calendar(t,{...a,...this.config}),this.$wire.getCalendarEventsBeingListenedFor().then(e=>{e.forEach(n=>{this.calendar.on(n,i=>{this.$wire.emit(n,i)})})}),this.calendar.render()}});export{d as c};