/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,ui_hint,main_core,main_popup) {
	'use strict';

	let _ = t => t,
	  _t;
	var _getText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getText");
	class Tooltip {
	  constructor() {
	    Object.defineProperty(this, _getText, {
	      value: _getText2
	    });
	    this.popup = null;
	    this.cursorOnPopup = false;
	  }
	  show(element, params) {
	    var _params$popupOptions, _this$popup;
	    this.hide(false);
	    const popupOptions = {
	      id: `bx-vue-hint-${Date.now()}`,
	      bindElement: element,
	      bindOptions: {
	        position: params.position === 'top' ? 'top' : 'bottom'
	      },
	      content: main_core.Tag.render(_t || (_t = _`
				<span class='ui-hint-content'>${0}</span>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getText)[_getText](element, params)),
	      darkMode: true,
	      autoHide: true,
	      cacheable: false,
	      animation: 'fading',
	      ...((_params$popupOptions = params.popupOptions) != null ? _params$popupOptions : null)
	    };
	    this.popup = new main_popup.Popup(popupOptions);
	    this.popup.show();
	    if (params.interactivity && (_this$popup = this.popup) != null && _this$popup.getPopupContainer()) {
	      main_core.Event.bind(this.popup.getPopupContainer(), 'mouseenter', () => {
	        this.cursorOnPopup = true;
	      });
	      main_core.Event.bind(this.popup.getPopupContainer(), 'mouseleave', () => {
	        this.cursorOnPopup = false;
	        this.hide(true);
	      });
	    }
	  }
	  hide(isInteractive) {
	    if (isInteractive) {
	      setTimeout(() => {
	        if (this.popup && this.popup.getPopupContainer() && !this.cursorOnPopup) {
	          this.popup.close();
	        }
	      }, 100);
	    } else {
	      var _this$popup2;
	      (_this$popup2 = this.popup) == null ? void 0 : _this$popup2.close();
	    }
	  }
	}
	function _getText2(element, params) {
	  if (main_core.Type.isStringFilled(params) && main_core.Type.isUndefined(element.dataset.hintHtml)) {
	    return main_core.Text.encode(params);
	  }
	  return params.html || main_core.Text.encode(params.text) || params;
	}
	const tooltip = new Tooltip();

	/**
	 * Hint Vue directive
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2025 Bitrix
	 */
	const hint = {
	  async mounted(element, {
	    value
	  }) {
	    var _value$interactivity;
	    if (!value) {
	      return;
	    }
	    main_core.Event.bind(element, 'mouseenter', () => onMouseEnter(element, getParams(value)));
	    const isInteractive = (_value$interactivity = value.interactivity) != null ? _value$interactivity : false;
	    main_core.Event.bind(element, 'mouseleave', () => hideTooltip(isInteractive));
	    main_core.Event.bind(element, 'click', () => hideTooltip());
	  }
	};
	let showTimeout = null;
	function onMouseEnter(element, params) {
	  var _params$timeout;
	  clearTimeouts();
	  showTimeout = setTimeout(() => showTooltip(element, params), (_params$timeout = params.timeout) != null ? _params$timeout : 0);
	}
	function showTooltip(element, params) {
	  clearTimeouts();
	  tooltip.show(element, params);
	}
	function hideTooltip(isInteractive) {
	  clearTimeouts();
	  tooltip.hide(isInteractive);
	}
	function clearTimeouts() {
	  clearTimeout(showTimeout);
	}
	function getParams(value) {
	  return main_core.Type.isFunction(value) ? value() : value;
	}

	exports.hint = hint;

}((this.BX.Vue3.Directives = this.BX.Vue3.Directives || {}),BX,BX,BX.Main));
//# sourceMappingURL=hint.bundle.js.map
