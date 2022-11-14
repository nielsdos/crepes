import { atcb_init } from 'add-to-calendar-button';

(() => {
    const template = document.getElementById('add-calendar-button-inner');
    const buttonIds = atcb_init();
    for (const buttonId of buttonIds) {
        const button = document.getElementById(buttonId);
        const classList = button.classList;
        classList.add('btn', 'btn-outline-primary', 'btn-hide-label-if-small');
        classList.remove('atcb-button');
        button.innerHTML = template.innerHTML;
    }
})();
