

(function($) {

	var mapSettings = {};


	$(document).ready(function() {

		$.wptheme.initMobileMenu();
		$.wptheme.initGalleries();
		$.wptheme.initSliders();
		$.wptheme.initMaps();

		$('.card-grid').cardGrid({});

	});


	$(window).load(function() {

		

	});


	$.wptheme = (function(wptheme) {


		wptheme.initMobileMenu = function() {

			$('#mobile-menu-toggle').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$('body').toggleClass('mobile-menu-active');
			}).on('vmousedown', function(e) {
				e.stopPropagation();
			});
			$(document).on('vmousedown', 'body.mobile-menu-active #page', function() {
				$('body').removeClass('mobile-menu-active');
			});

			$('#mobile-navigation a').on('click', function(e) {
				var menuItem = $(this).parent();
				var subMenu = $('> ul.sub-menu', menuItem);
				if(subMenu.length && !menuItem.hasClass('sub-menu-active')) {
					e.preventDefault();
					$('#mobile-navigation .menu-item.sub-menu-active').not(menuItem.parents('.sub-menu-active')).each(function(i, el) {
						$(el).removeClass('sub-menu-active');
						$('> ul.sub-menu', el).animate({ height: 0 }, 400, 'easeOutExpo');
					});
					menuItem.addClass('sub-menu-active');
					subMenu.css({ height: 'auto' });
					var height = subMenu.outerHeight();
					subMenu.css({ height: 0 }).animate({ height: height }, 400, 'easeOutExpo', function() { $(this).css({ height: 'auto' }); });
				}
			});

			$('#mobile-navigation .menu-item.current-menu-ancestor, #mobile-navigation .menu-item.current-menu-item').each(function(i, el) {
				var subMenu = $('> ul.sub-menu', el);
				if(subMenu.length) {
					$(el).addClass('sub-menu-active');
					subMenu.css({ height: 'auto' });
				}
			});

		};


		wptheme.initGalleries = function() {

			$(document).on('click', '.gallery .gallery-icon a', function(e) {
				e.preventDefault();
				var gallery = $(this).closest('.gallery');
				var galleryLinks = [];
				$('.gallery-item', gallery).each(function(i, el) {
					galleryLinks.push({
						title: $('.gallery-icon img', el).attr('alt'),
						href: $('.gallery-icon a', el).attr('href'),
						thumbnail: $('.gallery-icon img', el).attr('src')
					});
				});
				blueimp.Gallery(galleryLinks, {
					index: $(this).closest('.gallery-item').index()
				});
			});

			$(document).on('click', '.section-gallery a', function(e) {
				e.preventDefault();
				var gallery = $(this).closest('.section-gallery');
				var galleryLinks = [];
				$('.image-container', gallery).each(function(i, el) {
					galleryLinks.push({
						title: $('img', el).attr('alt'),
						href: $('a', el).attr('href'),
						thumbnail: $('img', el).attr('src')
					});
				});
				blueimp.Gallery(galleryLinks, {
					index: $(this).closest('.image-container').index()
				});
			});

		};


		wptheme.initSliders = function() {

			$('section.page-section.hero-slider-section .section-slider').each(function(i, el) {
				var slider = $(el);
				slider.slick({
					arrows: false,
					dots: true,
					mobileFirst: true,
					responsive: [
						{
							breakpoint: 1330,
							settings: {
								arrows: true,
							}
						}
					]
				}).on('setPosition', function(event, slick) {
					var track = $('.slick-track', slick.$slider);
					var slides = $('.slick-slide', slick.$slider);
					slides.css({ height: 'auto' });
					slides.css({ height: track.height() });
				});
			});

			$('section.page-section.image-slider-section .section-slider').each(function(i, el) {
				var slider = $(el);
				slider.slick({
					dots: false
				});
			});

			$('section.page-section.logo-slider-section .section-slider').each(function(i, el) {
				var slider = $(el);
				slider.slick({
					infinite: true,
					slidesToShow: 4,
					slidesToScroll: 4,
					mobileFirst: true,
					responsive: [
						{
							breakpoint: 768,
							settings: {
								slidesToShow: 6,
								slidesToScroll: 6
							}
						}
					]
				});
			});

			$('section.page-section.testimonial-slider-section .section-slider').each(function(i, el) {
				var slider = $(el);
				slider.slick({}).on('setPosition', function(event, slick) {
					var track = $('.slick-track', slick.$slider);
					var slides = $('.slick-slide', slick.$slider);
					slides.css({ height: 'auto' });
					slides.css({ height: track.height() });
				});
			});

		};


		wptheme.initMaps = function() {
			if(typeof google === 'undefined') return;

			mapSettings = {
				markerShapes: {
					default: {
						coords: [ 15,0 , 26,4 , 30,15 , 26,19 , 15,37 , 4,19 , 0,15 , 4,4 ],
						type: 'poly'
					}
				},
				markerImages: {
					blue: {
						url: themeData.themeUrl + '/images/miscellaneous/map-markers.png',
						size: new google.maps.Size(30, 37),
						scaledSize: new google.maps.Size(150, 37),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(15, 37)
					}
				}
			};
			mapSettings.markerImages.green = $.extend(true, {}, mapSettings.markerImages.blue, { origin: new google.maps.Point((30 * 1), 0) });
			mapSettings.markerImages.lightBlue = $.extend(true, {}, mapSettings.markerImages.blue, { origin: new google.maps.Point((30 * 2), 0) });
			mapSettings.markerImages.yellow = $.extend(true, {}, mapSettings.markerImages.blue, { origin: new google.maps.Point((30 * 3), 0) });
			mapSettings.markerImages.red = $.extend(true, {}, mapSettings.markerImages.blue, { origin: new google.maps.Point((30 * 4), 0) });

			$(document).on('mapInitialized', 'section.page-section.locations-map-section .google-map', function(e) {
				var mapData = $(this).data('map-data');
				if(mapData.settings.points.length && !mapData.markers.length) {

					var infoWindow = null;

					infoWindow = new InfoBox({
						pixelOffset: new google.maps.Size(-120, -50),
						alignBottom: true,
						closeBoxURL: ''
					});

					google.maps.event.addListener(mapData.map, 'click', function() {
						infoWindow.close();
					});

					mapData.bounds = new google.maps.LatLngBounds();
					for(var i in mapData.settings.points) {
						var point = mapData.settings.points[i];

						var position = new google.maps.LatLng(point.lat, point.lng);
						var markerSettings = {
							markerIndex: parseInt(i),
							position: position,
							map: mapData.map,
							icon: mapSettings.markerImages.red,
							shape: mapSettings.markerShapes.default,
							title: point.title,
							data: point
						};
						var marker = new google.maps.Marker(markerSettings);

						if(markerSettings.data.infoBoxContent) {
							google.maps.event.addListener(marker, 'click', function() {
								infoWindow.setOptions({ boxClass: 'infoBox' });
								infoWindow.setContent(this.data.infoBoxContent);
								infoWindow.open(this.map, this);
								setTimeout(function() { infoWindow.setOptions({ boxClass: 'infoBox active' }); }, 100);
							});
						}

						mapData.markers.push(marker);
						mapData.bounds.extend(position);
					}

					if(mapData.settings.points.length) {

						mapData.map.fitBounds(mapData.bounds);

						google.maps.event.addListenerOnce(mapData.map, 'idle', function() { 
							var mapData = $(this.getDiv()).data('map-data');
							if(mapData.map.getZoom() > mapData.settings.options.zoom) {
								mapData.map.setZoom(mapData.settings.options.zoom);
							}
						});

					}

				}
			});

		};


		wptheme.smoothScrollToElement = function(element, speed, offset) {
			speed = typeof speed !== 'undefined' ? speed : 1000;
			offset = typeof offset !== 'undefined' ? offset : 0;
			if(element.length > 0) {
				var margin = parseInt(element.css('margin-top'));
				wptheme.smoothScrollToPos(element.offset().top - (margin > 0 ? margin : 0), speed, offset);
			}
		};
		wptheme.smoothScrollToPos = function(y, speed, offset) {
			speed = typeof speed !== 'undefined' ? speed : 1000;
			offset = typeof offset !== 'undefined' ? offset : 0;
			var fixedHeaderOffset = 0;
			$('html, body').stop(true).animate({ scrollTop: y - fixedHeaderOffset + offset }, speed, 'easeInOutExpo');
		};


		return wptheme;
		
	})({});

})(jQuery);