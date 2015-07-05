
window.iScroll = (function(){
            function iScroll (el, options) {
                var that = this, i;
                that.element = typeof el == 'object' ? el : document.getElementById(el);
                that.wrapper = that.element.parentNode;

                that.element.style.webkitTransitionProperty = '-webkit-transform';
                that.element.style.webkitTransitionTimingFunction = 'cubic-bezier(0,0,0.25,1)';
                that.element.style.webkitTransitionDuration = '0';
                that.element.style.webkitTransform = translateOpen + '0,0' + translateClose;

                // Default options
                that.options = {
                    bounce: has3d,
                    momentum: has3d,
                    checkDOMChanges: true,
                    topOnDOMChanges: false,
                    hScrollbar: has3d,
                    vScrollbar: has3d,
                    fadeScrollbar: isIphone || isIpad || !isTouch,
                    shrinkScrollbar: isIphone || isIpad || !isTouch,
                    desktopCompatibility: false,
                    overflow: 'hidden',
                    snap: false,elasticity
                    /*是否允许拖出界限*/
                    :true
                };

                // User defined options
                if (typeof options == 'object') {
                    for (i in options) {
                        that.options[i] = options[i];
                    }
                }

                if (that.options.desktopCompatibility) {
                    that.options.overflow = 'hidden';
                }

                that.wrapper.style.overflow = that.options.overflow;

                that.refresh();

                window.addEventListener('onorientationchange' in window ? 'orientationchange' : 'resize', that, false);

                if (isTouch || that.options.desktopCompatibility) {
                    that.element.addEventListener(START_EVENT, that, false);
                    that.element.addEventListener(MOVE_EVENT, that, false);
                    that.element.addEventListener(END_EVENT, that, false);
                }

                if (that.options.checkDOMChanges) {
                    that.element.addEventListener('DOMSubtreeModified', that, false);
                }
            }

            iScroll.prototype = {
                x: 0,
                y: 0,
                enabled: true,

                handleEvent: function (e) {
                    var that = this;

                    switch (e.type) {
                        case START_EVENT:
                            that.touchStart(e);
                            break;
                        case MOVE_EVENT:
                            that.touchMove(e);
                            break;
                        case END_EVENT:
                            that.touchEnd(e);
                            break;
                        case 'webkitTransitionEnd':
                            that.transitionEnd();
                            break;
                        case 'orientationchange':
                        case 'resize':
                            that.refresh();
                            break;
                        case 'DOMSubtreeModified':
                            that.onDOMModified(e);
                            break;
                    }
                },

                onDOMModified: function (e) {
                    var that = this;

                    // (Hopefully) execute onDOMModified only once
                    if (e.target.parentNode != that.element) {
                        return;
                    }

                    setTimeout(function () { that.refresh(); }, 0);

                    if (that.options.topOnDOMChanges && (that.x!=0 || that.y!=0)) {
                        that.scrollTo(0,0,'0');
                    }
                },

                refresh: function () {
                    var that = this,
                        resetX = this.x, resetY = this.y,
                        snap;

                    that.scrollWidth = that.wrapper.clientWidth;
                    that.scrollHeight = that.wrapper.clientHeight;
                    that.scrollerWidth = that.element.offsetWidth;
                    that.scrollerHeight = that.element.offsetHeight;
                    that.maxScrollX = that.scrollWidth - that.scrollerWidth;
                    that.maxScrollY = that.scrollHeight - that.scrollerHeight;
                    that.directionX = 0;
                    that.directionY = 0;

                    if (that.scrollX) {
                        if (that.maxScrollX >= 0) {
                            resetX = 0;
                        } else if (that.x < that.maxScrollX) {
                            resetX = that.maxScrollX;
                        }
                    }
                    if (that.scrollY) {
                        if (that.maxScrollY >= 0) {
                            resetY = 0;
                        } else if (that.y < that.maxScrollY) {
                            resetY = that.maxScrollY;
                        }
                    }
                    // Snap
                    if (that.options.snap) {
                        that.maxPageX = -Math.floor(that.maxScrollX/that.scrollWidth);
                        that.maxPageY = -Math.floor(that.maxScrollY/that.scrollHeight);

                        snap = that.snap(resetX, resetY);
                        resetX = snap.x;
                        resetY = snap.y;
                    }

                    if (resetX!=that.x || resetY!=that.y) {
                        that.setTransitionTime('0');
                        that.setPosition(resetX, resetY, true);
                    }

                    that.scrollX = that.scrollerWidth > that.scrollWidth;
                    that.scrollY = !that.scrollX || that.scrollerHeight > that.scrollHeight;

                    // Update horizontal scrollbar
                    if (that.options.hScrollbar && that.scrollX) {
                        that.scrollBarX = that.scrollBarX || new scrollbar('horizontal', that.wrapper, that.options.fadeScrollbar, that.options.shrinkScrollbar);
                        that.scrollBarX.init(that.scrollWidth, that.scrollerWidth);
                    } else if (that.scrollBarX) {
                        that.scrollBarX = that.scrollBarX.remove();
                    }

                    // Update vertical scrollbar
                    if (that.options.vScrollbar && that.scrollY && that.scrollerHeight > that.scrollHeight) {
                        that.scrollBarY = that.scrollBarY || new scrollbar('vertical', that.wrapper, that.options.fadeScrollbar, that.options.shrinkScrollbar);
                        that.scrollBarY.init(that.scrollHeight, that.scrollerHeight);
                    } else if (that.scrollBarY) {
                        that.scrollBarY = that.scrollBarY.remove();
                    }
                },

                setPosition: function (x, y, hideScrollBars) {
                    var that = this;
                    that.x = x;
                    that.y = y;
                    that.element.style.webkitTransform = translateOpen + that.x + 'px,' + that.y + 'px' + translateClose;

                    // Move the scrollbars
                    if (!hideScrollBars) {
                        if (that.scrollBarX) {
                            that.scrollBarX.setPosition(that.x);
                        }
                        if (that.scrollBarY) {
                            that.scrollBarY.setPosition(that.y);
                        }
                    }
                },

                setTransitionTime: function(time) {
                    var that = this;

                    time = time || '0';
                    that.element.style.webkitTransitionDuration = time;

                    if (that.scrollBarX) {
                        that.scrollBarX.bar.style.webkitTransitionDuration = time;
                        that.scrollBarX.wrapper.style.webkitTransitionDuration = has3d && that.options.fadeScrollbar ? '300ms' : '0';
                    }
                    if (that.scrollBarY) {
                        that.scrollBarY.bar.style.webkitTransitionDuration = time;
                        that.scrollBarY.wrapper.style.webkitTransitionDuration = has3d && that.options.fadeScrollbar ? '300ms' : '0';
                    }
                },

                touchStart: function(e) {
                    var that = this,
                        matrix;

                    var noscrollTname = {
                            input:true
                            ,textarea:true
                    }
                    if (e.target && e.target.tagName) {
                        var tName= e.target.tagName.toLowerCase();

                        if(noscrollTname[tName]){

                            return;
                        }
                    }


                    (isIphone || isIpad)&& e.preventDefault();
                    e.stopPropagation();

                    if (!that.enabled) {
                        return;
                    }

                    that.scrolling = true;		// This is probably not needed, but may be useful if iScroll is used in conjuction with other frameworks

                    that.moved = false;
                    that.dist = 0;

                    that.setTransitionTime('0');

                    // Check if the scroller is really where it should be
                    if (that.options.momentum || that.options.snap) {
                        matrix = new WebKitCSSMatrix(window.getComputedStyle(that.element).webkitTransform);
                        if (matrix.e != that.x || matrix.f != that.y) {
                            document.removeEventListener('webkitTransitionEnd', that, false);
                            that.setPosition(matrix.e, matrix.f);
                            that.moved = true;
                        }
                    }

                    that.touchStartX = isTouch ? e.changedTouches[0].pageX : e.pageX;
                    that.scrollStartX = that.x;

                    that.touchStartY = isTouch ? e.changedTouches[0].pageY : e.pageY;
                    that.scrollStartY = that.y;

                    that.scrollStartTime = e.timeStamp;

                    that.directionX = 0;
                    that.directionY = 0;
                },

                touchMove: function(e) {
                    var that = this,
                        pageX = isTouch ? e.changedTouches[0].pageX : e.pageX,
                        pageY = isTouch ? e.changedTouches[0].pageY : e.pageY,
                        leftDelta = that.scrollX ? pageX - that.touchStartX : 0,
                        topDelta = that.scrollY ? pageY - that.touchStartY : 0,
                        newX = that.x + leftDelta,
                        newY = that.y + topDelta;

                        //Debug.trace([newX,newY].toString());

                    if (!that.scrolling) {
                        return;
                    }

                    //e.preventDefault();
                    e.stopPropagation();	// Stopping propagation just saves some cpu cycles (I presume)

                    that.touchStartX = pageX;
                    that.touchStartY = pageY;

                    // Slow down if outside of the boundaries
                    if(that.options.elasticity){
                        if (newX >= 0 || newX < that.maxScrollX) {
                            newX = that.options.bounce ? Math.round(that.x + leftDelta / 3) : (newX >= 0 || that.maxScrollX>=0) ? 0 : that.maxScrollX;
                        }
                        if (newY >= 0 || newY < that.maxScrollY) {
                            newY = that.options.bounce ? Math.round(that.y + topDelta / 3) : (newY >= 0 || that.maxScrollY>=0) ? 0 : that.maxScrollY;
                        }
                    }else{
                        if (newX >= 0) {
                            newX = 0;
                        }else if (newX < that.maxScrollX) {
                            newX = that.maxScrollX;
                        }
                        if (newY >= 0) {
                            newY = 0;
                        }else if(newY < that.maxScrollY) {
                            newY = that.maxScrollY
                        }
                    }

                    if (that.dist > 5) {			// 5 pixels threshold is needed on Android, but also on iPhone looks more natural
                        that.setPosition(newX, newY);
                        that.moved = true;
                        that.directionX = leftDelta > 0 ? -1 : 1;
                        that.directionY = topDelta > 0 ? -1 : 1;
                    } else {
                        that.dist+= Math.abs(leftDelta) + Math.abs(topDelta);
                    }
                },

                touchEnd: function(e) {



                    var that = this,
                        time = e.timeStamp - that.scrollStartTime,
                        point = isTouch ? e.changedTouches[0] : e,
                        target, ev,
                        momentumX, momentumY,
                        newDuration = 0,
                        newPositionX = that.x, newPositionY = that.y,
                        snap;

                    if (!that.scrolling) {
                        return;
                    }
                    that.scrolling = false;

                    if (!that.moved) {
                        that.resetPosition();

                        if (isTouch) {
                            // Find the last touched element
                            target = point.target;
                            while (target.nodeType != 1) {
                                target = target.parentNode;
                            }

                            // Create the fake event
                            target.style.pointerEvents = 'auto';
                            ev = document.createEvent('MouseEvents');
                            ev.initMouseEvent('click', true, true, e.view, 1,
                                point.screenX, point.screenY, point.clientX, point.clientY,
                                e.ctrlKey, e.altKey, e.shiftKey, e.metaKey,
                                0, null);
                            ev._fake = true;
                            target.dispatchEvent(ev);
                        }

                        return;
                    }

                    if (!that.options.snap && time > 250) {			// Prevent slingshot effect
                        that.resetPosition();
                        return;
                    }

                    //if (that.options.elasticity && that.options.momentum) {
                    if (that.options.momentum) {
                        momentumX = that.scrollX === true
                            ? that.momentum(that.x - that.scrollStartX,
                                            time,
                                            that.options.bounce ? -that.x + that.scrollWidth/5 : -that.x,
                                            that.options.bounce ? that.x + that.scrollerWidth - that.scrollWidth + that.scrollWidth/5 : that.x + that.scrollerWidth - that.scrollWidth)
                            : { dist: 0, time: 0 };

                        momentumY = that.scrollY === true
                            ? that.momentum(that.y - that.scrollStartY,
                                            time,
                                            that.options.bounce ? -that.y + that.scrollHeight/5 : -that.y,
                                            that.options.bounce ? (that.maxScrollY < 0 ? that.y + that.scrollerHeight - that.scrollHeight : 0) + that.scrollHeight/5 : that.y + that.scrollerHeight - that.scrollHeight)
                            : { dist: 0, time: 0 };

                        newDuration = Math.max(Math.max(momentumX.time, momentumY.time), 1);		// The minimum animation length must be 1ms
                        newPositionX = that.x + momentumX.dist;
                        newPositionY = that.y + momentumY.dist;

                        if(!that.options.elasticity){
                            if (newPositionX >= 0) {
                                newPositionX = 0;
                            }else if (newPositionX < that.maxScrollX) {
                                newPositionX = that.maxScrollX;
                            }
                            if (newPositionY >= 0) {
                                newPositionY = 0;
                            }else if(newPositionY < that.maxScrollY) {
                                newPositionY = that.maxScrollY
                            }
                        }
                    }
                    if(that.options)

                    if (that.options.snap) {
                        snap = that.snap(newPositionX, newPositionY);
                        newPositionX = snap.x;
                        newPositionY = snap.y;
                        newDuration = Math.max(snap.time, newDuration);
                    }

                    that.scrollTo(newPositionX, newPositionY, newDuration + 'ms');
                },

                transitionEnd: function () {
                    var that = this;
                    document.removeEventListener('webkitTransitionEnd', that, false);
                    that.resetPosition();
                },

                resetPosition: function () {
                    var that = this,
                        resetX = that.x,
                        resetY = that.y;

                    if (that.x >= 0) {
                        resetX = 0;
                    } else if (that.x < that.maxScrollX) {
                        resetX = that.maxScrollX;
                    }

                    if (that.y >= 0 || that.maxScrollY > 0) {
                        resetY = 0;
                    } else if (that.y < that.maxScrollY) {
                        resetY = that.maxScrollY;
                    }

                    if (resetX != that.x || resetY != that.y) {
                        that.scrollTo(resetX, resetY);
                    } else {
                        if (that.moved) {
                            that.onScrollEnd();		// Execute custom code on scroll end
                            that.moved = false;
                        }

                        // Hide the scrollbars
                        if (that.scrollBarX) {
                            that.scrollBarX.hide();
                        }
                        if (that.scrollBarY) {
                            that.scrollBarY.hide();
                        }
                    }
                },

                snap: function (x, y) {
                    var that = this, time;

                    if (that.directionX > 0) {
                        x = Math.floor(x/that.scrollWidth);
                    } else if (that.directionX < 0) {
                        x = Math.ceil(x/that.scrollWidth);
                    } else {
                        x = Math.round(x/that.scrollWidth);
                    }
                    that.pageX = -x;
                    x = x * that.scrollWidth;
                    if (x > 0) {
                        x = that.pageX = 0;
                    } else if (x < that.maxScrollX) {
                        that.pageX = that.maxPageX;
                        x = that.maxScrollX;
                    }

                    if (that.directionY > 0) {
                        y = Math.floor(y/that.scrollHeight);
                    } else if (that.directionY < 0) {
                        y = Math.ceil(y/that.scrollHeight);
                    } else {
                        y = Math.round(y/that.scrollHeight);
                    }
                    that.pageY = -y;
                    y = y * that.scrollHeight;
                    if (y > 0) {
                        y = that.pageY = 0;
                    } else if (y < that.maxScrollY) {
                        that.pageY = that.maxPageY;
                        y = that.maxScrollY;
                    }

                    // Snap with constant speed (proportional duration)
                    time = Math.round(Math.max(
                            Math.abs(that.x - x) / that.scrollWidth * 500,
                            Math.abs(that.y - y) / that.scrollHeight * 500
                        ));

                    return { x: x, y: y, time: time };
                },

                scrollTo: function (destX, destY, runtime) {
                    var that = this;

                    if (that.x == destX && that.y == destY) {
                        that.resetPosition();
                        return;
                    }

                    that.moved = true;
                    that.setTransitionTime(runtime || '350ms');
                    that.setPosition(destX, destY);

                    if (runtime==='0' || runtime=='0s' || runtime=='0ms') {
                        that.resetPosition();
                    } else {
                        document.addEventListener('webkitTransitionEnd', that, false);	// At the end of the transition check if we are still inside of the boundaries
                    }
                },

                scrollToPage: function (pageX, pageY, runtime) {
                    var that = this, snap;

                    if (!that.options.snap) {
                        that.pageX = -Math.round(that.x / that.scrollWidth);
                        that.pageY = -Math.round(that.y / that.scrollHeight);
                    }

                    if (pageX == 'next') {
                        pageX = ++that.pageX;
                    } else if (pageX == 'prev') {
                        pageX = --that.pageX;
                    }

                    if (pageY == 'next') {
                        pageY = ++that.pageY;
                    } else if (pageY == 'prev') {
                        pageY = --that.pageY;
                    }

                    pageX = -pageX*that.scrollWidth;
                    pageY = -pageY*that.scrollHeight;

                    snap = that.snap(pageX, pageY);
                    pageX = snap.x;
                    pageY = snap.y;

                    that.scrollTo(pageX, pageY, runtime || '500ms');
                },

                scrollToElement: function (el, runtime) {
                    el = typeof el == 'object' ? el : this.element.querySelector(el);

                    if (!el) {
                        return;
                    }

                    var that = this,
                        x = that.scrollX ? -el.offsetLeft : 0,
                        y = that.scrollY ? -el.offsetTop : 0;

                    if (x >= 0) {
                        x = 0;
                    } else if (x < that.maxScrollX) {
                        x = that.maxScrollX;
                    }

                    if (y >= 0) {
                        y = 0;
                    } else if (y < that.maxScrollY) {
                        y = that.maxScrollY;
                    }
                    that.scrollTo(x, y, runtime);
                },

                scrollToElement2: function (el, yfix, runtime) {
                    el = typeof el == 'object' ? el : this.element.querySelector(el);

                    if (!el) {
                        return;
                    }

                    var that = this,
                    x = that.scrollX ? -el.offsetLeft : 0,
                    y = that.scrollY ? -el.offsetTop : 0;

                    //apply fix
                    if(yfix + that.maxScrollY < 0)
                    {
                        that.maxScrollY = -yfix;
                    }

                    if (x >= 0) {
                        x = 0;
                    } else if (x < that.maxScrollX) {
                        x = that.maxScrollX;
                    }
                    if (y >= 0) {
                        y = 0;
                    } else if (y < that.maxScrollY) {
                        y = that.maxScrollY;
                    }
                    //Debug.trace(y);
                    that.scrollTo(x, y, runtime);
                },

                momentum: function (dist, time, maxDistUpper, maxDistLower) {
                    var friction = 2.5,
                        deceleration = 1.2,
                        speed = Math.abs(dist) / time * 1000,
                        newDist = speed * speed / friction / 1000,
                        newTime = 0;

                    // Proportinally reduce speed if we are outside of the boundaries
                    if (dist > 0 && newDist > maxDistUpper) {
                        speed = speed * maxDistUpper / newDist / friction;
                        newDist = maxDistUpper;
                    } else if (dist < 0 && newDist > maxDistLower) {
                        speed = speed * maxDistLower / newDist / friction;
                        newDist = maxDistLower;
                    }

                    newDist = newDist * (dist < 0 ? -1 : 1);
                    newTime = speed / deceleration;

                    return { dist: Math.round(newDist), time: Math.round(newTime) };
                },

                onScrollEnd: function () {},

                destroy: function (full) {
                    var that = this;

                    window.removeEventListener('onorientationchange' in window ? 'orientationchange' : 'resize', that, false);
                    that.element.removeEventListener(START_EVENT, that, false);
                    that.element.removeEventListener(MOVE_EVENT, that, false);
                    that.element.removeEventListener(END_EVENT, that, false);
                    document.removeEventListener('webkitTransitionEnd', that, false);

                    if (that.options.checkDOMChanges) {
                        that.element.removeEventListener('DOMSubtreeModified', that, false);
                    }

                    if (that.scrollBarX) {
                        that.scrollBarX = that.scrollBarX.remove();
                    }

                    if (that.scrollBarY) {
                        that.scrollBarY = that.scrollBarY.remove();
                    }

                    if (full) {
                        that.wrapper.parentNode.removeChild(that.wrapper);
                    }

                    return null;
                }
            };

            function scrollbar (dir, wrapper, fade, shrink) {
                var that = this, style;

                that.dir = dir;
                that.fade = fade;
                that.shrink = shrink;
                that.uid = ++uid;

                // Create main scrollbar
                that.bar = document.createElement('div');

                style = 'position:absolute;top:0;left:0;-webkit-transition-timing-function:cubic-bezier(0,0,0.25,1);pointer-events:none;-webkit-transition-duration:0;-webkit-transition-delay:0;-webkit-transition-property:-webkit-transform;z-index:10;background:rgba(0,0,0,0.5);' +
                    '-webkit-transform:' + translateOpen + '0,0' + translateClose + ';' +
                    (dir == 'horizontal' ? '-webkit-border-radius:3px 2px;min-width:6px;min-height:5px' : '-webkit-border-radius:2px 3px;min-width:5px;min-height:6px');

                that.bar.setAttribute('style', style);

                // Create scrollbar wrapper
                that.wrapper = document.createElement('div');
                style = '-webkit-mask:-webkit-canvas(scrollbar' + that.uid + that.dir + ');position:absolute;z-index:10;pointer-events:none;overflow:hidden;opacity:0;-webkit-transition-duration:' + (fade ? '300ms' : '0') + ';-webkit-transition-delay:0;-webkit-transition-property:opacity;' +
                    (that.dir == 'horizontal' ? 'bottom:2px;left:2px;right:7px;height:5px' : 'top:2px;right:2px;bottom:7px;width:5px;');
                that.wrapper.setAttribute('style', style);

                // Add scrollbar to the DOM
                that.wrapper.appendChild(that.bar);
                wrapper.appendChild(that.wrapper);
            }

            scrollbar.prototype = {
                init: function (scroll, size) {
                    var that = this,
                        ctx;

                    // Create scrollbar mask
                    if (that.dir == 'horizontal') {
                        if (that.maxSize != that.wrapper.offsetWidth) {
                            that.maxSize = that.wrapper.offsetWidth;
                            ctx = document.getCSSCanvasContext("2d", "scrollbar" + that.uid + that.dir, that.maxSize, 5);
                            ctx.fillStyle = "rgb(0,0,0)";
                            ctx.beginPath();
                            ctx.arc(2.5, 2.5, 2.5, Math.PI/2, -Math.PI/2, false);
                            ctx.lineTo(that.maxSize-2.5, 0);
                            ctx.arc(that.maxSize-2.5, 2.5, 2.5, -Math.PI/2, Math.PI/2, false);
                            ctx.closePath();
                            ctx.fill();
                        }
                    } else {
                        if (that.maxSize != that.wrapper.offsetHeight) {
                            that.maxSize = that.wrapper.offsetHeight;
                            ctx = document.getCSSCanvasContext("2d", "scrollbar" + that.uid + that.dir, 5, that.maxSize);
                            ctx.fillStyle = "rgb(0,0,0)";
                            ctx.beginPath();
                            ctx.arc(2.5, 2.5, 2.5, Math.PI, 0, false);
                            ctx.lineTo(5, that.maxSize-2.5);
                            ctx.arc(2.5, that.maxSize-2.5, 2.5, 0, Math.PI, false);
                            ctx.closePath();
                            ctx.fill();
                        }
                    }

                    that.size = Math.max(Math.round(that.maxSize * that.maxSize / size), 6);
                    that.maxScroll = that.maxSize - that.size;
                    that.toWrapperProp = that.maxScroll / (scroll - size);
                    that.bar.style[that.dir == 'horizontal' ? 'width' : 'height'] = that.size + 'px';
                },

                setPosition: function (pos) {
                    var that = this;

                    if (that.wrapper.style.opacity != '1') {
                        that.show();
                    }

                    pos = Math.round(that.toWrapperProp * pos);

                    if (pos < 0) {
                        pos = that.shrink ? pos + pos*3 : 0;
                        if (that.size + pos < 7) {
                            pos = -that.size + 6;
                        }
                    } else if (pos > that.maxScroll) {
                        pos = that.shrink ? pos + (pos-that.maxScroll)*3 : that.maxScroll;
                        if (that.size + that.maxScroll - pos < 7) {
                            pos = that.size + that.maxScroll - 6;
                        }
                    }

                    pos = that.dir == 'horizontal'
                        ? translateOpen + pos + 'px,0' + translateClose
                        : translateOpen + '0,' + pos + 'px' + translateClose;

                    that.bar.style.webkitTransform = pos;
                },

                show: function () {
                    if (has3d) {
                        this.wrapper.style.webkitTransitionDelay = '0';
                    }
                    this.wrapper.style.opacity = '1';
                },

                hide: function () {
                    if (has3d) {
                        this.wrapper.style.webkitTransitionDelay = '350ms';
                    }
                    this.wrapper.style.opacity = '0';
                },

                remove: function () {
                    this.wrapper.parentNode.removeChild(this.wrapper);
                    return null;
                }
            };

            // Is translate3d compatible?
            var has3d = ('WebKitCSSMatrix' in window && 'm11' in new WebKitCSSMatrix()),
                // Device sniffing
                isIphone = (/iphone/gi).test(navigator.appVersion),
                isIpad = (/ipad/gi).test(navigator.appVersion),
                isAndroid = (/android/gi).test(navigator.appVersion),
                isTouch = isIphone || isIpad || isAndroid,
                // Event sniffing
                START_EVENT = isTouch ? 'touchstart' : 'mousedown',
                MOVE_EVENT = isTouch ? 'touchmove' : 'mousemove',
                END_EVENT = isTouch ? 'touchend' : 'mouseup',
                // Translate3d helper
                translateOpen = 'translate' + (has3d ? '3d(' : '('), translateClose = has3d ? ',0)' : ')',
                // Unique ID
                uid = 0;

            // Expose iScroll to the world
            return  iScroll;
            })();

