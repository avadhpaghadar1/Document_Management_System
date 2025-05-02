document.addEventListener('DOMContentLoaded', function() {
    const addFieldButton = document.getElementById('add-field-button');
    const fieldsTableBody = document.querySelector('#fields-table tbody');
    const form = document.getElementById('document-form');

    addFieldButton.addEventListener('click', function() {
        const fieldName = document.getElementById('field-name').value;
        const fieldType = document.getElementById('field-type').value;

        if (fieldName.trim() !== '') {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                        <td class="border border-0">${fieldName}</td>
                        <td class="border border-0">${fieldType}</td>
                        <td class="border border-0">
                            <button type="button" class="btn btn-danger remove-field-button">
                                <i data-feather="trash-2"></i>
                            </button>
                        </td>
                    `;
            fieldsTableBody.appendChild(newRow);

            newRow.querySelector('.remove-field-button').addEventListener('click', function() {
                newRow.remove();
            });

            feather.replace();

            document.getElementById('field-name').value = '';
        }
    });

    document.querySelectorAll('.remove-field-button').forEach(function(button) {
        button.addEventListener('click', function() {
            button.closest('tr').remove();
        });
    });

    form.addEventListener('submit', function(event) {
        document.querySelectorAll('.dynamic-field-input').forEach(function(input) {
            input.remove();
        });

        fieldsTableBody.querySelectorAll('tr').forEach(function(row, index) {
            const fieldName = row.cells[0].innerText.trim();
            const fieldType = row.cells[1].innerText.trim();

            const fieldNameInput = document.createElement('input');
            fieldNameInput.type = 'hidden';
            fieldNameInput.name = `fields[${index}][name]`;
            fieldNameInput.value = fieldName;
            fieldNameInput.classList.add('dynamic-field-input');

            const fieldTypeInput = document.createElement('input');
            fieldTypeInput.type = 'hidden';
            fieldTypeInput.name = `fields[${index}][type]`;
            fieldTypeInput.value = fieldType;
            fieldTypeInput.classList.add('dynamic-field-input');

            form.appendChild(fieldNameInput);
            form.appendChild(fieldTypeInput);
        });
    });
    feather.replace();
});