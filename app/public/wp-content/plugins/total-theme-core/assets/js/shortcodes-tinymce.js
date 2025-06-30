( function( ) {

	if ( 'undefined' !== typeof wpexTinymce && wpexTinymce ) {

		tinymce.PluginManager.add( 'wpex_shortcodes_mce_button', function( editor, url ) {

			var menuData = [];

			var shortcodes = wpexTinymce.shortcodes;

			jQuery.each( shortcodes, function( key, valueObj ) {

				var $obj = {
					text: valueObj.text,
					onclick: function() {
						editor.insertContent( valueObj.insert );
					}
				};

				menuData.push( $obj );

			} );

			// Add button data
			editor.addButton( 'wpex_shortcodes_mce_button', {

				text : wpexTinymce.btnLabel,
				type : 'menubutton',
				icon : false,
				menu : menuData

			} );

		} );

	}

} ) ( );