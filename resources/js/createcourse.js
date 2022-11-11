'use strict';

import
{
    addCheckListeners, getSessionGroupCount, getSessionCount,
    checkOverlaps, validateDate,
    getField,
    FIELD_DATE, FIELD_START, FIELD_END, getInner
} from './coursevalidation';

const EPSILON = 50;

const FIELD_LOCATION = 'session_location';
const FIELD_MAX_PPL = 'group_max_ppl';

let currentPage = 0;
const pgb = document.getElementById('progressbar');
const fsc = document.getElementById('fieldset-carousel');
const form = document.getElementById('form');

let oldGroups = [];
const GROUP_PROPS = [ FIELD_LOCATION, FIELD_DATE, FIELD_START, FIELD_END ];

/**
 * Sets the current header.
 */
function setHeader()
{
    document.getElementById('header').innerHTML = getInner(`header${currentPage}`);
}

/**
 * Pagination helper
 * @param e The event
 * @param newCurrentLeft The offset to the left of the current page
 * @param newEl How to get the new element using a property
 */
function pageHelper(e, newCurrentLeft, newEl)
{
    const current = e.target.parentNode.parentNode.parentNode.parentNode;
    current.style.left = newCurrentLeft;
    current.disabled = true;

    const nextset = current[newEl];
    nextset.style.left = '0';

    pgb.children[currentPage].classList.add('active');
    fsc.scrollTop = 0;
    fsc.style.overflowY = (currentPage > 0) ? 'auto' : 'hidden';

    setHeader();

    setTimeout(function()
    {
        nextset.disabled = false;

        const els = nextset.querySelectorAll('input,textarea');
        if(els.length > 0)
            els[0].focus();
    }, 500 + EPSILON);
}

/**
 * Previous page
 * @param e The event
 */
function next(e)
{
    currentPage++;
    pageHelper(e, '-100%', 'nextElementSibling');
}

/**
 * Next page
 * @param e The event
 */
function prev(e)
{
    // Little bit of a hack: dynamic content is always larger than the current page
    // which causes a strangely long scrollbar
    // So remove the contents to make it look right
    if(currentPage === 2)
    {
        setTimeout(function()
        {
            document.getElementById('step3').innerHTML = '';
        }, 500 - EPSILON);
    }

    pgb.children[currentPage].classList.remove('active');
    currentPage--;
    pageHelper(e, '100%', 'previousElementSibling');
}

/**
 * Converts a template to a string
 * @param id The Id of the template tag
 * @param mapObj The mapping of the variables to their values
 */
function fromTemplate(id, mapObj)
{
    const str = document.getElementById(id).innerText;
    return str.replace(new RegExp(Object.keys(mapObj).map(x => `{${x}}`).join('|'), 'g'),
                        m => mapObj[m.slice(1, -1)]);
}

/**
 * Fills a page from a template
 * @param count The count
 * @param stepId The Id of the step container
 * @param templateName The Id of the template
 * @param mapFunc The function that maps an index to a replacement object
 */
function fillPageFromTemplate(count, stepId, templateName, mapFunc)
{
    const step = document.getElementById(stepId);

    let html = '';
    for(let i = 0; i < count; i++)
    {
        const o = mapFunc(i);
        o.num = i + 1;
        html += fromTemplate(templateName, o);
    }

    step.innerHTML = html;
}

/**
 * Get description name
 * @param i The Id
 */
function getDescName(i)
{
    return `desc[${i}]`;
}

/**
 * Triggers HTML5 validation of a page (== part of the form)
 * @param stepId
 * @returns {boolean}
 */
function triggerHTML5ValidationForSubPage(stepId) {
    const elements = document.getElementById(stepId).querySelectorAll('input, select, textarea');
    let isValid = true;
    for (let i = 0; i < elements.length && isValid; i++) {
        const element = elements[i];
        if (!element.checkValidity())
            isValid = false;
    }
    if (!isValid) {
        form.classList.add('was-validated');
        form.reportValidity();
    } else {
        form.classList.remove('was-validated');
    }
    return isValid;
}

