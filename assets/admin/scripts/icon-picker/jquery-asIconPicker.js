
/**
 * jQuery asTooltip v0.4.2
 * https://github.com/amazingSurge/jquery-asTooltip
 *
 * Copyright (c) amazingSurge
 * Released under the LGPL-3.0 license
 */
!function(t,e){if("function"==typeof define&&define.amd)define(["jquery"],e);else if("undefined"!=typeof exports)e(require("jquery"));else{var i={exports:{}};e(t.jQuery),t.jqueryAsTooltipEs=i.exports}}(this,function(t){"use strict";function e(t){return t&&t.__esModule?t:{default:t}}function i(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}var n=e(t),o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},s=function(){function t(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}return function(e,i,n){return i&&t(e.prototype,i),n&&t(e,n),e}}(),a={namespace:"asTooltip",skin:"",closeBtn:!1,position:{value:"right middle",target:!1,container:!1,auto:!1,adjust:{mouse:!0,resize:!0,scroll:!0}},show:{target:!1,event:"mouseenter",delay:0},hide:{target:!1,event:"mouseleave",delay:0,container:!1,inactive:!1},content:null,contentAttr:"title",ajax:!1,tpl:'<div class="{{namespace}}"><div class="{{namespace}}-inner"><div class="{{namespace}}-loading"></div><div class="{{namespace}}-content"></div></div></div>',onInit:null,onShow:null,onHide:null,onFocus:null,onBlur:null},r="asTooltip",l=(0,n.default)(window),u=[],h=function(t,e,i,o){var s=void 0,a=void 0,r=void 0,l=void 0,u=void 0,h=(0,n.default)(t),d=0,c=0;s=o?t:h.offset(),a=o?0:h.outerWidth(),r=o?0:h.outerHeight(),l=e.outerWidth(),u=e.outerHeight();for(var f=0;f<i.length;f++)switch(i[f]){case"left":d=0===f?s.left-l:s.left;break;case"middle":switch(i[0]){case"left":case"right":c=s.top+(r-u)/2;break;case"top":case"bottom":d=s.left+(a-l)/2}break;case"right":d=0===f?s.left+a:s.left+a-l;break;case"top":c=0===f?s.top-u:s.top;break;case"bottom":c=0===f?s.top+r:s.top+r-u}return{left:Math.round(d),top:Math.round(c)}},d=function(t,e,i){var o=(0,n.default)(t),s=o.offset(),a=i.offset(),r="BODY"===i[0].tagName?l.scrollLeft():i.scrollLeft(),u="BODY"===i[0].tagName?l.scrollTop():i.scrollTop(),h="BODY"===i[0].tagName?s:{top:s.top-a.top,left:s.left-a.left},d=o.outerWidth(),c=o.outerHeight(),f=e.outerWidth(),p=e.outerHeight(),g="BODY"===i[0].tagName?l.innerWidth():i.outerWidth(),v="BODY"===i[0].tagName?l.innerHeight():i.outerHeight(),m=[];return p>h.top-u&&m.push("top"),p+c+h.top>u+v&&m.push("bottom"),f>h.left-r&&m.push("left"),f+d+h.left>r+g&&m.push("right"),m},c=function(){function t(e,o){i(this,t);var s=(0,n.default)(document.body),r=e[0]===document?s:e,l=void 0,u=void 0;u=this.parseTargetData((0,n.default)(r).data()),l=this.options=n.default.extend(!0,{},a,o,u),l.position.container=l.position.container?(0,n.default)(l.position.container):s,l.position.target||(l.position.target=r),l.show.target||(l.show.target=r),l.hide.target||(l.hide.target=r),this.$element=(0,n.default)(r),this.namespace=this.options.namespace,l.content=this.getContent(),this.enabled=!0,this.isOpen=!1,this.loadFlag=!1,this.moveFlag=!1,this.showTimer=null,this.hideTimer=null,this.classes={show:this.namespace+"_isShow",isLoading:this.namespace+"_isLoading",active:this.namespace+"_active",enabled:this.namespace+"_enabled"},this.trigger("init"),this.init()}return s(t,[{key:"init",value:function(){var t=this,e=this.options,i=e.show.target,o=e.hide.target,s=e.show.event,a=e.hide.event;this.$tip=(0,n.default)(e.tpl.replace(/{{namespace}}/g,this.namespace)),this.$loading=(0,n.default)("."+this.namespace+"-loading",this.$tip),this.$content=(0,n.default)("."+this.namespace+"-content",this.$tip),i===o&&s===a?this._bind(i,s,function(e){t.isOpen?t.hideMethod(e):t.showMethod(e)}):(this._bind(i,s,function(e){t.showMethod(e)}),this._bind(o,a,function(e){t.hideMethod(e)})),"BODY"===e.position.container[0].tagName&&(e.position.adjust.resize&&this._bind(l,"resize",function(){t.isOpen&&t.setPosition()}),e.position.adjust.scroll&&this._bind(l,"scroll",function(){t.isOpen&&t.setPosition()}))}},{key:"trigger",value:function(t){for(var e=arguments.length,i=Array(e>1?e-1:0),n=1;n<e;n++)i[n-1]=arguments[n];var o=[this].concat(i);this.$element.trigger(r+"::"+t,o),t=t.replace(/\b\w+\b/g,function(t){return t.substring(0,1).toUpperCase()+t.substring(1)});var s="on"+t;"function"==typeof this.options[s]&&this.options[s].apply(this,i)}},{key:"_bind",value:function(t,e,i,o){if(t&&i&&e.length){var s=o?e:e+"."+o;return(0,n.default)(t).on(s,n.default.proxy(i,this)),this}}},{key:"_unbind",value:function(t,e,i){return t&&(0,n.default)(t).unbind(i?e:e+"."+i),this}},{key:"parseTargetData",value:function(t){var e={};return n.default.each(t,function(t,i){var n=t.split("_"),o=n.length,s=e;if(1===o)e[n[0]]=i;else for(var a=0;a<o;a++)0===a?void 0===e[n[a]]&&(e[n[a]]={}):a===o-1?s[n[a]]=i:void 0===s[n[a]]&&(s[n[a]]={}),s=e[n[a]]}),e}},{key:"parseTpl",value:function(t){return t.replace("{{namespace}}",self.namespace)}},{key:"getDelegateOptions",value:function(){var t={};return this._options&&n.default.each(this._options,function(e,i){a[e]!==i&&(t[e]=i)}),t}},{key:"showMethod",value:function(t){var e=t instanceof this.constructor?t:(0,n.default)(t.currentTarget).data(r),i=this.options;if(e||(e=new this.constructor(t.currentTarget,this.getDelegateOptions()),(0,n.default)(t.currentTarget).data(r,e)),i.ajax||e.options.content){if(e.isOpen?clearTimeout(e.hideTimer):(clearTimeout(e.showTimer),e.showTimer=setTimeout(function(){n.default.proxy(e.show,e)()},i.show.delay)),"mouse"===i.position.target){if(this.moveFlag)return;this.isFirst=!0,(0,n.default)(document).on("mousemove."+r,n.default.proxy(this.move,e)),this.moveFlag=!0}"click"===i.hide.event&&i.hide.container&&this._bind(i.hide.container,i.hide.event,function(t){var i=(0,n.default)(t.target);0===i.closest(e.$el).length&&0===i.closest(e.$tip).length&&e.isOpen&&n.default.proxy(e.hide,e)()})}}},{key:"hideMethod",value:function(t){var e=t instanceof this.constructor?t:(0,n.default)(t.currentTarget).data(r),i=this.options,o=!1;if(e||(e=new this.constructor(t.currentTarget,this.getDelegateOptions()),(0,n.default)(t.currentTarget).data(r,e)),i.ajax||e.options.content)return e.isOpen?void("mouse"===i.position.target&&this.moveFlag||("click"===i.hide.event&&i.hide.container&&this._unbind(i.hide.container,i.hide.event),i.hide.inactive&&(this._bind(e.$tip,"mouseenter."+r,function(){o=!0}),this._bind(e.$tip,"mouseleave."+r,function(){o=!1,clearTimeout(e.hideTimer),e.hideTimer=setTimeout(function(){n.default.proxy(e.hide,e)()},e.options.hide.delay),e._unbind(e.$tip,"mouseenter."+r+" mouseleave."+r)})),clearTimeout(e.hideTimer),e.hideTimer=setTimeout(function(){o||n.default.proxy(e.hide,e)()},i.hide.delay))):void clearTimeout(e.showTimer)}},{key:"move",value:function(t){var e=Math.round(t.pageX),i=Math.round(t.pageY),o=this.$element.offset().top,s=this.$element.offset().left,a=this.$element.outerWidth(),l=this.$element.outerHeight();e>=s&&e<=s+a&&i>=o&&i<=o+l?this.options.position.adjust.mouse?this.setPosition(t):this.isFirst&&(this.setPosition(t),this.isFirst=!1):((0,n.default)(document).off("mousemove."+r),this.moveFlag=!1,this.hideMethod(this.$element.data(r)))}},{key:"getContent",value:function(){return this.$element.attr(this.options.contentAttr)||("function"==typeof this.options.content?this.options.content():this.options.content)}},{key:"setPosition",value:function(t){var e=this,i=void 0,s=void 0,a=void 0,r=this.options,l=this.$el,u=r.position.container,c=!1,f=!1;a=u.css("position");var p=r.position.value.split(" ");"mouse"===r.position.target&&t?(l={top:Math.round(t.pageY),left:Math.round(t.pageX)},f=!0):"object"===o(r.position.target)&&(l=r.position.target),r.position.auto&&"mouse"!==r.position.target&&!function(){var t=d(l,e.$tip,u),i=["top","right","bottom","left"];n.default.each(t,function(t,e){i=n.default.map(i,function(t){return t!==e?t:null})}),i.length>0&&(p[0]=i[0])}(),this.$tip.addClass(this.namespace+"-element-"+p[0]).addClass(this.namespace+"-arrow-"+p[1]),i=h(l,this.$tip,p,f),"static"!==a&&(s=u.offset(),c=!0),this.$tip.css({top:i.top+(c?-s.top:0),left:i.left+(c?-s.left:0)})}},{key:"loadToggle",value:function(){var t=this.loadFlag;t?(this.$tip.removeClass(this.namespace+"_isLoading"),this.loadFlag=!1):(this.$tip.addClass(this.namespace+"_isLoading"),this.loadFlag=!0)}},{key:"statusToggle",value:function(t){t?this.$element.removeClass(this.classes.active):this.$element.addClass(this.classes.active)}},{key:"rePosition",value:function(t){return this.setPosition(t),this}},{key:"setContent",value:function(){var t=this.options;t.ajax&&this.loadToggle(),this.$content.html(t.content),this.$tip.appendTo(t.position.container),"mouse"!==t.position.target&&this.setPosition()}},{key:"show",value:function(){var t=this.options;if(this.enabled)return t.skin&&this.$tip.addClass(this.namespace+"_"+t.skin),t.ajax&&t.ajax(this),this.setContent(this.isOpen),this.statusToggle(this.isOpen),this.isOpen=!0,this.trigger("show"),this}},{key:"hide",value:function(){return this.options.ajax&&(this.$tip.removeClass(this.namespace+"_isLoading"),this.loadFlag=!1),this.$tip.off("."+r),this.statusToggle(this.isOpen),this.$tip.remove(),this.isOpen=!1,this.trigger("hide"),this}},{key:"enable",value:function(){return this.enabled=!0,this.$element.addClass(this.classes.enabled),this.trigger("enable"),this}},{key:"disable",value:function(){return this.enabled=!1,this.$element.removeClass(this.classes.enabled),this.trigger("disable"),this}},{key:"destroy",value:function(){return this.$element.off("."+r),this.trigger("destroy"),this}}],[{key:"closeAll",value:function(){u.map(function(t){t.isOpen&&t.hide()})}},{key:"setDefaults",value:function(t){n.default.isPlainObject(t)&&n.default.extend(!0,a,t)}}]),t}(),f={version:"0.4.2"},p="asTooltip",g=n.default.fn.asTooltip,v=function(t){for(var e=this,i=arguments.length,s=Array(i>1?i-1:0),a=1;a<i;a++)s[a-1]=arguments[a];if("string"==typeof t){var r=function(){var i=t;if(/^_/.test(i))return{v:!1};if(!/^(get)/.test(i))return{v:e.each(function(){var t=n.default.data(this,p);t&&"function"==typeof t[i]&&t[i].apply(t,s)})};var o=e.first().data(p);return o&&"function"==typeof o[i]?{v:o[i].apply(o,s)}:void 0}();if("object"===("undefined"==typeof r?"undefined":o(r)))return r.v}return this.each(function(){(0,n.default)(this).data(p)||(0,n.default)(this).data(p,new c(this,t))})};n.default.fn.asTooltip=v,n.default.asTooltip=n.default.extend({setDefaults:c.setDefaults,closeAll:c.closeAll,noConflict:function(){return n.default.fn.asTooltip=g,v}},f)});

