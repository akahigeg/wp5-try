(function($) {
    $("input:button[name=ajax]").on('click', function(){
        $.ajax({
            type:      "GET",
            url:       "admin-ajax.php?action=static-post-type",
            dataType:  "json",
            async:     true
        }).done(function(callback){
            alert('ok');
            //
        }).fail(function(XMLHttpRequest, textStatus, errorThrown){
            alert('ng');
            //
        });
        return;
    });
})(jQuery);