// =======================
//         ACTIONS
// =======================

const oldDescriptions = [];

document.getElementById('btnNext0').addEventListener('click', function(e)
{
    if (triggerHTML5ValidationForSubPage('fieldset-1'))
    {
        const sessionCount = getSessionCount();
        fillPageFromTemplate(sessionCount, 'step2', 'description_template', i => ({ name: getDescName(i) }));

        // Restore old descriptions
        const l = Math.min(oldDescriptions.length, sessionCount);
        for(let i = 0; i < l; i++)
            document.getElementById(getDescName(i)).value = oldDescriptions[i];

        next(e);
    }
}, false);

document.getElementById('btnPrev1').addEventListener('click', function(e)
{
    // Save old descriptions
    const sessionCount = getSessionCount();
    for(let i = 0; i < sessionCount; i++)
        oldDescriptions[i] = document.getElementById(getDescName(i)).value;

    prev(e);
}, false);

/**
 * Creates a value copy listener
 * @param g Group Id
 * @return Function
 */
function createCopyListener(g)
{
    return function(e)
    {
        const sessionCount = getSessionCount();
        for(let i = 1; i < sessionCount; i++)
        {
            const el = getField(FIELD_LOCATION, g, i);
            if(!el.value)
                el.value = this.value;
        }
    };
}

document.getElementById('btnNext1').addEventListener('click', function(e)
{
    const sessionCount = getSessionCount();
    const checks = Array(sessionCount);
    for(let i = 0; i < sessionCount; i++)
        checks[i] = getDescName(i);

    // If valid, fill in next page
    if (triggerHTML5ValidationForSubPage('fieldset-2'))
    {
        const sessionCount = getSessionCount();
        const groupCount = getSessionGroupCount();

        fillPageFromTemplate(groupCount, 'step3', 'group_header_template', i => ({ idx: i }));

        for(let g = 0; g < groupCount; g++)
        {
            fillPageFromTemplate(sessionCount, `step3_${g + 1}`, 'group_inner_template', i => ({ suffix: `[${g}][${i}]` }));

            // Locations most of the time the same, so use some autofill magic here
            getField(FIELD_LOCATION, g, 0).addEventListener('change', createCopyListener(g), false);
            //console.log('Enable copy listener for', getField(FIELD_LOCATION, g, 0));

            addCheckListeners(g);
        }

        // Restore old group data and do checks
        const l = Math.min(oldGroups.length, groupCount);
        for(let g = 0; g < l; g++)
        {
            const group = oldGroups[g];
            const sl = Math.min(group.length, sessionCount);
            for(let s = 0; s < sl; s++)
            {
                const data = group[s];
                for(const prop of GROUP_PROPS)
                    getField(prop, g, s).value = data[prop];

                // Revalidate dates
                validateDate(g, s);
            }

            // Revalidate times
            checkOverlaps(g);

            document.getElementById(`${FIELD_MAX_PPL}[${g}]`).value = group.max_ppl;
        }

        next(e);
    }
}, false);

document.getElementById('btnPrev2').addEventListener('click', function(e)
{
    const sessionCount = getSessionCount();
    const groupCount = getSessionGroupCount();

    // Save old group data
    for(let g = 0; g < groupCount; g++)
    {
        const group = oldGroups[g] = [];
        for(let s = 0; s < sessionCount; s++)
        {
            const data = group[s] = {};
            for(const prop of GROUP_PROPS)
                data[prop] = getField(prop, g, s).value;
        }

        group.max_ppl = document.getElementById(`${FIELD_MAX_PPL}[${g}]`).value;
    }

    prev(e);
}, false);

function submitFormValidationCheck() {
    form.classList.add('was-validated');
}

form.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && currentPage === 2)
        submitFormValidationCheck();
}, false);
document.getElementById('btnCreate').addEventListener('click', submitFormValidationCheck, false);
form.addEventListener('submit', function(e)
{
    document.getElementById('fieldset-1').disabled = false;
    document.getElementById('fieldset-2').disabled = false;
}, false);

setHeader();
