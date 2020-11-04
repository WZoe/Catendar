// cited from: https://stackoverflow.com/questions/3024391/how-do-i-iterate-through-children-elements-of-a-div-using-jquery
$("#taglist").children("a").each(function (idx) {
    $(this).click(function (event) {
        // citation ends
        // reset active group to default (can't select both group and tag at the same time)
        resetActiveGroup();

        let tagId = event.target.id;
        if (tagId == 1) {
            loadEvents();
        }
        else {
            loadTagEvents(tagId);
        }
    })
})

function loadTagEvents (tagId) {
    // clear old events
    clearEvents();
    // load new events
    const phpFile = "loadTagEvents.php";
    let data = createTimeDataForAjax();
    data["tag_id"] = tagId;
    data["token"] = getToken();
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => data.success ? displayOnHTML(data) : console.log(data.message))
        .catch(err => console.error(err));
}