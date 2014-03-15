/**
 * JavasScript code for the preview in the move plugin
 *
 * @author Michael Hamann <michael@content-space.de>
 */

jQuery(function() {
    jQuery('form.move__form').each(function() {
        var $this = jQuery(this);
        var $preview = jQuery('<p></p>');
        $this.find('input[type=submit]').before($preview);
        var updatePreview = function() {
            if ($this.find('input[name=move_type]').val() == 'namespace') {
                var targetns = $this.find('select[name=targetns]').val();
                var newnsname = $this.find('input[name=newnsname]').val();
                var previewns;
                if (targetns == ':') {
                    previewns = newnsname;
                } else {
                    previewns = targetns + ':' + newnsname;
                }
                $preview.text(LANG['plugins']['move']['previewns'].replace('OLDNS', JSINFO['namespace']).replace('NEWNS', previewns));
            } else {
                var ns_for_page = $this.find('select[name=ns_for_page]').val();
                var newns = $this.find('input[name=newns]').val();
                var newname = $this.find('input[name=newname]').val();
                var newid = '';
                if (typeof newns == 'undefined') {
                    return;
                }
                if (newns.replace(/\s/g) != '') {
                    newid = newns + ':';
                } else if (ns_for_page != ':') {
                    newid = ns_for_page + ':';
                }
                newid += newname;
                $preview.text(LANG['plugins']['move']['previewpage'].replace('OLDPAGE', JSINFO['id']).replace('NEWPAGE', newid));

            }
        };
        updatePreview();
        $this.find('input,select').change(updatePreview);
        $this.find('input').keyup(updatePreview);
    });

    jQuery('form.move__nscontinue').each(function() {
        var $this = jQuery(this);
        var $container = jQuery('div.plugin__move_forms');
        var submit_handler = function() {
            $container.empty();
            var $progressbar = jQuery('<div></div>');
            $container.append($progressbar);
            $progressbar.progressbar({value: false});
            var $message = jQuery('<div></div>');
            $container.append($message);
            var skip = jQuery(this).hasClass('move__nsskip');

            var continue_move = function() {
                jQuery.post(
                    DOKU_BASE + 'lib/exe/ajax.php',
                    {
                        call: 'plugin_move_ns_continue',
                        id: JSINFO['id'],
                        skip: skip
                    },
                    function(data) {
                        if (data.remaining === false) {
                            $progressbar.progressbar('option', 'value', false);
                        } else {
                            $progressbar.progressbar('option', 'value', data.pages + data.media - data.remaining);
                            $progressbar.progressbar('option', 'max', data.pages + data.media);
                        }
                        $message.html(data.html);
                        if (data.remaining === false) {
                            $container.find('form.move__nscontinue, form.move__nsskip').submit(submit_handler);
                        } else if (data.remaining === 0) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.setTimeout(continue_move, 200);
                        }
                    },
                    'json'
                );
                skip = false;
            };

            continue_move();
            return false;
        };
        $this.submit(submit_handler);
    });
});