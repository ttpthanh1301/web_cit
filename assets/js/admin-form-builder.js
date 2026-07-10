(function () {
    'use strict';

    const select = document.getElementById('field_type');
    const panel = document.getElementById('optionPanel');
    const options = document.getElementById('options');

    if (!select || !panel || !options) {
        return;
    }

    function syncOptionsPanel() {
        const visible = ['dropdown', 'radio', 'checkbox'].includes(select.value);
        panel.classList.toggle('is-visible', visible);
        options.required = visible;
    }

    select.addEventListener('change', syncOptionsPanel);
    syncOptionsPanel();
})();
