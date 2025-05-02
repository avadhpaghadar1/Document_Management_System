import $ from 'jquery';

// Set Input Field For Document Type 
document.addEventListener('DOMContentLoaded', function () {
    const documentTypeSelect = document.getElementById('document_type');

    const savedDocumentType = localStorage.getItem('documentType');
    if (savedDocumentType) {
        documentTypeSelect.value = savedDocumentType;
        documentTypeSelect.dispatchEvent(new Event('change'));
    }
    document.getElementById('document_type').addEventListener('change', function () {
        var documentTypeId = this.value;
        localStorage.setItem('documentType', documentTypeId);
        if (documentTypeId == "none") {
            document.getElementById('dynamic_fields_container').innerHTML = "";
        } else {
            fetch(`/document-types/${documentTypeId} `)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('dynamic_fields_container').innerHTML = html;
                    feather.replace();
                    loadFields();
                })
                .catch(error => console.error('Error fetching document fields:', error));
        }
    });

});

// Add Input Field For Document-Type
$(document).ready(function () {

    const fieldsData = JSON.parse(localStorage.getItem('fieldsData'));
    if (fieldsData) {
        fieldsData.forEach(field => {
            const newField = `
                <div class="mb-3 input-group">
                    <span class="input-group-text">${field.inputName}</span>
                    <input type="${field.inputType}" class="form-control" name="fields[${field.inputName}][value]" value="${field.inputValue}"></input>
                    <input type="hidden" name="fields[${field.inputName}][type]" value="${field.inputType}"></input>
                    <button type="button" class="btn btn-danger ms-2 remove-field">
                        <i class="align-middle" data-feather="trash-2"></i>
                    </button>
                </div>
                `;
            $('#fields-container').append(newField);
        });
        feather.replace();

    }

    $('#add-field').click(function () {
        const inputType = $('#input-type-select').val();
        const inputName = $('#input-name').val();

        const newField = `
        <div class="mb-3 input-group">
            <span class="input-group-text">${inputName}</span>
            <input type="${inputType}" class="form-control" name="fields[${inputName}][value]"></input>
            <input type="hidden" name="fields[${inputName}][type]" value="${inputType}"></input>
            <button type="button" class="btn btn-danger ms-2 remove-field">
                <i class="align-middle" data-feather="trash-2"></i>
            </button>
        </div>
        `;
        if (!inputName) {
            alert("Please Enter Field Name");
        } else {
            $('#fields-container').append(newField);
            feather.replace();
            $('#input-name').val('');
        }
    });

    $(document).on('click', '.remove-field', function () {
        $(this).closest('.input-group').remove();
    });
});


// Add Input Field For Notification 
$(document).ready(function () {
    var i = 1;

    $('#add').click(function () {
        ++i;
        $('#addNotification').append(
            `
            <div class="mb-3 d-flex w-50">
                <input type="number" class="form-control  {{ $errors->has('notification.'+${i}+'.day') ? ' is-invalid' : '' }}" name="notifications[${i}][day]" placeholder="Enter days" />
                <select class="form-select mx-2" name="notifications[${i}][name]">
                    <option class="text-success" value="dayBefore">days before expiration/due date</option>
                    <option class="text-danger" value="dayAfter">days after expiration/due date</option>
                </select>
                <button class="btn btn-danger mx-3 remove-button" type="button"><i class="align-middle" data-feather="trash-2"></i></button>
            </div>
            `
        );
        feather.replace();
    });

    $(document).on('click', '.remove-button', function () {
        $(this).closest('div.mb-3.d-flex.w-50').remove();
    });
});


// Update Group CheckBox
$(document).ready(function () {
    function updateGroupCheckbox() {
        var isEditChecked = $('#editGroup').is(':checked');
        var isDeleteChecked = $('#deleteGroup').is(':checked');

        if (isEditChecked || isDeleteChecked) {
            $('#viewGroup').prop('checked', true);
        }
    }
    $('#editGroup, #deleteGroup').on('change', function () {
        updateGroupCheckbox();
    });
});

