// global variable
let currentMonth;
let daysInCurrentMonth = new Array();

$(document).ready(function () {
    // initial calendar loading
    getCurrentMonth();
    updateCalendar(currentMonth);
    // load events from user.js

    $("#prev").click(function (event) {
        getPreviousMonth();
    });
    $("#next").click(function (event) {
        getNextMonth();
    });
});

// update global variable: currentMonth
function getCurrentMonth () {
    let currentD = new Date();
    let currentY = currentD.getFullYear();
    let currentM = currentD.getMonth();
    currentMonth = new Month(currentY, currentM);
}

// update calendar with previous month
function getPreviousMonth () {
    currentMonth = currentMonth.prevMonth();
    updateCalendar();
    loadEvents();
}

// update calendar with next month
function getNextMonth () {
    currentMonth = currentMonth.nextMonth();
    updateCalendar();
    loadEvents();
}

// clear calendar grids
function clearCalendar () {
    $("#calendar-weeks").empty();
}

// load calendar grids for a new month
function updateCalendar () {
    // empty previous calendar
    clearCalendar();

    // update title info
    updateTitle();

    // fill calendar with new month grids
    fillNewMonth();

    // highlight current date
    hightlightCurrentDate();
}

function updateTitle () {
    $("h2[id='current']").html(currentMonth.year + "/" + (currentMonth.month + 1));
}

function hightlightCurrentDate () {
    let currentD = new Date();
    let year = currentD.getFullYear();
    let month = currentD.getMonth() + 1;
    let date = currentD.getDate();
    if (currentMonth.year == year && (currentMonth.month + 1) == month) {
        $("#day-" + month + "-" + date).css("background-color", "PaleTurquoise");
    }
}

// clear all events shown on current month calendar grids
function clearEvents () {
    for (let dayId in daysInCurrentMonth) {
        let day = daysInCurrentMonth[dayId];
        let month = day.getMonth() + 1;
        let date = day.getDate();
        let dayFrame = document.getElementById("day-" + month + "-" + date);
        while (dayFrame.lastChild.className !== "dateNumber") {
            dayFrame.removeChild(dayFrame.lastChild);
        }
    }
    //$("#day-" + month + "-" + date).empty();
}

// load all events shown on current month calendar grids
function loadEvents () {
    clearEvents();

    // query current month + prev & next several days group by day
    let data = {};
    data["currentYear"] = currentMonth.year;
    data["currentMonth"] = currentMonth.month + 1;
    if (daysInCurrentMonth[0].getMonth() == currentMonth.month) {
        data["prevMonth"] = -1;
    }
    else {
        data["prevMonth"] = currentMonth.prevMonth().month + 1;
        data["prevMonthStartDate"] = daysInCurrentMonth[0].getDate();
    }
    if (daysInCurrentMonth[daysInCurrentMonth.length - 1].getMonth() == currentMonth.month) {
        data["nextMonth"] = -1;
    }
    else {
        data["nextMonth"] = currentMonth.nextMonth().month + 1;
        data["nextMonthEndDate"] = daysInCurrentMonth[daysInCurrentMonth.length - 1].getDate();
    }
    //console.log(data);

    // day.getMonth() -- [0,11]->[Jan,Dec]
    const phpFile = "loadCalendar.php";
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => data.success ? displayOnHTML(data) : console.log(data.message))
        .catch(err => console.error(err));
}

// display returned daily events on HTML
function displayOnHTML (events) {
    console.log(events);
    // load personal events
    if (events.personal_events.length != 0) {
        for (let dayId in events.personal_events) {
            let dayEvents = events.personal_events[dayId];
            if (dayEvents.length != 0) {
                let eventFrame = document.getElementById("day-" + dayEvents[0].month + "-" + dayEvents[0].date);
                for (let eventId in dayEvents) {
                    let event = createPersonalEvent(dayEvents[eventId]);
                    eventFrame.appendChild(event);
                }
            }
        }
    }
    // load shared events
    if (events.shared_events.length != 0) {
        for (let dayId in events.shared_events) {
            let dayEvents = events.shared_events[dayId];
            if (dayEvents.length != 0) {
                let eventFrame = document.getElementById("day-" + dayEvents[0].month + "-" + dayEvents[0].date);
                for (let eventId in dayEvents) {
                    let event = createSharedEvent(dayEvents[eventId]);
                    eventFrame.appendChild(event);
                }
            }
        }
    }
    // load group events
    if (events.group_events.length != 0) {
        for (let dayId in events.group_events) {
            let dayEvents = events.group_events[dayId];
            if (dayEvents.length != 0) {
                let eventFrame = document.getElementById("day-" + dayEvents[0].month + "-" + dayEvents[0].date);
                for (let eventId in dayEvents) {
                    let event = createGroupEvent(dayEvents[eventId]);
                    eventFrame.appendChild(event);
                }
            }
        }
    }
}

function createPersonalEvent (event) {
    let eventElement = document.createElement("div");
    eventElement.innerHTML = event.title;
    eventElement.setAttribute("class", "event user_event");
    eventElement.setAttribute("id", "event-" + event.event_id);
    return eventElement;
}

function createSharedEvent (event) {
    let eventElement = document.createElement("div");
    eventElement.innerHTML = event.title;
    eventElement.setAttribute("class", "event shared_event");
    eventElement.setAttribute("id", "event-" + event.event_id);
    return eventElement;
}

function createGroupEvent (event) {
    let eventElement = document.createElement("div");
    eventElement.innerHTML = event.title;
    eventElement.setAttribute("class", "event group_event");
    eventElement.setAttribute("id", "event-" + event.event_id);
    return eventElement;
}

function fillNewMonth () {
    // clear daysInCurrentMonth array
    daysInCurrentMonth = [];

    let calendar_frame = document.getElementById("calendar-weeks");
    let weeks = currentMonth.getWeeks();
    for (let weekId in weeks) {
        let week = weeks[weekId];
        let weekNo = Number(weekId) + 1;
        let weekElement = document.createElement("tr");
        weekElement.setAttribute("id", "week-" + weekNo);
        calendar_frame.appendChild(weekElement);

        let days = week.getDates();
        for (let dayId in days) {
            let day = days[dayId];
            let date = day.getDate();
            let month = day.getMonth() + 1;
            let year = day.getFullYear();
            // update daysInCurrentMonth
            daysInCurrentMonth.push(day);

            let dayElement = document.createElement("th");
            dayElement.setAttribute("class", "cell");
            dayElement.setAttribute("id", "day-" + month + "-" + date);
            // add date number
            let dateNumber = document.createElement("p");
            dateNumber.setAttribute("class", "dateNumber");
            dateNumber.innerHTML = date;
            dayElement.appendChild(dateNumber);

            // fade out days that don't belong to this month
            if ((weekId == 0 && date > 20) || (weekId == (weeks.length - 1) && date < 20)) {
                dayElement.setAttribute("style", "background-color: DarkGrey");
            }
            weekElement.appendChild(dayElement);
        }
    }
}