$(document).ready(function(){
    $.get("getUser.php", function (data) {
        if (!data.active) {
            guestInit()
        } else {
userInit(data)
        }
    })
});

function guestInit() {
    let userinfo = document.getElementById("userinfo");
    let additionalinfo = document.getElementById("additionalinfo");
    let eventbtn = document.getElementById("eventbtn");
    let guest = document.getElementById("guest");
    userinfo.setAttribute("hidden", true);
    additionalinfo.setAttribute("hidden", true);
    eventbtn.setAttribute("hidden", true);

    guest.removeAttribute("hidden");

    //clear events
}

function userInit(data) {
    let userinfo = document.getElementById("userinfo");
    let additionalinfo = document.getElementById("additionalinfo");
    let eventbtn = document.getElementById("eventbtn");
    let guest = document.getElementById("guest");
    userinfo.removeAttribute("hidden");
    additionalinfo.removeAttribute("hidden");
    eventbtn.removeAttribute("hidden");

    guest.setAttribute("hidden", true);
    $("#userinfo > h4").text(`Welcome back, ${data.username}`)

    // group load

    //event load
}