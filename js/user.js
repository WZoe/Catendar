//login
$("#login").click(function () {
    let username = $("#username").val()
    let password = $("#password").val()
    $.post("logIn.php", {'username': username, 'password': password}, function (data) {
        //send login msg
        //if success
        if (data.success) {
            userInit()
        } else {
            //if fail, alert
            let msg = data.message
            // this is cited from https://getbootstrap.com/docs/4.0/components/alerts/#dismissing
            $("#guest").append(`<div class="mt-1 alert alert-danger alert-dismissible fade show" role="alert">
 ${msg}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>`)
        }
    }, "json")
})

//signup
$("#signup").click(function () {
    let username = $("#username").val()
    let password = $("#password").val()
    $.post("signUp.php", {'username': username, 'password': password}, function (data) {
        //send register msg
        //if sucess
        if (data.success) {
            userInit()
        } else {
            //if fail, alert
            let msg = data.message
            // this is cited from https://getbootstrap.com/docs/4.0/components/alerts/#dismissing
            $("#guest").append(`<div class="mt-1 alert alert-danger alert-dismissible fade show" role="alert">
 ${msg}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>`)
        }
    }, "json")
})

//logout
$("#logout").click(function () {
    //send log out request
    $.get("logOut.php", function () {
        userInit()
    })
})