$("#taglist").children("a").each(function (idx) {
    $(this).click(function (event) {
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
    fetch(phpFile, {
        method: "POST",
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => data.success ? displayOnHTML(data) : console.log(data.message))
        .catch(err => console.error(err));
}