$(document).ready(function () {
	
	$('#migrateAddressbooks').click(function () {

		OC.msg.startSaving('#migrateAddressbooksSection .msg');
		$.post(
			OC.generateUrl('/apps/migrate_dav/addressbooks'),
			{}
		).done(function (data) {
				OC.msg.finishedSuccess('#migrateAddressbooksSection .msg', data.message);
			})
			.fail(function (jqXHR) {
				OC.msg.finishedError('#migrateAddressbooksSection .msg', JSON.parse(jqXHR.responseText).message);
			});

	});

});
