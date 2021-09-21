<?php

class Constants {

    const ARTIKEL = ["", "die", "der", "der", "der", "der", "der"];
    const TYPES = ["", "Dokumentenkamera", "Beamer", "Bildschirm", "Drucker", "Laptop", "Computer"];
    const PATTERN_DATE = "/^([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2})$/";
    const URL_SEPARATOR = ";";
    const STATUS = ["Offen", "In Bearbeitung", "Geschlossen", "Wiedereröffnet"];
    const LEVEL = ["First-Level-Support", "Second-Level-Support", "Third-Level-Support"];
    const USER_LEVEL_USER = 0;
    const USER_LEVEL_USER_HP = 1;
    const USER_LEVEL_ITCROWD = 2;
    const USER_LEVEL_ITCROWD_HP = 3;
    const USER_LEVEL_ADMIN = 4;
    const API_NAMESPACE = "itdesk/v1";
    const API_CREATE = "/create";
    const API_TICKETS = "/tickets";
    const API_TICKET = "/ticket";
	const API_HEARTBEAT = "/heartbeat";
	const HEARTBEAT_SECRET = "%g5f#&B\$pw*6M^#y46E66GNjwpcKJ^&$";
    const DB_TABLE_PREFIX = "it_";
    const DB_TABLE_TICKET = self::DB_TABLE_PREFIX . "ticket";
    const DB_TABLE_TICKET_EVENTS = self::DB_TABLE_PREFIX . "ticket_events";
    const DB_TABLE_ISSUE = self::DB_TABLE_PREFIX . "issue";
    const DB_TABLE_DEVICE = self::DB_TABLE_PREFIX . "device";
    const DB_TABLE_MODEL = self::DB_TABLE_PREFIX . "model";
    const DB_TABLE_MODEL_DETAILS = self::DB_TABLE_PREFIX . "model_details";
    const DB_TABLE_ROOM = self::DB_TABLE_PREFIX . "room";
    const DB_TABLE_WEBHOOKS = self::DB_TABLE_PREFIX . "webhooks";
    const DB_TABLE_TEAMS_USER = self::DB_TABLE_PREFIX . "teams_user";

}