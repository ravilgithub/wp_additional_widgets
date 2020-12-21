;( function( $ ) {
	$( document ).ready( function() {
		
		// Widget Tabbed animation
		var $widgetTabsAnimate = {
			classToggle: function( $el, $parent, $siblings ) {
				$( $el, $parent )
					.addClass( 'active' )
					.siblings( $siblings )
						.removeClass( 'active' );
			},

			setMaxHeight: function( $parent, $self ) {
				var $maxHeight = 0;
				$( '.tab-content-inner', $parent ).each( function() {
					$maxHeight = Math.max( $( this ).outerHeight( true ), $maxHeight );
				} );
				$( '.tabs-content', $parent ).height( $maxHeight );
			},
			
			getAllTabWidgets: function() {
				var $self = this;
				$( '.bri-widget-tabbed' ).each( function() {
					$self.setMaxHeight( this, $self );
					$self.clickHandler( this );
					$self.resizeHandler( this );
				} );
			},

			resizeHandler: function( $parent ) {
				var $self = this;
				$( window ).on( 'resize', function() {
					$self.setMaxHeight( $parent, $self );
				} );
			},

			clickHandler: function( $parent ) {
				var $self = this;
				$( '.tabs-list li', $parent ).on( 'click', function ( $event ) {
					$event.preventDefault();
					var $toggleTab = $( 'a', this ).attr( 'href' );

					$self.classToggle( this, 'li' );
					$self.classToggle( $toggleTab, $parent, '.tab-content-inner' );
				} );
			},

			init: function() {
				this.getAllTabWidgets();
			},
		};

		setTimeout( function() { 
			$widgetTabsAnimate.init();
		}, 500 );

	} );
} ) ( jQuery );
	