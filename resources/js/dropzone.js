import $ from 'jquery';
import Dropzone from 'dropzone';
Dropzone.autoDiscover = false;

const dropzoneRoot = document.querySelector('#myDropzone');
if (!dropzoneRoot) {
    // This script is bundled globally; only activate on pages that include #myDropzone
    // (Add/Update Document)
    // eslint-disable-next-line no-console
    // console.debug('dropzone: #myDropzone not present, skipping');
} else {

const myDropzone = new Dropzone("#myDropzone", {
    url: '/add-document-image',
    method: 'post',
    autoProcessQueue: true,
    addRemoveLinks: true,
    paramName: 'file[]',
    acceptedFiles: ".pdf,.jpg,.jpeg,.png,.doc,.gif,.xls",
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    init: function () {
        const existingFiles = document.querySelectorAll('input[name="file_names[]"]');
        existingFiles.forEach(input => {
            const mockFile = { name: input.value, size: 34512 };
            this.displayExistingFile(mockFile, `storage/temp/${input.value}`);
            this.files.push(mockFile);
            mockFile.previewElement.classList.add('dz-complete');
            mockFile.fileName = input.value;

            if (mockFile.previewElement) {
                mockFile.previewElement.style.backgroundColor = '#3498db';
                mockFile.previewElement.style.width = '150px';
                mockFile.previewElement.style.height = '150px';
                mockFile.previewElement.style.position = 'relative';
                mockFile.previewElement.style.display = 'flex';
                mockFile.previewElement.style.alignItems = 'center';
                mockFile.previewElement.style.justifyContent = 'center';

                const textOverlay = document.createElement('div');
                textOverlay.style.color = 'white';
                textOverlay.style.marginBottom = '50px';
                textOverlay.style.fontSize = '14px';
                textOverlay.style.position = 'absolute';
                mockFile.previewElement.appendChild(textOverlay);
            }
        });

        this.on("addedfile", function (file) {
            if (file.previewElement) {

                file.previewElement.style.backgroundColor = '#3498db';
                file.previewElement.style.width = '150px';
                file.previewElement.style.height = '150px';
                file.previewElement.style.position = 'relative';
                file.previewElement.style.display = 'flex';
                file.previewElement.style.alignItems = 'center';
                file.previewElement.style.justifyContent = 'center';

                const textOverlay = document.createElement('div');
                textOverlay.style.color = 'white';
                textOverlay.style.marginBottom = '50px';
                textOverlay.style.fontSize = '14px';
                textOverlay.innerText = file.name;
                textOverlay.style.position = 'absolute';
                file.previewElement.appendChild(textOverlay);

                const dzDetails = file.previewElement.querySelector('.dz-details');
                if (dzDetails) {
                    dzDetails.style.display = 'none';
                }
            }
        });

        this.on("thumbnail", function (file) {
            if (file.previewElement) {
                const imgElement = file.previewElement.querySelector('img');
                if (imgElement) {
                    imgElement.remove();
                }
            }
        });
    },
    success: function (file, response) {
        const fileName = response.file_paths[0].split('/').pop();
        file.fileName = fileName;
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'file_names[]';
        hiddenInput.value = fileName;
        hiddenInput.id = `file-input-${fileName}`;
        document.querySelector('#myForm').appendChild(hiddenInput);
    },
    error: function (response) {
        console.log('Upload Error:', response);
    },
    removedfile: function (file) {
        const fileName = file.fileName;
        const documentId = getDocumentIdFromURL();
        file.previewElement.remove();
        const path = file.dataURL !== undefined ? `temp/${fileName}` : `document_images/${fileName}`;
        
        fetch('/remove-document-image', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ path, document_id: documentId })
        }).then(response => {
            if (response.ok) {
                const hiddenInput = document.getElementById(`file-input-${fileName}`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            }
        }).catch(error => console.error('Error:', error));
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const existingImagesEl = document.getElementById('existingImages');
    if (!existingImagesEl) return;

    const existingImages = JSON.parse(existingImagesEl.value || '[]');
    existingImages.forEach(image => {
        const mockFile = { name: image.name, fileName: image.name };
        myDropzone.displayExistingFile(mockFile, mockFile.dataURL);
        myDropzone.emit("thumbnail", mockFile);
        myDropzone.emit("complete", mockFile);
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'file_names[]';
        hiddenInput.value = image.name;
        hiddenInput.id = `file-input-${image.name}`;
        document.querySelector('#myForm').appendChild(hiddenInput);
    });
});
function getDocumentIdFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

}