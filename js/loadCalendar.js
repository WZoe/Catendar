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

function updateCalendar () {
    // empty previous calendar
    $("#calendar-weeks").empty();

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
            let dayIndividualEvent = document.createElement("div");
            dayIndividualEvent.innerHTML = "individual";
            dayIndividualEvent.setAttribute("class", "event user_event");
            let daySharedEvent = document.createElement("div");
            daySharedEvent.setAttribute("class", "event shared_event")
            daySharedEvent.innerHTML = "shared";
            let dayGroupEvent = document.createElement("div");
            dayGroupEvent.setAttribute("class", "event group_event")
            dayGroupEvent.innerHTML = "group";
            dayElement.appendChild(dayNumber);
            dayElement.appendChild(dayIndividualEvent);
            dayElement.appendChild(daySharedEvent);
            dayElement.appendChild(dayGroupEvent);
            weekElement.appendChild(dayElement);
        }
    }
}