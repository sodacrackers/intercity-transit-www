(function($, document, window, viewport){
    var showTable = function( table ) {
        $(table).addClass('hidden');
    }
    var tableHider = function() {
        $('.table').removeClass('active');
        if( viewport.is("<=md") ) {
            showTable('.small-screen-route-table');
        }
        if( viewport.is(">md") ) {
            showTable('.large-screen-route-table');
        }
    }

   
    // Executes once whole document has been loaded

    tableHider();


    $(window).resize(
        viewport.changed(function(){
            tableHider();
        })
    );
})(jQuery, document, window);