/**
 * jQuery asScrollbar v0.5.4
 * https://github.com/amazingSurge/jquery-asScrollbar
 *
 * Copyright (c) amazingSurge
 * Released under the LGPL-3.0 license
 */
!function(t,e){if("function"==typeof define&&define.amd)define(["jquery"],e);else if("undefined"!=typeof exports)e(require("jquery"));else{var n={exports:{}};e(t.jQuery),t.jqueryAsScrollbarEs=n.exports}}(this,function(t){"use strict";function e(t){return t&&t.__esModule?t:{default:t}}function n(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function i(t){return"string"==typeof t&&t.indexOf("%")!==-1}function a(t){return parseFloat(t.slice(0,-1)/100,10)}function s(t){return!(!t||"matrix"!==t.substr(0,6))&&t.replace(/^.*\((.*)\)$/g,"$1").replace(/px/g,"").split(/, +/)}function o(){return"undefined"!=typeof window.performance&&window.performance.now?window.performance.now():Date.now()}var r=e(t),h="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol?"symbol":typeof t},l=function(){function t(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}return function(e,n,i){return n&&t(e.prototype,n),i&&t(e,i),e}}(),u={namespace:"asScrollbar",skin:null,handleSelector:null,handleTemplate:'<div class="{{handle}}"></div>',barClass:null,handleClass:null,disabledClass:"is-disabled",draggingClass:"is-dragging",hoveringClass:"is-hovering",direction:"vertical",barLength:null,handleLength:null,minHandleLength:30,maxHandleLength:null,mouseDrag:!0,touchDrag:!0,pointerDrag:!0,clickMove:!0,clickMoveStep:.3,mousewheel:!0,mousewheelSpeed:50,keyboard:!0,useCssTransforms3d:!0,useCssTransforms:!0,useCssTransitions:!0,duration:"500",easing:"ease"},d=function(t,e,n,i){var a=function(t,e){return 1-3*e+3*t},s=function(t,e){return 3*e-6*t},o=function(t){return 3*t},r=function(t,e,n){return((a(e,n)*t+s(e,n))*t+o(e))*t},h=function(t,e,n){return 3*a(e,n)*t*t+2*s(e,n)*t+o(e)},l=function(e){for(var i=e,a=0;a<4;++a){var s=h(i,t,n);if(0===s)return i;var o=r(i,t,n)-e;i-=o/s}return i};return t===e&&n===i?{css:"linear",fn:function(t){return t}}:{css:"cubic-bezier("+t+","+e+","+n+","+i+")",fn:function(t){return r(l(t),e,i)}}},c={ease:d(.25,.1,.25,1),linear:d(0,0,1,1),"ease-in":d(.42,0,1,1),"ease-out":d(0,0,.58,1),"ease-in-out":d(.42,0,.58,1)};Date.now||(Date.now=function(){return(new Date).getTime()});for(var f=["webkit","moz"],g=0;g<f.length&&!window.requestAnimationFrame;++g){var v=f[g];window.requestAnimationFrame=window[v+"RequestAnimationFrame"],window.cancelAnimationFrame=window[v+"CancelAnimationFrame"]||window[v+"CancelRequestAnimationFrame"]}!/iP(ad|hone|od).*OS (6|7|8)/.test(window.navigator.userAgent)&&window.requestAnimationFrame&&window.cancelAnimationFrame||!function(){var t=0;window.requestAnimationFrame=function(e){var n=o(),i=16,a=Math.max(t+i,n);return setTimeout(function(){e(t=a)},a-n)},window.cancelAnimationFrame=clearTimeout}();var p={};!function(t){var e={transition:{end:{WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd",transition:"transitionend"}},animation:{end:{WebkitAnimation:"webkitAnimationEnd",MozAnimation:"animationend",OAnimation:"oAnimationEnd",animation:"animationend"}}},n=["webkit","Moz","O","ms"],i=(0,r.default)("<support>").get(0).style,a={csstransforms:function(){return Boolean(s("transform"))},csstransforms3d:function(){return Boolean(s("perspective"))},csstransitions:function(){return Boolean(s("transition"))},cssanimations:function(){return Boolean(s("animation"))}},s=function(t,e){var a=!1,s=t.charAt(0).toUpperCase()+t.slice(1);return void 0!==i[t]&&(a=t),a||r.default.each(n,function(t,e){return void 0===i[e+s]||(a="-"+e.toLowerCase()+"-"+s,!1)}),e?a:!!a},o=function(t){return s(t,!0)};a.csstransitions()&&(t.transition=new String(o("transition")),t.transition.end=e.transition.end[t.transition]),a.cssanimations()&&(t.animation=new String(o("animation")),t.animation.end=e.animation.end[t.animation]),a.csstransforms()&&(t.transform=new String(o("transform")),t.transform3d=a.csstransforms3d()),"ontouchstart"in window||window.DocumentTouch&&document instanceof window.DocumentTouch?t.touch=!0:t.touch=!1,window.PointerEvent||window.MSPointerEvent?t.pointer=!0:t.pointer=!1,t.prefixPointerEvent=function(t){return window.MSPointerEvent?"MSPointer"+t.charAt(9).toUpperCase()+t.substr(10):t}}(p);var m="asScrollbar",b=function(){function t(e){var i=arguments.length<=1||void 0===arguments[1]?{}:arguments[1];n(this,t),this.$bar=(0,r.default)(e),i=this.options=r.default.extend({},u,i,this.$bar.data("options")||{}),e.direction=this.options.direction,this.classes={directionClass:i.namespace+"-"+i.direction,barClass:i.barClass?i.barClass:i.namespace,handleClass:i.handleClass?i.handleClass:i.namespace+"-handle"},"vertical"===this.options.direction?this.attributes={axis:"Y",position:"top",length:"height",clientLength:"clientHeight"}:"horizontal"===this.options.direction&&(this.attributes={axis:"X",position:"left",length:"width",clientLength:"clientWidth"}),this._states={},this._drag={time:null,pointer:null},this._frameId=null,this.handlePosition=0,this.easing=c[this.options.easing]||c.ease,this.init()}return l(t,[{key:"init",value:function(){var t=this.options;this.$handle=this.$bar.find(this.options.handleSelector),0===this.$handle.length?this.$handle=(0,r.default)(t.handleTemplate.replace(/\{\{handle\}\}/g,this.classes.handleClass)).appendTo(this.$bar):this.$handle.addClass(this.classes.handleClass),this.$bar.addClass(this.classes.barClass).addClass(this.classes.directionClass).attr("draggable",!1),t.skin&&this.$bar.addClass(t.skin),null!==t.barLength&&this.setBarLength(t.barLength),null!==t.handleLength&&this.setHandleLength(t.handleLength),this.updateLength(),this.bindEvents(),this.trigger("ready")}},{key:"trigger",value:function(t){for(var e=arguments.length,n=Array(e>1?e-1:0),i=1;i<e;i++)n[i-1]=arguments[i];var a=[this].concat(n);this.$bar.trigger(m+"::"+t,a),t=t.replace(/\b\w+\b/g,function(t){return t.substring(0,1).toUpperCase()+t.substring(1)});var s="on"+t;"function"==typeof this.options[s]&&this.options[s].apply(this,n)}},{key:"is",value:function(t){return this._states[t]&&this._states[t]>0}},{key:"enter",value:function(t){void 0===this._states[t]&&(this._states[t]=0),this._states[t]++}},{key:"leave",value:function(t){this._states[t]--}},{key:"eventName",value:function(t){if("string"!=typeof t||""===t)return"."+this.options.namespace;t=t.split(" ");for(var e=t.length,n=0;n<e;n++)t[n]=t[n]+"."+this.options.namespace;return t.join(" ")}},{key:"bindEvents",value:function(){var t=this;this.options.mouseDrag&&(this.$handle.on(this.eventName("mousedown"),r.default.proxy(this.onDragStart,this)),this.$handle.on(this.eventName("dragstart selectstart"),function(){return!1})),this.options.touchDrag&&p.touch&&(this.$handle.on(this.eventName("touchstart"),r.default.proxy(this.onDragStart,this)),this.$handle.on(this.eventName("touchcancel"),r.default.proxy(this.onDragEnd,this))),this.options.pointerDrag&&p.pointer&&(this.$handle.on(this.eventName(p.prefixPointerEvent("pointerdown")),r.default.proxy(this.onDragStart,this)),this.$handle.on(this.eventName(p.prefixPointerEvent("pointercancel")),r.default.proxy(this.onDragEnd,this))),this.options.clickMove&&this.$bar.on(this.eventName("mousedown"),r.default.proxy(this.onClick,this)),this.options.mousewheel&&this.$bar.on("mousewheel",function(e){var n=void 0;"vertical"===t.options.direction?n=e.deltaFactor*e.deltaY:"horizontal"===t.options.direction&&(n=-1*e.deltaFactor*e.deltaX);var i=t.getHandlePosition();return i<=0&&n>0||(i>=t.barLength&&n<0||(i-=t.options.mousewheelSpeed*n,t.move(i,!0),!1))}),this.$bar.on(this.eventName("mouseenter"),function(){t.$bar.addClass(t.options.hoveringClass),t.enter("hovering"),t.trigger("hover")}),this.$bar.on(this.eventName("mouseleave"),function(){t.$bar.removeClass(t.options.hoveringClass),t.is("hovering")&&(t.leave("hovering"),t.trigger("hovered"))}),this.options.keyboard&&(0,r.default)(document).on(this.eventName("keydown"),function(e){if((!e.isDefaultPrevented||!e.isDefaultPrevented())&&t.is("hovering")){for(var n=document.activeElement;n.shadowRoot;)n=n.shadowRoot.activeElement;if(!(0,r.default)(n).is(":input,select,option,[contenteditable]")){var i=0,a=null,s=40,o=35,h=36,l=37,u=34,d=33,c=39,f=32,g=38,v=63233,p=63275,m=63273,b=63234,y=63277,w=63276,k=63235,C=63232;switch(e.which){case l:case C:i=-30;break;case g:case v:i=-30;break;case c:case b:i=30;break;case s:case k:i=30;break;case d:case w:i=-90;break;case f:case u:case y:i=-90;break;case o:case p:a="100%";break;case h:case m:a=0;break;default:return}(i||null!==a)&&(i?t.moveBy(i,!0):null!==a&&t.moveTo(a,!0),e.preventDefault())}}})}},{key:"onClick",value:function(t){var e=3;if(t.which!==e&&t.target!==this.$handle[0]){this._drag.time=(new Date).getTime(),this._drag.pointer=this.pointer(t);var n=this.$handle.offset(),i=this.distance({x:n.left,y:n.top},this._drag.pointer),a=1;i>0?i-=this.handleLength:(i=Math.abs(i),a=-1),i>this.barLength*this.options.clickMoveStep&&(i=this.barLength*this.options.clickMoveStep),this.moveBy(a*i,!0)}}},{key:"onDragStart",value:function(t){var e=this,n=3;if(t.which!==n){this.$bar.addClass(this.options.draggingClass),this._drag.time=(new Date).getTime(),this._drag.pointer=this.pointer(t);var i=function(){e.enter("dragging"),e.trigger("drag")};this.options.mouseDrag&&((0,r.default)(document).on(this.eventName("mouseup"),r.default.proxy(this.onDragEnd,this)),(0,r.default)(document).one(this.eventName("mousemove"),r.default.proxy(function(){(0,r.default)(document).on(e.eventName("mousemove"),r.default.proxy(e.onDragMove,e)),i()},this))),this.options.touchDrag&&p.touch&&((0,r.default)(document).on(this.eventName("touchend"),r.default.proxy(this.onDragEnd,this)),(0,r.default)(document).one(this.eventName("touchmove"),r.default.proxy(function(){(0,r.default)(document).on(e.eventName("touchmove"),r.default.proxy(e.onDragMove,e)),i()},this))),this.options.pointerDrag&&p.pointer&&((0,r.default)(document).on(this.eventName(p.prefixPointerEvent("pointerup")),r.default.proxy(this.onDragEnd,this)),(0,r.default)(document).one(this.eventName(p.prefixPointerEvent("pointermove")),r.default.proxy(function(){(0,r.default)(document).on(e.eventName(p.prefixPointerEvent("pointermove")),r.default.proxy(e.onDragMove,e)),i()},this))),(0,r.default)(document).on(this.eventName("blur"),r.default.proxy(this.onDragEnd,this))}}},{key:"onDragMove",value:function(t){var e=this.distance(this._drag.pointer,this.pointer(t));this.is("dragging")&&(t.preventDefault(),this.moveBy(e,!0))}},{key:"onDragEnd",value:function(){(0,r.default)(document).off(this.eventName("mousemove mouseup touchmove touchend pointermove pointerup MSPointerMove MSPointerUp blur")),this.$bar.removeClass(this.options.draggingClass),this.handlePosition=this.getHandlePosition(),this.is("dragging")&&(this.leave("dragging"),this.trigger("dragged"))}},{key:"pointer",value:function(t){var e={x:null,y:null};return t=t.originalEvent||t||window.event,t=t.touches&&t.touches.length?t.touches[0]:t.changedTouches&&t.changedTouches.length?t.changedTouches[0]:t,t.pageX?(e.x=t.pageX,e.y=t.pageY):(e.x=t.clientX,e.y=t.clientY),e}},{key:"distance",value:function(t,e){return"vertical"===this.options.direction?e.y-t.y:e.x-t.x}},{key:"setBarLength",value:function(t,e){"undefined"!=typeof t&&this.$bar.css(this.attributes.length,t),e!==!1&&this.updateLength()}},{key:"setHandleLength",value:function(t,e){"undefined"!=typeof t&&(t<this.options.minHandleLength?t=this.options.minHandleLength:this.options.maxHandleLength&&t>this.options.maxHandleLength&&(t=this.options.maxHandleLength),this.$handle.css(this.attributes.length,t),e!==!1&&this.updateLength(t))}},{key:"updateLength",value:function(t,e){"undefined"!=typeof t?this.handleLength=t:this.handleLength=this.getHandleLenght(),"undefined"!=typeof e?this.barLength=e:this.barLength=this.getBarLength()}},{key:"getBarLength",value:function(){return this.$bar[0][this.attributes.clientLength]}},{key:"getHandleLenght",value:function(){return this.$handle[0][this.attributes.clientLength]}},{key:"getHandlePosition",value:function(){var t=void 0;if(this.options.useCssTransforms&&p.transform){if(t=s(this.$handle.css(p.transform)),!t)return 0;t="X"===this.attributes.axis?t[12]||t[4]:t[13]||t[5]}else t=this.$handle.css(this.attributes.position);return parseFloat(t.replace("px",""))}},{key:"makeHandlePositionStyle",value:function(t){var e=void 0,n="0",i="0";this.options.useCssTransforms&&p.transform?("X"===this.attributes.axis?n=t+"px":i=t+"px",e=p.transform.toString(),t=this.options.useCssTransforms3d&&p.transform3d?"translate3d("+n+","+i+",0)":"translate("+n+","+i+")"):e=this.attributes.position;var a={};return a[e]=t,a}},{key:"setHandlePosition",value:function(t){var e=this.makeHandlePositionStyle(t);this.$handle.css(e),this.is("dragging")||(this.handlePosition=parseFloat(t))}},{key:"moveTo",value:function(t,e,n){var s="undefined"==typeof t?"undefined":h(t);"string"===s&&(i(t)&&(t=a(t)*(this.barLength-this.handleLength)),t=parseFloat(t),s="number"),"number"===s&&this.move(t,e,n)}},{key:"moveBy",value:function(t,e,n){var s="undefined"==typeof t?"undefined":h(t);"string"===s&&(i(t)&&(t=a(t)*(this.barLength-this.handleLength)),t=parseFloat(t),s="number"),"number"===s&&this.move(this.handlePosition+t,e,n)}},{key:"move",value:function(t,e,n){"number"!=typeof t||this.is("disabled")||(t<0?t=0:t+this.handleLength>this.barLength&&(t=this.barLength-this.handleLength),this.is("dragging")||n===!0?(this.setHandlePosition(t),e&&this.trigger("change",t/(this.barLength-this.handleLength))):this.doMove(t,this.options.duration,this.options.easing,e))}},{key:"doMove",value:function(t,e,n,i){var a=this,s=void 0;this.enter("moving"),e=e?e:this.options.duration,n=n?n:this.options.easing;var r=this.makeHandlePositionStyle(t);for(s in r)if({}.hasOwnProperty.call(r,s))break;this.options.useCssTransitions&&p.transition?(this.enter("transition"),this.prepareTransition(s,e,n),this.$handle.one(p.transition.end,function(){a.$handle.css(p.transition,""),i&&a.trigger("change",t/(a.barLength-a.handleLength)),a.leave("transition"),a.leave("moving")}),this.setHandlePosition(t)):!function(){a.enter("animating");var e=o(),n=a.getHandlePosition(),s=t,r=function t(o){var r=(o-e)/a.options.duration;r>1&&(r=1),r=a.easing.fn(r);var h=10,l=parseFloat(n+r*(s-n),h);a.setHandlePosition(l),i&&a.trigger("change",l/(a.barLength-a.handleLength)),1===r?(window.cancelAnimationFrame(a._frameId),a._frameId=null,a.leave("animating"),a.leave("moving")):a._frameId=window.requestAnimationFrame(t)};a._frameId=window.requestAnimationFrame(r)}()}},{key:"prepareTransition",value:function(t,e,n,i){var a=[];t&&a.push(t),e&&(r.default.isNumeric(e)&&(e+="ms"),a.push(e)),n?a.push(n):a.push(this.easing.css),i&&a.push(i),this.$handle.css(p.transition,a.join(" "))}},{key:"enable",value:function(){this._states.disabled=0,this.$bar.removeClass(this.options.disabledClass),this.trigger("enable")}},{key:"disable",value:function(){this._states.disabled=1,this.$bar.addClass(this.options.disabledClass),this.trigger("disable")}},{key:"destroy",value:function(){this.$handle.removeClass(this.classes.handleClass),this.$bar.removeClass(this.classes.barClass).removeClass(this.classes.directionClass).attr("draggable",null),this.options.skin&&this.$bar.removeClass(this.options.skin),this.$bar.off(this.eventName()),this.$handle.off(this.eventName()),this.trigger("destroy")}}],[{key:"registerEasing",value:function(t){for(var e=arguments.length,n=Array(e>1?e-1:0),i=1;i<e;i++)n[i-1]=arguments[i];c[t]=d.apply(void 0,n)}},{key:"getEasing",value:function(t){return c[t]}},{key:"setDefaults",value:function(t){r.default.extend(u,r.default.isPlainObject(t)&&t)}}]),t}(),y={version:"0.5.4"},w="asScrollbar",k=r.default.fn.asScrollbar,C=function(t){for(var e=this,n=arguments.length,i=Array(n>1?n-1:0),a=1;a<n;a++)i[a-1]=arguments[a];if("string"==typeof t){var s=function(){var n=t;if(/^_/.test(n))return{v:!1};if(!/^(get)/.test(n))return{v:e.each(function(){var t=r.default.data(this,w);t&&"function"==typeof t[n]&&t[n].apply(t,i)})};var a=e.first().data(w);return a&&"function"==typeof a[n]?{v:a[n].apply(a,i)}:void 0}();if("object"===("undefined"==typeof s?"undefined":h(s)))return s.v}return this.each(function(){(0,r.default)(this).data(w)||(0,r.default)(this).data(w,new b(this,t))})};r.default.fn.asScrollbar=C,r.default.asScrollbar=r.default.extend({setDefaults:b.setDefaults,registerEasing:b.registerEasing,getEasing:b.getEasing,noConflict:function(){return r.default.fn.asScrollbar=k,C}},y)});


