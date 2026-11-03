(function () {
    const form = document.getElementById("menu-form");
    if (!form) return;

    const grid = document.getElementById("dish-grid");
    const count = document.getElementById("dock-count");
    const chips = document.getElementById("dock-chips");
    const total = document.getElementById("dock-total");
    const search = document.getElementById("menu-search");

    function serving() {
        const checked = form.querySelector('input[name="serving_size"]:checked');
        return checked ? Number(checked.value) : 4;
    }

    function money(amount) {
        return "PKR " + Number(amount).toLocaleString("en-PK");
    }

    function paint() {
        const people = serving();
        let sum = 0;
        let selected = 0;
        chips.innerHTML = "";

        grid.querySelectorAll(".dish-card").forEach((card) => {
            const input = card.querySelector("input");
            const qty = Math.max(0, Math.min(20, Number(input.value) || 0));
            input.value = String(qty);
            card.classList.toggle("is-picked", qty > 0);
            if (qty < 1) return;
            selected += 1;
            const price = Number(card.dataset.price);
            sum += price * people * qty;
            const chip = document.createElement("div");
            chip.className = "chip";
            chip.innerHTML = '<img alt="" src="' + card.dataset.image + '"><div><strong>' + card.querySelector("h3").textContent + '</strong><small>PKR ' + price.toLocaleString("en-PK") + " × " + people + '</small></div><span class="chip-qty">×' + qty + "</span>";
            chips.appendChild(chip);
        });

        count.textContent = String(selected);
        total.textContent = money(sum);
    }

    grid.addEventListener("click", (event) => {
        const button = event.target.closest("[data-qty]");
        if (!button) return;
        const input = button.parentElement.querySelector("input");
        input.value = String(Math.max(0, Math.min(20, (Number(input.value) || 0) + Number(button.dataset.qty))));
        paint();
    });

    form.addEventListener("change", (event) => {
        if (event.target.name === "serving_size" || event.target.matches(".qty input")) paint();
    });

    function applyFilter() {
        const active = document.querySelector("[data-filter].is-active");
        const filter = active ? active.dataset.filter : "all";
        const query = search.value.trim().toLowerCase();
        grid.querySelectorAll(".dish-card").forEach((card) => {
            const visible = (filter === "all" || card.dataset.category === filter) && card.dataset.name.includes(query);
            card.hidden = !visible;
        });
    }

    document.querySelectorAll("[data-filter]").forEach((button) => {
        button.addEventListener("click", () => {
            document.querySelectorAll("[data-filter]").forEach((other) => other.classList.remove("is-active"));
            button.classList.add("is-active");
            applyFilter();
        });
    });

    search.addEventListener("input", applyFilter);
    paint();
}());
