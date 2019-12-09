
// Avoid 'console' errors in browsers that lack a console.
(function() {
	var method;
	var noop = function() {};
	var methods = [
		'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
		'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
		'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
		'timeStamp', 'trace', 'warn'
	];
	var length = methods.length;
	var console = (window.console = window.console || {});

	while(length--) {
		method = methods[length];
		if(!console[method]) { // only stub undefined methods
			console[method] = noop;
		}
	}
}());

// Skip link focus fix for screen readers
(function() {
	var is_webkit = navigator.userAgent.toLowerCase().indexOf( 'webkit' ) > -1,
	    is_opera  = navigator.userAgent.toLowerCase().indexOf( 'opera' )  > -1,
	    is_ie     = navigator.userAgent.toLowerCase().indexOf( 'msie' )   > -1;
	if((is_webkit || is_opera || is_ie ) && document.getElementById && window.addEventListener) {
		window.addEventListener( 'hashchange', function() {
			var id = location.hash.substring(1), element;
			if(!(/^[A-z0-9_-]+$/.test(id))) {
				return;
			}
			element = document.getElementById(id);
			if(element) {
				if(!(/^(?:a|select|input|button|textarea)$/i.test(element.tagName))) {
					element.tabIndex = -1;
				}
				element.focus();
			}
		}, false);
	}
})();



// equalHeight

(function($) {

	var methods = {
		init: function(options) {
			var $group = this;
			var is_init = false;

			var settings = $.extend({
				breakpoint: 768
			}, options);

			$group.each(function() {
				var $this = $(this);
				var data = $this.data('equalHeight');
				if(!data) {
					$this.data('equalHeight', { breakpoint: settings.breakpoint });
					is_init = true;
				}
			});

			if(is_init) {
				$group.equalHeight('equalize');
				$(window).load(function() { $group.equalHeight('equalize'); });
				$(window).resize(function() { $group.equalHeight('equalize'); });
			}

			return $group;
		},
		equalize: function() {
			var $group = this;
			var windowWidth = document.body.clientWidth ? document.body.clientWidth : window.outerWidth;

			$group.css('height', 'auto');
			if(windowWidth < $group.first().data('equalHeight').breakpoint) { return false; }
			var maxHeight = 0;
			$group.each(function() { maxHeight = Math.max(maxHeight, $(this).height()); });
			$group.height(maxHeight);

			return $group;
		}
	};

	$.fn.equalHeight = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if(typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.equalHeight');
		}
	};

})(jQuery);



// card grid

(function($) {

	var methods = {
		init: function(options) {
			var $this = this;

			var settings = $.extend({}, options);

			$this.each(function() {
				var $this = $(this);
				var data = $this.data('cardGrid');
				if(!data) {

					var cards = $this.children();
					cards.addClass('card');

					var columnCounts = {};
					columnCounts.xs = $this.data('cols-xs') ? $this.data('cols-xs') : 1;
					columnCounts.sm = $this.data('cols-sm') ? $this.data('cols-sm') : cols.xs;
					columnCounts.md = $this.data('cols-md') ? $this.data('cols-md') : cols.sm;
					columnCounts.lg = $this.data('cols-lg') ? $this.data('cols-lg') : cols.md;

					var maxColumns = 1;
					for(var i in columnCounts) {
						maxColumns = Math.max(maxColumns, columnCounts[i]);
						$this.addClass('cols-' + i + '-' + columnCounts[i]);
					}

					for(var i = 0; i < maxColumns; i++) {
						var column = $('<div class="column"></div>');
						for(var j in columnCounts) {
							if(i < columnCounts[j]) {
								column.addClass('visible-col-' + j);
							}
						}
						$this.append(column);
					}
					var columns = $this.children('.column');

					$this.data('cardGrid', {
						settings: settings,
						cards: cards,
						columns: columns,
						columnCounts: columnCounts
					});

					$this.cardGrid('refresh');
					$(window).load(function() { $this.cardGrid('refresh'); });
					$(window).resize(function() { $this.cardGrid('refresh'); });

				}
			});

			return $this;
		},
		refresh: function() {
			var $this = this;
			var data = $this.data('cardGrid');

			data.columns.empty();
			var visibleColumns = data.columns.filter(':visible');

			data.cards.each(function(i, el) {
				var shortestColumn = null;
				visibleColumns.each(function(j, el) {
					if(shortestColumn === null || $(el).height() < shortestColumn.height()) {
						shortestColumn = $(el);
					}
				});
				if(shortestColumn !== null) {
					shortestColumn.append(el);
				}
			});

			$this.trigger('cardGridRefreshed');

			return $this;
		}
	};

	$.fn.cardGrid = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if(typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.cardGrid');
		}
	};

})(jQuery);



// Place any jQuery/helper plugins in here.