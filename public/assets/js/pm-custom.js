// Default theme js
window.hs_config = {
    autopath: "@@autopath",
    deleteLine: "hs-builder:delete",
    "deleteLine:build": "hs-builder:build-delete",
    "deleteLine:dist": "hs-builder:dist-delete",
    previewMode: false,
    startPath: "/index.html",
    vars: {
        themeFont:
            "https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap",
        version: "?v=1.0",
    },
    layoutBuilder: {
        extend: { switcherSupport: true },
        header: { layoutMode: "default", containerMode: "container-fluid" },
        sidebarLayout: "default",
    },
    themeAppearance: {
        layoutSkin: "default",
        sidebarSkin: "default",
        styles: {
            colors: {
                primary: "#377dff",
                transparent: "transparent",
                white: "#fff",
                dark: "132144",
                gray: { 100: "#f9fafc", 900: "#1e2022" },
            },
            font: "Inter",
        },
    },
    languageDirection: { lang: "en" },
    skipFilesFromBundle: {
        dist: [
            "assets/js/hs.theme-appearance.js",
            "assets/js/hs.theme-appearance-charts.js",
            "assets/js/demo.js",
        ],
        build: [
            "assets/css/theme.css",
            "assets/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js",
            "assets/js/demo.js",
            "assets/css/theme-dark.css",
            "assets/css/docs.css",
            "assets/vendor/icon-set/style.css",
            "assets/js/hs.theme-appearance.js",
            "assets/js/hs.theme-appearance-charts.js",
            "node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js",
            "assets/js/demo.js",
        ],
    },
    minifyCSSFiles: ["assets/css/theme.css", "assets/css/theme-dark.css"],
    copyDependencies: {
        dist: { "*assets/js/theme-custom.js": "" },
        build: {
            "*assets/js/theme-custom.js": "",
            "node_modules/bootstrap-icons/font/*fonts/**": "assets/css",
        },
    },
    buildFolder: "",
    replacePathsToCDN: {},
    directoryNames: { src: "./src", dist: "./dist", build: "./build" },
    fileNames: {
        dist: { js: "theme.min.js", css: "theme.min.css" },
        build: {
            css: "theme.min.css",
            js: "theme.min.js",
            vendorCSS: "vendor.min.css",
            vendorJS: "vendor.min.js",
        },
    },
    fileTypes: "jpg|png|svg|mp4|webm|ogv|json",
};
window.hs_config.gulpRGBA = (p1) => {
    const options = p1.split(",");
    const hex = options[0].toString();
    const transparent = options[1].toString();

    var c;
    if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
        c = hex.substring(1).split("");
        if (c.length == 3) {
            c = [c[0], c[0], c[1], c[1], c[2], c[2]];
        }
        c = "0x" + c.join("");
        return (
            "rgba(" +
            [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(",") +
            "," +
            transparent +
            ")"
        );
    }
    throw new Error("Bad Hex");
};
window.hs_config.gulpDarken = (p1) => {
    const options = p1.split(",");

    let col = options[0].toString();
    let amt = -parseInt(options[1]);
    var usePound = false;

    if (col[0] == "#") {
        col = col.slice(1);
        usePound = true;
    }
    var num = parseInt(col, 16);
    var r = (num >> 16) + amt;
    if (r > 255) {
        r = 255;
    } else if (r < 0) {
        r = 0;
    }
    var b = ((num >> 8) & 0x00ff) + amt;
    if (b > 255) {
        b = 255;
    } else if (b < 0) {
        b = 0;
    }
    var g = (num & 0x0000ff) + amt;
    if (g > 255) {
        g = 255;
    } else if (g < 0) {
        g = 0;
    }
    return (usePound ? "#" : "") + (g | (b << 8) | (r << 16)).toString(16);
};
window.hs_config.gulpLighten = (p1) => {
    const options = p1.split(",");

    let col = options[0].toString();
    let amt = parseInt(options[1]);
    var usePound = false;

    if (col[0] == "#") {
        col = col.slice(1);
        usePound = true;
    }
    var num = parseInt(col, 16);
    var r = (num >> 16) + amt;
    if (r > 255) {
        r = 255;
    } else if (r < 0) {
        r = 0;
    }
    var b = ((num >> 8) & 0x00ff) + amt;
    if (b > 255) {
        b = 255;
    } else if (b < 0) {
        b = 0;
    }
    var g = (num & 0x0000ff) + amt;
    if (g > 255) {
        g = 255;
    } else if (g < 0) {
        g = 0;
    }
    return (usePound ? "#" : "") + (g | (b << 8) | (r << 16)).toString(16);
};
// End Default theme js

