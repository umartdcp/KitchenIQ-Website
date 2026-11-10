(function () {
    document.querySelectorAll("[data-nav-toggle]").forEach((button) => {
        button.addEventListener("click", () => {
            button.closest(".site-header")?.classList.toggle("is-open");
        });
    });

    document.querySelectorAll("[data-eye]").forEach((button) => {
        button.addEventListener("click", () => {
            const input = button.parentElement.querySelector("input");
            if (!input) return;
            const show = input.type === "password";
            input.type = show ? "text" : "password";
            button.classList.toggle("is-revealed", show);
            button.setAttribute("aria-label", show ? "Hide password" : "Show password");
            if (!button.querySelector("svg")) {
                button.textContent = show ? "Hide" : "Show";
            }
        });
    });

    document.querySelectorAll("[data-pay]").forEach((button) => {
        button.addEventListener("click", () => {
            document.querySelectorAll("[data-pay]").forEach((other) => other.classList.remove("is-selected"));
            button.classList.add("is-selected");
            const field = document.getElementById("payment-method");
            if (field) field.value = button.dataset.pay;
        });
    });
}());
