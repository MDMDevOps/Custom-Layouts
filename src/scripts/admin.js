var ace = require('brace');
require('brace/mode/javascript');
require('brace/mode/twig');
require('brace/mode/html');
require('brace/theme/monokai');
require('brace/ext/language_tools');
// require('brace/ext/emmet');

import domReady from '@wordpress/dom-ready';

jQuery(function ($) {
	'use strict';
    const _initAce = () => {
        let $textarea = $( '.cl_ace textarea' );
        /**
         * If no editor div, bail
         */
        if ( ! $textarea.length ) {
            return;
        }
        /**
         * Create div to insert ace
         */
        let $editDiv = $('<div>', {
            width: '100%',
            height: '300px',
            'class': 'cl-ace-editor-container'
        }).insertAfter( $textarea );
        /**
         * Init ace
         */
        let $ace = ace.edit( $editDiv[0] );
        /**
         * Set the editor options
         */
        $ace.setOptions({
            mode: 'ace/mode/twig',
            selectionStyle : 'text',
            minLines : 30,
            highlightActiveLine : true,
            enableBasicAutocompletion : true,
            enableLiveAutocompletion : true,
            setBehavioursEnabled : true,
        });
        /**
         * Set intitial value from textarea
         */
        $ace.getSession().setValue($textarea.val());
        /**
         * Update textarea value on change
         */
        $ace.getSession().on('change', function(){
            $textarea.val( $ace.getSession().getValue() );
        });
    }
    /**
    * Init ace editor for code sections
    */
    domReady( function () {
        setTimeout(function() {
                _initAce();
        }, 100 );
    } );
    /**
     * Hide / Show editor and code section
     */
    ( function() {

        let $editor, $type_select, $custom_code;

        const _hideShowEditor = () => {
            /**
             * Update content editor
             */
            if ( $type_select.val() !== 'editor' ) {
                $editor.addClass( 'block-hidden' );
            } else {
                $editor.removeClass( 'block-hidden' );
            }
            /**
             * Update code editor
             */
            if ( $type_select.val() === 'code' ) {
                $custom_code.removeClass( 'block-hidden' ).addClass( 'block-shown' );
            } else {
                $custom_code.removeClass( 'block-shown' ).addClass( 'block-hidden' )
            }
        };

        const _init = () => {

            $editor = $( '.block-editor' );

            $type_select = $( '.editor-type-select select' );

            $custom_code = $( '.cf-field.cl_ace' );

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