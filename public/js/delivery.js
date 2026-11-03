(function () {
    const node = document.getElementById("delivery-state");
    const form = document.getElementById("delivery-form");
    if (!node || !form) return;

    const initial = JSON.parse(node.textContent);
    const calendarRoot = document.getElementById("calendar-root");
    const daysRoot = document.getElementById("days-root");
    const calcRoot = document.getElementById("calc-root");
    const payload = document.getElementById("delivery-payload");
    const modeSelect = document.getElementById("per-person");
    const daysSelect = document.getElementById("days-per-week");
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const shortMonths = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const anchor = Object.keys(initial.deliveries).sort()[0] || initial.minDate;

    const state = {
        cursor: parse(anchor),
        serving: initial.serving,
        packing: initial.packing,
        mode: initial.mode,
        deliveries: initial.deliveries || {},
    };
    state.cursor.setDate(1);

    function parse(value) {
        return new Date(value + "T00:00:00");
    }

    function iso(date) {
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        return date.getFullYear() + "-" + month + "-" + day;
    }

    function money(amount) {
        return "PKR " + Number(amount).toLocaleString("en-PK");
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (ch) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[ch]));
    }

    function menuById(id) {
        return initial.menu.find((item) => item.id === Number(id)) || initial.menu[0];
    }

    function sortedDates() {
        return Object.keys(state.deliveries).sort();
    }

    function blankLine() {
        const first = initial.menu[0];
        if (state.mode === "personalized") {
            return { menu_item_id: first.id, quantity: 1, persons: state.serving };
        }
        return { menu_item_id: first.id, quantity: 1, persons: null };
    }

    function lineAmount(line) {
        const item = menuById(line.menu_item_id);
        if (!item) return 0;
        if (state.mode === "personalized") return item.price * (Number(line.persons) || 0);
        return item.price * state.serving * (Number(line.quantity) || 1);
    }

    function mirrorMenus() {
        if (initial.menusVary) return;
        const dates = sortedDates();
        if (dates.length < 2) return;
        const first = JSON.parse(JSON.stringify(state.deliveries[dates[0]]));
        dates.slice(1).forEach((date) => {
            state.deliveries[date] = JSON.parse(JSON.stringify(first));
        });
    }

    function syncPayload() {
        payload.value = JSON.stringify({ deliveries: state.deliveries });
    }

    function renderCalendar() {
        const year = state.cursor.getFullYear();
        const month = state.cursor.getMonth();
        const pad = (new Date(year, month, 1).getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        let cells = "";
        for (let i = 0; i < pad; i++) cells += '<span class="cal-empty"></span>';
        for (let day = 1; day <= daysInMonth; day++) {
            const key = iso(new Date(year, month, day));
            const disabled = key < initial.minDate;
            const selected = Boolean(state.deliveries[key]);
            cells += '<button type="button" class="cal-day' + (selected ? " is-selected" : "") + (disabled ? " is-disabled" : "") + '" data-date="' + key + '"' + (disabled ? " disabled" : "") + ">" + day + "</button>";
        }
        calendarRoot.innerHTML = '<div class="cal-head"><button type="button" class="cal-nav" data-cal="-1" aria-label="Previous month">‹</button><strong>' + months[month] + " " + year + '</strong><button type="button" class="cal-nav" data-cal="1" aria-label="Next month">›</button></div><div class="cal-grid cal-labels"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div><div class="cal-grid">' + cells + "</div>";
    }

    function renderDays() {
        const dates = sortedDates();
        if (!dates.length) {
            daysRoot.innerHTML = '<p class="muted">Select at least one delivery day. Every date must be at least 2 days from today.</p>';
            return;
        }

        daysRoot.innerHTML = dates.map((date, index) => {
            const dt = parse(date);
            const locked = !initial.menusVary && index > 0;
            const lines = state.deliveries[date].map((line, lineIndex) => {
                const item = menuById(line.menu_item_id);
                const options = initial.menu.map((dish) => '<option value="' + dish.id + '"' + (dish.id === Number(line.menu_item_id) ? " selected" : "") + ">" + escapeHtml(dish.name) + "</option>").join("");
                const control = state.mode === "personalized"
                    ? '<label class="line-field">Persons<input class="line-persons" data-date="' + date + '" data-line="' + lineIndex + '" type="number" min="1" max="' + state.serving + '" value="' + (line.persons || 1) + '"' + (locked ? " disabled" : "") + "></label>"
                    : '<label class="line-field">Qty<input class="line-qty" data-date="' + date + '" data-line="' + lineIndex + '" type="number" min="1" max="20" value="' + (line.quantity || 1) + '"' + (locked || state.packing === "pateela" ? " disabled" : "") + "></label>";
                const remove = !locked && state.deliveries[date].length > 1
                    ? '<button type="button" class="line-remove" data-date="' + date + '" data-line="' + lineIndex + '" aria-label="Remove dish">×</button>'
                    : "";
                return '<div class="day-line"><img src="' + item.image + '" alt=""><div><span class="day-kicker">Menu for ' + weekdays[dt.getDay()] + ", " + dt.getDate() + " " + shortMonths[dt.getMonth()] + '</span><select class="control line-menu" data-date="' + date + '" data-line="' + lineIndex + '"' + (locked ? " disabled" : "") + ">" + options + "</select></div>" + control + "<strong>" + money(lineAmount(line)) + "</strong>" + remove + "</div>";
            }).join("");
            const add = !locked && state.packing !== "pateela" ? '<button type="button" class="add-line" data-date="' + date + '">+ Add a dish to this day</button>' : "";
            const portions = state.mode === "personalized"
                ? '<p class="portion-note">' + state.deliveries[date].reduce((sum, line) => sum + (Number(line.persons) || 0), 0) + " / " + state.serving + " persons assigned</p>"
                : "";
            const sameNote = locked ? '<p class="muted">Same menu as the first delivery day.</p>' : "";
            return '<article class="day-card"><div class="day-badge"><span>' + weekdays[dt.getDay()] + "</span><strong>" + dt.getDate() + "</strong><small>" + shortMonths[dt.getMonth()] + '</small></div><div>' + lines + portions + sameNote + add + '</div><button type="button" class="day-remove" data-remove-date="' + date + '" aria-label="Remove delivery day">×</button></article>';
        }).join("");
    }

    function renderCalc() {
        const dates = sortedDates();
        const dishes = dates.reduce((sum, date) => sum + state.deliveries[date].reduce((lineSum, line) => lineSum + lineAmount(line), 0), 0);
        const packing = state.packing === "pateela" ? initial.fees.pateela * dates.length : initial.fees.separatePerPerson * state.serving * dates.length;
        const delivery = initial.fees.delivery * dates.length;
        const packingLabel = state.packing === "pateela" ? "Whole pateela" : "Separate packing";
        calcRoot.innerHTML = "<h3>Cost calculator</h3><p class=\"muted\">Based on the menu, " + state.serving + " persons, " + packingLabel.toLowerCase() + ", and " + dates.length + " delivery day" + (dates.length === 1 ? "" : "s") + ".</p><div class=\"cost-line\"><span>Dishes</span><span>" + money(dishes) + "</span></div><div class=\"cost-line\"><span>" + packingLabel + "</span><span>" + money(packing) + "</span></div><div class=\"cost-line\"><span>Delivery</span><span>" + money(delivery) + "</span></div><div class=\"calc-total\"><span>Total order cost</span><strong>" + money(dishes + packing + delivery) + "</strong></div>";
    }

    function render() {
        mirrorMenus();
        renderCalendar();
        renderDays();
        renderCalc();
        syncPayload();
    }

    calendarRoot.addEventListener("click", (event) => {
        const nav = event.target.closest("[data-cal]");
        if (nav) {
            state.cursor = new Date(state.cursor.getFullYear(), state.cursor.getMonth() + Number(nav.dataset.cal), 1);
            renderCalendar();
            return;
        }
        const day = event.target.closest("[data-date]");
        if (!day || day.disabled) return;
        const date = day.dataset.date;
        if (state.deliveries[date]) {
            delete state.deliveries[date];
        } else if (sortedDates().length >= 24) {
            return;
        } else {
            const dates = sortedDates();
            const template = dates.length ? state.deliveries[dates[dates.length - 1]] : [blankLine()];
            state.deliveries[date] = JSON.parse(JSON.stringify(template));
        }
        render();
    });

    daysRoot.addEventListener("click", (event) => {
        const removeDay = event.target.closest("[data-remove-date]");
        if (removeDay) {
            delete state.deliveries[removeDay.dataset.removeDate];
            render();
            return;
        }
        const removeLine = event.target.closest(".line-remove");
        if (removeLine) {
            state.deliveries[removeLine.dataset.date].splice(Number(removeLine.dataset.line), 1);
            render();
            return;
        }
        const addLine = event.target.closest(".add-line");
        if (addLine) {
            const line = blankLine();
            if (state.mode === "personalized") line.persons = 1;
            state.deliveries[addLine.dataset.date].push(line);
            render();
        }
    });

    daysRoot.addEventListener("change", (event) => {
        const date = event.target.dataset.date;
        const index = Number(event.target.dataset.line);
        const line = state.deliveries[date] && state.deliveries[date][index];
        if (!line) return;
        if (event.target.classList.contains("line-menu")) line.menu_item_id = Number(event.target.value);
        if (event.target.classList.contains("line-qty")) line.quantity = Math.max(1, Math.min(20, Number(event.target.value) || 1));
        if (event.target.classList.contains("line-persons")) line.persons = Math.max(1, Number(event.target.value) || 1);
        render();
    });

    modeSelect?.addEventListener("change", () => {
        if (modeSelect.disabled) return;
        state.mode = modeSelect.value;
        Object.keys(state.deliveries).forEach((date) => {
            const first = state.deliveries[date][0] || blankLine();
            if (state.mode === "personalized") {
                state.deliveries[date] = [{ menu_item_id: first.menu_item_id, quantity: 1, persons: state.serving }];
            } else {
                state.deliveries[date] = state.deliveries[date].map((line) => ({
                    menu_item_id: line.menu_item_id,
                    quantity: line.quantity || 1,
                    persons: null,
                }));
            }
        });
        render();
    });

    document.getElementById("suggest-days")?.addEventListener("click", () => {
        const count = Number(daysSelect.value);
        const gap = count >= 5 ? 1 : count === 3 ? 2 : count === 2 ? 3 : 1;
        const template = sortedDates().length ? state.deliveries[sortedDates()[0]] : [blankLine()];
        state.deliveries = {};
        const cursor = parse(initial.minDate);
        for (let i = 0; i < count; i++) {
            state.deliveries[iso(cursor)] = JSON.parse(JSON.stringify(template));
            cursor.setDate(cursor.getDate() + gap);
        }
        state.cursor = parse(initial.minDate);
        state.cursor.setDate(1);
        render();
    });

    document.getElementById("add-day")?.addEventListener("click", () => {
        const cursor = parse(initial.minDate);
        for (let i = 0; i < 120; i++) {
            const key = iso(cursor);
            if (!state.deliveries[key]) {
                const dates = sortedDates();
                const template = dates.length ? state.deliveries[dates[dates.length - 1]] : [blankLine()];
                state.deliveries[key] = JSON.parse(JSON.stringify(template));
                state.cursor = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
                render();
                return;
            }
            cursor.setDate(cursor.getDate() + 1);
        }
    });

    form.addEventListener("submit", syncPayload);
    render();
}());
