<?php
/** @var array $_ */
/** @var OC_L10N $l */
script('migrate_dav', 'settings-admin');
?>

<form id="migrate-addressbooks" class="section">
	<h2><?php p($l->t('Migrate Addressbooks and Calendars'));?></h2>
	<p id="migrateAddressbooksSection">
		<?php p($l->t("You can migrate all addressbooks of all users by clicking the button below.")) ?>
		<br>
		<span class="msg"></span>
		<br>
		<input type="button"
		   name="migrateAddressbooks"
		   id="migrateAddressbooks"
		   value="<?php  p($l->t("Migrate Addressbooks")) ?>"/>
	</p>
	<input type="button"
		   name="migrateCalendars"
		   id="migrateCalendars"
		   value="<?php  p($l->t("Migrate Calendars")) ?>"/>
</form>
