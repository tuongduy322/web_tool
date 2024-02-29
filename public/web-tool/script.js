flatpickr.localize(flatpickr.l10ns.vn);

const firstDayOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
const lastDayOfMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0);

const fromDateFp = flatpickr("#calendar-from-date", {
    altInput: true,
    altFormat: "d-m-Y",
    dateFormat: "Y-m-d",
    allowInput: false,
    minDate: firstDayOfMonth,
    maxDate: lastDayOfMonth,
    disable: [
        function(date) {
            return (date.getDay() === 6 || date.getDay() === 0);
        }
    ]
});

// const toDateFp = flatpickr("#calendar-to-date", {
//     altInput: true,
//     altFormat: "d-m-Y",
//     dateFormat: "Y-m-d",
//     allowInput: false,
//     minDate: firstDayOfMonth,
//     maxDate: lastDayOfMonth,
// });
