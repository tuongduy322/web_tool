flatpickr.localize(flatpickr.l10ns.vn);

const today = new Date();
const quarterStartMonth = Math.floor(today.getMonth() / 3) * 3;
const firstDayOfQuarter = new Date(today.getFullYear(), quarterStartMonth, 1);

const fromDateFp = flatpickr("#calendar-from-date", {
    altInput: true,
    altFormat: "d-m-Y",
    dateFormat: "Y-m-d",
    allowInput: false,
    minDate: firstDayOfQuarter,
    maxDate: today, // Update maxDate to today
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
    selectedItemLabel.classList.remove('txt-red');

    if (selectedItemLabel.innerText !== item) {
        selectedItemLabel.innerText = item;
        if ((item === 'Có')) {
            selectedItemLabel.classList.add("txt-red");
        }

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
