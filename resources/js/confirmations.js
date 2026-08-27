import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const buttonClasses = {
    danger: 'it-confirm-button--danger',
    game: 'it-confirm-button--game',
    positive: 'it-confirm-button--positive',
    primary: 'it-confirm-button--primary',
};

export async function confirmAction({
    title,
    text = '',
    confirmButtonText = 'Sí, continuar',
    variant = 'primary',
    icon = 'warning',
} = {}) {
    const selectedVariant = buttonClasses[variant] ? variant : 'primary';
    const result = await Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
        allowOutsideClick: false,
        buttonsStyling: false,
        customClass: {
            popup: 'it-swal-popup',
            title: 'it-swal-title',
            htmlContainer: 'it-swal-text',
            actions: 'it-swal-actions',
            confirmButton: `it-confirm-button ${buttonClasses[selectedVariant]}`,
            cancelButton: 'it-confirm-button it-confirm-button--cancel',
        },
    });

    return result.isConfirmed;
}

export function showFeedback(message, type = 'success') {
    if (!message) {
        return Promise.resolve();
    }

    if (type === 'error') {
        return Swal.fire({
            title: 'No pudimos completar la acción',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            buttonsStyling: false,
            customClass: {
                popup: 'it-swal-popup',
                title: 'it-swal-title',
                htmlContainer: 'it-swal-text',
                actions: 'it-swal-actions',
                confirmButton: 'it-confirm-button it-confirm-button--primary',
            },
        });
    }

    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        customClass: {
            popup: 'it-swal-toast',
            title: 'it-swal-toast-title',
        },
    });
}

export function bindConfirmations(root = document) {
    root.querySelectorAll('form[data-confirm-title]').forEach((form) => {
        if (form.dataset.confirmationBound === 'true') {
            return;
        }

        form.dataset.confirmationBound = 'true';

        form.addEventListener('submit', async (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();

            const submitter = event.submitter;
            const confirmed = await confirmAction({
                title: form.dataset.confirmTitle,
                text: form.dataset.confirmText || '',
                icon: form.dataset.confirmIcon || 'warning',
                confirmButtonText: form.dataset.confirmButton || 'Sí, continuar',
                variant: form.dataset.confirmVariant || 'primary',
            });

            if (!confirmed) {
                return;
            }

            form.dataset.confirmed = 'true';

            if (submitter) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
        });
    });
}

export async function bindFlashFeedback(root = document) {
    const feedbackNodes = root.querySelectorAll('[data-flash-feedback]');

    for (const node of feedbackNodes) {
        if (node.dataset.flashError) {
            await showFeedback(node.dataset.flashError, 'error');
        } else if (node.dataset.flashSuccess) {
            await showFeedback(node.dataset.flashSuccess, 'success');
        }

        node.remove();
    }
}