(function(global){

/*
 * Scroller
 * http://github.com/zynga/scroller
 *
 * Copyright 2011, Zynga Inc.
 * Licensed under the MIT License.
 * https://raw.github.com/zynga/scroller/master/MIT-LICENSE.txt
 *
 * Based on the work of: Unify Project (unify-project.org)
 * http://unify-project.org
 * Copyright 2011, Deutsche Telekom AG
 * License: MIT + Apache (V2)
 *
 * Inspired by: https://github.com/inexorabletash/raf-shim/blob/master/raf.js
 */
(function(global)
{
	if(global.requestAnimationFrame) {
		return;
	}

	// Basic emulation of native methods for internal use

	var now = Date.now || function() {
		return +new Date;
	};

	var getKeys = Object.keys || function(obj) {

		var keys = {};
		for (var key in obj) {
			keys[key] = true;
		}

		return keys;

	};

	var isEmpty = Object.empty || function(obj) {

		for (var key in obj) {
			return false;
		}

		return true;

	};


	// requestAnimationFrame polyfill
	// http://webstuff.nfshost.com/anim-timing/Overview.html

	var postfix = "RequestAnimationFrame";
	var prefix = (function()
	{
		var all = "webkit,moz,o,ms".split(",");
		for (var i=0; i<4; i++) {
			if (global[all[i]+postfix] != null) {
				return all[i];
			}
		}
	})();

	// Vendor specific implementation
	if (prefix)
	{
		global.requestAnimationFrame = global[prefix+postfix];
		global.cancelRequestAnimationFrame = global[prefix+"Cancel"+postfix];
		return;
	}

	// Custom implementation
	var TARGET_FPS = 60;
	var requests = {};
	var rafHandle = 1;
	var timeoutHandle = null;

	global.requestAnimationFrame = function(callback, root)
	{
		var callbackHandle = rafHandle++;

		// Store callback
		requests[callbackHandle] = callback;

		// Create timeout at first request
		if (timeoutHandle === null)
		{
			timeoutHandle = setTimeout(function()
			{
				var time = now();
				var currentRequests = requests;
				var keys = getKeys(currentRequests);

				// Reset data structure before executing callbacks
				requests = {};
				timeoutHandle = null;

				// Process all callbacks
				for (var i=0, l=keys.length; i<l; i++) {
					currentRequests[keys[i]](time);
				}
			}, 1000 / TARGET_FPS);
		}

		return callbackHandle;
	};

	global.cancelRequestAnimationFrame = function(handle)
	{
		delete requests[handle];

		// Stop timeout if all where removed
		if (isEmpty(requests))
		{
			clearTimeout(timeoutHandle);
			timeoutHandle = null;
		}
	};

})(global);


/*
 * Scroller
 * http://github.com/zynga/scroller
 *
 * Copyright 2011, Zynga Inc.
 * Licensed under the MIT License.
 * https://raw.github.com/zynga/scroller/master/MIT-LICENSE.txt
 *
 * Based on the work of: Unify Project (unify-project.org)
 * http://unify-project.org
 * Copyright 2011, Deutsche Telekom AG
 * License: MIT + Apache (V2)
 */

Scroller =(function() {

	/**
	 * A pure logic 'component' for 'virtual' scrolling/zooming.
	 */
	var Scroller = function(callback, options) {

		this.__callback = callback;

		this.options = {

			/** Enable scrolling on x-axis */
			scrollingX: true,

			/** Enable scrolling on y-axis */
			scrollingY: true,

			/** Enable animations for deceleration, snap back, zooming and scrolling */
			animating: true,

			/** Enable bouncing (content can be slowly moved outside and jumps back after releasing) */
			bouncing: true,

			/** Enable locking to the main axis if user moves only slightly on one of them at start */
			locking: true,

			/** Enable pagination mode (switching between full page content panes) */
			paging: false,

			/** Enable snapping of content to a configured pixel grid */
			snapping: false,

			/** Enable zooming of content via API, fingers and mouse wheel */
			zooming: false,

			/** Minimum zoom level */
			minZoom: 0.5,

			/** Maximum zoom level */
			maxZoom: 3

		};

		for (var key in options) {
			this.options[key] = options[key];
		}

	};


	// Easing Equations (c) 2003 Robert Penner, all rights reserved.
	// Open source under the BSD License.

	/**
	 * @param pos {Number} position between 0 (start of effect) and 1 (end of effect)
	**/
	var easeOutCubic = function(pos) {
		return (Math.pow((pos - 1), 3) + 1);
	};

	/**
	 * @param pos {Number} position between 0 (start of effect) and 1 (end of effect)
	**/
	var easeInOutCubic = function(pos) {
		if ((pos /= 0.5) < 1) {
			return 0.5 * Math.pow(pos, 3);
		}

		return 0.5 * (Math.pow((pos - 2), 3) + 2);
	};


	var members = {

		/*
		---------------------------------------------------------------------------
			INTERNAL FIELDS :: STATUS
		---------------------------------------------------------------------------
		*/

		/** {Boolean} Whether only a single finger is used in touch handling */
		__isSingleTouch: false,

		/** {Boolean} Whether a touch event sequence is in progress */
		__isTracking: false,

		/**
		 * {Boolean} Whether a gesture zoom/rotate event is in progress. Activates when
		 * a gesturestart event happens. This has higher priority than dragging.
		 */
		__isGesturing: false,

		/**
		 * {Boolean} Whether the user has moved by such a distance that we have enabled
		 * dragging mode. Hint: It's only enabled after some pixels of movement to
		 * not interrupt with clicks etc.
		 */
		__isDragging: false,

		/**
		 * {Boolean} Not touching and dragging anymore, and smoothly animating the
		 * touch sequence using deceleration.
		 */
		__isDecelerating: false,

		/**
		 * {Boolean} Smoothly animating the currently configured change
		 */
		__isAnimating: false,



		/*
		---------------------------------------------------------------------------
			INTERNAL FIELDS :: DIMENSIONS
		---------------------------------------------------------------------------
		*/

		/** {Integer} Available outer left position (from document perspective) */
		__clientLeft: 0,

		/** {Integer} Available outer top position (from document perspective) */
		__clientTop: 0,

		/** {Integer} Available outer width */
		__clientWidth: 0,

		/** {Integer} Available outer height */
		__clientHeight: 0,

		/** {Integer} Outer width of content */
		__contentWidth: 0,

		/** {Integer} Outer height of content */
		__contentHeight: 0,

		/** {Integer} Snapping width for content */
		__snapWidth: 100,

		/** {Integer} Snapping height for content */
		__snapHeight: 100,

		/** {Integer} Height to assign to refresh area */
		__refreshHeight: null,

		/** {Boolean} Whether the refresh process is enabled when the event is released now */
		__refreshActive: false,

		/** {Function} Callback to execute on activation. This is for signalling the user about a refresh is about to happen when he release */
		__refreshActivate: null,

		/** {Function} Callback to execute on deactivation. This is for signalling the user about the refresh being cancelled */
		__refreshDeactivate: null,

		/** {Function} Callback to execute to start the actual refresh. Call {@link #refreshFinish} when done */
		__refreshStart: null,

		/** {Number} Zoom level */
		__zoomLevel: 1,

		/** {Number} Scroll position on x-axis */
		__scrollLeft: 0,

		/** {Number} Scroll position on y-axis */
		__scrollTop: 0,

		/** {Integer} Maximum allowed scroll position on x-axis */
		__maxScrollLeft: 0,

		/** {Integer} Maximum allowed scroll position on y-axis */
		__maxScrollTop: 0,

		/* {Number} Scheduled left position (final position when animating) */
		__scheduledLeft: 0,

		/* {Number} Scheduled top position (final position when animating) */
		__scheduledTop: 0,

		/* {Number} Scheduled zoom level (final scale when animating) */
		__scheduledZoom: 0,



		/*
		---------------------------------------------------------------------------
			INTERNAL FIELDS :: LAST POSITIONS
		---------------------------------------------------------------------------
		*/

		/** {Number} Left position of finger at start */
		__lastTouchLeft: null,

		/** {Number} Top position of finger at start */
		__lastTouchTop: null,

		/** {Date} Timestamp of last move of finger. Used to limit tracking range for deceleration speed. */
		__lastTouchMove: null,

		/** {Array} List of positions, uses three indexes for each state: left, top, timestamp */
		__positions: null,



		/*
		---------------------------------------------------------------------------
			INTERNAL FIELDS :: DECELERATION SUPPORT
		---------------------------------------------------------------------------
		*/

		/** {Integer} Minimum left scroll position during deceleration */
		__minDecelerationScrollLeft: null,

		/** {Integer} Minimum top scroll position during deceleration */
		__minDecelerationScrollTop: null,

		/** {Integer} Maximum left scroll position during deceleration */
		__maxDecelerationScrollLeft: null,

		/** {Integer} Maximum top scroll position during deceleration */
		__maxDecelerationScrollTop: null,

		/** {Number} Current factor to modify horizontal scroll position with on every step */
		__decelerationVelocityX: null,

		/** {Number} Current factor to modify vertical scroll position with on every step */
		__decelerationVelocityY: null,



		/*
		---------------------------------------------------------------------------
			PUBLIC API
		---------------------------------------------------------------------------
		*/

		/**
		 * Configures the dimensions of the client (outer) and content (inner) elements.
		 * Requires the available space for the outer element and the outer size of the inner element.
		 * All values which are falsy (null or zero etc.) are ignored and the old value is kept.
		 *
		 * @param clientWidth {Integer ? null} Inner width of outer element
		 * @param clientHeight {Integer ? null} Inner height of outer element
		 * @param contentWidth {Integer ? null} Outer width of inner element
		 * @param contentHeight {Integer ? null} Outer height of inner element
		 */
		setDimensions: function(clientWidth, clientHeight, contentWidth, contentHeight) {

			var self = this;

			// Only update values which are defined
			if (clientWidth) {
				self.__clientWidth = clientWidth;
			}

			if (clientHeight) {
				self.__clientHeight = clientHeight;
			}

			if (contentWidth) {
				self.__contentWidth = contentWidth;
			}

			if (contentHeight) {
				self.__contentHeight = contentHeight;
			}

			// Refresh maximums
			self.__computeScrollMax();

			// Refresh scroll position
			self.scrollTo(self.__scrollLeft, self.__scrollTop, true);

		},


		/**
		 * Sets the client coordinates in relation to the document.
		 *
		 * @param left {Integer ? 0} Left position of outer element
		 * @param top {Integer ? 0} Top position of outer element
		 */
		setPosition: function(left, top) {

			var self = this;

			self.__clientLeft = left || 0;
			self.__clientTop = top || 0;

		},


		/**
		 * Configures the snapping (when snapping is active)
		 *
		 * @param width {Integer} Snapping width
		 * @param height {Integer} Snapping height
		 */
		setSnapSize: function(width, height) {

			var self = this;

			self.__snapWidth = width;
			self.__snapHeight = height;

		},


		/**
		 * Activates pull-to-refresh. A special zone on the top of the list to start a list refresh whenever
		 * the user event is released during visibility of this zone. This was introduced by some apps on iOS like
		 * the official Twitter client.
		 *
		 * @param height {Integer} Height of pull-to-refresh zone on top of rendered list
		 * @param activateCallback {Function} Callback to execute on activation. This is for signalling the user about a refresh is about to happen when he release.
		 * @param deactivateCallback {Function} Callback to execute on deactivation. This is for signalling the user about the refresh being cancelled.
		 * @param startCallback {Function} Callback to execute to start the real async refresh action. Call {@link #finishPullToRefresh} after finish of refresh.
		 */
		activatePullToRefresh: function(height, activateCallback, deactivateCallback, startCallback) {

			var self = this;

			self.__refreshHeight = height;
			self.__refreshActivate = activateCallback;
			self.__refreshDeactivate = deactivateCallback;
			self.__refreshStart = startCallback;

		},


		/**
		 * Signalizes that pull-to-refresh is finished.
		 */
		finishPullToRefresh: function() {

			var self = this;

			self.__refreshActive = false;
			if (self.__refreshDeactivate) {
				self.__refreshDeactivate();
			}

			self.scrollTo(self.__scrollLeft, self.__scrollTop, true);

		},


		/**
		 * Returns the scroll position and zooming values
		 *
		 * @return {Map} `left` and `top` scroll position and `zoom` level
		 */
		getValues: function() {

			var self = this;

			return {
				left: self.__scrollLeft,
				top: self.__scrollTop,
				zoom: self.__zoomLevel
			};

		},


		/**
		 * Returns the maximum scroll values
		 *
		 * @return {Map} `left` and `top` maximum scroll values
		 */
		getScrollMax: function() {

			var self = this;

			return {
				left: self.__maxScrollLeft,
				top: self.__maxScrollTop
			};

		},


		/**
		 * Zooms to the given level. Supports optional animation. Zooms
		 * the center when no coordinates are given.
		 *
		 * @param level {Number} Level to zoom to
		 * @param animate {Boolean ? false} Whether to use animation
		 * @param originLeft {Number ? null} Zoom in at given left coordinate
		 * @param originTop {Number ? null} Zoom in at given top coordinate
		 */
		zoomTo: function(level, animate, originLeft, originTop) {

			var self = this;

			if (!self.options.zooming) {
				throw new Error("Zooming is not enabled!");
			}

			// Stop deceleration
			if (self.__isDecelerating) {
				core.effect.Animate.stop(self.__isDecelerating);
				self.__isDecelerating = false;
			}

			var oldLevel = self.__zoomLevel;

			// Normalize input origin to center of viewport if not defined
			if (originLeft == null) {
				originLeft = self.__clientWidth / 2;
			}

			if (originTop == null) {
				originTop = self.__clientHeight / 2;
			}

			// Limit level according to configuration
			level = Math.max(Math.min(level, self.options.maxZoom), self.options.minZoom);

			// Recompute maximum values while temporary tweaking maximum scroll ranges
			self.__computeScrollMax(level);

			// Recompute left and top coordinates based on new zoom level
			var left = ((originLeft + self.__scrollLeft) * level / oldLevel) - originLeft;
			var top = ((originTop + self.__scrollTop) * level / oldLevel) - originTop;

			// Limit x-axis
			if (left > self.__maxScrollLeft) {
				left = self.__maxScrollLeft;
			} else if (left < 0) {
				left = 0;
			}

			// Limit y-axis
			if (top > self.__maxScrollTop) {
				top = self.__maxScrollTop;
			} else if (top < 0) {
				top = 0;
			}

			// Push values out
			self.__publish(left, top, level, animate);

		},


		/**
		 * Zooms the content by the given factor.
		 *
		 * @param factor {Number} Zoom by given factor
		 * @param animate {Boolean ? false} Whether to use animation
		 * @param originLeft {Number ? 0} Zoom in at given left coordinate
		 * @param originTop {Number ? 0} Zoom in at given top coordinate
		 */
		zoomBy: function(factor, animate, originLeft, originTop) {

			var self = this;

			self.zoomTo(self.__zoomLevel * factor, animate, originLeft, originTop);

		},


		/**
		 * Scrolls to the given position. Respect limitations and snapping automatically.
		 *
		 * @param left {Number?null} Horizontal scroll position, keeps current if value is <code>null</code>
		 * @param top {Number?null} Vertical scroll position, keeps current if value is <code>null</code>
		 * @param animate {Boolean?false} Whether the scrolling should happen using an animation
		 * @param zoom {Number?null} Zoom level to go to
		 */
		scrollTo: function(left, top, animate, zoom) {

			var self = this;

			// Stop deceleration
			if (self.__isDecelerating) {
				core.effect.Animate.stop(self.__isDecelerating);
				self.__isDecelerating = false;
			}

			// Correct coordinates based on new zoom level
			if (zoom != null && zoom !== self.__zoomLevel) {

				if (!self.options.zooming) {
					throw new Error("Zooming is not enabled!");
				}

				left *= zoom;
				top *= zoom;

				// Recompute maximum values while temporary tweaking maximum scroll ranges
				self.__computeScrollMax(zoom);

			} else {

				// Keep zoom when not defined
				zoom = self.__zoomLevel;

			}

			if (!self.options.scrollingX) {

				left = self.__scrollLeft;

			} else {

				if (self.options.paging) {
					left = Math.round(left / self.__clientWidth) * self.__clientWidth;
				} else if (self.options.snapping) {
					left = Math.round(left / self.__snapWidth) * self.__snapWidth;
				}

			}

			if (!self.options.scrollingY) {

				top = self.__scrollTop;

			} else {

				if (self.options.paging) {
					top = Math.round(top / self.__clientHeight) * self.__clientHeight;
				} else if (self.options.snapping) {
					top = Math.round(top / self.__snapHeight) * self.__snapHeight;
				}

			}

			// Limit for allowed ranges
			left = Math.max(Math.min(self.__maxScrollLeft, left), 0);
			top = Math.max(Math.min(self.__maxScrollTop, top), 0);

			// Don't animate when no change detected, still call publish to make sure
			// that rendered position is really in-sync with internal data
			if (left === self.__scrollLeft && top === self.__scrollTop) {
				animate = false;
			}

			// Publish new values
			self.__publish(left, top, zoom, animate);

		},


		/**
		 * Scroll by the given offset
		 *
		 * @param left {Number ? 0} Scroll x-axis by given offset
		 * @param top {Number ? 0} Scroll x-axis by given offset
		 * @param animate {Boolean ? false} Whether to animate the given change
		 */
		scrollBy: function(left, top, animate) {

			var self = this;

			var startLeft = self.__isAnimating ? self.__scheduledLeft : self.__scrollLeft;
			var startTop = self.__isAnimating ? self.__scheduledTop : self.__scrollTop;

			self.scrollTo(startLeft + (left || 0), startTop + (top || 0), animate);

		},



		/*
		---------------------------------------------------------------------------
			EVENT CALLBACKS
		---------------------------------------------------------------------------
		*/

		/**
		 * Mouse wheel handler for zooming support
		 */
		doMouseZoom: function(wheelDelta, timeStamp, pageX, pageY) {

			var self = this;
			var change = wheelDelta > 0 ? 0.97 : 1.03;

			return self.zoomTo(self.__zoomLevel * change, false, pageX - self.__clientLeft, pageY - self.__clientTop);

		},


		/**
		 * Touch start handler for scrolling support
		 */
		doTouchStart: function(touches, timeStamp) {

			// Array-like check is enough here
			if (touches.length == null) {
				throw new Error("Invalid touch list: " + touches);
			}

			if (timeStamp instanceof Date) {
				timeStamp = timeStamp.valueOf();
			}
			if (typeof timeStamp !== "number") {
				throw new Error("Invalid timestamp value: " + timeStamp);
			}

			var self = this;

			// Stop deceleration
			if (self.__isDecelerating) {
				core.effect.Animate.stop(self.__isDecelerating);
				self.__isDecelerating = false;
			}

			// Stop animation
			if (self.__isAnimating) {
				core.effect.Animate.stop(self.__isAnimating);
				self.__isAnimating = false;
			}

			// Use center point when dealing with two fingers
			var currentTouchLeft, currentTouchTop;
			var isSingleTouch = touches.length === 1;
			if (isSingleTouch) {
				currentTouchLeft = touches[0].pageX;
				currentTouchTop = touches[0].pageY;
			} else {
				currentTouchLeft = Math.abs(touches[0].pageX + touches[1].pageX) / 2;
				currentTouchTop = Math.abs(touches[0].pageY + touches[1].pageY) / 2;
			}

			// Store initial positions
			self.__initialTouchLeft = currentTouchLeft;
			self.__initialTouchTop = currentTouchTop;

			// Store current zoom level
			self.__zoomLevelStart = self.__zoomLevel;

			// Store initial touch positions
			self.__lastTouchLeft = currentTouchLeft;
			self.__lastTouchTop = currentTouchTop;

			// Store initial move time stamp
			self.__lastTouchMove = timeStamp;

			// Reset initial scale
			self.__lastScale = 1;

			// Reset locking flags
			self.__enableScrollX = !isSingleTouch && self.options.scrollingX;
			self.__enableScrollY = !isSingleTouch && self.options.scrollingY;

			// Reset tracking flag
			self.__isTracking = true;

			// Dragging starts directly with two fingers, otherwise lazy with an offset
			self.__isDragging = !isSingleTouch;

			// Some features are disabled in multi touch scenarios
			self.__isSingleTouch = isSingleTouch;

			// Clearing data structure
			self.__positions = [];

		},


		/**
		 * Touch move handler for scrolling support
		 */
		doTouchMove: function(touches, timeStamp, scale) {

			// Array-like check is enough here
			if (touches.length == null) {
				throw new Error("Invalid touch list: " + touches);
			}

			if (timeStamp instanceof Date) {
				timeStamp = timeStamp.valueOf();
			}
			if (typeof timeStamp !== "number") {
				throw new Error("Invalid timestamp value: " + timeStamp);
			}

			var self = this;

			// Ignore event when tracking is not enabled (event might be outside of element)
			if (!self.__isTracking) {
				return;
			}


			var currentTouchLeft, currentTouchTop;

			// Compute move based around of center of fingers
			if (touches.length === 2) {
				currentTouchLeft = Math.abs(touches[0].pageX + touches[1].pageX) / 2;
				currentTouchTop = Math.abs(touches[0].pageY + touches[1].pageY) / 2;
			} else {
				currentTouchLeft = touches[0].pageX;
				currentTouchTop = touches[0].pageY;
			}

			var positions = self.__positions;

			// Are we already is dragging mode?
			if (self.__isDragging) {

				// Compute move distance
				var moveX = currentTouchLeft - self.__lastTouchLeft;
				var moveY = currentTouchTop - self.__lastTouchTop;

				// Read previous scroll position and zooming
				var scrollLeft = self.__scrollLeft;
				var scrollTop = self.__scrollTop;
				var level = self.__zoomLevel;

				// Work with scaling
				if (scale != null && self.options.zooming) {

					var oldLevel = level;

					// Recompute level based on previous scale and new scale
					level = level / self.__lastScale * scale;

					// Limit level according to configuration
					level = Math.max(Math.min(level, self.options.maxZoom), self.options.minZoom);

					// Only do further compution when change happened
					if (oldLevel !== level) {

						// Compute relative event position to container
						var currentTouchLeftRel = currentTouchLeft - self.__clientLeft;
						var currentTouchTopRel = currentTouchTop - self.__clientTop;

						// Recompute left and top coordinates based on new zoom level
						scrollLeft = ((currentTouchLeftRel + scrollLeft) * level / oldLevel) - currentTouchLeftRel;
						scrollTop = ((currentTouchTopRel + scrollTop) * level / oldLevel) - currentTouchTopRel;

						// Recompute max scroll values
						self.__computeScrollMax(level);

					}
				}

				if (self.__enableScrollX) {

					scrollLeft -= moveX;
					var maxScrollLeft = self.__maxScrollLeft;

					if (scrollLeft > maxScrollLeft || scrollLeft < 0) {

						// Slow down on the edges
						if (self.options.bouncing) {

							scrollLeft += (moveX / 2);

						} else if (scrollLeft > maxScrollLeft) {

							scrollLeft = maxScrollLeft;

						} else {

							scrollLeft = 0;

						}
					}
				}

				// Compute new vertical scroll position
				if (self.__enableScrollY) {

					scrollTop -= moveY;
					var maxScrollTop = self.__maxScrollTop;

					if (scrollTop > maxScrollTop || scrollTop < 0) {

						// Slow down on the edges
						if (self.options.bouncing) {

							scrollTop += (moveY / 2);

							// Support pull-to-refresh (only when only y is scrollable)
							if (!self.__enableScrollX && self.__refreshHeight != null) {

								if (!self.__refreshActive && scrollTop <= -self.__refreshHeight) {

									self.__refreshActive = true;
									if (self.__refreshActivate) {
										self.__refreshActivate();
									}

								} else if (self.__refreshActive && scrollTop > -self.__refreshHeight) {

									self.__refreshActive = false;
									if (self.__refreshDeactivate) {
										self.__refreshDeactivate();
									}

								}
							}

						} else if (scrollTop > maxScrollTop) {

							scrollTop = maxScrollTop;

						} else {

							scrollTop = 0;

						}
					}
				}

				// Keep list from growing infinitely (holding min 10, max 20 measure points)
				if (positions.length > 60) {
					positions.splice(0, 30);
				}

				// Track scroll movement for decleration
				positions.push(scrollLeft, scrollTop, timeStamp);

				// Sync scroll position
				self.__publish(scrollLeft, scrollTop, level);

			// Otherwise figure out whether we are switching into dragging mode now.
			} else {

				var minimumTrackingForScroll = self.options.locking ? 3 : 0;
				var minimumTrackingForDrag = 5;

				var distanceX = Math.abs(currentTouchLeft - self.__initialTouchLeft);
				var distanceY = Math.abs(currentTouchTop - self.__initialTouchTop);

				self.__enableScrollX = self.options.scrollingX && distanceX >= minimumTrackingForScroll;
				self.__enableScrollY = self.options.scrollingY && distanceY >= minimumTrackingForScroll;

				positions.push(self.__scrollLeft, self.__scrollTop, timeStamp);

				self.__isDragging = (self.__enableScrollX || self.__enableScrollY) && (distanceX >= minimumTrackingForDrag || distanceY >= minimumTrackingForDrag);

			}

			// Update last touch positions and time stamp for next event
			self.__lastTouchLeft = currentTouchLeft;
			self.__lastTouchTop = currentTouchTop;
			self.__lastTouchMove = timeStamp;
			self.__lastScale = scale;

		},


		/**
		 * Touch end handler for scrolling support
		 */
		doTouchEnd: function(timeStamp) {

			if (timeStamp instanceof Date) {
				timeStamp = timeStamp.valueOf();
			}
			if (typeof timeStamp !== "number") {
				throw new Error("Invalid timestamp value: " + timeStamp);
			}

			var self = this;

			// Ignore event when tracking is not enabled (no touchstart event on element)
			// This is required as this listener ('touchmove') sits on the document and not on the element itself.
			if (!self.__isTracking) {
				return;
			}

			// Not touching anymore (when two finger hit the screen there are two touch end events)
			self.__isTracking = false;

			// Be sure to reset the dragging flag now. Here we also detect whether
			// the finger has moved fast enough to switch into a deceleration animation.
			if (self.__isDragging) {

				// Reset dragging flag
				self.__isDragging = false;

				// Start deceleration
				// Verify that the last move detected was in some relevant time frame
				if (self.__isSingleTouch && self.options.animating && (timeStamp - self.__lastTouchMove) <= 100) {

					// Then figure out what the scroll position was about 100ms ago
					var positions = self.__positions;
					var endPos = positions.length - 1;
					var startPos = endPos;

					// Move pointer to position measured 100ms ago
					for (var i = endPos; i > 0 && positions[i] > (self.__lastTouchMove - 100); i -= 3) {
						startPos = i;
					}

					// If start and stop position is identical in a 100ms timeframe,
					// we cannot compute any useful deceleration.
					if (startPos !== endPos) {

						// Compute relative movement between these two points
						var timeOffset = positions[endPos] - positions[startPos];
						var movedLeft = self.__scrollLeft - positions[startPos - 2];
						var movedTop = self.__scrollTop - positions[startPos - 1];

						// Based on 50ms compute the movement to apply for each render step
						self.__decelerationVelocityX = movedLeft / timeOffset * (1000 / 60);
						self.__decelerationVelocityY = movedTop / timeOffset * (1000 / 60);

						// How much velocity is required to start the deceleration
						var minVelocityToStartDeceleration = self.options.paging || self.options.snapping ? 4 : 1;

						// Verify that we have enough velocity to start deceleration
						if (Math.abs(self.__decelerationVelocityX) > minVelocityToStartDeceleration || Math.abs(self.__decelerationVelocityY) > minVelocityToStartDeceleration) {

							// Deactivate pull-to-refresh when decelerating
							if (!self.__refreshActive) {

								self.__startDeceleration(timeStamp);

							}
						}
					}
				}
			}

			// If this was a slower move it is per default non decelerated, but this
			// still means that we want snap back to the bounds which is done here.
			// This is placed outside the condition above to improve edge case stability
			// e.g. touchend fired without enabled dragging. This should normally do not
			// have modified the scroll positions or even showed the scrollbars though.
			if (!self.__isDecelerating) {

				if (self.__refreshActive && self.__refreshStart) {

					// Use publish instead of scrollTo to allow scrolling to out of boundary position
					// We don't need to normalize scrollLeft, zoomLevel, etc. here because we only y-scrolling when pull-to-refresh is enabled
					self.__publish(self.__scrollLeft, -self.__refreshHeight, self.__zoomLevel, true);

					if (self.__refreshStart) {
						self.__refreshStart();
					}

				} else {

					self.scrollTo(self.__scrollLeft, self.__scrollTop, true, self.__zoomLevel);

					// Directly signalize deactivation (nothing todo on refresh?)
					if (self.__refreshActive) {

						self.__refreshActive = false;
						if (self.__refreshDeactivate) {
							self.__refreshDeactivate();
						}

					}
				}
			}

			// Fully cleanup list
			self.__positions.length = 0;

		},



		/*
		---------------------------------------------------------------------------
			PRIVATE API
		---------------------------------------------------------------------------
		*/

		/**
		 * Applies the scroll position to the content element
		 *
		 * @param left {Number} Left scroll position
		 * @param top {Number} Top scroll position
		 * @param animate {Boolean?false} Whether animation should be used to move to the new coordinates
		 */
		__publish: function(left, top, zoom, animate) {

			var self = this;

			// Remember whether we had an animation, then we try to continue based on the current "drive" of the animation
			var wasAnimating = self.__isAnimating;
			if (wasAnimating) {
				core.effect.Animate.stop(wasAnimating);
				self.__isAnimating = false;
			}

			if (animate && self.options.animating) {

				// Keep scheduled positions for scrollBy/zoomBy functionality
				self.__scheduledLeft = left;
				self.__scheduledTop = top;
				self.__scheduledZoom = zoom;

				var oldLeft = self.__scrollLeft;
				var oldTop = self.__scrollTop;
				var oldZoom = self.__zoomLevel;

				var diffLeft = left - oldLeft;
				var diffTop = top - oldTop;
				var diffZoom = zoom - oldZoom;

				var step = function(percent, now, render) {

					if (render) {

						self.__scrollLeft = oldLeft + (diffLeft * percent);
						self.__scrollTop = oldTop + (diffTop * percent);
						self.__zoomLevel = oldZoom + (diffZoom * percent);

						// Push values out
						if (self.__callback) {
							self.__callback(self.__scrollLeft, self.__scrollTop, self.__zoomLevel);
						}

					}
				};

				var verify = function(id) {
					return self.__isAnimating === id;
				};

				var completed = function(renderedFramesPerSecond, animationId, wasFinished) {
					if (animationId === self.__isAnimating) {
						self.__isAnimating = false;
					}

					if (self.options.zooming) {
						self.__computeScrollMax();
					}
				};

				// When continuing based on previous animation we choose an ease-out animation instead of ease-in-out
				self.__isAnimating = core.effect.Animate.start(step, verify, completed, 250, wasAnimating ? easeOutCubic : easeInOutCubic);

			} else {

				self.__scheduledLeft = self.__scrollLeft = left;
				self.__scheduledTop = self.__scrollTop = top;
				self.__scheduledZoom = self.__zoomLevel = zoom;

				// Push values out
				if (self.__callback) {
					self.__callback(left, top, zoom);
				}

				// Fix max scroll ranges
				if (self.options.zooming) {
					self.__computeScrollMax();
				}
			}
		},


		/**
		 * Recomputes scroll minimum values based on client dimensions and content dimensions.
		 */
		__computeScrollMax: function(zoomLevel) {

			var self = this;

			if (zoomLevel == null) {
				zoomLevel = self.__zoomLevel;
			}

			self.__maxScrollLeft = Math.max((self.__contentWidth * zoomLevel) - self.__clientWidth, 0);
			self.__maxScrollTop = Math.max((self.__contentHeight * zoomLevel) - self.__clientHeight, 0);

		},



		/*
		---------------------------------------------------------------------------
			ANIMATION (DECELERATION) SUPPORT
		---------------------------------------------------------------------------
		*/

		/**
		 * Called when a touch sequence end and the speed of the finger was high enough
		 * to switch into deceleration mode.
		 */
		__startDeceleration: function(timeStamp) {

			var self = this;

			if (self.options.paging) {

				var scrollLeft = Math.max(Math.min(self.__scrollLeft, self.__maxScrollLeft), 0);
				var scrollTop = Math.max(Math.min(self.__scrollTop, self.__maxScrollTop), 0);
				var clientWidth = self.__clientWidth;
				var clientHeight = self.__clientHeight;

				// We limit deceleration not to the min/max values of the allowed range, but to the size of the visible client area.
				// Each page should have exactly the size of the client area.
				self.__minDecelerationScrollLeft = Math.floor(scrollLeft / clientWidth) * clientWidth;
				self.__minDecelerationScrollTop = Math.floor(scrollTop / clientHeight) * clientHeight;
				self.__maxDecelerationScrollLeft = Math.ceil(scrollLeft / clientWidth) * clientWidth;
				self.__maxDecelerationScrollTop = Math.ceil(scrollTop / clientHeight) * clientHeight;

			} else {

				self.__minDecelerationScrollLeft = 0;
				self.__minDecelerationScrollTop = 0;
				self.__maxDecelerationScrollLeft = self.__maxScrollLeft;
				self.__maxDecelerationScrollTop = self.__maxScrollTop;

			}

			// Wrap class method
			var step = function(percent, now, render) {
				self.__stepThroughDeceleration(render);
			};

			// How much velocity is required to keep the deceleration running
			var minVelocityToKeepDecelerating = self.options.snapping ? 4 : 0.1;

			// Detect whether it's still worth to continue animating steps
			// If we are already slow enough to not being user perceivable anymore, we stop the whole process here.
			var verify = function() {
				return Math.abs(self.__decelerationVelocityX) >= minVelocityToKeepDecelerating || Math.abs(self.__decelerationVelocityY) >= minVelocityToKeepDecelerating;
			};

			var completed = function(renderedFramesPerSecond, animationId, wasFinished) {
				self.__isDecelerating = false;

				// Animate to grid when snapping is active, otherwise just fix out-of-boundary positions
				self.scrollTo(self.__scrollLeft, self.__scrollTop, self.options.snapping);
			};

			// Start animation and switch on flag
			self.__isDecelerating = core.effect.Animate.start(step, verify, completed);

		},


		/**
		 * Called on every step of the animation
		 *
		 * @param inMemory {Boolean?false} Whether to not render the current step, but keep it in memory only. Used internally only!
		 */
		__stepThroughDeceleration: function(render) {

			var self = this;


			//
			// COMPUTE NEXT SCROLL POSITION
			//

			// Add deceleration to scroll position
			var scrollLeft = self.__scrollLeft + self.__decelerationVelocityX;
			var scrollTop = self.__scrollTop + self.__decelerationVelocityY;


			//
			// HARD LIMIT SCROLL POSITION FOR NON BOUNCING MODE
			//

			if (!self.options.bouncing) {

				var scrollLeftFixed = Math.max(Math.min(self.__maxScrollLeft, scrollLeft), 0);
				if (scrollLeftFixed !== scrollLeft) {
					scrollLeft = scrollLeftFixed;
					self.__decelerationVelocityX = 0;
				}

				var scrollTopFixed = Math.max(Math.min(self.__maxScrollTop, scrollTop), 0);
				if (scrollTopFixed !== scrollTop) {
					scrollTop = scrollTopFixed;
					self.__decelerationVelocityY = 0;
				}

			}


			//
			// UPDATE SCROLL POSITION
			//

			if (render) {

				self.__publish(scrollLeft, scrollTop, self.__zoomLevel);

			} else {

				self.__scrollLeft = scrollLeft;
				self.__scrollTop = scrollTop;

			}


			//
			// SLOW DOWN
			//

			// Slow down velocity on every iteration
			if (!self.options.paging) {

				// This is the factor applied to every iteration of the animation
				// to slow down the process. This should emulate natural behavior where
				// objects slow down when the initiator of the movement is removed
				var frictionFactor = 0.95;

				self.__decelerationVelocityX *= frictionFactor;
				self.__decelerationVelocityY *= frictionFactor;

			}


			//
			// BOUNCING SUPPORT
			//

			if (self.options.bouncing) {

				var scrollOutsideX = 0;
				var scrollOutsideY = 0;

				// This configures the amount of change applied to deceleration/acceleration when reaching boundaries
				var penetrationDeceleration = 0.03;
				var penetrationAcceleration = 0.08;

				// Check limits
				if (scrollLeft < self.__minDecelerationScrollLeft) {
					scrollOutsideX = self.__minDecelerationScrollLeft - scrollLeft;
				} else if (scrollLeft > self.__maxDecelerationScrollLeft) {
					scrollOutsideX = self.__maxDecelerationScrollLeft - scrollLeft;
				}

				if (scrollTop < self.__minDecelerationScrollTop) {
					scrollOutsideY = self.__minDecelerationScrollTop - scrollTop;
				} else if (scrollTop > self.__maxDecelerationScrollTop) {
					scrollOutsideY = self.__maxDecelerationScrollTop - scrollTop;
				}

				// Slow down until slow enough, then flip back to snap position
				if (scrollOutsideX !== 0) {
					if (scrollOutsideX * self.__decelerationVelocityX <= 0) {
						self.__decelerationVelocityX += scrollOutsideX * penetrationDeceleration;
					} else {
						self.__decelerationVelocityX = scrollOutsideX * penetrationAcceleration;
					}
				}

				if (scrollOutsideY !== 0) {
					if (scrollOutsideY * self.__decelerationVelocityY <= 0) {
						self.__decelerationVelocityY += scrollOutsideY * penetrationDeceleration;
					} else {
						self.__decelerationVelocityY = scrollOutsideY * penetrationAcceleration;
					}
				}
			}
		},
        /*
        为了兼容接口
         */
        __destroy:function(){

        }
	};

	// Copy over members to prototype
	for (var key in members) {
		Scroller.prototype[key] = members[key];
	}

    return Scroller;

})();;



/**
 * Generic animation class with support for dropped frames both optional easing and duration.
 *
 * Optional duration is useful when the lifetime is defined by another condition than time
 * e.g. speed of an animating object, etc.
 *
 * Dropped frame logic allows to keep using the same updater logic independent from the actual
 * rendering. This eases a lot of cases where it might be pretty complex to break down a state
 * based on the pure time difference.
 */
(function(global) {
	var time = Date.now || function() {
		return +new Date();
	};
	var desiredFrames = 60;
	var millisecondsPerSecond = 1000;
	var running = {};
	var counter = 1;

	// Create namespaces
	if (!global.core) {
		global.core = { effect : {} };
	} else if (!core.effect) {
		core.effect = {};
	}

	core.effect.Animate = {

		/**
		 * Stops the given animation.
		 *
		 * @param id {Integer} Unique animation ID
		 * @return {Boolean} Whether the animation was stopped (aka, was running before)
		 */
		stop: function(id) {
			var cleared = running[id] != null;
			if (cleared) {
				running[id] = null;
			}

			return cleared;
		},


		/**
		 * Whether the given animation is still running.
		 *
		 * @param id {Integer} Unique animation ID
		 * @return {Boolean} Whether the animation is still running
		 */
		isRunning: function(id) {
			return running[id] != null;
		},


		/**
		 * Start the animation.
		 *
		 * @param stepCallback {Function} Pointer to function which is executed on every step.
		 *   Signature of the method should be `function(percent, now, virtual) { return continueWithAnimation; }`
		 * @param verifyCallback {Function} Executed before every animation step.
		 *   Signature of the method should be `function() { return continueWithAnimation; }`
		 * @param completedCallback {Function}
		 *   Signature of the method should be `function(droppedFrames, finishedAnimation) {}`
		 * @param duration {Integer} Milliseconds to run the animation
		 * @param easingMethod {Function} Pointer to easing function
		 *   Signature of the method should be `function(percent) { return modifiedValue; }`
		 * @param root {Element ? document.body} Render root, when available. Used for internal
		 *   usage of requestAnimationFrame.
		 * @return {Integer} Identifier of animation. Can be used to stop it any time.
		 */
		start: function(stepCallback, verifyCallback, completedCallback, duration, easingMethod, root) {

			var start = time();
			var lastFrame = start;
			var percent = 0;
			var dropCounter = 0;
			var id = counter++;

			if (!root) {
				root = document.body;
			}

			// Compacting running db automatically every few new animations
			if (id % 20 === 0) {
				var newRunning = {};
				for (var usedId in running) {
					newRunning[usedId] = true;
				}
				running = newRunning;
			}

			// This is the internal step method which is called every few milliseconds
			var step = function(virtual) {

				// Normalize virtual value
				var render = virtual !== true;

				// Get current time
				var now = time();

				// Verification is executed before next animation step
				if (!running[id] || (verifyCallback && !verifyCallback(id))) {

					running[id] = null;
					completedCallback && completedCallback(desiredFrames - (dropCounter / ((now - start) / millisecondsPerSecond)), id, false);
					return;

				}

				// For the current rendering to apply let's update omitted steps in memory.
				// This is important to bring internal state variables up-to-date with progress in time.
				if (render) {

					var droppedFrames = Math.round((now - lastFrame) / (millisecondsPerSecond / desiredFrames)) - 1;
					for (var j = 0; j < Math.min(droppedFrames, 4); j++) {
						step(true);
						dropCounter++;
					}

				}

				// Compute percent value
				if (duration) {
					percent = (now - start) / duration;
					if (percent > 1) {
						percent = 1;
					}
				}

				// Execute step callback, then...
				var value = easingMethod ? easingMethod(percent) : percent;
				if ((stepCallback(value, now, render) === false || percent === 1) && render) {
					running[id] = null;
					completedCallback && completedCallback(desiredFrames - (dropCounter / ((now - start) / millisecondsPerSecond)), id, percent === 1 || duration == null);
				} else if (render) {
					lastFrame = now;
					requestAnimationFrame(step, root);
				}
			};

			// Mark as running
			running[id] = true;

			// Init first step
			requestAnimationFrame(step, root);

			// Return unique animation ID
			return id;
		}
	};
})(global);

var iscroll = function(dom, option){
    var content = this.content = typeof dom == 'string'?document.getElementById(dom):dom;
    var container =this.container = content.parentNode;
    container.style['overflow'] = 'hidden';
    option = option || {};

    if(container.clientWidth >= content.offsetWidth){
        option.scrollingX = false;
    }

    var scroller = this.scroller = new Scroller((function(globa, contentl) {

        var docStyle = document.documentElement.style;

        var engine;
        if (global.opera && Object.prototype.toString.call(opera) === '[object Opera]') {
            engine = 'presto';
        } else if ('MozAppearance' in docStyle) {
            engine = 'gecko';
        } else if ('WebkitAppearance' in docStyle) {
            engine = 'webkit';
        } else if (typeof navigator.cpuClass === 'string') {
            engine = 'trident';
        }

        var vendorPrefix = {
            trident: 'ms',
            gecko: 'Moz',
            webkit: 'Webkit',
            presto: 'O'
        }[engine];

        var helperElem = document.createElement("div");
        var undef;

        var perspectiveProperty = vendorPrefix + "Perspective";
        var transformProperty = vendorPrefix + "Transform";

        if (helperElem.style[perspectiveProperty] !== undef) {

            return function(left, top, zoom) {
                content.style[transformProperty] = 'translate3d(' + (-left) + 'px,' + (-top) + 'px,0) scale(' + zoom + ')';
            };

        } else if (helperElem.style[transformProperty] !== undef) {

            return function(left, top, zoom) {
                content.style[transformProperty] = 'translate(' + (-left) + 'px,' + (-top) + 'px) scale(' + zoom + ')';
            };

        } else {

            return function(left, top, zoom) {
                content.style.marginLeft = left ? (-left/zoom) + 'px' : '';
                content.style.marginTop = top ? (-top/zoom) + 'px' : '';
                content.style.zoom = zoom || '';
            };

        }
    })(global, content), option);
    scroller.setDimensions(container.clientWidth, container.clientHeight, content.offsetWidth, content.offsetHeight);

    var noscrollTname = {
        input:true
        ,textarea:true
        ,select:true
        ,a:true
    }

     if ('ontouchstart' in window) {

		container.addEventListener("touchstart", function(e) {
			// Don't react if initial down happens on a form element
            //alert(e.target.tagName);


            //if (e.target && e.target.tagName && e.target.tagName.match(/input|textarea|select|a/i)) {


            if(e.target && (typeof e.target.getAttribute == 'function') && e.target.getAttribute('noscroll') == 'true'){
                //console.log(e.target.getAttribute('noscroll'));
                return;
            }

            if (e.target && e.target.tagName) {
			    var tName= e.target.tagName.toLowerCase();

                if(noscrollTname[tName]){

                    return;
                }
			}



			scroller.doTouchStart(e.touches, e.timeStamp);
			e.preventDefault();
		}, false);

		document.addEventListener("touchmove", function(e) {

            /*if (e.target.tagName.match(/input|textarea|select/i)) {
				return;
			}*/
			scroller.doTouchMove(e.touches, e.timeStamp);
		}, false);

		document.addEventListener("touchend", function(e) {
            /*if (e.target.tagName.match(/input|textarea|select/i)) {
				return;
			}*/
			scroller.doTouchEnd(e.timeStamp);
		}, false);

	}
    else {



		var mousedown = false;

		container.addEventListener("mousedown", function(e) {

            var target = e.target;

			// Don't react if initial down happens on a form element
			//if (!e.target || !e.target.tagName || e.target.tagName.match(/input|textarea|select|a/i)) {
			if (!e.target || !e.target.tagName) {
				return;
			}
            var tName= e.target.tagName.toLowerCase();

            if(noscrollTname[tName]){
                return;
            }

             if(target.getAttribute('noscroll') == 'true'){
                //console.log(target.getAttribute('noscroll'));
                return;
            }

			scroller.doTouchStart([{
				pageX: e.pageX,
				pageY: e.pageY
			}], e.timeStamp);

			mousedown = true;
		}, false);

		document.addEventListener("mousemove", function(e) {
			if (!mousedown) {
				return;
			}

			scroller.doTouchMove([{
				pageX: e.pageX,
				pageY: e.pageY
			}], e.timeStamp);

			mousedown = true;
		}, false);

		document.addEventListener("mouseup", function(e) {
			if (!mousedown) {
				return;
			}

			scroller.doTouchEnd(e.timeStamp);

			mousedown = false;
		}, false);

	}

    //return scroller;
}

iscroll.prototype._touchstart = function(){

}
iscroll.prototype._touchmove = function(){}

iscroll.prototype.destroy = function(){
    return this.scroller.destroy();
}
iscroll.prototype.setPosition = function(x , y){
    return this.scroller.setPosition(x, y);
}

iscroll.prototype.scrollTo = function(x, y, z){
    return this.scroller.scrollTo(x, y ,z);
}
iscroll.prototype.refresh = function(){
    this.scroller.setDimensions(this.container.clientWidth, this.container.clientHeight, this.content.offsetWidth, this.content.offsetHeight);
}

window.iScroll3 = window.iScroll2 = iscroll;


})(this);