"use strict";

/**
 * Get tested up to version of a specific plugin
 */
function pluginCompatibilityInfoDisplayVersion( plugin, element ) {

	var data = {
		'action': 'plugin_compatibility_info_get_version',
		'plugin_compatibility_info_plugin_file': plugin
	};

	jQuery.post( ajaxurl, data, function(response) {
		if ( response.success == 'true' ) {
			element.replaceWith( response.output );
		} else {
			return false;
		}
	});

}

/**
 * Document ready
 */
jQuery(document).ready(function(){

	// hook fired by WordPress after plugin update
	jQuery(document).on('wp-plugin-update-success', function( event, response ) {
		
		// make sure plugin was updated ( might not be needed, just in case )
		if ( response.update != 'plugin' ) {
			return;
		}

		// make sure it's successful update
		if ( 'wp-' + response.update + '-update-success' === event.type ) {
			
			// the version element
			var element = jQuery('[data-plugin-compatibility-info-slug="' + response.slug + '"]' );

			// make sure the element exists
			if ( element.length ) {

				// get and display the new version
				pluginCompatibilityInfoDisplayVersion( response.plugin, element );			

			}

		}

	});

});