'use strict';

/**
 * Get inner HTML
 * @param x The element
 * @return The HTML
 */
export function getInner(x)
{
    return document.getElementById(x).innerHTML;
}

export const FIELD_DATE = 'session_date';
export const FIELD_START = 'session_starttime';
export const FIELD_END = 'session_endtime';

const MSG_OVERLAPPING_SESSIONS = getInner('msg_overlapping_sessions');
const MSG_TIME = getInner('msg_time');
const MSG_LAST_DATE_VIOLATION = getInner('msg_last_date_violation');
const MSG_DATE_BEFORE_PREVIOUS_DATE = getInner('msg_date_before_previous_date');
const MSG_DATE_AFTER_NEXT_DATE = getInner('msg_date_after_next_date');

/**
 * Gets the count of a number element
 * @param id The Id of the element
 * @return The count
 */
function getCountOf(id)
{
    return +document.getElementById(id).value;
}

/**
 * Gets the session count
 * @return The session count
 */
export function getSessionCount()
{
    return getCountOf('session_count');
}

/**
 * Gets the amount of times the course is given (=session groups)
 * @return The amount of session groups
 */
export function getSessionGroupCount()
{
    return getCountOf('times');
}

/**
 * Sets the validity of an element
 * @param el The element
 * @param cond The condition
 * @param msg The message container
 */
function setInvalidity(el, cond, msg)
{
    if(cond)
    {
        if(!el.classList.contains('is-invalid'))
            el.classList.add('is-invalid');

        el.setCustomValidity(msg);
    }
}

/**
 * Sets invalidities
 * @param els The elements
 * @param cond The condition
 * @param msg The message container
 */
function setInvalidities(els, cond, msg)
{
    for(const el of els)
        setInvalidity(el, cond, msg);
}

/**
 * Remove invalidity
 * @param el Element
 */
export function removeInvalidity(el)
{
    el.classList.remove('is-invalid');
    el.setCustomValidity('');
}

/**
 * Creates a time listener
 * @param g The group
 */
function createTimeListener(g)
{
    return function(e)
    {
        checkOverlaps(g);
    };
}

/**
 * Checks for overlaps
 * @param g The group
 */
export function checkOverlaps(g)
{
    const sessionCount = getSessionCount();

    // Cleanup errors
    for(let i = 0; i < sessionCount; i++)
    {
        removeInvalidity(getField(FIELD_START, g, i));
        removeInvalidity(getField(FIELD_END, g, i));
    }

    // Set new errors
    let lastStart, lastEnd, lastDate;
    for(let i = 0; i < sessionCount; i++)
    {
        // If we always check if end_1 < start_2, then everything works allright
        // because we also ensure that start_1 < end_1
        const thisStart = getField(FIELD_START, g, i);
        const thisEnd = getField(FIELD_END, g, i);
        const thisDate = getField(FIELD_DATE, g, i);

        let invalid = true;

        // Local checks: check if this field is even valid
        // Requirement: start < end
        if(thisStart.value && thisEnd.value)
        {
            invalid = isTimeBeforeOrEqual(thisEnd.value, thisStart.value);
            setInvalidities([ thisStart, thisEnd ], invalid, MSG_TIME);
        }

        // Global check
        if(i > 0 && !invalid
            && lastDate.value && thisDate.value
            && lastStart.value && lastEnd.value)
        {
            setInvalidities([
                    lastStart, lastEnd, thisStart, thisEnd
                ],
                lastDate.value === thisDate.value && thisStart.value < lastEnd.value,
                MSG_OVERLAPPING_SESSIONS
            );
        }

        // Shift to next
        lastStart = thisStart;
        lastEnd = thisEnd;
        lastDate = thisDate;
    }
}

/**
 * Validates a date
 * @param g Group Id
 * @param s The current session
 */
export function validateDate(g, s)
{
    const me = getField(FIELD_DATE, g, s);
    const myDate = new Date(me.value);

    removeInvalidity(me);

    // First date must be on or after the last possible subscription date
    if(s === 0)
        setInvalidity(me, myDate < new Date(document.getElementById('last_date').value), MSG_LAST_DATE_VIOLATION);

    // The sessions before this must be before this date
    if(s > 0)
        setInvalidity(me, myDate < new Date(getField(FIELD_DATE, g, s - 1).value), MSG_DATE_BEFORE_PREVIOUS_DATE);

    // The sessions after this must be after this date
    if(s < getSessionCount() - 1)
        setInvalidity(me, myDate > new Date(getField(FIELD_DATE, g, s + 1).value), MSG_DATE_AFTER_NEXT_DATE)
}

/**
 * Creates a listener that checks the date of sessions
 * @param g Group Id
 * @param s The current session
 * @return Function
 */
function createDateListener(g, s)
{
    return function(e)
    {
        if(!this.validity.rangeUnderflow)
        {
            validateDate(g, s);
            checkOverlaps(g);
        }
    };
}

/**
 * Returns true if t1 <= t2
 * @param t1 First time
 * @param t2 Second time
 */
function isTimeBeforeOrEqual(t1, t2)
{
    // Works because of the same format means we can sort lexicographically
    return t1 <= t2;
}

/**
 * Gets a field
 * @param prefix The prefix
 * @param g The group
 * @param s The session
 */
export function getField(prefix, g, s)
{
    return document.getElementById(`${prefix}[${g}][${s}]`);
}

/**
 * Adds check listeners
 * @param g Group Id
 */
export function addCheckListeners(g)
{
    const timeListener = createTimeListener(g);

    const sessionCount = getSessionCount();
    for(let s = 0; s < sessionCount; s++)
    {
        getField(FIELD_START, g, s).onchange = timeListener;
        getField(FIELD_END, g, s).onchange = timeListener;
        getField(FIELD_DATE, g, s).onchange = createDateListener(g, s);
    }
}

/**
 * Adds check for first date listener
 */
export function addFirstDateCheckListener()
{
    const groupCount = getSessionGroupCount();
    document.getElementById('last_date').onchange = function(e)
    {
        for(let g = 0; g < groupCount; g++)
            validateDate(g, 0);
    };
}
