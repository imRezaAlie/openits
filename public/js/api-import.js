(function ($) {
    'use strict';

    $(document).ready(function () {
        const importUrl = window.location.pathname.includes('/apis')
            ? '/apis/import'
            : $('form#importForm').closest('[data-import-url]').data('import-url') || '/apis/import';

        $('#import-type').on('change', function () {
            const isOpenApi = $(this).val() === 'openapi';
            $('#base-url-field').toggle(isOpenApi);
            $('#import-file-hint').text(isOpenApi
                ? 'Accepted: .json, .yaml, .yml'
                : 'Accepted: .wsdl, .xml');
            $('#import-file').attr('accept', isOpenApi ? '.json,.yaml,.yml' : '.wsdl,.xml');
        });

        const dropzone = document.getElementById('import-dropzone');
        const fileInput = document.getElementById('import-file');

        if (dropzone && fileInput) {
            dropzone.addEventListener('dragover', function (e) {
                e.preventDefault();
                dropzone.classList.add('border-primary');
            });
            dropzone.addEventListener('dragleave', function () {
                dropzone.classList.remove('border-primary');
            });
            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                dropzone.classList.remove('border-primary');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                }
            });
        }

        $('#importSubmitBtn').on('click', function () {
            const form = document.getElementById('importForm');
            const formData = new FormData(form);
            const resultEl = $('#import-result');
            const progressEl = $('#import-progress');

            if (!fileInput.files.length) {
                resultEl.removeClass('d-none alert-success').addClass('alert-danger').text('Please select a file.');
                return;
            }

            progressEl.removeClass('d-none');
            resultEl.addClass('d-none');
            $('#importSubmitBtn').prop('disabled', true);

            $.ajax({
                url: importUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    progressEl.addClass('d-none');
                    resultEl.removeClass('d-none alert-danger').addClass('alert-success').text(response.message);
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: response.message, duration: 3000, gravity: 'top', position: 'right', style: { background: '#198754' } }).showToast();
                    }
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                },
                error: function (xhr) {
                    progressEl.addClass('d-none');
                    const msg = xhr.responseJSON?.message || 'Import failed.';
                    resultEl.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
                },
                complete: function () {
                    $('#importSubmitBtn').prop('disabled', false);
                }
            });
        });
    });
})(jQuery);
