import $ from 'jquery';
$(document).ready(function () {
    let rowCount = $('#table tr').length;
    $(`#add`).click(function () {
        rowCount++;
        const newRow = `
        <tr>
            <td>
                <input type="day" class="form-control my-1" name="inputs[${rowCount}][day]" placeholder="Enter days" />
            </td>
            <td>
                <select class="form-select mx-2" name="inputs[${rowCount}][name]">
                    <option class="text-success" value="dayBefore">days before expiration/due date</option>
                    <option class="text-danger" value="dayAfter">days after expiration/due date</option>
                </select>
            </td>
            <td>
                <x-button type="button" class="btn btn-danger mx-3 remove-table-row">
                    <i class="align-middle" data-feather="trash-2"></i>
                </x-button>
            </td>
        </tr>
    `;
        $('#table').append(newRow);
        feather.replace();
    });
});
$(document).on('click', '.remove-table-row', function () {
    $(this).parents('tr').remove();
})