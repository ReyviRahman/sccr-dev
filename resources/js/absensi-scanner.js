let inputElement = null;
let scannerIdleTimer = null;
let lastSubmittedValue = "";
let lastSubmittedAt = 0;

function normalizeScannerValue(value) {
    return (value || "").replace(/\D+/g, "").trim();
}

function focusInput() {
    if (!inputElement) return;

    setTimeout(() => {
        inputElement.focus();
        inputElement.select?.();
    }, 100);
}

function getLivewireComponent() {
    if (!inputElement || !window.Livewire) return null;

    const root = inputElement.closest("[wire\\:id]");
    if (!root) return null;

    const componentId = root.getAttribute("wire:id");
    if (!componentId) return null;

    return window.Livewire.find(componentId);
}

function submitScannerValue() {
    if (!inputElement) return;

    const normalized = normalizeScannerValue(inputElement.value);
    if (!normalized) return;

    const now = Date.now();

    // Hindari double submit dalam 1 detik
    if (lastSubmittedValue === normalized && now - lastSubmittedAt <= 1000) {
        return;
    }

    lastSubmittedValue = normalized;
    lastSubmittedAt = now;

    const component = getLivewireComponent();
    if (!component) return;

    component.call("processAttendance", normalized);
}

function handleScannerInput() {
    if (!inputElement) return;

    const normalized = normalizeScannerValue(inputElement.value);

    if (normalized !== inputElement.value) {
        inputElement.value = normalized;
    }

    clearTimeout(scannerIdleTimer);

    if (!normalized) return;

    // Scanner biasanya kirim burst karakter sangat cepat.
    // Saat idle sebentar, anggap scan selesai lalu langsung proses.
    scannerIdleTimer = setTimeout(() => {
        submitScannerValue();
    }, 80);
}

function bindScannerEvents() {
    inputElement = document.getElementById("nipInput");
    if (!inputElement) return;

    // reset clone supaya tidak numpuk listener saat hot reload / rerender
    const newInput = inputElement.cloneNode(true);
    inputElement.parentNode.replaceChild(newInput, inputElement);
    inputElement = newInput;

    focusInput();

    inputElement.addEventListener("input", handleScannerInput);

    inputElement.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            clearTimeout(scannerIdleTimer);
            submitScannerValue();
        }
    });
}

function initScanner() {
    bindScannerEvents();
}

document.addEventListener("DOMContentLoaded", () => {
    initScanner();
});

document.addEventListener("livewire:init", () => {
    initScanner();
});

document.addEventListener("livewire:navigated", () => {
    initScanner();
});

window.addEventListener("focus", () => {
    focusInput();
});

document.addEventListener("livewire:initialized", () => {
    if (!window.Livewire) return;

    window.Livewire.on("focusInput", () => {
        if (inputElement) {
            inputElement.value = "";
            clearTimeout(scannerIdleTimer);
        }
        focusInput();
    });

    window.Livewire.on("resetInput", () => {
        if (inputElement) {
            inputElement.value = "";
            clearTimeout(scannerIdleTimer);
        }
    });

    window.Livewire.on("scannerToast", () => {
        focusInput();
    });
});
