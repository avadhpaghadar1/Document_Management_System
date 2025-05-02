import $ from 'jquery';
document.addEventListener('DOMContentLoaded', function () {


    const addUserCheckbox = document.getElementById('add_user');
    const editUserCheckbox = document.getElementById('edit_user');
    const deleteUserCheckbox = document.getElementById('delete_user');
    const viewUserCheckbox = document.getElementById('view_user');
    const addGroupCheckbox = document.getElementById('create_group');
    const editGroupCheckbox = document.getElementById('edit_group');
    const deleteGroupCheckbox = document.getElementById('delete_group');
    const viewGroupCheckbox = document.getElementById('view_group');
    const addDocumentTypeCheckbox = document.getElementById('create_document_type');
    const editDocumentTypeCheckbox = document.getElementById('edit_document_type');
    const deleteDocumentTypeCheckbox = document.getElementById('delete_document_type');
    const viewDocumentTypeCheckbox = document.getElementById('view_document_type');

    function handleUserCheckboxChange() {
        viewUserCheckbox.checked = addUserCheckbox.checked || editUserCheckbox.checked || deleteUserCheckbox.checked;
    }
    function handleGroupCheckboxChange() {
        viewGroupCheckbox.checked = addGroupCheckbox.checked || editGroupCheckbox.checked || deleteGroupCheckbox.checked;
    }
    function handleDocumentTypeCheckboxChange() {
        viewDocumentTypeCheckbox.checked = addDocumentTypeCheckbox.checked || editDocumentTypeCheckbox.checked || deleteDocumentTypeCheckbox.checked;
    }

    addUserCheckbox.addEventListener('change', handleUserCheckboxChange);
    editUserCheckbox.addEventListener('change', handleUserCheckboxChange);
    deleteUserCheckbox.addEventListener('change', handleUserCheckboxChange);

    addGroupCheckbox.addEventListener('change', handleGroupCheckboxChange);
    editGroupCheckbox.addEventListener('change', handleGroupCheckboxChange);
    deleteGroupCheckbox.addEventListener('change', handleGroupCheckboxChange);

    addDocumentTypeCheckbox.addEventListener('change', handleDocumentTypeCheckboxChange);
    editDocumentTypeCheckbox.addEventListener('change', handleDocumentTypeCheckboxChange);
    deleteDocumentTypeCheckbox.addEventListener('change', handleDocumentTypeCheckboxChange);

});