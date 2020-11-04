$(document).ready(userInit());

function userInit() {
    let userinfo = document.getElementById("userinfo");
    let additionalinfo = document.getElementById("additionalinfo");
    let eventbtn = document.getElementById("eventbtn");
    let guest = document.getElementById("guest");

    $(".alert").hide()
    $.get("getUser.php", function (data) {
        if (!data.active) {
            userinfo.setAttribute("hidden", true);
            additionalinfo.setAttribute("hidden", true);
            eventbtn.setAttribute("hidden", true);

            guest.removeAttribute("hidden");

            //clear events
            clearEvents();
        } else {
            userinfo.removeAttribute("hidden");
            additionalinfo.removeAttribute("hidden");
            eventbtn.removeAttribute("hidden");

            guest.setAttribute("hidden", true);
            $("#userinfo > h4").text(`Welcome back, ${data.username}`)

            // group load
            getGroupList();
            //event load
            loadEvents();
        }
    })

}
