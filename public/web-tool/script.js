flatpickr.localize(flatpickr.l10ns.vn);

const firstDayOfPreviousMonth = new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1);
const lastDayOfCurrentMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0);

const fromDateFp = flatpickr("#calendar-from-date", {
    altInput: true,
    altFormat: "d-m-Y",
    dateFormat: "Y-m-d",
    allowInput: false,
    minDate: firstDayOfPreviousMonth,
    maxDate: lastDayOfCurrentMonth,
    disable: [
        function(date) {
            return (date.getDay() === 6 || date.getDay() === 0);
        }
    ]
});

document.addEventListener("click", function(event) {
    const dropdownContent = document.getElementById("container-select");
    if (!dropdownContent.contains(event.target)) {
        toggleDropdown(true);
    }
});

function toggleDropdown(forceClose = false) {
    const dropdownContent = document.getElementById("dropdown-content");
    dropdownContent.style.display = forceClose ? "none" : (dropdownContent.style.display === "block" ? "none" : "block");
}

function selectItem(item) {
    const selectedItemLabel = document.getElementById("selected-item-label");

    if (selectedItemLabel.innerText !== item) {
        selectedItemLabel.innerText = item;
        selectedItemLabel.style.color = (item === 'Có') ? "red" : "";
        handleFilter(selectedItemLabel.innerText);
    }

    toggleDropdown(true);
}

function clearSelection() {
    const selectedItemLabel = document.getElementById("selected-item-label");

    if (selectedItemLabel.innerText !== 'Tất cả') {
        selectedItemLabel.innerText = 'Tất cả';
        selectedItemLabel.classList.remove('txt-red');
        handleFilter(selectedItemLabel.innerText);
    }

    toggleDropdown(true);
}

function handleFilter(value) {
    const selectBoxValue = value;
    const currentUrl = window.location.href;
    const urlParams = new URLSearchParams(window.location.search);

    urlParams.set('filter', selectBoxValue);
    window.location.href = currentUrl.split('?')[0] + '?' + urlParams.toString();
}
