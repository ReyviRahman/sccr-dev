import "./bootstrap";

// Livewire v3 otomatis menangani Alpine.
// Jika butuh akses global dan bootstrap.js memang sudah memuat Alpine:
document.addEventListener("livewire:init", () => {
    if (window.Alpine) {
        window.Alpine = window.Alpine;
    }
});