// Company step form
(function () {
    window.onload = function () {
        // INITIALIZATION OF STEP FORM
        // =======================================================
        new HSStepForm(".js-step-form", {
            finish: () => {
                document.getElementById(
                    "addUserStepFormProgress"
                ).style.display = "none";
                document.getElementById("addUserStepProfile").style.display =
                    "none";
                document.getElementById(
                    "addUserStepCompanyDetails"
                ).style.display = "none";
                document.getElementById(
                    "otherDetailsStepConfirmation"
                ).style.display = "none";

                scrollToTop("#header");
                const formContainer = document.getElementById("formContainer");
            },
            onNextStep: function () {
                scrollToTop();
            },
            onPrevStep: function () {
                scrollToTop();
            },
        });

        function scrollToTop(el = ".js-step-form") {
            el = document.querySelector(el);
            window.scrollTo({
                top: el.getBoundingClientRect().top + window.scrollY - 30,
                left: 0,
                behavior: "smooth",
            });
        }

        // INITIALIZATION OF ADD FIELD
        // =======================================================
        new HSAddField(".js-add-field", {
            addedField: (field) => {
                HSCore.components.HSTomSelect.init(
                    field.querySelector(".js-select-dynamic")
                );
                HSCore.components.HSMask.init(
                    field.querySelector(".js-input-mask")
                );
            },
        });

        // INITIALIZATION OF SELECT
        // =======================================================
        HSCore.components.HSTomSelect.init(".js-select", {
            render: {
                option: function (data, escape) {
                    return data.optionTemplate || `<div>${data.text}</div>>`;
                },
                item: function (data, escape) {
                    return data.optionTemplate || `<div>${data.text}</div>>`;
                },
            },
        });

        // INITIALIZATION OF INPUT MASK
        // =======================================================
        HSCore.components.HSMask.init(".js-input-mask");
    };
})();
// End Company step form

// Select All Checkbox & Columns toggle at orders Start
$(document).on("ready", function () {
    // INITIALIZATION OF DATATABLES
    // =======================================================
    HSCore.components.HSDatatables.init($("#datatable"), {
        select: {
            style: "multi",
            selector: 'td:first-child input[type="checkbox"]',
            classMap: {
                checkAll: "#datatableCheckAll",
                counter: "#datatableCounter",
                counterInfo: "#datatableCounterInfo",
            },
        },
    });
});
// End Select All Checkbox & Columns toggle at orders Start

// Date Range Picker
$(document).on("ready", function () {
    var start = moment().subtract(29, "days");
    var end = moment();

    function cb(start, end) {
        $(
            "#js-daterangepicker-predefined .js-daterangepicker-predefined-preview"
        ).html(
            start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY")
        );
    }

    HSCore.components.HSDaterangepicker.init(
        "#js-daterangepicker-predefined",
        {
            startDate: start,
            endDate: end,
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(29, "days"), moment()],
                "This Month": [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
                "Last Month": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
        },
        cb
    );

    cb(start, end);

    $("#js-daterangepicker-predefined").on(
        "apply.daterangepicker",
        function (ev, picker) {
            $(this)
                .find(".js-daterangepicker-predefined-preview")
                .html(
                    picker.startDate.format("MMMM D, YYYY") +
                        " - " +
                        picker.endDate.format("MMMM D, YYYY")
                );
        }
    );
});
// End Date Range Picker
//select and unselect all checkboxes
$(document).ready(function () {
    $('#selectAllInput').val('false');
    var total = total_orders??0;
    function updateSelectedCount() {
        const totalCheckboxes = $('.rowCheckbox').length;
        const checkedCheckboxes = $('.rowCheckbox:checked').length;
        $('#datatableWithCheckboxSelectCounter').text(checkedCheckboxes); // Update the count display
        if(checkedCheckboxes>0){
            $('#datatableWithCheckboxSelectCounterInfo').css({           
                'display': 'block'
            });
            if(totalCheckboxes===checkedCheckboxes && checkedCheckboxes<total){
                $('#datatableWithCheckboxSelectAllCounter').text(total);
                $('#datatableWithCheckboxSelectAllCounterInfo').css({           
                    'display': 'block'
                });
            }else{
                $('#selectAllInput').val('false');
                $('datatableWithCheckboxSelectAllCounterInfo').css('color', '');
                $('#datatableWithCheckboxSelectAllCounterInfo').css({           
                    'display': 'none'
                }); 
            }
        }else{
            $('#datatableWithCheckboxSelectCounterInfo').css({           
                'display': 'none'
            }); 
            $('#selectAllInput').val('false');
            $('datatableWithCheckboxSelectAllCounterInfo').css('color', '');
            $('#datatableWithCheckboxSelectAllCounterInfo').css({           
                'display': 'none'
            }); 
        }
        
    }

    // Handle "Select All" checkbox
    $('#datatableWithCheckboxSelectAll').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.rowCheckbox').prop('checked', isChecked);
        updateSelectedCount(); // Update the count when "Select All" is clicked

    });

     // Handle "Select All" checkbox
    $('#datatableWithCheckboxSelectAllCounterInfo').on('click', function () {
        const currentColor = $(this).css('color');

        if (currentColor === 'rgb(0, 0, 255)' || currentColor === 'blue') {
            $(this).css('color', ''); // remove color
            $('#selectAllInput').val('false');
        } else {
            $(this).css('color', 'blue');
            $('#selectAllInput').val('true');
        }
    });

    // Handle individual row checkboxes
    $('.rowCheckbox').on('change', function () {
        const totalCheckboxes = $('.rowCheckbox').length;
        const checkedCheckboxes = $('.rowCheckbox:checked').length;
        // Toggle the "Select All" checkbox state
        $('#datatableWithCheckboxSelectAll').prop('checked', totalCheckboxes === checkedCheckboxes);        
        updateSelectedCount(); // Update the count when an individual checkbox is toggled
    });

    // Initialize the selected count
    updateSelectedCount();
});

