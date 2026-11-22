(() => {
    const rootSelector = '.admin-main';
    const dropdownSelector = `${rootSelector} select:not([multiple])`;
    const openDropdowns = new Set();
    let menuSequence = 0;

    function getFocusableElements() {
        return [...document.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )].filter(element => !element.closest('[hidden], [aria-hidden="true"]') && element.getClientRects().length > 0);
    }

    function positionMenu(dropdown) {
        if (dropdown.menu.hidden) return;

        // Shell dropdowns sit inside decorated fields (calendar icon, padding,
        // etc.), so align their menu to the full field rather than the inner
        // text trigger. This keeps Dashboard period menus the same width as
        // their visible box.
        const anchor = dropdown.control.classList.contains('admin-select-shell')
            ? dropdown.parent
            : dropdown.trigger;
        const bounds = anchor.getBoundingClientRect();
        const margin = 8;
        const width = Math.min(bounds.width, window.innerWidth - margin * 2);
        const left = Math.max(margin, Math.min(bounds.left, window.innerWidth - width - margin));
        const availableHeight = Math.max(48, window.innerHeight - bounds.bottom - 14);

        dropdown.menu.style.left = `${left}px`;
        dropdown.menu.style.top = `${bounds.bottom + 5}px`;
        dropdown.menu.style.width = `${width}px`;
        dropdown.menu.style.maxHeight = `${Math.min(320, availableHeight)}px`;
    }

    function closeDropdown(dropdown, returnFocus = false) {
        dropdown.menu.hidden = true;
        dropdown.control.classList.remove('is-open');
        dropdown.parent.classList.remove('admin-select-open');
        dropdown.trigger.setAttribute('aria-expanded', 'false');
        openDropdowns.delete(dropdown);
        if (returnFocus) dropdown.trigger.focus();
    }

    function closeOtherDropdowns(current) {
        [...openDropdowns].forEach(dropdown => {
            if (dropdown !== current) closeDropdown(dropdown);
        });
    }

    function initializeSelect(select) {
        if (select.dataset.adminDropdownReady === 'true' || !select.isConnected) return;

        const label = select.labels?.[0]?.textContent.trim()
            || select.getAttribute('aria-label')
            || select.getAttribute('name')?.replace(/[_-]+/g, ' ')
            || 'Pilih opsi';
        const originalParent = select.parentElement;
        const control = document.createElement('div');
        control.className = 'admin-select-control';
        select.classList.forEach(className => control.classList.add(className));

        if (originalParent.matches('.dashboard-period, .chart-period-select, .dashboard-status-filter, .cucian-month-field, .transaksi-month-field, .update-status-month, .pelanggan-month')) {
            control.classList.add('admin-select-shell');
        }
        if (originalParent.matches('.cucian-field, .cucian-figma-field, .paket-field')) {
            control.classList.add('admin-select-form-field');
        }
        if (select.classList.contains('new-status-select') || select.closest('td')) {
            control.classList.add('admin-select-table');
        }
        if (originalParent.matches('.segment-table-filters')) {
            control.classList.add('admin-select-segment');
        }

        select.parentNode.insertBefore(control, select);
        control.appendChild(select);

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'admin-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const value = document.createElement('span');
        value.className = 'admin-select-value';
        const arrow = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        arrow.setAttribute('viewBox', '0 0 24 24');
        arrow.setAttribute('aria-hidden', 'true');
        arrow.classList.add('admin-select-arrow');
        const arrowPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        arrowPath.setAttribute('d', 'm6 9 6 6 6-6');
        arrow.appendChild(arrowPath);
        trigger.append(value, arrow);

        const menu = document.createElement('div');
        menu.className = 'admin-select-menu';
        menu.id = `admin-select-options-${++menuSequence}`;
        menu.setAttribute('role', 'listbox');
        menu.setAttribute('aria-label', label);
        menu.hidden = true;
        trigger.setAttribute('aria-controls', menu.id);

        control.appendChild(trigger);
        document.body.appendChild(menu);

        select.dataset.adminDropdownReady = 'true';
        select.classList.add('admin-native-select');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const dropdown = { select, control, trigger, value, menu, options: [], parent: originalParent };
        let validationAttempted = false;

        function syncValue() {
            const selected = select.options[select.selectedIndex];
            value.textContent = selected?.textContent.trim() || '';
            trigger.setAttribute('aria-label', `${label}: ${value.textContent}`);
            trigger.disabled = select.disabled;
            trigger.setAttribute('aria-disabled', String(select.disabled));
            if (select.disabled) closeDropdown(dropdown);
            dropdown.options.forEach(optionButton => {
                optionButton.setAttribute('aria-selected', String(optionButton.dataset.value === select.value));
            });
            if (!select.matches(':invalid')) {
                validationAttempted = false;
                control.classList.remove('has-error');
                trigger.removeAttribute('aria-invalid');
            } else if (validationAttempted) {
                control.classList.add('has-error');
                trigger.setAttribute('aria-invalid', 'true');
            }
        }

        function renderOptions() {
            menu.replaceChildren();
            dropdown.options = [];

            [...select.options].forEach((option, index) => {
                const optionButton = document.createElement('button');
                optionButton.type = 'button';
                optionButton.className = 'admin-select-option';
                optionButton.textContent = option.textContent.trim();
                optionButton.dataset.value = option.value;
                optionButton.dataset.index = String(index);
                optionButton.setAttribute('role', 'option');
                optionButton.setAttribute('aria-selected', String(option.selected));
                optionButton.disabled = option.disabled;

                optionButton.addEventListener('click', () => {
                    if (optionButton.disabled) return;
                    select.value = optionButton.dataset.value;
                    syncValue();
                    closeDropdown(dropdown);
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });

                menu.appendChild(optionButton);
                dropdown.options.push(optionButton);
            });
            syncValue();
        }

        function openDropdown() {
            if (trigger.disabled || dropdown.options.length === 0) return;
            closeOtherDropdowns(dropdown);
            renderOptions();
            menu.hidden = false;
            control.classList.add('is-open');
            originalParent.classList.add('admin-select-open');
            trigger.setAttribute('aria-expanded', 'true');
            openDropdowns.add(dropdown);
            positionMenu(dropdown);

            const selectedIndex = Math.max(0, select.selectedIndex);
            const selectedOption = dropdown.options[selectedIndex];
            selectedOption?.focus();
            selectedOption?.scrollIntoView({ block: 'nearest' });
            requestAnimationFrame(() => positionMenu(dropdown));
        }

        function focusFromTrigger(reverse) {
            const focusable = getFocusableElements();
            const index = focusable.indexOf(trigger);
            const target = focusable[index + (reverse ? -1 : 1)];
            if (target) target.focus();
        }

        trigger.addEventListener('click', () => {
            if (menu.hidden) openDropdown();
            else closeDropdown(dropdown);
        });
        trigger.addEventListener('keydown', event => {
            if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                openDropdown();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                openDropdown();
                dropdown.options.at(-1)?.focus();
            }
        });

        menu.addEventListener('keydown', event => {
            const currentIndex = dropdown.options.indexOf(document.activeElement);
            let nextIndex = currentIndex;
            if (event.key === 'ArrowDown') nextIndex = Math.min(dropdown.options.length - 1, currentIndex + 1);
            else if (event.key === 'ArrowUp') nextIndex = Math.max(0, currentIndex - 1);
            else if (event.key === 'Home') nextIndex = 0;
            else if (event.key === 'End') nextIndex = dropdown.options.length - 1;
            else if (event.key === 'Escape') {
                event.preventDefault();
                closeDropdown(dropdown, true);
                return;
            } else if (event.key === 'Tab') {
                event.preventDefault();
                closeDropdown(dropdown);
                focusFromTrigger(event.shiftKey);
                return;
            } else {
                return;
            }

            event.preventDefault();
            while (nextIndex >= 0 && nextIndex < dropdown.options.length && dropdown.options[nextIndex].disabled) {
                nextIndex += event.key === 'ArrowUp' ? -1 : 1;
            }
            dropdown.options[nextIndex]?.focus();
            dropdown.options[nextIndex]?.scrollIntoView({ block: 'nearest' });
        });

        select.addEventListener('change', syncValue);
        select.addEventListener('invalid', event => {
            event.preventDefault();
            validationAttempted = true;
            control.classList.add('has-error');
            trigger.setAttribute('aria-invalid', 'true');
            trigger.focus();
            openDropdown();
        });
        select.addEventListener('input', syncValue);
        select.form?.addEventListener('reset', () => window.setTimeout(syncValue));

        renderOptions();

        const optionObserver = new MutationObserver(() => {
            renderOptions();
            if (menu.hidden === false) positionMenu(dropdown);
        });
        optionObserver.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'selected'] });
        const stateObserver = new MutationObserver(syncValue);
        stateObserver.observe(select, { attributes: true, attributeFilter: ['disabled'] });
    }

    function initializeAll(root = document) {
        root.querySelectorAll?.(dropdownSelector).forEach(initializeSelect);
        if (root.matches?.('select:not([multiple])') && root.matches(rootSelector + ' select')) initializeSelect(root);
    }

    function start() {
        initializeAll();

        const root = document.querySelector(rootSelector);
        if (!root) return;

        const observer = new MutationObserver(records => {
            records.forEach(record => record.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) initializeAll(node);
            }));
        });
        observer.observe(root, { childList: true, subtree: true });

        document.addEventListener('click', event => {
            openDropdowns.forEach(dropdown => {
                if (!dropdown.control.contains(event.target) && !dropdown.menu.contains(event.target)) closeDropdown(dropdown);
            });
        });

        const repositionOpenMenus = () => openDropdowns.forEach(positionMenu);
        window.addEventListener('resize', repositionOpenMenus);
        window.addEventListener('scroll', repositionOpenMenus, true);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
})();
