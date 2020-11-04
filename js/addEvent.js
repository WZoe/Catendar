// date picker init
$(".month").change(function () {
    if (this.value > 12) {
        this.value = 12
    }
    if (this.value < 1) {
        this.value = 1
    }
    let month = this.value
    $(".date").val(1)
    if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) {
        $(".date").attr("max", 31)
    } else if (month == 2) {
        $(".date").attr("max", 29)
    } else {
        $(".date").attr("max", 30)
    }
})
$(".year").change(function () {
    if (this.value > 2100) {
        this.value = 2100
    }
    if (this.value < 1970) {
        this.value = 1970
    }
})
$(".date").change(function () {
    let max = parseInt(this.getAttribute("max"))
    if (this.value > max) {
        this.value = max
    }
    if (this.value < 1) {
        this.value = 1
    }
})
$(".hour").change(function () {
    if (this.value > 23) {
        this.value = 23
    }
    if (this.value < 0) {
        this.value = 0
    }
})
$(".minute").change(function () {
    if (this.value > 59) {
        this.value = 59
    }
    if (this.value < 0) {
        this.value = 0
    }
})
// persnal add init
$("#newevent").click($(".alert").hide)

// group init
$("#newgroupevent").click(function () {
    // $("#newGroupEventGroup").removeAttr("disabled")
    // $("#newGroupEventModal").find(".modal-title").text("Create New Event")
    // $("#submitGroupEvent").text("Create")
    $(".alert").hide()
    $.get("getGroupList.php", function (data) {
        $("#newGroupEventGroup").html("")
        data.forEach(function (group) {
            $("#newGroupEventGroup").append(`
            <option value="${group.group_id}">${group.group_name}</option>>
            `)
        })
    })
})

// personal add
$("#submitEvent").click(function () {
    let title = $("#newEventName").val()
    let year = $("#newEventYear").val()
    let month = $("#newEventMonth").val()
    let date = $("#newEventDate").val()
    let hour = $("#newEventHour").val()
    let minute = $("#newEventMinute").val()
    let description = $("#newEventDes").val()
    let tag = $("#newEventTag").find(":selected").val()

    $.post("addEvent.php", {
        "title": title,
        "year": year,
        "month": month,
        "date": date,
        "hour": hour,
        "minute": minute,
        "description": description,
        "tag": tag,
        "token": getToken()
    }, function (data) {
        if (data.success) {
            $('#newEventModal').modal('toggle');
            //empty input
            $("#newEventName").val("")
            $("#newEventYear").val(2020)
            $("#newEventMonth").val(1)
            $("#newEventDate").val(1)
            $("#newEventHour").val("00")
            $("#newEventMinute").val("00")
            $("#newEventDes").val("")
            loadEvents();
            resetActiveInLeftBar();
        } else {
            //if fail, alert
            let msg = data.message
            // this is cited from https://getbootstrap.com/docs/4.0/components/alerts/#dismissing
            $("#newEventModalBody").append(`<div class="mt-1 alert alert-danger alert-dismissible fade show" role="alert">
 ${msg}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>`)
        }
    }, "json")
})

//group add
$("#submitGroupEvent").click(function () {
    let title = $("#newGroupEventName").val()
    let year = $("#newGroupEventYear").val()
    let month = $("#newGroupEventMonth").val()
    let date = $("#newGroupEventDate").val()
    let hour = $("#newGroupEventHour").val()
    let minute = $("#newGroupEventMinute").val()
    let description = $("#newGroupEventDes").val()
    let tag = $("#newGroupEventTag").find(":selected").val()
    let group = $("#newGroupEventGroup").find(":selected").val()

    $.post("addGroupEvent.php", {
        "title": title,
        "year": year,
        "month": month,
        "date": date,
        "hour": hour,
        "minute": minute,
        "description": description,
        "tag": tag,
        "group": group,
        "token": getToken()
    }, function (data) {
        if (data.success) {
            $('#newGroupEventModal').modal('toggle');
            //empty input
            $("#newEventName").val("")
            $("#newEventYear").val(2020)
            $("#newEventMonth").val(1)
            $("#newEventDate").val(1)
            $("#newEventHour").val("00")
            $("#newEventMinute").val("00")
            $("#newEventDes").val("")
            loadEvents();
            resetActiveInLeftBar();
        } else {
            //if fail, alert
            let msg = data.message
            // this is cited from https://getbootstrap.com/docs/4.0/components/alerts/#dismissing
            $("#newGroupEventModalBody").append(`<div class="mt-1 alert alert-danger alert-dismissible fade show" role="alert">
 ${msg}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>`)
        }
    }, "json")
})

function resetActiveInLeftBar () {
    resetActiveGroup();
    resetActiveTag();
}

function resetActiveTag () {
    // cancel old selected item
    $("#taglist > a.active").removeClass("active");
    // select default item
    $("#taglist > a#1").addClass("active");
}

function resetActiveGroup () {
    // cancel old selected item
    $("#grouplist > a.active").removeClass("active");
    // select default item
    $("#grouplist > a#groupAllEvents").addClass("active");
}