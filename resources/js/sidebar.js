import * as bootstrap from 'bootstrap';

const KEY = 'wms-sidebar-groups';

function load() {
    try {
        return JSON.parse(localStorage.getItem(KEY) || '{}');
    } catch {
        return {};
    }
}

function save(state) {
    try {
        localStorage.setItem(KEY, JSON.stringify(state));
    } catch {
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const panels = document.querySelectorAll('[data-wms-group]');
    if (!panels.length) return;
    const state = load();
    panels.forEach((el) => {
        const name = el.getAttribute('data-wms-group');
        const inst = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
        if (el.getAttribute('data-wms-active') === '1') {
            inst.show();
            return;
        }
        if (state[name] === true) inst.show();
        else if (state[name] === false) inst.hide();
        el.addEventListener('shown.bs.collapse', () => {
            save({ ...load(), [name]: true });
        });
        el.addEventListener('hidden.bs.collapse', () => {
            save({ ...load(), [name]: false });
        });
    });
});