/*
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

/**
* jQuery asIconPicker v0.2.3
* https://github.com/amazingSurge/jquery-asIconPicker
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
(function(global, factory) {
  if (typeof define === "function" && define.amd) {
    define(['jquery'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery);
    global.jqueryAsIconPickerEs = mod.exports;
  }
})(this,

  function(_jquery) {
    'use strict';

    var _jquery2 = _interopRequireDefault(_jquery);

    function _interopRequireDefault(obj) {
      return obj && obj.__esModule ? obj : {
        default: obj
      };
    }

    var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ?

      function(obj) {
        return typeof obj;
      }
      :

      function(obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
      };

    function _classCallCheck(instance, Constructor) {
      if (!(instance instanceof Constructor)) {
        throw new TypeError("Cannot call a class as a function");
      }
    }

    var _createClass = function() {
      function defineProperties(target, props) {
        for (var i = 0; i < props.length; i++) {
          var descriptor = props[i];
          descriptor.enumerable = descriptor.enumerable || false;
          descriptor.configurable = true;

          if ("value" in descriptor)
            descriptor.writable = true;
          Object.defineProperty(target, descriptor.key, descriptor);
        }
      }

      return function(Constructor, protoProps, staticProps) {
        if (protoProps)
          defineProperties(Constructor.prototype, protoProps);

        if (staticProps)
          defineProperties(Constructor, staticProps);

        return Constructor;
      };
    }();

    /* eslint no-empty-function:"off" */
    var DEFAULTS = {
      namespace: 'asIconPicker',
      source: false, // Icons source
      tooltip: true,
      hasSearch: true,
      extraClass: 'fa',
      iconPrefix: 'fa-',
      emptyText: 'None Selected',
      searchText: 'Search',
      cancelSelected: true,
      keyboard: true,
      flat: false,
      heightToScroll: '290',

      iconPicker: function iconPicker() {
        return '<div class="namespace-selector">' + '<span class="namespace-selected-icon">' + 'None selected' + '</span>' + '<span class="namespace-selector-arrow">' + '<i></i>' + '</span>' + '</div>' + '<div class="namespace-selector-popup">' + '<div class="namespace-icons-container"></div>' + '</div>';
      },
      iconSearch: function iconSearch() {
        return '<div class="namespace-selector-search">' + '<input type="text" name="" value="" placeholder="searchText" class="namespace-search-input"/>' + '<i class="namespace-search-icon"></i>' + '</div>';
      },
      formatNoMatches: function formatNoMatches() {
        return 'No matches found';
      },
      errorHanding: function errorHanding() {},
      process: function process(value) {
        if (value && value.match(this.iconPrefix)) {

          return value.replace(this.iconPrefix, '');
        }

        return value;
      },
      parse: function parse(value) {
        if (value.match(this.iconPrefix)) {

          return value;
        }

        return this.iconPrefix + value;
      },
      // callback
      onInit: null,
      onReady: null,
      onAfterFill: null
    };

    var keyboard = {
      init: function init(self) {
        this.attach(self, this.gather(self));
      },
      destroy: function destroy(self) {
        self.$wrapper.off('keydown');
        self.bound = false;
      },
      keys: function keys() {
        return {
          LEFT: 37,
          UP: 38,
          RIGHT: 39,
          DOWN: 40,
          ENTER: 13,
          ESC: 27
        };
      },
      horizontalChange: function horizontalChange(step) {
        if (!this.$mask && !this.options.flat) {
          this._open();

          return;
        }
        this.index += parseInt(step, 10);

        if (this.index >= this.iconsAll.length) {
          this.index = this.iconsAll.length - 1;
        } else if (this.index < 0) {
          this.index = 0;
        }
        this.current = this.iconsAll[this.index];
        this.set(this.current);
      },
      verticalChange: function verticalChange(step) {
        if (!this.$mask && !this.options.flat) {
          this._open();

          return;
        }
        var ulHeight = this.$iconContainer.find('.' + this.namespace + '-list').width();
        var liHeight = this.$iconContainer.find('.' + this.namespace + '-list li').width(),
          lineNumber = Math.floor(ulHeight / liHeight);

        step = parseInt(step, 10);

        if (this.index >= 0 && this.$iconContainer.find('.' + this.namespace + '-group').text()) {
          var siblingNumber = this.$iconContainer.find('.' + this.current).parent().siblings().length + 1;
          var nextNumber = this.$iconContainer.find('.' + this.current).parents('.' + this.namespace + '-group').next().find('li').length;
          var prevNumber = this.$iconContainer.find('.' + this.current).parents('.' + this.namespace + '-group').prev().find('li').length;
          var index = this.$iconContainer.find('.' + this.current).parent().index();
          var remain = void 0;

          if (step === 1) {
            remain = siblingNumber % lineNumber;

            if (index + lineNumber >= siblingNumber && nextNumber) {

              if (index + remain >= siblingNumber && remain > 0) {

                if (index + remain >= siblingNumber + nextNumber) {
                  this.index += nextNumber;
                } else {
                  this.index += remain;
                }
              } else {

                if (index + remain + lineNumber >= siblingNumber + nextNumber) {
                  this.index += remain + nextNumber;
                } else {
                  this.index += remain + lineNumber;
                }
              }
            } else {
              this.index += lineNumber;
            }
          } else if (step === -1) {
            remain = prevNumber % lineNumber;

            if (index > remain - 1 && index < lineNumber) {

              if (prevNumber >= lineNumber) {
                this.index -= lineNumber + remain;
              } else {
                this.index -= index + 1;
              }
            } else if (index <= remain - 1) {
              this.index -= remain;
            } else {
              this.index -= lineNumber;
            }
          }
        } else {
          this.index += lineNumber * step;
        }

        if (this.index >= this.iconsAll.length) {
          this.index = this.iconsAll.length - 1;
        } else if (this.index < 0) {
          this.index = 0;
        }
        this.current = this.iconsAll[this.index];
        this.set(this.current);
      },
      enter: function enter() {
        if (this.$mask) {

          if (this.current) {
            this.set(this.current);
            this._hide();
          }
        } else {
          this._open();
        }
      },
      esc: function esc() {
        this.set(this.previous);
        this._hide();
      },
      tab: function tab() {
        this._hide();
      },
      gather: function gather(self) {

        return {
          left: _jquery.proxy(this.horizontalChange, self, '-1'),
          up: _jquery.proxy(this.verticalChange, self, '-1'),
          right: _jquery.proxy(this.horizontalChange, self, '1'),
          down: _jquery.proxy(this.verticalChange, self, '1'),
          enter: _jquery.proxy(this.enter, self),
          esc: _jquery.proxy(this.esc, self)
        };
      },
      press: function press(e) {
        var key = e.keyCode || e.which;

        if (key === 9) {
          this._keyboard.tab.call(this);
        }

        if (key in this.map && typeof this.map[key] === 'function') {
          e.preventDefault();

          return this.map[key].call(this);
        }
        var that = this;
        this.$iconPicker.find('.' + this.namespace + '-search-input').one('keyup',

          function() {
            that.searching(_jquery(this).val());
          }
        );
      },
      attach: function attach(self, map) {
        var _this = this;

        var key = void 0;

        for (key in map) {

          if (map.hasOwnProperty(key)) {
            var parts = this._stringSeparate(key, '_'),
              uppercase = [];
            var len = parts.length;

            if (len === 1) {
              uppercase[0] = parts[0].toUpperCase();
              self.map[this.keys()[uppercase[0]]] = map[key];
            } else {

              for (var i = 0; i < parts.length; i++) {
                uppercase[i] = parts[i].toUpperCase();

                if (i === 0) {

                  if (self.map[this.keys()[uppercase[0]]] === undefined) {
                    self.map[this.keys()[uppercase[0]]] = {};
                  }
                } else {
                  self.map[this.keys()[uppercase[0]]][this.keys()[uppercase[i]]] = map[key];
                }
              }
            }
          }
        }

        if (!self.bound) {
          self.bound = true;
          self.$wrapper.on('keydown',

            function(e) {
              _this.press.call(self, e);
            }
          );
        }
      },
      _stringSeparate: function _stringSeparate(str, separator) {
        var re = new RegExp('[.\\' + separator + '\\s].*?');
        separator = str.match(re);
        var parts = str.split(separator);

        return parts;
      }
    };

    var NAMESPACE$1 = 'asIconPicker';

    var asIconPicker = function() {
      function asIconPicker(element, options) {
        _classCallCheck(this, asIconPicker);

        this.element = element;
        this.$element = (0, _jquery2.default)(element);

        this.options = _jquery2.default.extend({}, DEFAULTS, options, this.$element.data());

        this.namespace = this.options.namespace;

        this.classes = {
          disabled: this.namespace + '_disabled',
          wrapper: this.namespace + '-wrapper',
          search: this.namespace + '_with_search',
          active: this.namespace + '_active',
          flat: this.namespace + '_isFlat',
          hide: this.namespace + '_hide',
          hover: this.namespace + '_hover',
          mask: this.namespace + '-mask'
        };

        this.$element.addClass(this.namespace);
        this.$element.wrap('<div class="' + this.classes.wrapper + '"></div>');
        this.$wrapper = this.$element.parent();

        //make $wrapper can be focused
        this.$wrapper.attr('tabindex', '0');

        var iconPicker = this.options.iconPicker().replace(/namespace/g, this.namespace),
          iconSearch = this.options.iconSearch().replace(/namespace/g, this.namespace).replace(/searchText/g, this.options.searchText);
        this.$iconPicker = (0, _jquery2.default)(iconPicker);
        this.$iconContainer = this.$iconPicker.find('.' + this.namespace + '-icons-container');
        this.$iconSearch = (0, _jquery2.default)(iconSearch);

        if (this.options.hasSearch) {
          this.$iconContainer.before(this.$iconSearch);
          this.$iconContainer.parent().addClass(this.classes.search);
          this.iconsSearched = [];
        }
        this.map = {};
        this.bound = false;
        this.isSearch = false;
        this.current = this.$element.val();
        this.source = [];

        // flag
        this.disabled = false;
        this.initialized = false;

        this._trigger('init');
        this.init();
      }

      _createClass(asIconPicker, [{
        key: 'init',
        value: function init() {
          var that = this;
          // Hide source element
          this.$element.hide();

          // Add the icon picker after the select
          this.$element.before(this.$iconPicker);

          if (!this.options.source && this.$element.is('select')) {
            this.source = this._getSourceFromSelect();
          } else {
            this.source = this._processSource(this.options.source);
          }

          // Load icons
          this.showLoading();

          this.$wrapper.find('.' + this.namespace + '-selector-popup').addClass(this.classes.hide);
          /**
           * On down arrow click
           */

          if (!that.options.flat) {
            this.$wrapper.find('.' + this.namespace + '-selector').on('click',

              function() {
                // Open/Close the icon picker
                that._open();
              }
            );
          } else {
            that._open();
          }

          if (!this.options.keyboard) {
            this.$iconPicker.find('.' + this.namespace + '-search-input').keyup(_jquery2.default.proxy(

              function(e) {
                that.searching((0, _jquery2.default)(e.currentTarget).val());
              }
              , this));
          } else {
            this.$wrapper.on('focus',

              function() {
                keyboard.init(that);
              }
            );
          }

          this.$iconPicker.on('click', '.' + this.namespace + '-isSearching .' + this.namespace + '-search-icon', _jquery2.default.proxy(

            function() {
              this.$iconPicker.find('.' + this.namespace + '-search-input').focus().select();
              this.reset();
            }
            , this));

          this.$iconContainer.on('click', '.' + this.namespace + '-list li', _jquery2.default.proxy(

            function(e) {
              if (this.options.cancelSelected && (0, _jquery2.default)(e.currentTarget).hasClass(this.namespace + '-current')) {
                (0, _jquery2.default)(e.currentTarget).removeClass(this.namespace + '-current');
                this.set();

                return;
              }
              this.set((0, _jquery2.default)(e.currentTarget).children().data('value'));
              this._hide();
            }
            , this)).on('mouseenter', '.' + this.namespace + '-list li', _jquery2.default.proxy(

            function(e) {
              this.highlight((0, _jquery2.default)(e.currentTarget).children().data('value'));
            }
            , this)).on('mouseleave', '.' + this.namespace + '-list li', _jquery2.default.proxy(

            function() {
              this.highlight();
            }
            , this));

          /**
           * Stop click propagation on iconpicker
           */
          this.$iconPicker.click(

            function(event) {
              event.stopPropagation();
              that.$iconPicker.find('.' + that.namespace + '-search-input').focus().select();

              return false;
            }
          );

          this.initialized = true;
          // after init end trigger 'ready'
          this._trigger('ready');
        }
      }, {
        key: '_getSourceFromSelect',
        value: function _getSourceFromSelect() {
          var source = [];
          this.$element.children().each(

            function(i, el) {
              var $el = (0, _jquery2.default)(el);

              if ($el.is('optgroup')) {
                var group = _jquery2.default.extend({}, $el.data(), {
                  label: el.label,
                  items: []
                });
                var $children = $el.children();
                var length = $children.length;

                for (var j = 0; j < length; j++) {
                  group.items.push({
                    value: $children.eq(j).val(),
                    text: $children.eq(j).text()
                  });
                }
                source.push(group);
              } else if ($el.is('option')) {
                source.push({
                  value: $el.val(),
                  text: $el.text()
                });
              }
            }
          );

          return source;
        }
      }, {
        key: '_processSource',
        value: function _processSource(source) {
          var processItem = function processItem(key, item) {
            if (typeof key === 'string') {

              return {
                value: key,
                text: item
              };
            }

            if (typeof item === 'string') {

              return {
                value: item,
                text: item
              };
            }

            return item;
          };
          var processSource = [];

          if (!_jquery2.default.isArray(source)) {

            for (var key in source) {

              if ({}.hasOwnProperty.call(source, key)) {
                processSource.push(processItem(key, source[key]));
              }
            }
          } else {

            for (var i = 0; i < source.length; i++) {

              if (source[i].items) {

                if (_jquery2.default.isArray(source[i].items)) {

                  for (var j = 0; j < source[i].items.length; j++) {
                    source[i].items[j] = processItem(j, source[i].items[j]);
                  }
                  processSource[i] = source[i];
                } else {
                  processSource[i] = {
                    label: source[i].label,
                    items: []
                  };

                  for (var k in source[i].items) {

                    if ({}.hasOwnProperty.call(source[i].item, k)) {
                      processSource[i].items.push(processItem(k, source[i].items[k]));
                    }
                  }
                }
              } else {
                processSource[i] = processItem(i, source[i]);
              }
            }
          }

          return processSource;
        }
      }, {
        key: 'showLoading',
        value: function showLoading() {
          this.$iconContainer.html('<span class="' + this.namespace + '-loading"><i></i></span>');

          // If source is set

          if (this.source.length > 0) {

            // Render icons
            this.fillIcon();
          }
        }
      }, {
        key: 'searching',
        value: function searching(value) {
          var _this2 = this;

          // If the string is not empty

          if (value === '') {
            this.reset();

            return;
          }

          // Set icon search to X to reset search
          this.$iconSearch.addClass(this.namespace + '-isSearching');

          // Set this as a search
          this.isSearch = true;
          this.iconsSearched = [];

          var isMatchedItem = function isMatchedItem(item) {
            return _this2.replaceDiacritics(item.text).toLowerCase().search(value.toLowerCase()) >= 0;
          };
          var groupSearched = {};
          // Actual search

          for (var i = 0, item; item = this.source[i]; i++) {

            if (typeof item.items !== 'undefined') {
              groupSearched = {
                label: item.label,
                items: _jquery2.default.grep(item.items,

                  function(n) {
                    return isMatchedItem(n);
                  }
                )
              };

              if (groupSearched.items.length > 0) {
                this.iconsSearched.push(groupSearched);
              }
            } else {

              if (isMatchedItem(item)) {
                this.iconsSearched.push(item);
              }
            }
          }

          if (this.iconsSearched.length > 0) {
            var first = this.iconsSearched[0];

            if (typeof first.items !== 'undefined') {
              this.current = first.items[0].value;
            } else {
              this.current = first.value;
            }
          } else {
            this.current = '';
          }

          // Render icon list
          this.fillIcon();
        }
      }, {
        key: 'fillIcon',
        value: function fillIcon() {
          var that = this;

          if (typeof this.$iconContainer.data('asIconPicker') !== 'undefined') {
            this.$iconContainer.asIconPicker('destroy');
          }
          var tempIcons = [];
          this.iconsAll = [];

          // Set a temporary array for icons

          if (this.isSearch) {
            tempIcons = this.iconsSearched;
          } else {
            tempIcons = this.source;
          }

          // If not show an error when no icons are found

          if (tempIcons.length < 1) {
            this.$iconContainer.html('<div class="' + this.namespace + '-noMatch">' + this.options.formatNoMatches() + '</div>');

            return;

          // else empty the container
          }
          this.$iconContainer.html('');

          // List icons
          var itemHTML = function itemHTML(item) {
            that.iconsAll.push(item.value);

            return (0, _jquery2.default)('<li/>', {
              html: '<i class="' + that.options.extraClass + ' ' + item.value + '" data-value="' + item.value + '"></i>',
              title: that.options.tooltip ? item.text : ''
            });
          };
          var $group = void 0;

          for (var i = 0, item; i < tempIcons.length; i++) {
            item = tempIcons[i];

            if (typeof item.label !== 'undefined') {

              if (item.items.length) {
                $group = (0, _jquery2.default)('<div class="' + this.namespace + '-group"><div class="' + this.namespace + '-group-label">' + item.label + ':</div><ul class="' + this.namespace + '-list"></ul></div>').appendTo(this.$iconContainer);
              }

              for (var j = 0, option; option = item.items[j]; j++) {
                itemHTML(option).appendTo($group.find('ul'));
              }
            } else {
              var listClass = this.$iconContainer.children().last().attr('class');

              if (listClass !== this.namespace + '-list') {
                (0, _jquery2.default)('<ul class="' + this.namespace + '-list"></ul>').appendTo(this.$iconContainer);
              }
              itemHTML(item).appendTo(this.$iconContainer.children().last());
            }
          }

          if (this.options.tooltip) {
            _jquery2.default.asTooltip.closeAll();
            this.$iconContainer.find('.' + this.namespace + '-list li').asTooltip({
              namespace: 'asTooltip',
              skin: 'skin-dream',
              onlyOne: true
            });
          }

          this.index = _jquery2.default.inArray(this.current, this.iconsAll);

          if (this.index >= 0) {
            this.set(this.current, false);
          } else {
            this.set(null, false);
          }

          // Add the scrollbar in the iconContainer
          this.$iconContainer.asScrollbar({
            namespace: that.namespace + '-icons'
          });

          this._trigger('afterFill');
        }
      }, {
        key: 'replaceDiacritics',
        value: function replaceDiacritics(s) {
          var k = void 0;
          var d = '40-46 50-53 54-57 62-70 71-74 61 47 77'.replace(/\d+/g, '\\3$&').split(' ');

          for (k in d) {

            if ({}.hasOwnProperty.call(d, k)) {
              s = s.toLowerCase().replace(new RegExp('[' + d[k] + ']', 'g'), 'aeiouncy'.charAt(k));
            }
          }

          return s;
        }
      }, {
        key: 'highlight',
        value: function highlight(icon) {
          if (icon) {
            this.$iconPicker.find('.' + icon).parent().addClass(this.classes.hover);
          } else {
            this.$iconPicker.find('.' + this.classes.hover).removeClass(this.classes.hover);
          }
        }
      }, {
        key: 'scrollToSelectedIcon',
        value: function scrollToSelectedIcon() {
          if (this.current) {
            var ulWidth = this.$iconContainer.find('.' + this.namespace + '-list').width();
            var containerHeight = this.$iconContainer.height(),
              liHeight = this.$iconContainer.find('.' + this.namespace + '-list li').height(),
              liTop = this.$iconContainer.find('.' + this.current).parent().offset().top,
              liWidth = this.$iconContainer.find('.' + this.namespace + '-list li').width(),
              lineNumber = Math.floor(ulWidth / liWidth),
              ulTop = this.$iconContainer.find('.' + this.namespace + '-list').offset().top;

            if (this.index < lineNumber) {
              this.value = 0;
            } else {
              this.value = (liTop + liHeight - ulTop) / containerHeight;
            }
          }
          this.$iconContainer.asIconPicker('move', this.value, true);
        }
      }, {
        key: 'reset',
        value: function reset() {
          // Empty input
          this.$iconPicker.find('.' + this.namespace + '-search-input').val('');

          // Reset search icon class
          this.$iconSearch.removeClass(this.namespace + '-isSearching');
          this.isSearch = false;

          // Fill icons
          this.fillIcon();

          // Add the scrollbar in the iconContainer

          if (this.$iconContainer.outerHeight() >= this.options.heightToScroll) {
            this.$iconContainer.asScrollbar();
          }
        }
      }, {
        key: '_open',
        value: function _open() {
          var $selector = this.$wrapper.find('.' + this.namespace + '-selector'),
            that = this;

          if (that.options.flat) {
            $selector.addClass(this.classes.flat);
            $selector.siblings('.' + this.namespace + '-selector-popup').addClass(this.classes.flat).removeClass(this.classes.hide);
          } else {
            $selector.addClass(this.classes.active);
            $selector.siblings('.' + this.namespace + '-selector-popup').addClass(this.classes.active).removeClass(this.classes.hide);
            this.previous = this.current;

            if ($selector.hasClass(this.classes.active) && !that.options.flat) {
              this.$iconPicker.find('.' + this.namespace + '-search-input').focus().select();
              this.$mask = (0, _jquery2.default)('<div></div>').addClass(this.classes.mask).appendTo(this.$element.parent());
              this.$mask.on('click',

                function() {
                  that._hide();
                }
              );
            }
          }
        }
      }, {
        key: '_hide',
        value: function _hide() {
          if (this.options.flat) {

            return;
          }

          if (this.options.keyboard) {
            keyboard.destroy(this);
          }

          this._clearMask();
          this.$wrapper.find('.' + this.namespace + '-selector').removeClass(this.classes.active);
          this.$wrapper.find('.' + this.namespace + '-selector-popup').addClass(this.classes.hide).removeClass(this.classes.active);
          this.$wrapper.focus();
        }
      }, {
        key: '_clearMask',
        value: function _clearMask() {
          if (this.$mask) {
            this.$mask.off('.asIconPicker');
            this.$mask.remove();
            this.$mask = null;
          }
        }
      }, {
        key: '_trigger',
        value: function _trigger(eventType) {
          for (var _len = arguments.length, params = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
            params[_key - 1] = arguments[_key];
          }

          var data = [this].concat(params);

          // event
          this.$element.trigger(NAMESPACE$1 + '::' + eventType, data);

          // callback
          eventType = eventType.replace(/\b\w+\b/g,

            function(word) {
              return word.substring(0, 1).toUpperCase() + word.substring(1);
            }
          );
          var onFunction = 'on' + eventType;

          if (typeof this.options[onFunction] === 'function') {
            this.options[onFunction].apply(this, params);
          }
        }
      }, {
        key: '_update',
        value: function _update() {
          this.$element.val(this.val());
          this._trigger('change', this.current);
        }
      }, {
        key: 'load',
        value: function load(source) {
          if (typeof source !== 'undefined') {
            this.source = this._processSource(source);
          }

          if (this.options.flat) {
            this.showLoading();
          } else {
            this.$wrapper.find('.' + this.namespace + '-selector-popup').removeClass(this.classes.hide);
            this.showLoading();
            this.$wrapper.find('.' + this.namespace + '-selector-popup').addClass(this.classes.hide);
          }
        }
      }, {
        key: 'get',
        value: function get() {
          return this.current;
        }
      }, {
        key: 'set',
        value: function set(icon, update) {

          this.$iconContainer.find('.' + this.namespace + '-current').removeClass(this.namespace + '-current');

          if (icon) {
            this.$iconContainer.find('[data-value="' + icon + '"]').parent().addClass(this.namespace + '-current');
            this.$iconPicker.find('.' + this.namespace + '-selected-icon').removeClass(this.namespace + '-none-selected').html('<i class="' + this.options.extraClass + ' ' + icon + '"></i>' + this.options.process(icon));
          } else {
            this.$iconPicker.find('.' + this.namespace + '-selected-icon').addClass(this.namespace + '-none-selected').html('<i class="' + this.options.extraClass + ' ' + this.options.iconPrefix + 'ban"></i>' + this.options.emptyText);
          }

          this.current = icon;
          this.index = _jquery2.default.inArray(this.current, this.iconsAll);
          this.scrollToSelectedIcon();

          if (update !== false) {
            this._update();
          }
        }
      }, {
        key: 'clear',
        value: function clear() {
          this.set(null);
        }
      }, {
        key: 'val',
        value: function val(value) {
          if (typeof value === 'undefined') {

            return this.options.process(this.current);
          }

          var valueObj = this.options.parse(value);

          if (valueObj) {
            this.set(valueObj);
          } else {
            this.clear();
          }
        }
      }, {
        key: 'enable',
        value: function enable() {
          this.disabled = false;

          // which element is up to your requirement
          this.$wrapper.removeClass(this.classes.disabled);
          this._trigger('enable');
        // here maybe have some events detached
        }
      }, {
        key: 'disable',
        value: function disable() {
          this.disabled = true;
          // which element is up to your requirement
          // .disabled { pointer-events: none; } NO SUPPORT IE11 BELOW
          this.$wrapper.addClass(this.classes.disabled);
          this._trigger('disable');
        }
      }, {
        key: 'destroy',
        value: function destroy() {
          // detached events first
          // then remove all js generated html
          this.$element.data(NAMESPACE$1, null);
          this._trigger('destroy');
        }
      }], [{
        key: 'setDefaults',
        value: function setDefaults(options) {
          _jquery2.default.extend(DEFAULTS, _jquery2.default.isPlainObject(options) && options);
        }
      }]);

      return asIconPicker;
    }();

    var info = {
      version: '0.2.3'
    };

    var NAMESPACE = 'asIconPicker';
    var OtherAsIconPicker = _jquery2.default.fn.asIconPicker;

    var jQueryAsIconPicker = function jQueryAsIconPicker(options) {
      var _this3 = this;

      for (var _len2 = arguments.length, args = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        args[_key2 - 1] = arguments[_key2];
      }

      if (typeof options === 'string') {
        var _ret = function() {
          var method = options;

          if (/^_/.test(method)) {

            return {
              v: false
            };
          } else if (/^(get)$/.test(method) || method === 'val' && args.length === 0) {
            var instance = _this3.first().data(NAMESPACE);

            if (instance && typeof instance[method] === 'function') {

              return {
                v: instance[method].apply(instance, args)
              };
            }
          } else {

            return {
              v: _this3.each(

                function() {
                  var instance = _jquery2.default.data(this, NAMESPACE);

                  if (instance && typeof instance[method] === 'function') {
                    instance[method].apply(instance, args);
                  }
                }
              )
            };
          }
        }();

        if ((typeof _ret === 'undefined' ? 'undefined' : _typeof(_ret)) === "object")

          return _ret.v;
      }

      return this.each(

        function() {
          if (!(0, _jquery2.default)(this).data(NAMESPACE)) {
            (0, _jquery2.default)(this).data(NAMESPACE, new asIconPicker(this, options));
          }
        }
      );
    };

    _jquery2.default.fn.asIconPicker = jQueryAsIconPicker;

    _jquery2.default.asIconPicker = _jquery2.default.extend({
      setDefaults: asIconPicker.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asIconPicker = OtherAsIconPicker;

        return jQueryAsIconPicker;
      }
    }, info);
  }
);