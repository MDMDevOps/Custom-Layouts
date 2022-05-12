// acl_layout_type
jQuery(function ($) {
	'use strict';
    ( function() {

        let $editor, $type_select;

        const _hideShowEditor = () => {

            if ( $type_select.val() !== 'editor' ) {

                $editor.addClass( 'block-hidden' );
            }
            else {
                $editor.removeClass( 'block-hidden' );
            }
        };

        const _init = () => {

            $editor = $( '.block-editor' );

            $type_select = $( '.editor-type-select select' );

            if ( ! $editor.length ) {
                $editor = $( '#post-body-content' );
            }

            if ( ! $type_select.length || ! $editor.length ) {
                return;
            }

            $type_select.on( 'change', _hideShowEditor );

            _hideShowEditor();
        }
        _init();
    } )();
});