/**
 * Fetch states for a country and populate the given state <select>.
 * @param {String} countryCode
 * @param {String} stateSelectId - id attribute of the state <select> to populate (e.g. 's_state_code')
 * @param {String|null} selectedStateCode - state code to pre-select (optional)
 */
function fetchStates(countryCode,selectedStateCode = null,stateSelectId = 'state' ) {
    if (!countryCode) {
        // Clear the target select if no country
        const emptySel = document.getElementById(stateSelectId);
        if (emptySel) emptySel.innerHTML = '<option value="">Select State</option>';
        return;
    }

    const stateUrl = routes.states; // expects a URL template with :country_code
    const url = stateUrl.replace(':country_code', encodeURIComponent(countryCode));

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const stateSelect = document.getElementById(stateSelectId);
            if (!stateSelect) {
                console.warn('fetchStates: target select not found ->', stateSelectId);
                return;
            }

            // reset options
            stateSelect.innerHTML = '<option value="">Select State</option>';

            if (!Array.isArray(data) || data.length === 0) {
                return;
            }

            data.forEach(function (state) {
                // flexible keys: try multiple common names
                const code = state.state_code ?? state.code ?? state.id ?? state.stateCode;
                const name = state.state_name ?? state.name ?? state.title ?? code;

                if (!code) return;

                const option = document.createElement('option');
                option.value = code;
                option.text = name;

                if (selectedStateCode && selectedStateCode.toString() === code.toString()) {
                    option.selected = true;
                }

                stateSelect.appendChild(option);
            });
        },
        error: function (xhr, status, error) {
            console.error('Error fetching states:', error);
            // optionally display an inline error instead of alert
            // alert("Unable to fetch states. Please try again later.");
        }
    });
}


// End Function to fetch states based on country code

// Drag and Drop file upload
(function () {
    // INITIALIZATION OF DROPZONE
    // =======================================================
    HSCore.components.HSDropzone.init(".js-dropzone");
})();
// End Drag and Drop file upload

// Advanced Select
(function () {
    // INITIALIZATION OF SELECT
    // =======================================================
    HSCore.components.HSTomSelect.init(".js-select");
})();
// End Advanced Selectcj

// Bootstrap validation
(function () {
    "use strict";
    const forms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            },
            false
        );
    });
})();
// End Bootstrap validation

// File Attachments
(function() {
    // INITIALIZATION OF FILE ATTACH
    // =======================================================
    new HSFileAttach('.js-file-attach')
  })();
// End File Attachments

// INITIALIZATION OF CLIPBOARD
(function() {
    HSCore.components.HSClipboard.init('.js-clipboard')
})();
// =======================================================  