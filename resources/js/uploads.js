import Dropzone from 'dropzone';

Dropzone.autoDiscover = false;

function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
}

document.addEventListener('DOMContentLoaded', function () {
    const dropzoneEl = document.querySelector('#uploadsDropzone');
    if (dropzoneEl) {
        const dz = new Dropzone('#uploadsDropzone', {
            url: '/uploads/upload',
            method: 'post',
            autoProcessQueue: true,
            addRemoveLinks: false,
            paramName: 'file[]',
            acceptedFiles: '.pdf,.jpg,.jpeg,.png',
            clickable: true,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
            },
            success: function () {
                window.location.reload();
            },
            error: function (file, errorMessage) {
                // Keep it simple; server validation messages will show here
                // eslint-disable-next-line no-console
                console.error('Upload error', errorMessage);
            },
        });
    }

    document.querySelectorAll('.js-delete-upload').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-upload-id');
            if (!id) return;

            if (!window.confirm('Delete this upload?')) return;

            const res = await fetch('/uploads/delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: Number(id) }),
            });

            if (res.ok) {
                window.location.reload();
            }
        });
    });
});
