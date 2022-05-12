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

/***/ "./src/scripts/frontend.js":
/*!*********************************!*\
  !*** ./src/scripts/frontend.js ***!
  \*********************************/
/***/ (function() {

eval("jQuery(function ($) {\n  'use strict';\n\n  (function () {\n    $.map($('.mdm-accordian'), function (el) {\n      return new Accordian($(el));\n    });\n    $.map($('.page-panel.expandable'), function (el) {\n      return new PagePanel($(el));\n    });\n  })();\n  /**\n   * Accordians\n   * @param {[type]} $el [description]\n   */\n\n\n  function Accordian($el) {\n    var $panels, $panel_container;\n\n    var _trigger = function _trigger(e) {\n      e.preventDefault();\n\n      var _loop = function _loop(i) {\n        if ($panels[i].is(e.data.panel) && !$panels[i].hasClass('expanded')) {\n          $panels[i].addClass('expanded');\n          $panels[i].panel_body.slideDown('300', 'linear', function () {\n            if ($panel_container.length && !$panel_container.hasClass('expanded')) {\n              $panel_container.animate({\n                scrollTop: $panels[i].position_top - 18\n              }, 300);\n            } else {\n              $('html, body').animate({\n                scrollTop: $panels[i].offset().top - 200\n              }, 300);\n            }\n          });\n        } else if ($panels[i].hasClass('expanded')) {\n          $panels[i].removeClass('expanded');\n          $panels[i].panel_body.slideUp('300', 'linear');\n        }\n      };\n\n      for (var i in $panels) {\n        _loop(i);\n      }\n    };\n\n    var _calcOffset = function _calcOffset() {\n      for (var i in $panels) {\n        $panels[i].position_top = $panels[i].position().top;\n      }\n    };\n\n    var _init = function _init() {\n      $panels = $.map($el.find('.accordian-item'), function (panel) {\n        var $panel = $(panel);\n        $panel.panel_body = $panel.find('.accordian-body');\n        $panel.position_top = $panel.position().top;\n        $panel.find('a.accordian-expand').on('click', {\n          panel: $panel\n        }, _trigger);\n        return $panel;\n      });\n      $panel_container = $el.closest('.page-panel.expandable .panel-content');\n    };\n\n    return _init();\n  }\n  /**\n   * Page Panels\n   */\n\n\n  function PagePanel($el) {\n    var $button, $content, frameHeight;\n\n    var _trigger = function _trigger(e) {\n      e.preventDefault();\n\n      if ($content.hasClass('expanded')) {\n        $content.css({\n          '--frame-max-height': $content[0].scrollHeight + 'px'\n        }).removeClass('expanded');\n        $button.text_container.text($button.original_text);\n        $('html, body').animate({\n          scrollTop: $el.offset().top - 200\n        }, 300);\n        $el.removeClass('expanded-active');\n      } else {\n        $content.addClass('expanded').one('transitionend webkitTransitionEnd oTransitionEnd', function (end) {\n          $content.css({\n            '--frame-max-height': 'none'\n          });\n        });\n        $button.text_container.text('Collapse');\n        $el.addClass('expanded-active');\n      }\n    };\n\n    var _frameheight = function _frameheight() {\n      frameHeight = $content[0].scrollHeight + 'px';\n      $content.css({\n        '--frame-max-height': frameHeight\n      });\n    };\n\n    var _init = function _init() {\n      $button = $el.find('a.show-all');\n      $button.text_container = $button.find('.text');\n      $button.original_text = $button.text_container.text();\n      $button.icon_container = $button.find('.icon');\n      $content = $el.find('.panel-content');\n      $button.on('click', _trigger);\n\n      _frameheight();\n\n      return $el;\n    };\n\n    return _init();\n  }\n  /**\n   * Gravity form redirect\n   */\n\n\n  (function () {\n    var $posting_redirect = $('input#input_1_1');\n    var $posting_title = $('input#input_1_5');\n\n    if (!$posting_redirect.length) {\n      return;\n    }\n\n    var $form_wrapper = $posting_redirect.closest('.gform_wrapper');\n    var $buttons = $.map($('.apply-now-insert'), function (el) {\n      var $el = $(el);\n      $el.on('click', function (e) {\n        e.preventDefault();\n        /**\n         * Set redirect\n         */\n\n        $posting_redirect.val($el.attr('href'));\n        /**\n         * Set title\n         */\n\n        if ($posting_title.length) {\n          $posting_title.val($el.data('item-title'));\n        }\n        /**\n         * Scroll to form\n         */\n\n\n        $('html, body').animate({\n          scrollTop: $form_wrapper.offset().top - 300\n        }, 300);\n      });\n    });\n  })();\n});\n\n//# sourceURL=webpack://wppluginboilerplace/./src/scripts/frontend.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/scripts/frontend.js"]();
/******/ 	
/******/ })()
;