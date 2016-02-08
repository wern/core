$(document).ready(function() {

	OCA.External.Settings.mountConfig.whenSelectBackend(function($tr, backend, onCompletion) {
		if (backend === 'dropbox') {
			var config = $tr.find('.configuration');
			config.append($(document.createElement('input'))
				.addClass('button')
				.attr('type', 'button')
				.attr('value', t('files_external', 'Dropbox Configuration'))
				.attr('name', 'dropbox_config')
			);
		}
	});

	$('#externalStorage').on('click', '[name="dropbox_config"]', function(event) {
		event.preventDefault();
		var app_key = $(this).parent().find('[data-parameter="app_key"]').val();
		var url;
		if (app_key) {
			url = 'https://www.dropbox.com/developers/apps/info/' + app_key;
		} else {
			url = 'https://www.dropbox.com/developers/apps';
		}
		window.open(url, '_blank');
	});

});
