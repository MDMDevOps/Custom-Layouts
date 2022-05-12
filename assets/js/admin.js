/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/scripts/admin.js":
/*!******************************!*\
  !*** ./src/scripts/admin.js ***!
  \******************************/
/***/ (function() {

eval("// acl_layout_type\njQuery(function ($) {\n  'use strict';\n\n  (function () {\n    var $editor, $type_select;\n\n    var _hideShowEditor = function _hideShowEditor() {\n      if ($type_select.val() !== 'editor') {\n        $editor.addClass('block-hidden');\n      } else {\n        $editor.removeClass('block-hidden');\n      }\n    };\n\n    var _init = function _init() {\n      $editor = $('.block-editor');\n      $type_select = $('.editor-type-select select');\n\n      if (!$editor.length) {\n        $editor = $('#post-body-content');\n      }\n\n      if (!$type_select.length || !$editor.length) {\n        return;\n      }\n\n      $type_select.on('change', _hideShowEditor);\n\n      _hideShowEditor();\n    };\n\n    _init();\n  })();\n});\n\n//# sourceURL=webpack://wppluginboilerplace/./src/scripts/admin.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/scripts/admin.js"]();
/******/ 	
/******/ })()
;