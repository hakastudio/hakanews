/**
 * Haka — JavaScript frontend.
 *
 * Vanilla JS, tanpa dependency. Semua fitur bersifat progressive enhancement:
 * halaman tetap berfungsi penuh bila JavaScript gagal dimuat.
 */
( function () {
	'use strict';

	var i18n = window.hakaI18n || {};
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ------------------------------------------------------------ Utilitas */
	function on( el, type, handler, options ) {
		if ( el ) {
			el.addEventListener( type, handler, options );
		}
	}

	function focusables( container ) {
		return Array.prototype.slice.call(
			container.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])'
			)
		).filter( function ( el ) {
			return el.offsetParent !== null;
		} );
	}

	/* --------------------------------------------------------- Menu mobile */
	var menuToggle = document.getElementById( 'hk-menu-toggle' );
	var menuClose = document.getElementById( 'hk-menu-close' );
	var mobileNav = document.getElementById( 'hk-mobile-nav' );
	var overlay = document.getElementById( 'hk-overlay' );

	function openMenu() {
		if ( ! mobileNav ) {
			return;
		}

		mobileNav.hidden = false;
		if ( overlay ) {
			overlay.hidden = false;
		}
		document.body.classList.add( 'hk-nav-open' );
		menuToggle.setAttribute( 'aria-expanded', 'true' );

		var first = focusables( mobileNav )[ 0 ];
		if ( first ) {
			first.focus();
		}
	}

	function closeMenu( returnFocus ) {
		if ( ! mobileNav || mobileNav.hidden ) {
			return;
		}

		mobileNav.hidden = true;
		if ( overlay ) {
			overlay.hidden = true;
		}
		document.body.classList.remove( 'hk-nav-open' );
		menuToggle.setAttribute( 'aria-expanded', 'false' );

		if ( returnFocus !== false ) {
			menuToggle.focus();
		}
	}

	on( menuToggle, 'click', function () {
		if ( mobileNav && mobileNav.hidden ) {
			openMenu();
		} else {
			closeMenu();
		}
	} );

	on( menuClose, 'click', function () {
		closeMenu();
	} );

	on( overlay, 'click', function () {
		closeMenu();
	} );

	// Focus trap sederhana + ESC.
	on( document, 'keydown', function ( event ) {
		if ( ! mobileNav || mobileNav.hidden ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			closeMenu();
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var items = focusables( mobileNav );

		if ( ! items.length ) {
			return;
		}

		var first = items[ 0 ];
		var last = items[ items.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	// Tutup menu saat viewport melebar ke desktop.
	var desktop = window.matchMedia( '(min-width: 1024px)' );
	var onBreakpoint = function ( event ) {
		if ( event.matches ) {
			closeMenu( false );
		}
	};

	if ( desktop.addEventListener ) {
		desktop.addEventListener( 'change', onBreakpoint );
	} else if ( desktop.addListener ) {
		desktop.addListener( onBreakpoint );
	}

	/* ------------------------------------------------------ Panel pencarian */
	var searchToggle = document.getElementById( 'hk-search-toggle' );
	var searchPanel = document.getElementById( 'hk-search-panel' );

	on( searchToggle, 'click', function () {
		if ( ! searchPanel ) {
			return;
		}

		var isHidden = searchPanel.hidden;
		searchPanel.hidden = ! isHidden;
		searchToggle.setAttribute( 'aria-expanded', isHidden ? 'true' : 'false' );

		if ( isHidden ) {
			var input = searchPanel.querySelector( 'input[type="search"]' );
			if ( input ) {
				input.focus();
			}
		}
	} );

	on( document, 'keydown', function ( event ) {
		if ( 'Escape' === event.key && searchPanel && ! searchPanel.hidden ) {
			searchPanel.hidden = true;
			searchToggle.setAttribute( 'aria-expanded', 'false' );
			searchToggle.focus();
		}
	} );

	/* ------------------------------------------------------- Breaking news */
	var track = document.getElementById( 'hk-breaking-track' );
	var pauseBtn = document.getElementById( 'hk-breaking-pause' );

	if ( track && ! reduceMotion ) {
		// Gandakan isi agar loop terasa mulus (translateX -50%).
		var clone = track.cloneNode( true );
		var clonedItems = clone.children;

		while ( clonedItems.length ) {
			var item = clonedItems[ 0 ];
			item.setAttribute( 'aria-hidden', 'true' );
			Array.prototype.forEach.call( item.querySelectorAll( 'a' ), function ( link ) {
				link.setAttribute( 'tabindex', '-1' );
			} );
			track.appendChild( item );
		}

		// Durasi = panjang isi / kecepatan (px per detik dari Customizer),
		// supaya kecepatannya konsisten berapa pun jumlah judulnya. Dihitung
		// ulang setelah web font termuat, karena lebar teks ikut berubah.
		var setTickerDuration = function () {
			var pps = parseFloat( getComputedStyle( document.documentElement ).getPropertyValue( '--hk-ticker-pps' ) ) || 50;
			var width = track.scrollWidth / 2;
			track.style.animationDuration = Math.max( 8, Math.round( width / pps ) ) + 's';
		};

		setTickerDuration();
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( setTickerDuration );
		}
	}

	on( pauseBtn, 'click', function () {
		var section = pauseBtn.closest( '.hk-breaking' );

		if ( ! section ) {
			return;
		}

		var paused = section.classList.toggle( 'is-paused' );
		pauseBtn.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
	} );

	/* ---------------------------------------------- Hitungan view (beacon) */
	// Dikirim dari peramban supaya tetap terhitung walau HTML disajikan dari
	// cache. localStorage mencegah kiriman ulang dari tab yang sama dalam 1 jam.
	( function () {
		var data = window.hakaData || {};
		var postId = parseInt( data.postId, 10 );

		if ( ! postId || ! data.viewEndpoint || ! document.body.classList.contains( 'single-post' ) ) {
			return;
		}

		var key = 'haka-view-' + postId;
		var last = 0;

		try {
			last = parseInt( localStorage.getItem( key ), 10 ) || 0;
		} catch ( e ) {}

		if ( Date.now() - last < 3600000 ) {
			return;
		}

		var url = data.viewEndpoint + postId;
		var sent = false;

		if ( navigator.sendBeacon ) {
			sent = navigator.sendBeacon( url, new Blob( [ '{}' ], { type: 'application/json' } ) );
		}

		if ( ! sent ) {
			fetch( url, { method: 'POST', credentials: 'same-origin', keepalive: true } ).catch( function () {} );
		}

		try {
			localStorage.setItem( key, String( Date.now() ) );
		} catch ( e ) {}
	} )();

	/* ------------------------------------- Fragmen dinamis di balik cache */
	// Jadwal shalat dan ticker saham ikut tersimpan di cache halaman. Kalau ada
	// plugin cache, keduanya diminta ulang dari server saat halaman dibuka.
	( function () {
		var data = window.hakaData || {};

		if ( ! data.liveFragments || ! data.fragmentEndpoint || ! window.fetch ) {
			return;
		}

		var today = new Date();
		var todayKey = today.getFullYear() + '-' + String( today.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( today.getDate() ).padStart( 2, '0' );

		Array.prototype.forEach.call( document.querySelectorAll( '[data-hk-live]' ), function ( el ) {
			var name = el.getAttribute( 'data-hk-live' );
			var date = el.getAttribute( 'data-hk-date' );

			// Jadwal harian hanya diminta ulang bila tanggal di HTML sudah lewat.
			if ( date && date === todayKey ) {
				return;
			}

			fetch( data.fragmentEndpoint + encodeURIComponent( name ), { credentials: 'same-origin' } )
				.then( function ( r ) { return r.ok ? r.json() : Promise.reject( r.status ); } )
				.then( function ( json ) {
					if ( ! json || typeof json.html !== 'string' ) {
						return;
					}

					var wrap = document.createElement( 'div' );
					wrap.innerHTML = json.html.trim();
					var fresh = wrap.firstElementChild;

					if ( fresh ) {
						el.replaceWith( fresh );
					} else {
						el.remove();
					}
				} )
				.catch( function () {} );
		} );
	} )();

	/* ---------------------------------------------------------- Hero carousel */
	Array.prototype.forEach.call( document.querySelectorAll( '[data-hk-carousel]' ), function ( root ) {
		var track = root.querySelector( '.hk-hero__track' );
		var slides = Array.prototype.slice.call( root.querySelectorAll( '.hk-hero__slide' ) );

		if ( ! track || slides.length < 2 ) {
			return;
		}

		var dots = Array.prototype.slice.call( root.querySelectorAll( '[data-hk-dot]' ) );
		var prevBtn = root.querySelector( '[data-hk-prev]' );
		var nextBtn = root.querySelector( '[data-hk-next]' );
		var playBtn = root.querySelector( '[data-hk-playpause]' );
		var playLabel = root.querySelector( '[data-hk-playpause-label]' );
		var progress = root.querySelector( '[data-hk-progress]' );

		var interval = parseInt( root.getAttribute( 'data-interval' ), 10 ) || 6000;
		var wantsAutoplay = root.getAttribute( 'data-autoplay' ) === 'true' && ! reduceMotion;

		var index = 0;
		var timer = null;
		var playing = false;

		root.classList.add( 'is-enhanced' );
		root.style.setProperty( '--hk-hero-interval', interval + 'ms' );

		function scrollToIndex( i, instant ) {
			var slide = slides[ i ];

			if ( ! slide ) {
				return;
			}

			track.scrollTo( {
				left: slide.offsetLeft - track.offsetLeft,
				behavior: ( instant || reduceMotion ) ? 'auto' : 'smooth'
			} );
		}

		function currentIndex() {
			var best = 0;
			var min = Infinity;

			slides.forEach( function ( slide, i ) {
				var delta = Math.abs( ( slide.offsetLeft - track.offsetLeft ) - track.scrollLeft );

				if ( delta < min ) {
					min = delta;
					best = i;
				}
			} );

			return best;
		}

		function restartProgress() {
			if ( ! progress ) {
				return;
			}

			progress.classList.remove( 'is-running' );

			if ( playing ) {
				// Paksa reflow agar animasi benar-benar diulang dari nol.
				void progress.offsetWidth;
				progress.classList.add( 'is-running' );
			}
		}

		function sync() {
			index = currentIndex();

			dots.forEach( function ( dot, i ) {
				var active = i === index;
				dot.classList.toggle( 'is-active', active );
				dot.setAttribute( 'aria-selected', active ? 'true' : 'false' );
			} );

			// Tanpa autoplay, panah di ujung dinonaktifkan agar arah jelas.
			if ( ! playing && prevBtn && nextBtn ) {
				prevBtn.disabled = index === 0;
				nextBtn.disabled = index === slides.length - 1;
			} else if ( prevBtn && nextBtn ) {
				prevBtn.disabled = false;
				nextBtn.disabled = false;
			}
		}

		// Setelah pengunjung menggeser sendiri, hitungan autoplay dimulai ulang
		// supaya slide tidak melompat dua langkah tepat setelah diklik.
		function resetTimer() {
			if ( ! playing ) {
				return;
			}

			window.clearInterval( timer );
			timer = window.setInterval( function () {
				scrollToIndex( ( index + 1 ) % slides.length );
				restartProgress();
			}, interval );
		}

		function go( delta ) {
			var next = index + delta;

			if ( playing ) {
				next = ( next + slides.length ) % slides.length;
			} else {
				next = Math.max( 0, Math.min( slides.length - 1, next ) );
			}

			scrollToIndex( next );
			resetTimer();
			restartProgress();
		}

		function start() {
			if ( ! wantsAutoplay || timer ) {
				return;
			}

			playing = true;
			timer = window.setInterval( function () {
				scrollToIndex( ( index + 1 ) % slides.length );
				restartProgress();
			}, interval );

			if ( playBtn ) {
				playBtn.setAttribute( 'aria-pressed', 'false' );
				if ( playLabel ) {
					playLabel.textContent = i18n.pauseSlider || 'Hentikan geser otomatis';
				}
			}

			if ( track ) {
				track.setAttribute( 'aria-live', 'off' );
			}

			sync();
			restartProgress();
		}

		function stop( byUser ) {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}

			playing = false;

			if ( progress ) {
				progress.classList.remove( 'is-running' );
			}

			if ( byUser && playBtn ) {
				playBtn.setAttribute( 'aria-pressed', 'true' );
				if ( playLabel ) {
					playLabel.textContent = i18n.playSlider || 'Jalankan geser otomatis';
				}
			}

			if ( track ) {
				track.setAttribute( 'aria-live', 'polite' );
			}

			sync();
		}

		// Sinkronisasi saat digeser (jari, trackpad, keyboard).
		var scrollTick = false;
		on( track, 'scroll', function () {
			if ( ! scrollTick ) {
				window.requestAnimationFrame( function () {
					sync();
					scrollTick = false;
				} );
				scrollTick = true;
			}
		}, { passive: true } );

		on( prevBtn, 'click', function () { go( -1 ); } );
		on( nextBtn, 'click', function () { go( 1 ); } );

		dots.forEach( function ( dot ) {
			on( dot, 'click', function () {
				scrollToIndex( parseInt( dot.getAttribute( 'data-hk-dot' ), 10 ) || 0 );
				resetTimer();
				restartProgress();
			} );
		} );

		on( playBtn, 'click', function () {
			if ( playing ) {
				stop( true );
			} else {
				wantsAutoplay = true;
				start();
			}
		} );

		// Jeda saat pengunjung sedang membaca atau menavigasi.
		on( root, 'mouseenter', function () { if ( playing ) { stop( false ); } } );
		on( root, 'mouseleave', function () { if ( wantsAutoplay && playBtn && playBtn.getAttribute( 'aria-pressed' ) !== 'true' ) { start(); } } );
		on( root, 'focusin', function () { if ( playing ) { stop( false ); } } );

		on( document, 'visibilitychange', function () {
			if ( document.hidden ) {
				if ( playing ) { stop( false ); }
			} else if ( wantsAutoplay && playBtn && playBtn.getAttribute( 'aria-pressed' ) !== 'true' ) {
				start();
			}
		} );

		// Panah kiri/kanan saat fokus berada di dalam carousel.
		on( root, 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				go( -1 );
			} else if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				go( 1 );
			}
		} );

		sync();

		if ( wantsAutoplay ) {
			start();
		}
	} );

	/* ------------------------------------------------------ Salin tautan */
	Array.prototype.forEach.call( document.querySelectorAll( '[data-hk-copy]' ), function ( button ) {
		on( button, 'click', function () {
			var url = button.getAttribute( 'data-hk-copy' );

			var done = function () {
				button.classList.add( 'is-copied' );
				button.setAttribute( 'aria-label', i18n.copied || 'Tautan disalin' );

				window.setTimeout( function () {
					button.classList.remove( 'is-copied' );
					button.removeAttribute( 'aria-label' );
				}, 2000 );
			};

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( url ).then( done ).catch( function () {} );
				return;
			}

			var temp = document.createElement( 'textarea' );
			temp.value = url;
			temp.setAttribute( 'readonly', '' );
			temp.style.position = 'absolute';
			temp.style.left = '-9999px';
			document.body.appendChild( temp );
			temp.select();

			try {
				document.execCommand( 'copy' );
				done();
			} catch ( error ) {
				// Diabaikan: browser lama tanpa clipboard API.
			}

			document.body.removeChild( temp );
		} );
	} );

	/* ------------------------------------------------------- Mode gelap */
	var themeToggle = document.getElementById( 'hk-theme-toggle' );

	if ( themeToggle ) {
		var themeLabel = themeToggle.querySelector( '[data-hk-theme-label]' );

		var paintTheme = function ( theme ) {
			document.documentElement.setAttribute( 'data-theme', theme );
			themeToggle.setAttribute( 'aria-pressed', theme === 'dark' ? 'true' : 'false' );

			if ( themeLabel ) {
				themeLabel.textContent = theme === 'dark'
					? ( i18n.darkOn || 'Ganti ke mode terang' )
					: ( i18n.darkOff || 'Ganti ke mode gelap' );
			}
		};

		paintTheme( document.documentElement.getAttribute( 'data-theme' ) || 'light' );

		on( themeToggle, 'click', function () {
			var next = document.documentElement.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
			paintTheme( next );

			try {
				localStorage.setItem( 'haka-theme', next );
			} catch ( error ) {
				// Penyimpanan diblokir (mode privat): pilihan berlaku untuk sesi ini saja.
			}
		} );
	}

	/* -------------------------------------------------- Ukuran huruf artikel */
	var fontButtons = document.querySelectorAll( '[data-hk-font]' );

	if ( fontButtons.length ) {
		var STEPS = [ 0.9, 1, 1.12, 1.26 ];
		var step = 1;

		try {
			var saved = parseInt( localStorage.getItem( 'haka-fontstep' ), 10 );
			if ( ! isNaN( saved ) && saved >= 0 && saved < STEPS.length ) {
				step = saved;
			}
		} catch ( error ) {}

		var applyFont = function () {
			document.documentElement.style.setProperty( '--hk-prose-scale', STEPS[ step ] );

			Array.prototype.forEach.call( fontButtons, function ( btn ) {
				btn.classList.toggle( 'is-active', btn.getAttribute( 'data-hk-font' ) === 'reset' && step === 1 );
			} );

			try {
				localStorage.setItem( 'haka-fontstep', String( step ) );
			} catch ( error ) {}
		};

		Array.prototype.forEach.call( fontButtons, function ( btn ) {
			on( btn, 'click', function () {
				var action = btn.getAttribute( 'data-hk-font' );

				if ( 'up' === action ) {
					step = Math.min( STEPS.length - 1, step + 1 );
				} else if ( 'down' === action ) {
					step = Math.max( 0, step - 1 );
				} else {
					step = 1;
				}

				applyFont();
			} );
		} );

		applyFont();
	}

	/* ------------------------------------------- Tombol berbagi melayang */
	var stickyShare = document.getElementById( 'hk-stickyshare' );
	var articleBody = document.querySelector( '.hk-article__content' );

	if ( stickyShare && articleBody && 'IntersectionObserver' in window ) {
		var shareObserver = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				stickyShare.hidden = ! entry.isIntersecting;
			} );
		}, { rootMargin: '-120px 0px -160px 0px' } );

		shareObserver.observe( articleBody );
	}

	/* --------------------------------------------- Atribusi saat menyalin */
	if ( document.body.classList.contains( 'single' ) ) {
		on( document, 'copy', function ( event ) {
			var selection = window.getSelection();

			if ( ! selection || ! selection.toString().trim() ) {
				return;
			}

			// Kutipan pendek dibiarkan apa adanya agar tidak mengganggu.
			if ( selection.toString().trim().length < 60 ) {
				return;
			}

			var body = document.querySelector( '.hk-article__content' );

			if ( ! body || ! selection.anchorNode || ! body.contains( selection.anchorNode ) ) {
				return;
			}

			var note = '\n\n' + ( i18n.source || 'Sumber' ) + ': ' + document.title + ' — ' + window.location.href;

			if ( event.clipboardData ) {
				event.clipboardData.setData( 'text/plain', selection.toString() + note );
				event.preventDefault();
			}
		} );
	}

	/* ------------------------------------------------- Section bertab */
	Array.prototype.forEach.call( document.querySelectorAll( '[data-hk-tabs]' ), function ( root ) {
		var buttons = Array.prototype.slice.call( root.querySelectorAll( '.hk-tabs__btn' ) );
		var panels = Array.prototype.slice.call( root.querySelectorAll( '.hk-tabs__panel' ) );

		if ( buttons.length < 2 || panels.length < 2 ) {
			return;
		}

		root.classList.add( 'is-enhanced' );

		var show = function ( index ) {
			buttons.forEach( function ( btn, i ) {
				var active = i === index;
				btn.classList.toggle( 'is-active', active );
				btn.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				btn.setAttribute( 'tabindex', active ? '0' : '-1' );
			} );

			panels.forEach( function ( panel, i ) {
				if ( i === index ) {
					panel.removeAttribute( 'data-hk-tab-hidden' );
				} else {
					panel.setAttribute( 'data-hk-tab-hidden', '' );
				}
			} );
		};

		buttons.forEach( function ( btn, i ) {
			on( btn, 'click', function () {
				show( i );
			} );

			on( btn, 'keydown', function ( event ) {
				var next = null;

				if ( 'ArrowRight' === event.key ) {
					next = ( i + 1 ) % buttons.length;
				} else if ( 'ArrowLeft' === event.key ) {
					next = ( i - 1 + buttons.length ) % buttons.length;
				}

				if ( null !== next ) {
					event.preventDefault();
					show( next );
					buttons[ next ].focus();
				}
			} );
		} );

		show( 0 );
	} );

	/* ---------------------------------------------------- Tombol suka */
	Array.prototype.forEach.call( document.querySelectorAll( '[data-hk-likes]' ), function ( box ) {
		var button = box.querySelector( '[data-hk-like]' );
		var output = box.querySelector( '[data-hk-like-count]' );
		var postId = box.getAttribute( 'data-post' );
		var storeKey = 'haka-liked-' + postId;
		var busy = false;

		if ( ! button || ! postId ) {
			return;
		}

		var liked = false;

		try {
			liked = localStorage.getItem( storeKey ) === '1';
		} catch ( error ) {}

		button.setAttribute( 'aria-pressed', liked ? 'true' : 'false' );

		on( button, 'click', function () {
			if ( busy ) {
				return;
			}

			busy = true;
			var next = ! liked;

			// Endpoint diambil dari WordPress supaya benar walau situs
			// dipasang di subdirektori atau memakai permalink berbeda.
			var endpoint = ( window.hakaData && hakaData.likeEndpoint ) || '';

			if ( ! endpoint ) {
				busy = false;
				return;
			}

			// Tanpa X-WP-Nonce: endpoint publik, dan nonce yang tersimpan di
			// halaman ter-cache sudah kedaluwarsa sehingga REST menolaknya.
			var headers = { 'Content-Type': 'application/json' };

			fetch( endpoint + encodeURIComponent( postId ) + '?action=' + ( next ? 'like' : 'unlike' ), {
				method: 'POST',
				credentials: 'same-origin',
				headers: headers
			} )
				.then( function ( response ) {
					return response.ok ? response.json() : Promise.reject( response.status );
				} )
				.then( function ( data ) {
					if ( data && typeof data.count !== 'undefined' ) {
						liked = next;
						button.setAttribute( 'aria-pressed', liked ? 'true' : 'false' );

						if ( output ) {
							output.textContent = data.count;
						}

						try {
							if ( liked ) {
								localStorage.setItem( storeKey, '1' );
							} else {
								localStorage.removeItem( storeKey );
							}
						} catch ( error ) {}
					}
				} )
				.catch( function () {
					// Gagal terhubung: biarkan tampilan apa adanya, jangan menipu pembaca.
				} )
				.finally( function () {
					busy = false;
				} );
		} );
	} );

	/* -------------------------------------------------- Infinite scroll */
	var infinite = document.querySelector( '[data-hk-infinite]' );
	var infiniteList = document.querySelector( '[data-hk-infinite-list]' );

	if ( infinite && infiniteList ) {
		var nextLink = document.querySelector( '.hk-pagination a.next' );
		var loadBtn = infinite.querySelector( '[data-hk-infinite-btn]' );
		var status = infinite.querySelector( '.hk-infinite__status' );
		var loading = false;

		document.body.classList.add( 'hk-infinite-on' );

		var finish = function () {
			infinite.classList.add( 'is-done' );

			if ( status ) {
				status.textContent = i18n.noMore || 'Semua berita sudah dimuat.';
			}
		};

		if ( ! nextLink ) {
			finish();
		}

		var loadMore = function () {
			if ( loading || ! nextLink ) {
				return;
			}

			loading = true;

			if ( loadBtn ) {
				loadBtn.disabled = true;
			}

			if ( status ) {
				status.textContent = i18n.loading || 'Memuat…';
			}

			fetch( nextLink.href, { credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.ok ? response.text() : Promise.reject( response.status );
				} )
				.then( function ( html ) {
					var doc = new DOMParser().parseFromString( html, 'text/html' );
					var incoming = doc.querySelector( '[data-hk-infinite-list]' );

					if ( ! incoming || ! incoming.children.length ) {
						finish();
						return;
					}

					// Pindahkan anak satu per satu agar skrip pihak ketiga tidak dieksekusi ulang.
					while ( incoming.firstElementChild ) {
						infiniteList.appendChild( incoming.firstElementChild );
					}

					nextLink = doc.querySelector( '.hk-pagination a.next' );

					if ( status ) {
						status.textContent = '';
					}

					if ( ! nextLink ) {
						finish();
					}
				} )
				.catch( function () {
					if ( status ) {
						status.textContent = i18n.loadFailed || 'Gagal memuat. Coba lagi.';
					}
				} )
				.finally( function () {
					loading = false;

					if ( loadBtn ) {
						loadBtn.disabled = false;
					}
				} );
		};

		on( loadBtn, 'click', loadMore );

		if ( 'auto' === infinite.getAttribute( 'data-mode' ) && 'IntersectionObserver' in window ) {
			var autoObserver = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						loadMore();
					}
				} );
			}, { rootMargin: '400px 0px' } );

			autoObserver.observe( infinite );
		}
	}

	/* ------------------------------------------------ Progres baca */
	var progress = document.getElementById( 'hk-progress' );
	var progressFill = progress ? progress.firstElementChild : null;
	var progressTarget = document.querySelector( '.hk-article__content' );

	if ( progressFill && progressTarget ) {
		var progressTick = false;

		var updateProgress = function () {
			var box = progressTarget.getBoundingClientRect();
			var viewport = window.innerHeight;

			// Diukur dari tepi bawah layar terhadap badan artikel. Cara ini
			// tetap bertahap pada artikel yang lebih pendek dari layar;
			// rumus (tinggi - viewport) menghasilkan lompatan 0 ke 100
			// karena pembaginya negatif.
			var height = box.height;
			var passed = viewport - box.top;
			var ratio = height > 0 ? passed / height : 0;

			ratio = Math.max( 0, Math.min( 1, ratio ) );
			progressFill.style.width = ( ratio * 100 ).toFixed( 1 ) + '%';
			progressTick = false;
		};

		on( window, 'scroll', function () {
			if ( ! progressTick ) {
				window.requestAnimationFrame( updateProgress );
				progressTick = true;
			}
		}, { passive: true } );

		on( window, 'resize', updateProgress, { passive: true } );
		updateProgress();
	}

	/* -------------------------------------------------- Tombol ke atas */
	var toTop = document.getElementById( 'hk-totop' );

	if ( toTop ) {
		var ticking = false;

		var updateToTop = function () {
			toTop.hidden = window.scrollY < 600;
			ticking = false;
		};

		on( window, 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( updateToTop );
				ticking = true;
			}
		}, { passive: true } );

		on( toTop, 'click', function () {
			window.scrollTo( {
				top: 0,
				behavior: reduceMotion ? 'auto' : 'smooth'
			} );
		} );

		updateToTop();
	}

	/* ------------------------------------------- Submenu di menu ponsel */
	var mobileMenu = document.getElementById( 'mobile-menu' );

	if ( mobileMenu ) {
		var parents = mobileMenu.querySelectorAll( '.menu-item-has-children' );

		if ( parents.length ) {
			mobileMenu.classList.add( 'is-enhanced' );
		}

		Array.prototype.forEach.call( parents, function ( item, index ) {
			var submenu = item.querySelector( '.sub-menu' );

			if ( ! submenu ) {
				return;
			}

			var subId = submenu.id || ( 'hk-submenu-' + index );
			submenu.id = subId;

			// Tombol terpisah: tautan induk tetap bisa diketuk untuk membuka
			// halaman kategorinya, tombol ini hanya membuka daftar turunan.
			var toggle = document.createElement( 'button' );
			toggle.type = 'button';
			toggle.className = 'hk-submenu-toggle';
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-controls', subId );
			toggle.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6"/></svg>';

			var label = document.createElement( 'span' );
			label.className = 'screen-reader-text';
			var parentLink = item.querySelector( ':scope > a' );
			label.textContent = ( i18n.openSubmenu || 'Buka submenu' ) + ( parentLink ? ' ' + parentLink.textContent.trim() : '' );
			toggle.appendChild( label );

			var link = item.querySelector( 'a' );

			if ( link && link.parentNode === item ) {
				item.insertBefore( toggle, link.nextSibling );
			} else {
				item.appendChild( toggle );
			}

			on( toggle, 'click', function () {
				var open = item.classList.toggle( 'is-open' );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		} );
	}
}() );