// Add Group
$(document).ready(function () {
    feather.replace();

    const badgeClasses = {
        'view': 'badge bg-primary',
        'edit': 'badge bg-info',
        'delete': 'badge bg-danger'
    };

    let existingPermissions = [];
    $('#groupPermissionTable tbody tr').each(function () {
        let groupId = $(this).data('group-id');
        let permissions = JSON.parse($(this).find('input[name^="groupPermissions"][name$="[permissions]"]').val());
        existingPermissions.push({ groupId, permissions });
    });
    let i = existingPermissions.length;

    $('#saveGroupPermission').click(function () {
        let groupId = $('#groupSelect').val();
        let groupName = $('#groupSelect option:selected').text();
        let permissions = [];

        // Collect selected permissions
        $('input[name="groupPermissions[]"]:checked').each(function () {
            permissions.push($(this).val());
        });

        if (groupId && permissions.length > 0) {
            // Create badge HTML for each permission
            let badges = permissions.map(permission => {
                return `<span class="${badgeClasses[permission] || 'badge bg-secondary'}">${permission.charAt(0).toUpperCase() + permission.slice(1)}</span>`;
            }).join(' ');

            // Append new row to the table
            $('#groupPermissionTable tbody').append(`
            <tr data-group-id="${groupId}">
                <td class="w-25 group-name">${groupName}</td>
                <input type="hidden" name="groupPermissions[${i}][groupId]" value="${groupId}">
                <input type="hidden" name="groupPermissions[${i}][permissions]" value='${JSON.stringify(permissions)}'>
                <td class="w-50 text-center group-permissions">${badges}</td>
                <td class="w-25">
                    <button type="button" class="btn btn-danger remove-row">
                        <i class="align-middle" data-feather="trash-2"></i>
                    </button>
                </td>
            </tr>
        `);

            i++;
            feather.replace();

            // Clear form inputs
            $('#groupSelect').val('');
            $('input[name="groupPermissions[]"]').prop('checked', false);
        }
    });

    // Close the modal after saving permissions
    $('#saveGroupPermission').on('click', function () {
        $('#AddGroupModel .btn-close').click();
    });

    // Remove row functionality
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });
});



// Update User Checkbox
$(document).ready(function () {
    function updateUserCheckbox() {
        var isEditChecked = $('#editUser').is(':checked');
        var isDeleteChecked = $('#deleteUser').is(':checked');

        if (isEditChecked || isDeleteChecked) {
            $('#viewUser').prop('checked', true);
        }
    }
    $('#editUser, #deleteUser').on('change', function () {
        updateUserCheckbox();
    });
});

// Add User
$(function () {
    const badgeClasses = {
        'view': 'badge bg-primary',
        'edit': 'badge bg-info',
        'delete': 'badge bg-danger'
    };

    // Initialize existing permissions
    let existingPermissions = [];
    $('#userPermissionTable tbody tr').each(function () {
        let userId = $(this).data('user-id');
        let permissions = JSON.parse($(this).find('input[name^="userPermissions"][name$="[permissions]"]').val());
        existingPermissions.push({ userId, permissions });
    });

    let j = existingPermissions.length; // Start index for new permissions

    $('#saveUserPermission').click(function () {
        let userId = $('#userSelect').val();
        let userName = $('#userSelect option:selected').text();
        let permissions = $('input[name="userPermissions[]"]:checked').map(function () {
            return $(this).val();
        }).get();

        if (userId && permissions.length > 0) {
            let badges = permissions.map(permission => {
                return `<span class="${badgeClasses[permission] || 'badge bg-secondary'}">${permission.charAt(0).toUpperCase() + permission.slice(1)}</span>`;
            }).join(' ');

            let permissionInputs = permissions.map(permission =>
                `<input type="hidden" name="userPermissions[${j}][permissions][]" value="${permission}">`
            ).join('');

            $('#userPermissionTable tbody').append(
                `<tr data-user-id="${userId}">
                <td class="w-25 user-name">${userName}</td>
                <input type="hidden" name="userPermissions[${j}][userId]" value="${userId}">
                <input type="hidden" name="userPermissions[${j}][permissions]" value='${JSON.stringify(permissions)}'>
                <td class="w-50 text-center user-permissions">${badges}</td>
                <td class="w-25">
                    <button type="button" class="btn btn-danger remove-row">
                      <i class="align-middle" data-feather="trash-2"></i>
                    </button>
                </td>
            </tr>`
            );
            j++;
            feather.replace();
            $('#userSelect').val('');
            $('input[name="userPermissions[]"]').prop('checked', false);
        }
    });

    $('#saveUserPermission').on('click', function () {
        $('#AddUserModel .btn-close').click();
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });
});

// Owner Select
$(document).ready(function () {
    $('#users').select2({
        placeholder: "Select User",
        tags: true
    });
});

// Search Document
$('#search').on('keyup', function () {
    $value = $(this).val();
    $.ajax({
        type: 'get',
        url: "{{'document')}}",
        data: { 'search': $value },
        success: function (data) {
            $('tbody').html(data);
        }
    });
});    