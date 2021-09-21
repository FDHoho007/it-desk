<?php

class Profile {

    static function email_status($user, $type)
    {
        $values = get_user_meta($user->ID, "itdesk-email-$type");
        return sizeof($values) == 0 ? false : $values[0];
    }

    static function teams_status($user, $type)
    {
        $values = get_user_meta($user->ID, "itdesk-teams-$type");
        return sizeof($values) == 0 ? false : $values[0];
    }

    static function register($user_id)
    {
        update_user_meta($user_id, "itdesk-email-create", true);
        update_user_meta($user_id, "itdesk-email-message", true);
        update_user_meta($user_id, "itdesk-email-status", true);
        update_user_meta($user_id, "itdesk-email-operator", true);
        update_user_meta($user_id, "itdesk-teams-create", true);
        update_user_meta($user_id, "itdesk-teams-message", true);
        update_user_meta($user_id, "itdesk-teams-status", true);
        update_user_meta($user_id, "itdesk-teams-operator", true);
    }

    static function extra_user_profile_fields($user)
    { ?>
        <h2><?php _e("Ticket Benachrichtigungen", "blank"); ?></h2>

        <table class="form-table">
            <tr>
                <th><?php _e("Neues Ticket"); ?></th>
                <td>
                    <label for="itdesk-email-create">
                        <input type="checkbox" name="itdesk-email-create" id="itdesk-email-create"
                               value="false"<?php if (self::email_status($user, "create"))
                            echo(" checked"); ?>/>
                        <?php _e("E-Mail bei neuen Tickets erhalten"); ?>
                    </label><br>
                    <label for="itdesk-teams-create">
                        <input type="checkbox" name="itdesk-teams-create" id="itdesk-teams-create"
                               value="false"<?php if (self::teams_status($user, "create"))
                            echo(" checked"); ?>/>
                        <?php _e("Teams Benachrichtigung bei neuen Tickets erhalten"); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><?php _e("Neue Nachricht"); ?></th>
                <td>
                    <label for="itdesk-email-message">
                        <input type="checkbox" name="itdesk-email-message" id="itdesk-email-message"
                               value="false"<?php if (self::email_status($user, "message"))
                            echo(" checked"); ?>/>
                        <?php _e("E-Mail bei neuer Nachricht in meinen Tickets erhalten."); ?>
                    </label><br>
                    <label for="itdesk-teams-message">
                        <input type="checkbox" name="itdesk-teams-message" id="itdesk-teams-message"
                               value="false"<?php if (self::teams_status($user, "message"))
                            echo(" checked"); ?>/>
                        <?php _e("Teams Benachrichtigung bei neuer Nachricht in meinen Tickets erhalten."); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><?php _e("Zustandänderung"); ?></th>
                <td>
                    <label for="itdesk-email-status">
                        <input type="checkbox" name="itdesk-email-status" id="itdesk-email-status"
                               value="false"<?php if (self::email_status($user, "status"))
                            echo(" checked"); ?>/>
                        <?php _e("E-Mail bei Änderung des Zustand eines meiner Tickets erhalten."); ?>
                    </label><br>
                    <label for="itdesk-teams-status">
                        <input type="checkbox" name="itdesk-teams-status" id="itdesk-teams-status"
                               value="false"<?php if (self::teams_status($user, "status"))
                            echo(" checked"); ?>/>
                        <?php _e("Teams Benachrichtigung bei Änderung des Zustand eines meiner Tickets erhalten."); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><?php _e("Bearbeiter geändert"); ?></th>
                <td>
                    <label for="itdesk-email-operator">
                        <input type="checkbox" name="itdesk-email-operator" id="itdesk-email-operator"
                               value="false"<?php if (self::email_status($user, "operator"))
                            echo(" checked"); ?>/>
                        <?php _e("E-Mail bei Wechsel des Bearbeiters einer meiner Tickets erhalten."); ?>
                    </label><br>
                    <label for="itdesk-teams-operator">
                        <input type="checkbox" name="itdesk-teams-operator" id="itdesk-teams-operator"
                               value="false"<?php if (self::teams_status($user, "operator"))
                            echo(" checked"); ?>/>
                        <?php _e("Teams Benachrichtigung bei Wechsel des Bearbeiters einer meiner Tickets erhalten."); ?>
                    </label>
                </td>
            </tr>
        </table>
    <?php }

    static function save_extra_user_profile_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id))
            return false;
        update_user_meta($user_id, "itdesk-email-create", isset($_POST["itdesk-email-create"]));
        update_user_meta($user_id, "itdesk-email-message", isset($_POST["itdesk-email-message"]));
        update_user_meta($user_id, "itdesk-email-status", isset($_POST["itdesk-email-status"]));
        update_user_meta($user_id, "itdesk-email-operator", isset($_POST["itdesk-email-operator"]));
        update_user_meta($user_id, "itdesk-teams-create", isset($_POST["itdesk-teams-create"]));
        update_user_meta($user_id, "itdesk-teams-message", isset($_POST["itdesk-teams-message"]));
        update_user_meta($user_id, "itdesk-teams-status", isset($_POST["itdesk-teams-status"]));
        update_user_meta($user_id, "itdesk-teams-operator", isset($_POST["itdesk-teams-operator"]));
        return true;
    }

}