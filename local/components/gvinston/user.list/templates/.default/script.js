document.addEventListener('DOMContentLoaded', function(){

    var component_name = $('.fdb-block').data('component');

    var AjaxProcessingUser = false;

    $('body').on('click', '.bx-pagination-container a', function (evt) {
        evt.preventDefault();
        var link =  $(this).attr('href');
        if(!AjaxProcessingUser)
        {
            AjaxProcessingUser = true;
            $.ajax({
                type: "POST",
                url: link,
                data: 'component_name='+component_name,
                success: function(html){
                    $('.bx-pagination').remove();
                    $('.fdb-block').replaceWith(html);
                    window.history.pushState("","", link);
                    AjaxProcessingUser = false;
                },
                error: function(){
                    alert('Ошибка');
                    AjaxProcessingUser = false;
                }
            })
        }
    });

    var AjaxProcessingExport = false;

    $('body').on('click', 'a.export_link-js', function (evt) {
        evt.preventDefault();

        if(!AjaxProcessingExport)
        {
            AjaxProcessingExport = true;
            $.ajax({
                type: "POST",
                url:  $(this).attr('href'),
                data: 'component_name='+component_name,
                dataType: 'json',
                success: function(json){
                    console.log(json);
                    download_link(json.href);
                    AjaxProcessingExport = false;
                },
                error: function(){
                    alert('Ошибка');
                    AjaxProcessingExport = false;
                }
            })
        }
    });

});

function download_link(href) {
    let link = document.createElement('a');
    link.setAttribute('href', href);
    link.setAttribute('download','download');
    link.click();
    delete link;
}
