import $ from 'jquery';
import 'select2/dist/css/select2.min.css';

import select2 from 'select2';
select2({
    placeholder: "Select users",
    tags: true
});

$(document).ready(function () {
    $('#select').select2({
        placeholder: "Select users",
        tags: true
    });
});
