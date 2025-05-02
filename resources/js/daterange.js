import $ from "jquery";
window.$ = $;
import moment from 'moment';
window.moment = moment;
import './daterangepicker.js';

$(function () {
    $(function () {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left',

            ranges: {
                'Expired 7 Days': [moment().subtract(7, 'days'), moment()],
                'Expired 15 Days': [moment().subtract(15, 'days'), moment()],
                'Expired 30 Days': [moment().subtract(30, 'days'), moment()],
                'Expiring 7 Days': [moment(), moment().add(7, 'days')],
                'Expiring 15 Days': [moment(), moment().add(15, 'days')],
                'Expiring 30 Days': [moment(), moment().add(30, 'days')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            },
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false
        });

        $('input[name="daterange"]').on('apply.daterangepicker', function (ev, picker) {
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            $('#dateRangeForm').submit();
        });

        $('input[name="daterange"]').on('cancel.daterangepicker', function (ev, picker) {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#daterange').val('');
        });
    })
});




