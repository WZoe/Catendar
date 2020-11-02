// global variable
let currentMonth;

$(document).ready(function () {

    // initial calendar loading
    getCurrentMonth();
    updateCalendar(currentMonth);
    $("#prev").click(function (event) {
        getPreviousMonth();
    });
    $("#next").click(function (event) {
        getNextMonth();
    });
});

function getCurrentMonth () {
    let currentD = new Date();
    let currentY = currentD.getFullYear();
    let currentM = currentD.getMonth();
    currentMonth = new Month(currentY, currentM);
}

function getPreviousMonth () {
    currentMonth = currentMonth.prevMonth();
    updateCalendar();
}

function getNextMonth () {
    currentMonth = currentMonth.nextMonth();
    updateCalendar();
}

function clearCalendar () {
    $("#calendar-weeks").empty();
}

function updateCalendar () {
    // empty previous calendar
    clearCalendar();

    // update title info
    updateTitle();

    // fill calendar with new month
    fillNewMonth();

    // highlight current date
    hightlightCurrentDate();
}

function updateTitle () {
    $("h2[id='current']").html(currentMonth.year + "/" + (currentMonth.month + 1));
}

function hightlightCurrentDate () {
    let currentD = new Date();
    let currentY = currentD.getFullYear();
    let currentM = currentD.getMonth();
    if (currentMonth.month == currentM && currentMonth.year == currentY) {
        let currentDate = currentD.getDate();
        $("#day-" + currentDate).css("background-color", "PaleTurquoise");
    }
}

// load daily events using ajax
function loadEvents (day) {
    const phpFile = "loadCalendar.php";
    // just for debug
    const data = { 'year': day.getFullYear(), 'month': day.getMonth() + 1, 'date': day.getDate() };
    //const data = { 'year': day.getFullYear(), 'month': day.getMonth(), 'date': day.getDate() };
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(events => displayOnHTML(day, events))
        .catch(err => console.error(err));
}

// display returned daily events on HTML
function displayOnHTML (day, events) {
    let eventFrame = document.getElementById("day-" + day.getDate());
    if (events.personal_events.length != 0) {
        for (let event_id in events.personal_events) {

            let event = createPersonalEvent(events.personal_events[event_id]);
            eventFrame.appendChild(event);
        }
    }
    if (events.shared_events.length != 0) {
        for (let event_id in events.shared_events) {
            let event = createSharedEvent(events.shared_events[event_id]);
            eventFrame.appendChild(event);
        }
    }
    if (events.group_events.length != 0) {
        for (let event_id in events.group_events) {
            let event = createGroupEvent(events.group_events[event_id]);
            eventFrame.appendChild(event);
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
    let calendar_frame = document.getElementById("calendar-weeks");
    let weeks = currentMonth.getWeeks();
    for (let weekId in weeks) {
        let week = weeks[weekId];
        let weekElement = document.createElement("tr");
        weekElement.setAttribute("id", "week-" + (weekId + 1));
        calendar_frame.appendChild(weekElement);

        let days = week.getDates();
        for (var dayId in days) {
            var day = days[dayId];
            var date = day.getDate();

            let dayElement = document.createElement("th");
            dayElement.setAttribute("class", "cell");
            dayElement.setAttribute("id", "day-" + date);

            let dayNumber = document.createElement("p");
            dayNumber.innerHTML = date;
            dayElement.appendChild(dayNumber);

            // fade out days that don't belong to this month
            if ((weekId == 0 && date > 20) || (weekId == (weeks.length - 1) && date < 20)) {
                dayElement.setAttribute("style", "background-color: DarkGrey");
            }
            weekElement.appendChild(dayElement);

            // load daily events
            loadEvents(day);
        }
    }
}