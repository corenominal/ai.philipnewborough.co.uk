document.addEventListener("DOMContentLoaded", function() {

    // Mark the /admin sidebar link as active
    document.querySelectorAll("#sidebar .nav-link").forEach(link => {
        if (link.getAttribute("href") === "/admin") {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });

    // Greeting with current date
    const greeting = document.getElementById("dashboard-greeting");
    if (greeting) {
        const now  = new Date();
        const hour = now.getHours();
        const tod  = hour < 12 ? "morning" : hour < 17 ? "afternoon" : "evening";
        const date = now.toLocaleDateString("en-GB", { weekday: "long", day: "numeric", month: "long", year: "numeric" });
        greeting.textContent = `Good ${tod} — ${date}`;
    }

});
