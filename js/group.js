$(document).ready(function () {
    // new group
    $("#newgroup").click(function () {
        //creating an adding modal
        let groupName = $("#groupname").val()
        let members = $("#members").val()

        $.post("newGroup.php", { "name": groupName, "members": members,
            "token": getToken() }, function (data) {
            if (data.success) {
                // not reacting to these
                // reload group list
                $('#newGroupModal').modal('toggle');
                $(".alert").hide()
                getGroupList();
            } else {
                //if fail, alert
                let msg = data.message
                // this is cited from https://getbootstrap.com/docs/4.0/components/alerts/#dismissing
                $("#newGroupModalBody").append(`<div class="mt-1 alert alert-danger alert-dismissible fade show" role="alert">
    ${msg}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
    </div>`)
            }
        }, "json")
    });
});

function getGroupList () {
    $.get("getGroupList.php", function (data) {
        // display groups
        $("#grouplist").html("<a href=\"#\" class=\"list-group-item list-group-item-action active\" data-toggle=\"list\" id='groupAllEvents'>\nAll Events\n</a>\n" +
            "<a href=\"#\" class=\"list-group-item list-group-item-action\" data-toggle=\"list\" id='groupSharedEvents'>Shared to Me</a>" +
            "<a href=\"#\" class=\"list-group-item list-group-item-action\" data-toggle=\"list\" id='groupPersonalEvents'>My Events</a>")
        data.forEach(function (item) {
            $("#grouplist").append(`
        <a href="#" class="list-group-item list-group-item-action" data-toggle="list" id="group${item.group_id}">${item.group_name}</a>
        `)
        })
        // set onclick for newly created group elements
        loadEventsOnClick();
    }, "json")
}

function loadEventsOnClick () {
    $("#grouplist").children("a").each(function (idx) {
        $(this).click(function (event) {
            // reset active tag to default (can't select both group and tag at the same time)
            resetActiveTag();

            let id = event.target.id;
            // load all events
            if (id == "groupAllEvents") {
                loadEvents();
            }
            // load personal events
            else if (id == "groupPersonalEvents") {
                loadPersonalEvents();
            }
            // load shared events
            else if (id == "groupSharedEvents") {
                loadSharedEvents();
            }
            // load group events
            else {
                let groupId = parseInt(id.slice(5));
                loadGroupEvents(groupId);
            }
        })
    });
}

function loadPersonalEvents () {
    // clear old events
    clearEvents();
    // load new events
    const phpFile = "loadPersonalEvents.php";
    let data = createTimeDataForAjax();
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => data.success ? displayOnHTML(data) : console.log(data.message))
        .catch(err => console.error(err));
}

function loadSharedEvents () {
    // clear old events
    clearEvents();
    // load new events
    const phpFile = "loadSharedEvents.php";
    let data = createTimeDataForAjax();
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => data.success ? displayOnHTML(data) : console.log(data.message))
        .catch(err => console.error(err));
}

function loadGroupEvents (groupId) {
    // clear old events
    clearEvents();
    // load new events
    const phpFile = "loadGroupEvents.php";
    let data = createTimeDataForAjax();
    data["group_id"] = groupId;
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => data.success ? displayOnHTML(data) : console.log(data.message))
        .catch(err => console.error(err));
}