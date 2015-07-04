/**
 * Created by SnUser on 14-8-23.
 */
define(['jquery'],function($){
    var utils = {};
    utils.Class = (function(){  //基础类
        // -----------------------------------------------------------------------------
        // Class object based on John Resigs code; inspired by base2 and Prototype
        // http://ejohn.org/blog/simple-javascript-inheritance/
        var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\bparent\b/ : /.*/;
        var Class = function(){};
        Class.extend = function(prop) {
            var parent = this.prototype;

            initializing = true;
            var prototype = new this();
            initializing = false;

            for( var name in prop ) {
                if(
                    typeof(prop[name]) == "function" &&
                        typeof(parent[name]) == "function" &&
                        fnTest.test(prop[name])
                    ) {
                    prototype[name] = (function(name, fn){
                        return function() {
                            var tmp = this.parent;
                            this.parent = parent[name];
                            var ret = fn.apply(this, arguments);
                            this.parent = tmp;
                            return ret;
                        };
                    })( name, prop[name] );
                }
                else {
                    prototype[name] = prop[name];
                }
            }
            var ig = {
                copy: function( object ) {
                    if(
                        !object || typeof(object) != 'object' ||
                            object instanceof HTMLElement ||
                            //object instanceof ig.Class
                            object instanceof Class
                        ) {
                        return object;
                    }
                    else if( object instanceof Array ) {
                        var c = [];
                        for( var i = 0, l = object.length; i < l; i++) {
                            c[i] = ig.copy(object[i]);
                        }
                        return c;
                    }
                    else {
                        var c = {};
                        for( var i in object ) {
                            c[i] = ig.copy(object[i]);
                        }
                        return c;
                    }
                }
            }

            function Class() {
                if( !initializing ) {

                    // If this class has a staticInstantiate method, invoke it
                    // and check if we got something back. If not, the normal
                    // constructor (init) is called.
                    if( this.staticInstantiate ) {
                        var obj = this.staticInstantiate.apply(this, arguments);
                        if( obj ) {
                            return obj;
                        }
                    }
                    for( var p in this ) {
                        if( typeof(this[p]) == 'object' ) {
                            this[p] = ig.copy(this[p]); // deep copy!
                        }
                    }
                    if( this.init ) {
                        this.init.apply(this, arguments);
                    }
                }
                return this;
            }

            Class.prototype = prototype;
            Class.constructor = Class;
            Class.extend = arguments.callee;
            Class.inject = function(prop) {
                var proto = this.prototype;
                var parent = {};
                for( var name in prop ) {
                    if(
                        typeof(prop[name]) == "function" &&
                            typeof(proto[name]) == "function" &&
                            fnTest.test(prop[name])
                        ) {
                        parent[name] = proto[name]; // save original function
                        proto[name] = (function(name, fn){
                            return function() {
                                var tmp = this.parent;
                                this.parent = parent[name];
                                var ret = fn.apply(this, arguments);
                                this.parent = tmp;
                                return ret;
                            };
                        })( name, prop[name] );
                    }
                    else {
                        proto[name] = prop[name];
                    }
                }
            };

            return Class;
        };
        return Class;
    })();
    utils.jqmapping = function(metaObj, context, defaultPrefix) {       //映射
        var dpf = '';
        if (typeof defaultPrefix != 'undefined' && null != defaultPrefix) {
            dpf = defaultPrefix;
        }
        metaObj = metaObj || {};

        for (var key in metaObj) {
            if (null == metaObj[key]) {
                metaObj[key] = context.find(dpf + key);
            } else {
                var val = metaObj[key];
                var type = typeof val;
                switch (type) {
                    case 'string':
                        metaObj[key] = context.find(val);
                        break;
                    case 'object':
                        metaObj[key] = arguments.callee(metaObj[key], context, defaultPrefix);
                        break;
                    default:        //不处理
                        break;
                }
            }
        }
        return metaObj;
    }
    $.fn.aclick = function(func){
        var ename = '';
        if(utils.browser.versions.mobile){
            ename='touchstart';
        }else{
            ename='click';
        }
        if(func){
            return $(this).bind(ename, func);
        }else{
            return $(this).trigger(ename);
        }
    }
    $.fn.endclick = function(func){
        $(this).bind('touchend', func);
    }
    $.fn.touchSlider =(function(){
                    var touchSlider = {

                            		init : function () {
                            			var _this = this;

                            			$(this).children().css({
                            				"width":this._width + "px",
                            				"overflow":"visible"
                            			});

                            			if(this.opts.flexible) this._item_w = this._width / this._view;
                            			if(this.opts.roll) this._len = Math.ceil(this._len / this._view) * this._view;

                            			var page_gap = (this.opts.page > 1 && this.opts.page <= this._len) ? (this.opts.page - 1) * this._item_w : 0;

                            			for(var i=0; i<this._len; ++i) {
                            				this._pos[i] = this._item_w * i - page_gap;
                            				this._start[i] = this._pos[i];
                            				this._list.eq(i).css({
                            					"float" : "none",
                            					"display" : "block",
                            					"position" : "absolute",
                            					"top" : "0",
                            					"left" : this._pos[i] + "px",
                            					"width" : this._item_w + "px"
                            				});
                            				if(this.opts.supportsCssTransitions && this.opts.transition) {
                            					this._list.eq(i).css({
                            						"-moz-transition" : "0ms",
                            						"-moz-transform" : "",
                            						"-ms-transition" : "0ms",
                            						"-ms-transform" : "",
                            						"-webkit-transition" : "0ms",
                            						"-webkit-transform" : "",
                            						"transition" : "0ms",
                            						"transform" : ""
                            					});
                            				}
                            			}

                            			if(this.opts.btn_prev && this.opts.btn_next) {
                            				this.opts.btn_prev.bind("click", function() {
                            					_this.animate(1, true);
                            					return false;
                            				})
                            				this.opts.btn_next.bind("click", function() {
                            					_this.animate(-1, true);
                            					return false;
                            				});
                            			}

                            			if(this.opts.paging) {
                            				$(this._list).each(function (i, el) {
                            					//var btn_page = _this.opts.paging.eq(0).clone();
                            					var btn_page = _this.opts.paging.eq(i);
                            					//_this.opts.paging.before(btn_page);

                            					btn_page.bind("click", function(e) {
                            						_this.go_page(i, e);
                            						return false;
                            					});
                            				});

                            				//this.opts.paging.remove();
                            			}

                            			this.counter();
                            			this.initComplete();
                            		},

                            		initComplete : function () {
                            			if(typeof(this.opts.initComplete) == "function") {
                            				this.opts.initComplete(this);
                            			}
                            		},

                            		resize : function (e) {
                            			if(e.opts.flexible) {
                            				var tmp_w = e._item_w;

                            				e._width = parseInt(e._tg.css("width"));
                            				e._item_w = e._width / e._view;
                            				e._range = e.opts.range * e._width;

                            				for(var i=0; i<e._len; ++i) {
                            					e._pos[i] = e._pos[i] / tmp_w * e._item_w;
                            					e._start[i] = e._start[i] / tmp_w * e._item_w;
                            					e._list.eq(i).css({
                            						"left" : e._pos[i] + "px",
                            						"width" : e._item_w + "px"
                            					});
                            					if(this.opts.supportsCssTransitions && this.opts.transition) {
                            						e._list.eq(i).css({
                            							"-moz-transition" : "0ms",
                            							"-moz-transform" : "",
                            							"-ms-transition" : "0ms",
                            							"-ms-transform" : "",
                            							"-webkit-transition" : "0ms",
                            							"-webkit-transform" : "",
                            							"transition" : "0ms",
                            							"transform" : ""
                            						});
                            					}
                            				}
                            			}

                            			this.counter();
                            		},

                            		touchstart : function (e) {
                            			if((e.type == "touchstart" && e.originalEvent.touches.length <= 1) || e.type == "dragstart") {
                            				this._startX = e.pageX || e.originalEvent.touches[0].pageX;
                            				this._startY = e.pageY || e.originalEvent.touches[0].pageY;
                            				this._scroll = false;

                            				this._start = [];
                            				for(var i=0; i<this._len; ++i) {
                            					this._start[i] = this._pos[i];
                            				}
                            			}
                            		},

                            		touchmove : function (e) {
                            			if((e.type == "touchmove" && e.originalEvent.touches.length <= 1) || e.type == "drag") {
                            				this._left = (e.pageX || e.originalEvent.touches[0].pageX) - this._startX;
                            				this._top = (e.pageY || e.originalEvent.touches[0].pageY) - this._startY;
                            				var w = this._left < 0 ? this._left * -1 : this._left;
                            				var h = this._top < 0 ? this._top * -1 : this._top;

                            				if (w < h || this._scroll) {
                            					this._left = 0;
                            					this._drag = false;
                            					this._scroll = true;
                            				} else {
                            					e.preventDefault();
                            					this._drag = true;
                            					this._scroll = false;
                            					this.position(e);
                            				}

                            				for(var i=0; i<this._len; ++i) {
                            					var tmp = this._start[i] + this._left;

                            					if(this.opts.supportsCssTransitions && this.opts.transition) {
                            						var trans = "translate3d(" + tmp + "px,0,0)";
                            						this._list.eq(i).css({
                            							"left" : "",
                            							"-moz-transition" : "0ms",
                            							"-moz-transform" : trans,
                            							"-ms-transition" : "0ms",
                            							"-ms-transform" : trans,
                            							"-webkit-transition" : "0ms",
                            							"-webkit-transform" : trans,
                            							"transition" : "0ms",
                            							"transform" : trans
                            						});
                            					} else {
                            						this._list.eq(i).css("left", tmp + "px");
                            					}

                            					this._pos[i] = tmp;
                            				}
                            			}
                            		},

                            		touchend : function (e) {
                            			if((e.type == "touchend" && e.originalEvent.touches.length <= 1) || e.type == "dragend") {
                            				if(this._scroll) {
                            					this._drag = false;
                            					this._scroll = false;
                            					//return false;
                            					return true;
                            				}

                            				this.animate(this.direction());
                            				this._drag = false;
                            				this._scroll = false;
                            			}
                            		},

                            		position : function (d) {
                            			var gap = this._view * this._item_w;

                            			if(d == -1 || d == 1) {
                            				this._startX = 0;
                            				this._start = [];
                            				for(var i=0; i<this._len; ++i) {
                            					this._start[i] = this._pos[i];
                            				}
                            				this._left = d * gap;
                            			} else {
                            				if(this._left > gap) this._left = gap;
                            				if(this._left < - gap) this._left = - gap;
                            			}

                            			if(this.opts.roll) {
                            				var tmp_pos = [];
                            				for(var i=0; i<this._len; ++i) {
                            					tmp_pos[i] = this._pos[i];
                            				}
                            				tmp_pos.sort(function(a,b){return a-b;});


                            				var max_chk = tmp_pos[this._len-this._view];
                            				var p_min = $.inArray(tmp_pos[0], this._pos);
                            				var p_max = $.inArray(max_chk, this._pos);


                            				if(this._view <= 1) max_chk = this._len - 1;
                            				if(this.opts.multi) {
                            					if((d == 1 && tmp_pos[0] >= 0) || (this._drag && tmp_pos[0] >= 100)) {
                            						for(var i=0; i<this._view; ++i, ++p_min, ++p_max) {
                            							this._start[p_max] = this._start[p_min] - gap;
                            							this._list.eq(p_max).css("left", this._start[p_max] + "px");
                            						}
                            					} else if((d == -1 && tmp_pos[0] <= 0) || (this._drag && tmp_pos[0] <= -100)) {
                            						for(var i=0; i<this._view; ++i, ++p_min, ++p_max) {
                            							this._start[p_min] = this._start[p_max] + gap;
                            							this._list.eq(p_min).css("left", this._start[p_min] + "px");
                            						}
                            					}
                            				} else {
                            					if((d == 1 && tmp_pos[0] >= 0) || (this._drag && tmp_pos[0] > 0)) {
                            						for(var i=0; i<this._view; ++i, ++p_min, ++p_max) {
                            							this._start[p_max] = this._start[p_min] - gap;
                            							this._list.eq(p_max).css("left", this._start[p_max] + "px");
                            						}
                            					} else if((d == -1 && tmp_pos[max_chk] <= 0) || (this._drag && tmp_pos[max_chk] <= 0)) {
                            						for(var i=0; i<this._view; ++i, ++p_min, ++p_max) {
                            							this._start[p_min] = this._start[p_max] + gap;
                            							this._list.eq(p_min).css("left", this._start[p_min] + "px");
                            						}
                            					}
                            				}
                            			} else {
                            				if(this.limit_chk()) this._left = this._left / 2;
                            			}
                            		},

                            		animate : function (d, btn_click) {
                            			if(this._drag || !this._scroll || btn_click) {
                            				var _this = this;
                            				var speed = this._speed;

                            				if(btn_click) this.position(d);

                            				var gap = d * (this._item_w * this._view);
                            				if(this._left == 0 || (!this.opts.roll && this.limit_chk()) ) gap = 0;

                            				this._list.each(function (i, el) {
                            					_this._pos[i] = _this._start[i] + gap;

                            					if(_this.opts.supportsCssTransitions && _this.opts.transition) {
                            						var transition = speed + "ms";
                            						var transform = "translate3d(" + _this._pos[i] + "px,0,0)";

                            						if(btn_click) transition = "0ms";

                            						$(this).css({
                            							"left" : "",
                            							"-moz-transition" : transition,
                            							"-moz-transform" : transform,
                            							"-ms-transition" : transition,
                            							"-ms-transform" : transform,
                            							"-webkit-transition" : transition,
                            							"-webkit-transform" : transform,
                            							"transition" : transition,
                            							"transform" : transform
                            						});
                            					} else {
                            						$(this).stop();
                            						$(this).animate({"left": _this._pos[i] + "px"}, speed);
                            					}
                            				});

                            				this.counter();
                            			}
                            		},

                            		direction : function () {
                            			var r = 0;

                            			if(this._left < -(this._range)) r = -1;
                            			else if(this._left > this._range) r = 1;

                            			if(!this._drag || this._scroll) r = 0;

                            			return r;
                            		},

                            		limit_chk : function () {
                            			var last_p = parseInt((this._len - 1) / this._view) * this._view;
                            			return ( (this._start[0] == 0 && this._left > 0) || (this._start[last_p] == 0 && this._left < 0) );
                            		},

                            		go_page : function (i, e) {
                            			var crt = ($.inArray(0, this._pos) / this._view) + 1;
                            			var cal = crt - (i + 1);

                            			while(cal != 0) {
                            				if(cal < 0) {
                            					this.animate(-1, true);
                            					cal++;
                            				} else if(cal > 0) {
                            					this.animate(1, true);
                            					cal--;
                            				}
                            			}
                            		},

                            		counter : function () {
                            			if(typeof(this.opts.counter) == "function") {
                            				var param = {
                            					total : Math.ceil(this._len / this._view),
                            					current : ($.inArray(0, this._pos) / this._view) + 1
                            				};
                            				this.opts.counter(param);
                            			}
                            		}

                            	};
                    return function (settings) {

                            		settings.supportsCssTransitions = (function (style) {
                            			var prefixes = ['Webkit','Moz','Ms'];
                            			for(var i=0, l=prefixes.length; i < l; i++ ) {
                            				if( typeof style[prefixes[i] + 'Transition'] !== 'undefined') {
                            					return true;
                            				}
                            			}
                            			return false;
                            		})(document.createElement('div').style);

                            		settings = jQuery.extend({
                            			roll : true,
                            			flexible : false,
                            			btn_prev : null,
                            			btn_next : null,
                            			paging : null,
                            			speed : 75,
                            			view : 1,
                            			range : 0.15,
                            			page : 1,
                            			transition : false,
                            			initComplete : null,
                            			counter : null,
                            			multi : false
                            		}, settings);

                            		var opts = [];
                            		opts = $.extend({}, $.fn.touchSlider.defaults, settings);

                            		return this.each(function () {

                            			$.fn.extend(this, touchSlider);

                            			var _this = this;

                            			this.opts = opts;
                            			this._view = this.opts.view;
                            			this._speed = this.opts.speed;
                            			this._tg = $(this);
                            			this._list = this._tg.children().children();
                            			this._width = parseInt(this._tg.css("width"));
                            			this._item_w = parseInt(this._list.css("width"));
                            			this._len = this._list.length;
                            			this._range = this.opts.range * this._width;
                            			this._pos = [];
                            			this._start = [];
                            			this._startX = 0;
                            			this._startY = 0;
                            			this._left = 0;
                            			this._top = 0;
                            			this._drag = false;
                            			this._scroll = false;
                            			this._btn_prev;
                            			this._btn_next;

                            			this.init();

                            			$(this)
                            			.bind("touchstart", this.touchstart)
                            			.bind("touchmove", this.touchmove)
                            			.bind("touchend", this.touchend)
                            			.bind("dragstart", this.touchstart)
                            			.bind("drag", this.touchmove)
                            			.bind("dragend", this.touchend)

                            			$(window).bind("orientationchange resize", function () {
                            				_this.resize(_this);
                            			});
                            		});

                            	};
                })();
    utils.jq = {};
    utils.jq.initTab = function(tabs, contents, activecls) {      //onlyTab：表示不控制CONTENT面板
            var reg = /_user$/;
            var activeIndex = null;                //当前最后激活的tab null表示没有

            var m = {
                activeTab: function(index, refresh) {       //激活指定索引的tab  refresh:true 表示必须刷新
                    if (refresh || index != activeIndex) {
                        showTab(index);
                        showContent(index);     //显示对应的内容
                        if (activeIndex != null && this.passiveHandle.length > activeIndex) {
                            if (this.passiveHandle[activeIndex]) {
                                if (this.passiveHandle[activeIndex]) {
                                    this.passiveHandle[activeIndex]($(contents[activeIndex]));
                                }
                            }
                        }
                        if (this.activeHandle.length > index) {
                            //调用当前要激活的tab的激活事件
                            if (this.activeHandle[index]) {
                                this.activeHandle[index]($(contents[index]));
                            }
                        }
                        activeIndex = index;
                    }
                    return this;
                }

                    , getActiveIndex: function() {         //当前激活的tab的索引 返回null表示没有被激活的tab
                        return activeIndex;
                    }
                    , passiveHandle: new Array(tabs.length)           //所有取消激活的方法集合，没个方法对应对等索引的tab
                    , activeHandle: new Array(tabs.length)            ////所有激活的方法集合，没个方法对应对等索引的tab
            }
            tabs.each(function(index) {
                $(this).aclick(function() {
                    m.activeTab(index);
                });
            });

            function showTab(showindex) {
                tabs.each(function(index) {
                    if (showindex == index) {
                        $(this).addClass(activecls || 'active');
                    } else {
                        $(this).removeClass(activecls || 'active');
                    }
                });
            }

            function showContent(showindex) {     /*要显示的内容的索引*/
                contents.each(function(index) {
                    if (index == showindex) {
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            }


            return m;
        }
    utils.jq.switch = function(dom, activecls){
        var defaultcls = 'mui-active';
        var changefn = null;
        var obj = {
            onchange:function(fn){changefn = fn;return this;}
            ,isSwitch:function(){
                return dom.hasClass(activecls || defaultcls);
            }
        };
        dom.click(function(){
            dom.toggleClass(activecls || defaultcls);
            changefn && changefn(obj.isSwitch());
        });
        return obj;
    }

    utils.random = function(min,max){
        return Math.floor(min+Math.random()*(max-min));

    }

    utils.browser = {
            versions: function () {
                var u = navigator.userAgent, app = navigator.appVersion;
                var o = {//移动终端浏览器版本信息
                    trident: u.indexOf('Trident') > -1, //IE内核
                    presto: u.indexOf('Presto') > -1, //opera内核
                    webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                    gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                    mobile: !!u.match(/AppleWebKit.*Mobile.*/) || !!u.match(/AppleWebKit/), //是否为移动终端
                    ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                    iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器
                    iPad: u.indexOf('iPad') > -1, //是否iPad
                    webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
                };
                o.mobile = !!u.match(/AppleWebKit.*Mobile.*/);
                return o;
            } (),
            language: (navigator.browserLanguage || navigator.language).toLowerCase()
        }

    utils.tools = {};
    utils.tools.loadimg = function(c, d) {
        var b = new Image();
        b.src = c;
        var a = function() {
            d && d(b)
        };
        if (b.width) {
            a()
        } else {
            b.onload = a
        }
        b.onerror = function() {
            d && d(false)
        }
    };
    utils.tools.loadimgs = function(c, h) {
        var a = 0;
        var f = [];
        var b = [];
        if (!c || 0 == c.length) {
            h(f, b)
        } else {
            for (var d = 0; d < c.length; d++) {
                a++;
                var g = c[d];
                (function(i) {
                    setTimeout(function() {
                        utils.tools.loadimg(i, function(j) {
                            e(i, j)
                        })
                    }, d * 10)
                })(g)
            }
        }
        function e(j, i) {
            if (i) {
                f.push({src: j,img: i})
            } else {
                b.push({src: j,img: i})
            }
            h(f, b)
        }
    };
    utils.tools.getUrlParam  = function(c) {
        var b = window.location.href;
        var d = new RegExp("[?&]" + c + "=([^&]+)", "g");
        var g = d.exec(b);
        var a = null;
        if (null != g) {
            try {
                a = decodeURIComponent(decodeURIComponent(g[1]))
            } catch (f) {
                try {
                    a = decodeURIComponent(g[1])
                } catch (f) {
                    a = g[1]
                }
            }
        }
        return a
    };
    utils.getUrlPath = (function() {
        var a = window.location.origin;
        var d = window.location.pathname.split("/");
        var c = a + "/";
        for (var b = 0; b < d.length - 1; b++) {
            if (d[b]) {
                c += d[b] + "/"
            }
        }
        return c
    })();
    return utils;
});
