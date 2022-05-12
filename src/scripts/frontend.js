jQuery(function ($) {
	'use strict';
	( function(){

		$.map( $( '.mdm-accordian' ), ( el ) => {
			return new Accordian( $( el ) );
		});

		$.map( $( '.page-panel.expandable' ), ( el ) => {
			return new PagePanel( $( el ) );
		});

	} )();
	/**
	 * Accordians
	 * @param {[type]} $el [description]
	 */
	function Accordian( $el ) {

		let $panels, $panel_container;

		const _trigger = ( e ) => {
			e.preventDefault();

			for ( let i in $panels ) {
				if ( $panels[i].is( e.data.panel ) && ! $panels[i].hasClass( 'expanded' ) ) {
					$panels[i].addClass( 'expanded' )
					$panels[i].panel_body.slideDown( '300', 'linear', function() {

						if ( $panel_container.length && ! $panel_container.hasClass( 'expanded' ) ) {

							$panel_container.animate({
							    scrollTop: $panels[i].position_top - 18,
							}, 300);
						} else {
							$('html, body').animate({
							    scrollTop: $panels[i].offset().top - 200
							}, 300);
						}
					} );

				}
				else if ( $panels[i].hasClass( 'expanded' ) ) {
					$panels[i].removeClass( 'expanded' )
					$panels[i].panel_body.slideUp( '300', 'linear' );
				}
			}
		}

		const _calcOffset = () => {
			for ( let i in $panels ) {
				$panels[i].position_top = $panels[i].position().top;
			}
		}

		const _init = () => {
			$panels = $.map( $el.find( '.accordian-item' ), ( panel ) => {
				let $panel = $( panel );

				$panel.panel_body = $panel.find( '.accordian-body' );

				$panel.position_top = $panel.position().top;

				$panel.find( 'a.accordian-expand' ).on( 'click', { panel : $panel }, _trigger );

				return $panel;
			} );

			$panel_container = $el.closest( '.page-panel.expandable .panel-content' );


		}

		return _init();
	}
	/**
	 * Page Panels
	 */
	function PagePanel( $el ) {

		let $button, $content, frameHeight;

		const _trigger = ( e ) => {
			e.preventDefault();

			if ( $content.hasClass( 'expanded' ) ) {

				$content.css( {'--frame-max-height' : $content[0].scrollHeight + 'px'} ).removeClass( 'expanded' );

				$button.text_container.text( $button.original_text );

				$('html, body').animate({
				    scrollTop: $el.offset().top - 200
				}, 300);

				$el.removeClass( 'expanded-active' );

			} else {
				$content.addClass( 'expanded' ).one( 'transitionend webkitTransitionEnd oTransitionEnd', ( end ) => {
					$content.css( {'--frame-max-height' : 'none'} );
				} );
				$button.text_container.text( 'Collapse' );

				$el.addClass( 'expanded-active' );

			}


		}

		const _frameheight = () => {

			frameHeight = $content[0].scrollHeight + 'px';

			$content.css( {'--frame-max-height' : frameHeight} );
		}

		const _init = () => {

			$button = $el.find( 'a.show-all' );

			$button.text_container = $button.find( '.text' );

			$button.original_text = $button.text_container.text();

			$button.icon_container = $button.find( '.icon' );

			$content = $el.find( '.panel-content' );

			$button.on( 'click', _trigger );

			_frameheight();

			return $el;
		}

		return _init();
	}
	/**
	 * Gravity form redirect
	 */
	( function(){

		let $posting_redirect = $( 'input#input_1_1' );

		let $posting_title = $( 'input#input_1_5' );

		if ( ! $posting_redirect.length ) {
			return;
		}

		let $form_wrapper = $posting_redirect.closest( '.gform_wrapper' );

		let $buttons = $.map( $( '.apply-now-insert' ), ( el ) => {
			var $el = $( el );

			$el.on( 'click', ( e ) => {

				e.preventDefault();
				/**
				 * Set redirect
				 */
				$posting_redirect.val( $el.attr( 'href' ) );
				/**
				 * Set title
				 */
				if ( $posting_title.length ) {
					$posting_title.val( $el.data( 'item-title' ) );
				}
				/**
				 * Scroll to form
				 */
				$('html, body').animate({

				    scrollTop: $form_wrapper.offset().top - 300
				}, 300);
			} );
		} );

	} )();
});