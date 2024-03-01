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
