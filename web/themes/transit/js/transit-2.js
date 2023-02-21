(function ($) {
    $(document).ready(function(){
    // Disable search and ordering by default
        $.extend( $.fn.dataTable.defaults, {
            searching: false,
            ordering:  false
        } );

        $('#myTable').DataTable({
            paging: false,
            responsive: true,
            fixedHeader: true
        });
     });
})(jQuery);