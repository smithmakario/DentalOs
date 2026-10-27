{{-- Global button/form loading states. Opt out with data-no-loading on the button or form. --}}
<style>
    .ui-btn-loading {
        position: relative !important;
        pointer-events: none !important;
        opacity: 0.72 !important;
        cursor: wait !important;
    }

    .ui-btn-loading .ui-btn-label {
        opacity: 0;
    }

    .ui-btn-loading:not(input)::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 1.1rem;
        height: 1.1rem;
        margin: -0.55rem 0 0 -0.55rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 9999px;
        animation: ui-btn-spin 0.65s linear infinite;
    }

    @keyframes ui-btn-spin {
        to { transform: rotate(360deg); }
    }
</style>
<script>
    (() => {
        if (window.__dentalOsUiLoading) {
            return;
        }
        window.__dentalOsUiLoading = true;

        const LOADING_CLASS = 'ui-btn-loading';

        const isSkipped = (el) => Boolean(el?.closest?.('[data-no-loading]'));

        const isChromeControl = (button) => {
            if (button.hasAttribute('aria-expanded') || button.hasAttribute('aria-haspopup')) {
                return true;
            }

            const text = (button.textContent || '').replace(/\s+/g, ' ').trim();

            return text.length === 0;
        };

        const wrapLabel = (button) => {
            if (button.dataset.uiLoadingPrepared === '1' || button.matches('input')) {
                return;
            }

            const label = document.createElement('span');
            label.className = 'ui-btn-label';
            while (button.firstChild) {
                label.appendChild(button.firstChild);
            }
            button.appendChild(label);
            button.dataset.uiLoadingPrepared = '1';
        };

        const setLoading = (button, { sticky = false } = {}) => {
            if (! button || isSkipped(button) || button.classList.contains(LOADING_CLASS)) {
                return;
            }

            wrapLabel(button);
            button.classList.add(LOADING_CLASS);
            button.setAttribute('aria-busy', 'true');

            if ('disabled' in button) {
                button.disabled = true;
            }

            if (! sticky) {
                window.setTimeout(() => clearLoading(button), 1200);
            }
        };

        const clearLoading = (button) => {
            if (! button) {
                return;
            }

            button.classList.remove(LOADING_CLASS);
            button.removeAttribute('aria-busy');

            if ('disabled' in button && ! button.hasAttribute('data-keep-disabled')) {
                button.disabled = false;
            }
        };

        const submitButtonsIn = (form) => Array.from(
            form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]'),
        ).filter((button) => ! isSkipped(button));

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (! (form instanceof HTMLFormElement) || isSkipped(form) || event.defaultPrevented) {
                return;
            }

            submitButtonsIn(form).forEach((button) => setLoading(button, { sticky: true }));
        }, true);

        document.addEventListener('click', (event) => {
            const button = event.target.closest('button, input[type="submit"], a[role="button"]');
            if (! (button instanceof HTMLElement) || isSkipped(button) || button.disabled) {
                return;
            }

            if (button.matches('a[role="button"]')) {
                setLoading(button, { sticky: true });

                return;
            }

            const type = (button.getAttribute('type') || 'submit').toLowerCase();

            if (type === 'reset' || type === 'submit' || button.matches('input[type="submit"]')) {
                return;
            }

            if (type === 'button' && ! isChromeControl(button)) {
                setLoading(button, { sticky: false });
            }
        }, true);

        window.addEventListener('pageshow', () => {
            document.querySelectorAll('.' + LOADING_CLASS).forEach((button) => clearLoading(button));
        });
    })();
</script>
