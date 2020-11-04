function getToken() {
    //modified from https://www.w3schools.com/js/js_cookies.asp
    decodedCookie = decodeURIComponent(document.cookie);
    let tokenString = decodedCookie.split(';')[0]
    return tokenString.substring(6)
}

// jQuery.post() is modified from  https://api.jquery.com/jQuery.post/#jQuery-post-url-data-success-dataType
//login
$("#login").click(function () {
    let username = $("#username").val()
    let password = $("#password").val()
    $.post("logIn.php", {'username': username, 'password': password}, function (data) {
        //send login msg
        //if success
        if (data.success) {
            document.cookie = "token=" + data.token
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
            document.cookie = "token=" + data.token
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
        document.cookie = "token="
        userInit()
    })
})