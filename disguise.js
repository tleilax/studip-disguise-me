jQuery(function ($) {
    function getUserLink(username) {
        return STUDIP.URLHelper.getURL('plugins.php/disguiseme/as', {username: username});
    }
    
    if (location.href.match(/\bdispatch\.php\/profile\b/) && location.search.match(/[?&]username=/)) {
        var username = decodeURIComponent(location.search.match(/[\?&]username=([^&$]+)/)[1]);
        $('<a class="disguise-me">').attr('href', getUserLink(username))
            .text('Als dieser Nutzer einloggen'.toLocaleString())
            .before('<br>')
            .appendTo('#layout_content td:first');
    }
    
    if (location.href.match(/\bdispatch\.php\/admin\/user\//)) {
        $('#layout_content form:last tbody tr').each(function () {
            var username = $('td:eq(1)', this).text().trim(),
                link     = $('<a class="disguise-me">');
            link.attr('href', getUserLink(username));
            link.attr('title', 'Als dieser Nutzer einloggen'.toLocaleString());
            $('td:last', this).prepend(link);
        });
    }
    
    if ($('#disguised').length > 0) {
        $('body').addClass('disguised');

        $(document).on('click', 'a[href$="logout.php"]', function () {
            $(this).attr('href', STUDIP.URLHelper.getURL('plugins.php/disguiseme/logout'));
        });
    }    
});
