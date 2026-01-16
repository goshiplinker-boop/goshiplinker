document
    .getElementById("changePassTarget")
    .addEventListener("click", function () {
        var passwordInput = document.getElementById("signupSrPassword");
        var passIcon = document.getElementById("changePassIcon");

        // Toggle the type attribute
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passIcon.classList.remove("bi-eye-slash");
            passIcon.classList.add("bi-eye"); // Change the icon to a slashed eye
        } else {
            passwordInput.type = "password";
            passIcon.classList.remove("bi-eye");
            passIcon.classList.add("bi-eye-slash"); // Change back to regular eye icon
        }
    });
