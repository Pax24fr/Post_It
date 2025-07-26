postit = {
	config: {bouton_fermer:true, deux_notes:true, compteur:true, max_car:9999, btn1:'URGENT', btn2:'Plus tard'},
	loadConfig: function(callback) {
		$.ajax({url: 'plugins/post_it/core/ajax/post_it.ajax.php',
			data: { action: 'getConfig' }, dataType: 'json', timeout: 5000,
			success: ({ state, result }) => {
				if (state === 'ok') this.config = result;
				callback();
			}
		});
	},

	injectCSS: function() {
		if ($('#post_it-style').length) return; // déjà injecté
		const css = `
			<style id="post_it-style">
              #post_itBtn {font-size: 20px;z-index: 10000;}
              #post_it-container {
                position:fixed; top:34px; right:24px; z-index:10000;
				width:220px;                
                padding:6px;
                background: linear-gradient(145deg, #fffad0, #fff9a0);
                border: 1px solid #e0d890;
                box-shadow:0 5px 10px rgba(0,0,0,0.1);
                font-family: 'Roboto', sans-serif;
                border-radius: 6px;
                transform: rotate(-2deg);
                transition: transform 0.3s ease;
                overflow: hidden;
              }

              #post_it-container::before {
                content: "";
                position: absolute;
                top: -6px;
                left: 50%;
                transform: translateX(-50%) rotate(-3deg);
                width: 60px;
                height: 12px;
                background: rgba(200, 200, 200, 0.4);
                border-radius: 4px;
                box-shadow: 0 0 2px rgba(0,0,0,0.2);
              }
              
              #post_it-container::after {
                content: '';
                position: absolute;
                bottom: 0;
                right: 0;
                width: 32px;
                height: 32px;
                background: linear-gradient(-45deg, #fffad0 0%, #fff38a 50%, #ddd096 100%);
                clip-path: polygon(100% 0, 0% 100%, 100% 100%);
                box-shadow: -2px -2px 3px rgba(0, 0, 0, 0.1);
              }

              #post_it-container:hover {
                transform: rotate(-1deg);
              }

              #post_it-counter {
                position: absolute; top: 0px; right: 4px; font-size: 9px;
              }
              #post_it-area1, #post_it-area2 {
              	width:100%; height:138px;
				margin-top:8px;margin-bottom:5px;
				background: transparent !important;
              }
              .post_it-area {
                display: none;
              }
              .post_it-area.active {
                display: block;
              }
			</style>`;
		$('head').append(css);
	},
	injectHTML: function() {
		const html = `
			<div class="navbar-brand pull-right post_it-item">
				<a href="#" id="post_itBtn" title="Ouvrir le pense-bête"><i class="fas fa-sticky-note icon_yellow"></i></a>
				<div id="post_it-container" style="display:none;">
					<div id="post_it-counter">0/${this.config.max_car}</div>
					<textarea id="post_it-area1" class="post_it-area active" placeholder="Tapez vos notes ici..."></textarea>
					<textarea id="post_it-area2" class="post_it-area" placeholder="Tapez vos notes ici (Note 2)..."></textarea>
					<div class="post_it-btns">
						<button id="post_it-close" class="btn btn-xs btn-success">X</button>
						<button id="post_it-tab1" class="btn btn-xs btn-success tab-link" data-tab="1">${this.config.btn1}</button>
						<button id="post_it-tab2" class="btn btn-xs tab-link" data-tab="2">${this.config.btn2}</button>
					</div>
				</div>
			</div>`;
		$('.nav.navbar-nav.navbar-right .hidden-sm.navTime').after(html);
		// rendre le post-it déplaçable
		this.makeDraggable();
      	this.restorePosition();
	},

	updateCounter: function($textarea) {
		const text = $textarea.val();
		$('#post_it-counter').text(`${text.length}/${this.config.max_car}`);
		if (text.length > this.config.max_car * 0.96 && text.length < this.config.max_car * 0.99) $('#post_it-counter').css('color', '#f4a039');
		else if (text.length > this.config.max_car * 0.99) $('#post_it-counter').css('color', '#be0000');
		else $('#post_it-counter').css('color', 'var(--txt-color)');
	},

	switchTab: function(noteId) {
		$('.tab-link').removeClass('btn-success');
		$(`#post_it-tab${noteId}`).addClass('btn-success');
		$('.post_it-area').removeClass('active');
		$(`#post_it-area${noteId}`).addClass('active').trigger('focus');
		if (this.config.compteur) this.updateCounter($(`#post_it-area${noteId}`));
	},

	adjustPosition: function() {
		const $postIt = $('.post_it-item');
		if ($('.collapse').css('display') === 'none' && $postIt.parent().is('.nav.navbar-nav.navbar-right')) {
			$postIt.detach().insertAfter('.navbar-header #mainMenuHamburgerToggle');
			$postIt.removeClass('pull-right');
		}
		if ($('.navbar-toggle').css('display') === 'none' && $postIt.parent().is('.navbar-header')) {
			$postIt.detach().insertAfter('.nav.navbar-nav.navbar-right .hidden-sm.navTime');
			$postIt.addClass('pull-right');
		}
	},

	setupUI: function() {
		console.log('setupUI->', this.config);
		if (!this.config.bouton_fermer) $('#post_it-close').remove();
		if (!this.config.compteur) $('#post_it-counter').remove();
		if (!this.config.deux_notes) {
			$('.tab-link').remove();
			$('#post_it-area2').remove();
		}
	},

	loadNotes: function() {
		// Charger depuis localStorage immédiatement
		$('#post_it-area1').val(localStorage.getItem('post_it1') || '');
		if (this.config.compteur) this.updateCounter($('#post_it-area1'));
		if (this.config.deux_notes) $('#post_it-area2').val(localStorage.getItem('post_it2') || '');
		
		$.ajax({url: 'plugins/post_it/core/ajax/post_it.ajax.php',
			data: { action: 'loadPostIt' }, dataType: 'json', timeout: 10000,
			success: ({ state, result }) => {
				if (state === 'ok') {
					localStorage.setItem('post_it1', result.note1);
					$('#post_it-area1').val(result.note1 || '');// Note 1 (toujours chargée) + compteur
					if (this.config.compteur) this.updateCounter($('#post_it-area1'));
					if (this.config.deux_notes) {
						localStorage.setItem('post_it2', result.note2);
						$('#post_it-area2').val(result.note2 || '');
					}
				}
			},
			error: () => {jeedomUtils.showAlert({ type: 'error', message: 'Erreur lors du chargement des notes. Vérifiez votre connexion.', level: 'danger' });}
		});
	},

	init: function() {
		if (window.postItLoaded) return;// ne pas charger en double
		window.postItLoaded = true;
		// Configuration dynamique
		this.loadConfig(() => {
			this.injectCSS();
			this.injectHTML();
			this.adjustPosition();
			this.setupUI();
			this.loadNotes();
			this.bindEvents();
		});
		// Gestion du redimensionnement
		$(window).on('resize', () => this.adjustPosition());
	},

    makeDraggable: function() {
        const $el = $('#post_it-container');

        let isDragging = false;
        let offsetX = 0;
        let offsetY = 0;

        // Déclenchement du drag (hors boutons et textarea)
        $el.on('mousedown.postitDrag', function(e) {
            if ($(e.target).is('textarea, button')) return;
            isDragging = true;
            offsetX = e.clientX - $el.offset().left;
            offsetY = e.clientY - $el.offset().top;
            $el.css('transition', 'none'); // désactive les transitions pendant le drag
            e.preventDefault();
        });

        $(document).on('mousemove.postitDrag', function(e) {
            if (isDragging) {
                $el.css({
                    left: (e.clientX - offsetX) + 'px',
                    top: (e.clientY - offsetY) + 'px',
                    right: 'auto'
                });
            }
        });

        $(document).on('mouseup.postitDrag', function() {
            isDragging = false;
          
          	// Sauvegarder la position
            const position = {
                top: $el.css('top'),
                left: $el.css('left')
            };
            localStorage.setItem('post_it_position', JSON.stringify(position));
        });
    },

    restorePosition: function() {
        const savedPosition = localStorage.getItem('post_it_position');
        if (savedPosition) {
            const { top, left } = JSON.parse(savedPosition);
            const $el = $('#post_it-container');
            $el.css({
                top,
                left,
                right: 'auto'
            });
        }
    },

	bindEvents: function() {
		$(document)
			.on('click', '#post_it-tab1', () => this.switchTab(1))
			.on('click', '#post_it-tab2', () => this.switchTab(2))
			.on('click.post_it', '#post_itBtn', (e) => {
				e.preventDefault();
				$('#post_it-container').toggle();
				if ($('#post_it-container').is(':visible')) $('#post_it-area1').trigger('focus');
			})
			.on('click.post_it', '#post_it-close', () => $('#post_it-container').hide())
			.on('input.post_it', '.post_it-area', (e) => {
				if (this.config.compteur) this.updateCounter($(e.target));
				const noteId = e.target.id.replace('post_it-area', '');
				const notes = $(e.target).val();
				if (notes.length > this.config.max_car) {
					$(e.target).val(notes.substring(0, this.config.max_car));
					jeedomUtils.showAlert({message: `Limite du Post It atteinte ! (${this.config.max_car} caractères maximum)`,level: 'warning',timeout: 4000});
					return;
				}
				localStorage.setItem('post_it' + noteId, notes);
				clearTimeout(window['notesSaveTimeout' + noteId]);
				window['notesSaveTimeout' + noteId] = setTimeout(() => {
					$.ajax({type: 'POST',url: 'plugins/post_it/core/ajax/post_it.ajax.php',
						data: { action: 'savePostIt', noteId, notes },dataType: 'json', timeout: 10000,
						success: ({ state, result }) => {
							if (state === 'error') {
								jeedomUtils.showAlert({message: `Erreur : ${result || 'Erreur lors de la sauvegarde des notes.'}`,level: 'danger'});
							}
						},
						error: () => {jeedomUtils.showAlert({message: 'Erreur lors de la sauvegarde des notes.',level: 'danger'});}
					});
				}, 2000);
			});

		// Mise à jour synchrone entre onglets/fenêtres
		$(window).off('storage.post_it').on('storage.post_it', (e) => {
			if (e.key.startsWith('post_it')) {
				const noteId = e.key.slice(7);
				$('#post_it-area' + noteId).val(e.originalEvent.newValue);
				if ($('#post_it-area' + noteId).hasClass('active') && this.config.compteur) this.updateCounter($('#post_it-area' + noteId));
			}
		});
		// Fermer avec la touche Escape
		$(document).on('keydown.post_it', (e) => {
        if (e.key === 'Escape' && $('#post_it-container').is(':visible')) {
            $('#post_it-container').hide();
            e.preventDefault(); // Empêche tout comportement par défaut
        }
    });
	}
};

// Initialisation
$(document).ready(() => postit.init());
