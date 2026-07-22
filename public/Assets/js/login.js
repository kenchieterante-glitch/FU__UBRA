// ========================================
// FU-UBRA LOGIN JAVASCRIPT
// Foundation University
// ========================================

// Show / Hide Password
function togglePassword() {

    const password = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");

    if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");

    }

}


// ========================================
// Login Card Animation
// ========================================

window.addEventListener("load", () => {

    const card = document.querySelector(".login-card");

    card.style.opacity = "0";
    card.style.transform = "translateY(40px)";

    setTimeout(() => {

        card.style.transition = "all .8s ease";

        card.style.opacity = "1";
        card.style.transform = "translateY(0px)";

    }, 200);

});


// ========================================
// Button Loading Effect
// ========================================

const form = document.querySelector("form");

if (form) {

    form.addEventListener("submit", function () {

        const btn = document.querySelector(".login-btn");

        btn.innerHTML = "<i class='fa fa-spinner fa-spin'></i> Signing In...";

        btn.disabled = true;

    });

}


// ========================================
// Input Focus Effect
// ========================================

const inputs = document.querySelectorAll("input");

inputs.forEach(input => {

    input.addEventListener("focus", () => {

        input.parentElement.style.borderColor = "#701A25";

    });

    input.addEventListener("blur", () => {

        input.parentElement.style.borderColor = "#ddd";

    });

});


// ========================================
// Press Enter Animation
// ========================================

document.addEventListener("keydown", (e) => {

    if (e.key === "Enter") {

        const btn = document.querySelector(".login-btn");

        btn.style.transform = "scale(.98)";

        setTimeout(() => {

            btn.style.transform = "scale(1)";

        }, 150);

    }

});


// ========================================
// Remember Me
// (Frontend only for now)
// ========================================

const remember = document.querySelector("input[type='checkbox']");

if (localStorage.getItem("rememberFU") == "true") {

    remember.checked = true;

}

remember.addEventListener("change", () => {

    localStorage.setItem("rememberFU", remember.checked);

});



