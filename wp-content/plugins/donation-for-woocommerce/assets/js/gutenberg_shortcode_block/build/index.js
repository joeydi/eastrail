/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


const {
  registerBlockType
} = wp.blocks;
var wpep_block_container = {
  "display": 'flex',
  "justify-content": 'space-between',
  "align-items": 'center',
  "flex-wrap": 'wrap',
  "width": '100%'
};
registerBlockType('wc-donation/shortcode', {
  title: 'WC Donation',
  description: 'Block to add donation campaign shortcode to the page',
  icon: 'format-aside',
  category: 'layout',
  attributes: {
    donation_ids: {
      type: 'array',
      default: [""]
    },
    is_block: {
      type: 'boolean'
    },
    formattypes: {
      type: 'string',
      default: "block"
    },
    displaytype: {
      type: 'string',
      default: "page"
    },
    popup_header: {
      type: 'string',
      default: "Campaigns"
    },
    popup_button: {
      type: 'string',
      default: "View Campaigns"
    },
    display_button: {
      type: 'string',
      default: "auto_display"
    }
  },
  edit(props) {
    if (props.attributes.displaytype == 'page') {
      var style = {
        display: "none"
      };
    }
    var options = [{
      value: '',
      label: 'Select a Campaign',
      disabled: true
    }];
    var displayformatoptions = [{
      value: 'list',
      label: 'List'
    }, {
      value: 'block',
      label: 'Block'
    }, {
      value: 'table',
      label: 'Table'
    }, {
      value: 'grid',
      label: 'Grid'
    }];
    var displaytypeoptions = [{
      label: 'Page',
      value: 'page'
    }, {
      label: 'Popup',
      value: 'popup'
    }];
    var displaybuttonoptions = [{
      label: 'Auto Display',
      value: 'auto_display'
    }, {
      label: 'Button Display',
      value: 'button_display'
    }];
    var p = wc_donation_forms.forms;
    for (var key in p) {
      if (p.hasOwnProperty(key)) {
        var form_id = p[key].ID;
        var form_title = p[key].title;
        if (props.attributes.donation_ids.includes(form_id)) {
          options.push({
            'value': form_id,
            'label': form_title,
            selected: true
          });
        } else {
          options.push({
            'value': form_id,
            'label': form_title
          });
        }
      }
    }
    var donation_ids = props.attributes.donation_ids;
    function wpep_shortcode_change(e) {
      // props.attributes.donation_ids = e
      props.setAttributes({
        donation_ids: e
      });
      props.setAttributes({
        is_block: true
      });
    }
    function display_format_change(e) {
      props.setAttributes({
        formattypes: e
      });
    }
    function display_type_change(e) {
      if (e == 'page') {
        document.getElementById("popup-settings").style.display = "none";
      } else {
        document.getElementById("popup-settings").style.display = "flex";
      }
      props.setAttributes({
        displaytype: e
      });
    }
    function display_popup_title(e) {
      props.setAttributes({
        popup_header: e
      });
    }
    function display_popup_button(e) {
      props.setAttributes({
        popup_button: e
      });
    }
    function display_button_change(e) {
      props.setAttributes({
        display_button: e
      });
    }
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexBlock, {
      style: {
        marginRight: "10px"
      }
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      className: "abc",
      label: "Select Campaign : ",
      help: "Select the Campaign you want to display.",
      defaultValue: props.attributes.donation_ids,
      multiple: true,
      onChange: wpep_shortcode_change,
      options: options
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexBlock, {
      style: {
        marginRight: "10px"
      }
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      label: "Campaign Display Format : ",
      help: "Select the style you want to display.",
      defaultValue: props.attributes.formattypes,
      onChange: display_format_change,
      options: displayformatoptions
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexItem, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
      label: "Campaign Display Type",
      help: "Campaign Display Type",
      selected: props.attributes.displaytype,
      options: displaytypeoptions,
      onChange: display_type_change
    }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
      id: "popup-settings",
      style: style
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexBlock, {
      style: {
        marginRight: "10px"
      }
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
      label: "Pop-up Header Text",
      help: "Enter the title for the popup screen",
      value: props.attributes.popup_header,
      onChange: display_popup_title
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexBlock, {
      style: {
        marginRight: "10px"
      }
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
      label: "Button Text",
      help: "Enter the text for the button",
      value: props.attributes.popup_button,
      onChange: display_popup_button
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexItem, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
      label: "Display Popup",
      help: "Display popup via button or on page load",
      selected: props.attributes.display_button,
      options: displaybuttonoptions,
      onChange: display_button_change
    }))));
  },
  save(props) {
    return null;
  }
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map