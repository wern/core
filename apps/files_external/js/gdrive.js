$(document).ready(function() {

	OCA.External.Settings.mountConfig.whenSelectBackend(function($tr, backend, onCompletion) {
		if (backend === 'googledrive') {
			var config = $tr.find('.configuration');
			config.append($(document.createElement('input'))
				.addClass('button')
				.attr('type', 'button')
				.attr('value', t('files_external', 'Google Drive Configuration'))
				.attr('name', 'gdrive_config')
			);
		}
	});

	$('#externalStorage').on('click', '[name="gdrive_config"]', function(event) {
		event.preventDefault();
		var url = 'https://console.developers.google.com/';
		// no mapping between client ID and Google 'project', so we always load the same URL
		window.open(url, '_blank');
	});

});
