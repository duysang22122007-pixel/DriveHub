document.addEventListener('DOMContentLoaded', function () {
    var groupRadios = document.querySelectorAll('input[name="service_group"]');
    var serviceGroups = document.querySelectorAll('[data-booking-group]');
    var serviceRadios = document.querySelectorAll('input[name="service_key"]');
    var emptyState = document.querySelector('[data-booking-empty]');

    if (!groupRadios.length || !serviceGroups.length) {
        return;
    }

    var currentGroup = '';

    function getSelectedGroup() {
        var selected = document.querySelector('input[name="service_group"]:checked');

        return selected ? selected.value : '';
    }

    function syncServiceGroups(initial) {
        var group = getSelectedGroup();

        serviceGroups.forEach(function (element) {
            element.hidden = element.getAttribute('data-booking-group') !== group;
        });

        if (emptyState) {
            emptyState.hidden = group !== '';
        }

        if (!initial && group !== currentGroup) {
            serviceRadios.forEach(function (radio) {
                radio.checked = false;
            });
        }

        currentGroup = group;
    }

    groupRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            syncServiceGroups(false);
        });
    });

    syncServiceGroups(true);
});
