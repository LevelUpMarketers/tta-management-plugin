( function( $ ) {

	if ( typeof vc === 'undefined' || typeof vc.shortcode_view === 'undefined' ) {
		return false;
	}

	/**
	 * Shortcode vcex_heading
	 */
	window.vcexHeadingView = vc.shortcode_view.extend( {
		changeShortcodeParams: function ( model ) {
			window.vcexHeadingView.__super__.changeShortcodeParams.call( this, model );
			var inverted_value;
			if ( _.isString( model.getParam( 'text' ) ) ) {
				if ( 'custom' == model.getParam( 'source' ) ) {
					if ( model.getParam( 'text' ).match(/^#E\-8_/) ) {
						this.$el.find( '.vcex-heading-text > span' ).html( '' );
					} else {
						this.$el.find( '.vcex-heading-text > span' ).html( ': ' + model.getParam( 'text' ) );
					}
				} else {
					inverted_value = _.invert( this.params.source.value );
					this.$el.find( '.vcex-heading-text > span' ).html( ': ' + inverted_value[ model.getParam( 'source' ) ] );
				}
			}
		}
	} );

} ) ( jQuery );