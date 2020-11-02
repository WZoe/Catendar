function getGroupList() {
    $.get("getGroupList.php", function (data){
        // display groups
        $("#grouplist").html("                        <a href=\"#\" class=\"list-group-item list-group-item-action active\" data-toggle=\"list\" id='groupAllEvents'>\n" +
            "                            All Events\n" +
            "                        </a>\n" +
            "                        <a href=\"#\" class=\"list-group-item list-group-item-action\" data-toggle=\"list\" id='groupYourEvents'>Your Events</a><div id='otherGroups'>")
        data.forEach(function(item) {
            $("#grouplist").append(`
        <a href="#" class="list-group-item list-group-item-action" data-toggle="list" id="group${item.group_id}">${item.group_name}</a>
        `)
        })
        $("#grouplist").html("</div>")
    }, "json")
}

// new group
$("#newgroup").click(function () {
    //creating an adding modal
    let groupName = $("#groupname").val()
    let members = $("#members").val()
    $('#newGroupModal').modal('toggle');
    $.post("newGroup.php", {"name": groupName, "members": members}, function (data){
        if(data.success) {
            // not reacting to these
            // reload group list
            getGroupList()
        } else {
            //if fail, alert
            let msg = data.message
            // this is cited from https://getbootstrap.com/docs/4.0/components/alerts/#dismissing
            $("#groups").append(`<div class="mt-1 alert alert-danger alert-dismissible fade show" role="alert">
 ${msg}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>`)
        }
    },"json")
});

// todo:click on group button
function groupClick(group_id) {
    // clear cal
    // fetch events of group_id
}
// bond actions to each group button
//todo:bond to showAll function
$("#groupAllEvents").click()

//todo:bond to showPersonal function
$("#groupYourEvents").click()

//bond to group
// modified from https://stackoverflow.com/questions/4109376/jquery-iterate-over-child-elements
$("#otherGroups").children("a").each(function (i) {
    $(this).click(function () {
        //get group id
        let group_id = this.attr("id")
        group_id = parseInt(group_id.slice(6))
        groupClick(group_id)
    })
})
