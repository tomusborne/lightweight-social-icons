jQuery(document).ready(function($) {
	function lsi_updatePlaceholders(){
		$('#widgets-right .choose-icon').each(function(){
			jQuery(this).change(function() {
				var select = jQuery(this);

				if ( jQuery(this).attr('value') == 'phone' ) {
					jQuery(this).next('input').attr('placeholder',lsiPlaceholder.phone);
				} else if ( jQuery(this).attr('value') == 'email' ) {
					jQuery(this).next().attr('placeholder',lsiPlaceholder.email);
				} else if ( jQuery(this).attr('value') == 'skype' ) {
					jQuery(this).next().attr('placeholder',lsiPlaceholder.username);
				}else if ( jQuery(this).attr('value') == '' ) {
					jQuery(this).next().attr('placeholder','');
				} else {
					jQuery(this).next().attr('placeholder','http://');
				}
			});
		});
	}
	lsi_updatePlaceholders();
	$(document).ajaxSuccess(function(e, xhr, settings) {
		if (typeof settings.data.search !== 'undefined' && $.isFunction(settings.data.search)) {
			if(settings.data.search('action=save-widget') != -1 ) {
				lsi_updatePlaceholders();
			}
		}
	});

	$( document ).on( 'widget-added widget-updated', lsi_updatePlaceholders );
});
