<?php

	static function extra_user_profile_fields( $user ) { ?>
		<h2><?php _e("Ticket Benachrichtigungen", "blank"); ?></h2>

		<table class="form-table">
		<tr>
			<th><?php _e("Neues Ticket"); ?></th>
			<td>
				<label for="email-new-ticket">
					<input type="checkbox" name="email-new-ticket" id="email-new-ticket" value="false"<?php if(get_user_meta($user->ID, 'email-new-ticket', false)[0]) echo(" checked"); ?>/>
					<?php _e("E-Mail bei neuen Tickets erhalten"); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><?php _e("Neue Nachricht"); ?></th>
			<td>
				<label for="email-new-message">
					<input type="checkbox" name="email-new-message" id="email-new-message" value="false"<?php if(get_user_meta($user->ID, 'email-new-message', false)[0]) echo(" checked"); ?>/>
					<?php _e("E-Mail bei neuer Nachricht in meinen Tickets erhalten."); ?>
				</label>
			</td>
		</tr>
		</table>
	<?php }

	static function save_extra_user_profile_fields( $user_id ) {
		if (!current_user_can('edit_user', $user_id)) 
			return false;
		update_user_meta($user_id, "email-new-ticket", isset($_POST["email-new-ticket"]));
		update_user_meta($user_id, "email-new-message", isset($_POST["email-new-message"]));
	}