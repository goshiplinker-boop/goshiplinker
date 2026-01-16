// Function to handle select all for the current active tab
function handleSelectAll(tabId) {
    const selectAllCheckbox = document.querySelector(`#${tabId} #selectAll`);
    const checkboxes = document.querySelectorAll(`#${tabId} .order-checkbox`);

    // Select or deselect all checkboxes within the active tab
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("click", function () {
            checkboxes.forEach((checkbox) => (checkbox.checked = this.checked));
        });
    }

    // Update the "select all" checkbox status when individual checkboxes are clicked
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("click", function () {
            const allChecked =
                document.querySelectorAll(`#${tabId} .order-checkbox:checked`)
                    .length === checkboxes.length;
            if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
        });
    });
}

// Function to clear checkboxes in the current active tab
function clearCheckboxes(tabId) {
    const checkboxes = document.querySelectorAll(`#${tabId} .order-checkbox`);
    checkboxes.forEach((checkbox) => (checkbox.checked = false));

    const selectAllCheckbox = document.querySelector(`#${tabId} #selectAll`);
    if (selectAllCheckbox) selectAllCheckbox.checked = false; // Uncheck "select all" as well
}

// Event listener for tab change
document.querySelectorAll(".nav-link").forEach((tab) => {
    tab.addEventListener("shown.bs.tab", function (event) {
        const prevTab = document
            .querySelector(".tab-pane.active")
            .getAttribute("id"); // Get previously active tab ID
        clearCheckboxes(prevTab); // Clear checkboxes in the previously active tab

        const targetTabId = event.target.getAttribute("href").substring(1); // Get newly activated tab pane id
        handleSelectAll(targetTabId); // Set up select all for the new tab
    });
});

// Initialize for the default active tab (All)
document.addEventListener("DOMContentLoaded", function () {
    const defaultTabId = document
        .querySelector(".tab-pane.active")
        .getAttribute("id");
    handleSelectAll(defaultTabId);
});

// Function to toggle password visibility
function togglePasswordVisibility() {
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
}

// Call the password visibility toggle function
document.addEventListener("DOMContentLoaded", function () {
    togglePasswordVisibility(); // Set up password toggle
});

// this below is used for checkbox fucntionality for channelIntegration

document
    .getElementById("order_by_status_yes")
    .addEventListener("change", function () {
        var inputField = document.getElementById("order_status_input");
        if (this.checked) {
            inputField.style.display = "block"; // Show input field
        } else {
            inputField.style.display = "none"; // Hide input field
        }
    });

// To show input field if the checkbox is checked on page load (after form validation, etc.)
window.onload = function () {
    var checkbox = document.getElementById("order_by_status_yes");
    var inputField = document.getElementById("order_status_input");
    if (checkbox.checked) {
        inputField.style.display = "block"; // Show input field if checkbox is already checked
    }
};
