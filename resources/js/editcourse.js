import {
    addCheckListeners,
    getSessionGroupCount,
    addFirstDateCheckListener,
} from './coursevalidation';

{
    const gc = getSessionGroupCount();
    for(let g = 0; g < gc; g++)
    {
        addCheckListeners(g);
    }

    addFirstDateCheckListener();

    const form = document.getElementById('form');
    function reportValidity()
    {
        form.reportValidity();
    }
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter')
            reportValidity();
    }, false);
    document.getElementById('save').addEventListener('click', reportValidity, false);
    document.getElementById('save_and_back').addEventListener('click', reportValidity, false);
}
