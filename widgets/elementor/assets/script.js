jQuery(document).ready(function(jQuery) {
    jQuery(document).on('click', '.th-course-categories-filter li',  function(){
    	var _that = jQuery(this);
    	var id = '#' + _that.data('id');
    	jQuery(id).addClass('transitioning');
    	_that.parents().find('li').removeClass('active');
    	_that.addClass('active');
	    var category = _that.data('cat');
 		var settings = _that.closest('ul').data('settings');

        jQuery.ajax({
        	type: 'POST',
	        url: Metafans_JS.ajaxurl,
	        proccessData: false,
	        data: {
	            'action': 'course_grid_pull_cats',
	            'category' : category,
	            'settings' : settings
	        },
	        success:function(data) {
	            jQuery(id).addClass('transitionending');
	            setTimeout( function(){
	            	jQuery(id).html(data).removeClass('transitioning');
		            jQuery(id).removeClass('transitionending');
	            }, 400);
	        },
	        error: function(xhr, ajaxOptions, thrownError){
	        	console.log(xhr.responseText);
	        }
	    });  
    });
    jQuery(document).on('click', '.th-course-trending-popular-filter li',  function(){
    	var _that = jQuery(this);
    	var container 			= '.' + _that.data('id');
    	var container_replace 	= jQuery(container + ' .ec-dynamic-slides');
    	var style 				= jQuery(container).find('.swiper-slide').attr('style');
    	jQuery(container_replace).css('opacity', 0.2);

    	_that.parents().find('li').removeClass('active');
    	_that.addClass('active');

	    var category = _that.data('cat');
	    var sort 	 = _that.data('sort');
 		var settings = _that.closest('ul').data('settings');

        jQuery.ajax({
        	type: 'POST',
	        url: Metafans_JS.ajaxurl,
	        proccessData: false,
	        data: {
	            'action': 'course_grid_pull_cats',
	            'category' : category,
	            'settings' : settings,
	            'sort' : sort,
	            'style' : style,
	            'type' : 'slider'
	        },
	        success:function(data) {
	            jQuery(container_replace).html(data).css('opacity', 1);
	        },
	        error: function(xhr, ajaxOptions, thrownError){
	        	console.log(xhr.responseText);
	        }
	    });  
    });

    jQuery(document).on('click', '.th-course-pagination-arrow .ec-switch-button:not(.disabled)', function(){
    	var _that = jQuery(this); 
    	var target = _that.parents('.ajax-replace');
	    var category = _that.data('category');

	    var offset = _that.data('offset');
	    var type = _that.data('type');

	    if( type == 'arrow' ){
    		jQuery(target).addClass('transitioning');
	    }
	    if( type == 'load_more' ){
    		_that.addClass('loading');
	    }

 		var settings = _that.data('settings');
	    jQuery.ajax({
	    	type: 'POST',
	        url: Metafans_JS.ajaxurl,
	        // dataType: 'json',
	        data: {
	            'action': 'pull_course_paged',
	            'category' : category,
	            'offset' : offset,
	            'settings' : settings
	        },
	        success:function(data) {
	        	if( type == 'arrow' ){
	            	_that.addClass('ec-d-none').closest('.ajax-replace').html(data).removeClass('transitioning');
	        	}else{
	            	_that.addClass('ec-d-none').closest('.ajax-replace').append(data);
		            _that.remove();
	        	}
	        	jQuery( document.body ).trigger( 'post-load' );
	        },
	        error: function(xhr, ajaxOptions, thrownError){
	        	console.log(xhr);
	        }
	    });  
    });
    jQuery(document).on('click', '.th-blog-pagination-arrow .ec-switch-button:not(.disabled)', function(){
    	var _that = jQuery(this); 
    	_that.closest('.ajax-replace').addClass('transitioning');
	    var offset = _that.data('offset');
 		var settings = _that.data('settings');
	    jQuery.ajax({
	    	type: 'POST',
	        url: Metafans_JS.ajaxurl,
	        data: {
	            'action': 'pull_posts_paged',
	            'offset' : offset,
	            'settings' : settings
	        },
	        success:function(data) {
	            _that.closest('.ajax-replace').html(data).removeClass('transitioning');
	        },
	        error: function(xhr, ajaxOptions, thrownError){
	            console.log(xhr.responseText);
	        }
	    });  
    });
    
    jQuery(document).on('click', '.hover-info-wishlist a', function(e){
    	e.preventDefault();
    	var course_id = jQuery(this).data('id');
    	var nonce = jQuery(this).data('nonce');
    	if(jQuery(this).hasClass('on')){
	    	jQuery('.hover-wishlist-' + course_id + ' > .th-tooltip-text').html('Removing...');
    	}else{
	    	jQuery('.hover-wishlist-' + course_id + ' > .th-tooltip-text').html('Adding...');
    	}
    	jQuery.ajax({
			url     : window.location.href,
			type    : 'post',
			dataType: 'html',
			data    : {
				'lp-ajax': 'toggle_course_wishlist',
				course_id: course_id,
				nonce    : nonce
			},
			success : function (response) {
				var response = LP.parseJSON(response);
				console.log(response.state);
				if(response.state == 'on'){
					jQuery('.hover-wishlist-' + response.course_id).removeClass('on off').addClass(response.state);
					jQuery('.hover-wishlist-' + response.course_id + ' > .th-tooltip-text').html(response.button_text);
				}else if(response.state == 'off'){
					jQuery('.hover-wishlist-' + response.course_id).removeClass('on off');
					jQuery('.hover-wishlist-' + response.course_id + ' > .th-tooltip-text').html(response.button_text);
				}
			}
		});
    });

    jQuery('.tophive-advanced-search-form input.search-input').keyup(function(event) {
    	var text = this.value;
    	if( text !== '' ){
	    	jQuery(this).parent().find('.search-result-wrapper').slideDown(400, function() {
	    		jQuery(this).parent().find('.search-result-wrapper span.search-text').html(text);
	    		//ajax
	    		jQuery.ajax({
					url     : Metafans_JS.ajaxurl,
					type    : 'post',
					data    : {
						action : 'th_advanced_search',
						text : text,
						post_type : 'lp_course'
					},
					success : function (response) {
						jQuery('.search-result-wrapper').html(response);
						console.log(response);
					},
			        error: function(xhr, ajaxOptions, thrownError){
			        	console.log(xhr);
			        }
				});
	    	});
    	}else{
    		jQuery(this).parent().find('.search-result-wrapper').slideUp(200, function() {
	    		jQuery(this).parent().find('.search-result-wrapper span.search-text').html(text);
	    	});
    	}
    });

	function resetTabAnimator(){
	    jQuery('.tab-animator').stop();
	    jQuery('.tab-animator').css('width', '0%'); 
	}
	function switchNextTab(){
	    resetTabAnimator();
			var currentTab = jQuery('ul.tophive-advanced-tab-nav li a.active'),
				currentTabHref = jQuery(currentTab).attr('href');

				if( jQuery(currentTab).parent('li').next('li').length > 0 ){
					var nextTab = jQuery(currentTab).parent('li').next('li').find('a');
				}else{
					var nextTab = jQuery('ul.tophive-advanced-tab-nav li:first').find('a');
				}

				var nextTabHref = jQuery(nextTab).attr('href');
			  	jQuery(currentTab).removeClass('active');
			  	jQuery(nextTab).addClass('active');
			  	jQuery(currentTabHref).fadeOut(200, function(){
				jQuery(nextTabHref).fadeIn(200);
			});
			jQuery(nextTab).find('.tab-animator').animate({
				width: '100%'
			}, 7000, 'linear', function(){
				jQuery(nextTab).find('.tab-animator').css('width', '0%');
	      		switchNextTab();
			});
		}
	function selectCurrentTab( target ){
	  	resetTabAnimator();
		jQuery('ul.tophive-advanced-tab-nav li a').removeClass('active');
			currentTabHref = jQuery(target).attr('href');
			jQuery(target).addClass('active');
			jQuery('.tophive-advanced-tab-content-panel').hide()
			jQuery(currentTabHref).fadeIn(300);
		jQuery(target).find('.tab-animator').animate({
			width: '100%'
		}, 7000, 'linear', function(){
			jQuery(target).find('.tab-animator').css('width', '0%');
			switchNextTab();
		});
	}
	jQuery('ul.tophive-advanced-tab-nav li a').click(function(e){
	    e.preventDefault();
	    var target = jQuery(this);
		selectCurrentTab( target );
	});
	jQuery('ul.tophive-advanced-tab-nav li:first a').click();


	jQuery('.tophive-forum-tabs li a').on('click', function(e){
		e.preventDefault();
		var target = jQuery(this).attr('href');
		jQuery('.tophive-forum-tabs li').removeClass('active');
		jQuery(this).parent().addClass('active');
		jQuery('.tophive-forum-recent-topics-tab-container').fadeOut(200, function(){
			jQuery(target).fadeIn(200);
		});
	});
	if( jQuery('#thbbpresspostdesc').length ){
		var quillEditor = new Quill('#thbbpresspostdesc', {
		  modules: {
		    toolbar: [
		      [{ header: [1, 2, 3, 4, false] }],
		      ['bold', 'italic', 'underline'],
		      ['link', 'blockquote', 'code-block', 'image'],
		      [{ list: 'ordered' }, { list: 'bullet' }]
		    ]
		  },
		  placeholder: 'Your topic description...',
		  theme: 'snow'  // or 'bubble'
		});
	}

	jQuery('.tophive-bbpress-new-post-form form').on('submit', function(e){
		var that = jQuery(this);
		that.find('.thbbpresspostsubmit').addClass('loading');
		var data = that.serializeArray();
		var text = quillEditor.root.innerHTML;
		data[data.length] = { name: "thbbpresspostdesc", value: text };
		console.log(data);
		jQuery.ajax({
			url     : Metafans_JS.ajaxurl,
			type    : 'post',
			data    : {
				action : 'th_post_topic',
				nonce : th_elem_ajax_obj.nonce,
				data : data,
			},
			beforeSend: function(){
				that.find('.thbbpresspostsubmit').val('Posting...');
			},
			success : function (response) {
				console.log(response);
				if( response.post_id !== null && response.status == 'success' ){
					var responseText = '<span class="ec-text-success">' + response.msg + '</span>';
				}else{
					var responseText = '<span class="ec-text-danger">' + response.msg + '</span>';
				}
				jQuery('.tophive-bbpress-new-post-form').find('.response-text').html(responseText);
				window.location = response.redirect_url;
			},
	        error: function(xhr, ajaxOptions, thrownError){
	        	console.log(xhr);
	        }
		});
		return false;
	});

});
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define("MetafansSlider",[],t):"object"==typeof exports?exports.MetafansSlider=t():e.MetafansSlider=t()}("undefined"!=typeof self?self:this,function(){return function(e){function t(r){if(i[r])return i[r].exports;var n=i[r]={i:r,l:!1,exports:{}};return e[r].call(n.exports,n,n.exports,t),n.l=!0,n.exports}var i={};return t.m=e,t.c=i,t.d=function(e,i,r){t.o(e,i)||Object.defineProperty(e,i,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var i=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(i,"a",i),i},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=0)}([function(e,t,i){"use strict";function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},s=function(){function e(e,t){for(var i=0;i<t.length;i++){var r=t[i];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,i,r){return i&&e(t.prototype,i),r&&e(t,r),t}}(),l=function(){function e(t){var i=this;if(r(this,e),this.config=e.mergeSettings(t),this.selector="string"==typeof this.config.selector?document.querySelector(this.config.selector):this.config.selector,null===this.selector)throw new Error("Something wrong with your selector 😭");this.resolveSlidesNumber(),this.selectorWidth=this.selector.offsetWidth,this.innerElements=[].slice.call(this.selector.children),this.currentSlide=this.config.loop?this.config.startIndex%this.innerElements.length:Math.max(0,Math.min(this.config.startIndex,this.innerElements.length-this.perPage)),this.transformProperty=e.webkitOrNot(),["resizeHandler","touchstartHandler","touchendHandler","touchmoveHandler","mousedownHandler","mouseupHandler","mouseleaveHandler","mousemoveHandler","clickHandler"].forEach(function(e){i[e]=i[e].bind(i)}),this.init()}return s(e,[{key:"attachEvents",value:function(){window.addEventListener("resize",this.resizeHandler),this.config.draggable&&(this.pointerDown=!1,this.drag={startX:0,endX:0,startY:0,letItGo:null,preventClick:!1},this.selector.addEventListener("touchstart",this.touchstartHandler),this.selector.addEventListener("touchend",this.touchendHandler),this.selector.addEventListener("touchmove",this.touchmoveHandler),this.selector.addEventListener("mousedown",this.mousedownHandler),this.selector.addEventListener("mouseup",this.mouseupHandler),this.selector.addEventListener("mouseleave",this.mouseleaveHandler),this.selector.addEventListener("mousemove",this.mousemoveHandler),this.selector.addEventListener("click",this.clickHandler))}},{key:"detachEvents",value:function(){window.removeEventListener("resize",this.resizeHandler),this.selector.removeEventListener("touchstart",this.touchstartHandler),this.selector.removeEventListener("touchend",this.touchendHandler),this.selector.removeEventListener("touchmove",this.touchmoveHandler),this.selector.removeEventListener("mousedown",this.mousedownHandler),this.selector.removeEventListener("mouseup",this.mouseupHandler),this.selector.removeEventListener("mouseleave",this.mouseleaveHandler),this.selector.removeEventListener("mousemove",this.mousemoveHandler),this.selector.removeEventListener("click",this.clickHandler)}},{key:"init",value:function(){this.attachEvents(),this.selector.style.overflow="hidden",this.selector.style.direction=this.config.rtl?"rtl":"ltr",this.buildSliderFrame(),this.config.onInit.call(this)}},{key:"buildSliderFrame",value:function(){var e=this.selectorWidth/this.perPage,t=this.config.loop?this.innerElements.length+2*this.perPage:this.innerElements.length;this.sliderFrame=document.createElement("div"),this.sliderFrame.style.width=e*t+"px",this.enableTransition(),this.config.draggable&&(this.selector.style.cursor="-webkit-grab");var i=document.createDocumentFragment();if(this.config.loop)for(var r=this.innerElements.length-this.perPage;r<this.innerElements.length;r++){var n=this.buildSliderFrameItem(this.innerElements[r].cloneNode(!0));i.appendChild(n)}for(var s=0;s<this.innerElements.length;s++){var l=this.buildSliderFrameItem(this.innerElements[s]);i.appendChild(l)}if(this.config.loop)for(var o=0;o<this.perPage;o++){var a=this.buildSliderFrameItem(this.innerElements[o].cloneNode(!0));i.appendChild(a)}this.sliderFrame.appendChild(i),this.selector.innerHTML="",this.selector.appendChild(this.sliderFrame),this.slideToCurrent()}},{key:"buildSliderFrameItem",value:function(e){var t=document.createElement("div");return t.style.cssFloat=this.config.rtl?"right":"left",t.style.float=this.config.rtl?"right":"left",t.style.width=(this.config.loop?100/(this.innerElements.length+2*this.perPage):100/this.innerElements.length)+"%",t.appendChild(e),t}},{key:"resolveSlidesNumber",value:function(){if("number"==typeof this.config.perPage)this.perPage=this.config.perPage;else if("object"===n(this.config.perPage)){this.perPage=1;for(var e in this.config.perPage)window.innerWidth>=e&&(this.perPage=this.config.perPage[e])}}},{key:"prev",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments[1];if(!(this.innerElements.length<=this.perPage)){var i=this.currentSlide;if(this.config.loop){if(this.currentSlide-e<0){this.disableTransition();var r=this.currentSlide+this.innerElements.length,n=this.perPage,s=r+n,l=(this.config.rtl?1:-1)*s*(this.selectorWidth/this.perPage),o=this.config.draggable?this.drag.endX-this.drag.startX:0;this.sliderFrame.style[this.transformProperty]="translate3d("+(l+o)+"px, 0, 0)",this.currentSlide=r-e}else this.currentSlide=this.currentSlide-e}else this.currentSlide=Math.max(this.currentSlide-e,0);i!==this.currentSlide&&(this.slideToCurrent(this.config.loop),this.config.onChange.call(this),t&&t.call(this))}}},{key:"next",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments[1];if(!(this.innerElements.length<=this.perPage)){var i=this.currentSlide;if(this.config.loop){if(this.currentSlide+e>this.innerElements.length-this.perPage){this.disableTransition();var r=this.currentSlide-this.innerElements.length,n=this.perPage,s=r+n,l=(this.config.rtl?1:-1)*s*(this.selectorWidth/this.perPage),o=this.config.draggable?this.drag.endX-this.drag.startX:0;this.sliderFrame.style[this.transformProperty]="translate3d("+(l+o)+"px, 0, 0)",this.currentSlide=r+e}else this.currentSlide=this.currentSlide+e}else this.currentSlide=Math.min(this.currentSlide+e,this.innerElements.length-this.perPage);i!==this.currentSlide&&(this.slideToCurrent(this.config.loop),this.config.onChange.call(this),t&&t.call(this))}}},{key:"disableTransition",value:function(){this.sliderFrame.style.webkitTransition="all 0ms "+this.config.easing,this.sliderFrame.style.transition="all 0ms "+this.config.easing}},{key:"enableTransition",value:function(){this.sliderFrame.style.webkitTransition="all "+this.config.duration+"ms "+this.config.easing,this.sliderFrame.style.transition="all "+this.config.duration+"ms "+this.config.easing}},{key:"goTo",value:function(e,t){if(!(this.innerElements.length<=this.perPage)){var i=this.currentSlide;this.currentSlide=this.config.loop?e%this.innerElements.length:Math.min(Math.max(e,0),this.innerElements.length-this.perPage),i!==this.currentSlide&&(this.slideToCurrent(),this.config.onChange.call(this),t&&t.call(this))}}},{key:"slideToCurrent",value:function(e){var t=this,i=this.config.loop?this.currentSlide+this.perPage:this.currentSlide,r=(this.config.rtl?1:-1)*i*(this.selectorWidth/this.perPage);e?requestAnimationFrame(function(){requestAnimationFrame(function(){t.enableTransition(),t.sliderFrame.style[t.transformProperty]="translate3d("+r+"px, 0, 0)"})}):this.sliderFrame.style[this.transformProperty]="translate3d("+r+"px, 0, 0)"}},{key:"updateAfterDrag",value:function(){var e=(this.config.rtl?-1:1)*(this.drag.endX-this.drag.startX),t=Math.abs(e),i=this.config.multipleDrag?Math.ceil(t/(this.selectorWidth/this.perPage)):1,r=e>0&&this.currentSlide-i<0,n=e<0&&this.currentSlide+i>this.innerElements.length-this.perPage;e>0&&t>this.config.threshold&&this.innerElements.length>this.perPage?this.prev(i):e<0&&t>this.config.threshold&&this.innerElements.length>this.perPage&&this.next(i),this.slideToCurrent(r||n)}},{key:"resizeHandler",value:function(){this.resolveSlidesNumber(),this.currentSlide+this.perPage>this.innerElements.length&&(this.currentSlide=this.innerElements.length<=this.perPage?0:this.innerElements.length-this.perPage),this.selectorWidth=this.selector.offsetWidth,this.buildSliderFrame()}},{key:"clearDrag",value:function(){this.drag={startX:0,endX:0,startY:0,letItGo:null,preventClick:this.drag.preventClick}}},{key:"touchstartHandler",value:function(e){-1!==["TEXTAREA","OPTION","INPUT","SELECT"].indexOf(e.target.nodeName)||(e.stopPropagation(),this.pointerDown=!0,this.drag.startX=e.touches[0].pageX,this.drag.startY=e.touches[0].pageY)}},{key:"touchendHandler",value:function(e){e.stopPropagation(),this.pointerDown=!1,this.enableTransition(),this.drag.endX&&this.updateAfterDrag(),this.clearDrag()}},{key:"touchmoveHandler",value:function(e){if(e.stopPropagation(),null===this.drag.letItGo&&(this.drag.letItGo=Math.abs(this.drag.startY-e.touches[0].pageY)<Math.abs(this.drag.startX-e.touches[0].pageX)),this.pointerDown&&this.drag.letItGo){e.preventDefault(),this.drag.endX=e.touches[0].pageX,this.sliderFrame.style.webkitTransition="all 0ms "+this.config.easing,this.sliderFrame.style.transition="all 0ms "+this.config.easing;var t=this.config.loop?this.currentSlide+this.perPage:this.currentSlide,i=t*(this.selectorWidth/this.perPage),r=this.drag.endX-this.drag.startX,n=this.config.rtl?i+r:i-r;this.sliderFrame.style[this.transformProperty]="translate3d("+(this.config.rtl?1:-1)*n+"px, 0, 0)"}}},{key:"mousedownHandler",value:function(e){-1!==["TEXTAREA","OPTION","INPUT","SELECT"].indexOf(e.target.nodeName)||(e.preventDefault(),e.stopPropagation(),this.pointerDown=!0,this.drag.startX=e.pageX)}},{key:"mouseupHandler",value:function(e){e.stopPropagation(),this.pointerDown=!1,this.selector.style.cursor="-webkit-grab",this.enableTransition(),this.drag.endX&&this.updateAfterDrag(),this.clearDrag()}},{key:"mousemoveHandler",value:function(e){if(e.preventDefault(),this.pointerDown){"A"===e.target.nodeName&&(this.drag.preventClick=!0),this.drag.endX=e.pageX,this.selector.style.cursor="-webkit-grabbing",this.sliderFrame.style.webkitTransition="all 0ms "+this.config.easing,this.sliderFrame.style.transition="all 0ms "+this.config.easing;var t=this.config.loop?this.currentSlide+this.perPage:this.currentSlide,i=t*(this.selectorWidth/this.perPage),r=this.drag.endX-this.drag.startX,n=this.config.rtl?i+r:i-r;this.sliderFrame.style[this.transformProperty]="translate3d("+(this.config.rtl?1:-1)*n+"px, 0, 0)"}}},{key:"mouseleaveHandler",value:function(e){this.pointerDown&&(this.pointerDown=!1,this.selector.style.cursor="-webkit-grab",this.drag.endX=e.pageX,this.drag.preventClick=!1,this.enableTransition(),this.updateAfterDrag(),this.clearDrag())}},{key:"clickHandler",value:function(e){this.drag.preventClick&&e.preventDefault(),this.drag.preventClick=!1}},{key:"remove",value:function(e,t){if(e<0||e>=this.innerElements.length)throw new Error("Item to remove doesn't exist 😭");var i=e<this.currentSlide,r=this.currentSlide+this.perPage-1===e;(i||r)&&this.currentSlide--,this.innerElements.splice(e,1),this.buildSliderFrame(),t&&t.call(this)}},{key:"insert",value:function(e,t,i){if(t<0||t>this.innerElements.length+1)throw new Error("Unable to inset it at this index 😭");if(-1!==this.innerElements.indexOf(e))throw new Error("The same item in a carousel? Really? Nope 😭");var r=t<=this.currentSlide>0&&this.innerElements.length;this.currentSlide=r?this.currentSlide+1:this.currentSlide,this.innerElements.splice(t,0,e),this.buildSliderFrame(),i&&i.call(this)}},{key:"prepend",value:function(e,t){this.insert(e,0),t&&t.call(this)}},{key:"append",value:function(e,t){this.insert(e,this.innerElements.length+1),t&&t.call(this)}},{key:"destroy",value:function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0],t=arguments[1];if(this.detachEvents(),this.selector.style.cursor="auto",e){for(var i=document.createDocumentFragment(),r=0;r<this.innerElements.length;r++)i.appendChild(this.innerElements[r]);this.selector.innerHTML="",this.selector.appendChild(i),this.selector.removeAttribute("style")}t&&t.call(this)}}],[{key:"mergeSettings",value:function(e){var t={selector:".th-slider",duration:200,easing:"ease-out",perPage:1,startIndex:0,draggable:!0,multipleDrag:!0,threshold:20,loop:!1,rtl:!1,onInit:function(){},onChange:function(){}},i=e;for(var r in i)t[r]=i[r];return t}},{key:"webkitOrNot",value:function(){return"string"==typeof document.documentElement.style.transform?"transform":"WebkitTransform"}}]),e}();t.default=l,e.exports=t.default}])});