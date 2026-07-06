(function () {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    const titleEl = modal.querySelector('[data-confirm-title]');
    const messageEl = modal.querySelector('[data-confirm-message]');
    const acceptBtn = modal.querySelector('[data-confirm-accept]');
    const cancelBtn = modal.querySelector('[data-confirm-cancel]');
    const backdrop = modal.querySelector('[data-confirm-dismiss]');

    let pendingForm = null;

    function open(form) {
        pendingForm = form;
        titleEl.textContent = form.dataset.confirmTitle || 'Confirmation';
        messageEl.textContent = form.dataset.confirm || 'Confirmer cette action ?';

        const variant = form.dataset.confirmVariant || 'primary';
        acceptBtn.className = variant === 'danger' ? 'btn btn-danger' : 'btn btn-primary';
        acceptBtn.textContent = form.dataset.confirmAccept || 'Confirmer';

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('confirm-modal--open');
        cancelBtn.focus();
    }

    function close() {
        modal.classList.remove('confirm-modal--open');
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        pendingForm = null;
    }

    function accept() {
        if (!pendingForm) return;
        pendingForm.dataset.confirmed = 'true';
        pendingForm.submit();
        close();
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            open(form);
        });
    });

    acceptBtn.addEventListener('click', accept);
    cancelBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('confirm-modal--open')) return;
        if (event.key === 'Escape') close();
    });
})();
