/**
 * @source http://jsfiddle.net/kylemit/x9tgy/
 *
 * Example usage:
 *

 $("#something").contextMenu({
	menuSelector: "#contextMenu",
	menuSelected: function (invokedOn, selectedMenu) {
		var msg = "You selected the menu item '" + selectedMenu.text() +
				"' on the value '" + invokedOn.text() + "'";
		alert(msg);
	}
});

 <ul id="contextMenu" class="dropdown-menu" role="menu">
 <li><a tabindex="-1" href="#">Action</a></li>
 <li><a tabindex="-1" href="#">Another action</a></li>
 <li><a tabindex="-1" href="#">Something else here</a></li>
 <li class="divider"></li>
 <li><a tabindex="-1" href="#">Separated link</a></li>
 </ul>

 */
(function ($, window) {

    $.fn.contextMenu = function (settings) {

        function getMenuPosition(mouse, direction, scrollDir) {
            var win = $(window)[direction](),
                scroll = $(window)[scrollDir](),
                menu = $(settings.menuSelector)[direction](),
                position = mouse + scroll;

            // opening menu would pass the side of the page
            if (mouse + menu > win && menu < mouse) {
                position -= menu;
            }

            return position;
        }

        return this.each(function () {

            // Open context menu
            $(this).on('contextmenu', function (e) {

                // return native menu if pressing control
                if (e.ctrlKey) {
                    return;
                }

                //open menu
                $(settings.menuSelector)
                    .data('invokedOn', $(e.target))
                    .show()
                    .css({
                        position: 'absolute',
                        left: getMenuPosition(e.clientX, 'width', 'scrollLeft'),
                        top: getMenuPosition(e.clientY, 'height', 'scrollTop')
                    })
                    .off('click')
                    .on('click', function (e) {
                        $(this).hide();

                        var $invokedOn = $(this).data('invokedOn');
                        var $selectedMenu = $(e.target);

                        settings.menuSelected.call(this, $invokedOn, $selectedMenu);
                    });

                return false;
            });

            //make sure menu closes on any click
            $(document).click(function () {
                $(settings.menuSelector).hide();
            });
        });
    };
}(jQuery, window));
