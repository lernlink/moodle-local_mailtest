<?php
// This file is part of MailTest for Moodle - http://moodle.org/
//
// MailTest is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// MailTest is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MailTest.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of functions to support the MailTest plugin.
 *
 * @package    local_mailtest
 * @copyright  TNG Consulting Inc. - www.tngcosulting.ca
 * @author     Michael Milette
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Generate a user info object based on provided parameters.
 * @param $string $email plain text email address.
 * @param $string $name plain text real name (optional).
 * @return object user info.
 */
function local_mailtest_generate_email_user($email, $name='') {
    $emailuser = new stdClass();
    $emailuser->email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailuser->email = '';
    }
    $name = format_text($name, FORMAT_HTML, array('trusted' => false, 'noclean' => false));
    $emailuser->firstname = trim(filter_var($name, FILTER_SANITIZE_STRING));
    $emailuser->lastname = '';
    $emailuser->maildisplay = true;
    $emailuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML emails.
    $emailuser->id = -99;
    $emailuser->firstnamephonetic = '';
    $emailuser->lastnamephonetic = '';
    $emailuser->middlename = '';
    $emailuser->alternatename = '';
    return $emailuser;
}

/**
 * Outputs a message box.
 *
 * @param string $text The text of the message.
 * @param string $heading The text of the heading.
 * @param int $level The level of importance of the heading. Defaulting to 2.
 * @param string $classes A space-separated list of CSS classes.
 * @param string $id An optional ID. Is applied to body instead of heading if no heading.
 * @return string the HTML to output.
 */
function local_mailtest_msgbox($text, $heading = null, $level = 2, $classes = null, $link = null, $id = null) {
    global $OUTPUT;
    echo $OUTPUT->box_start(trim('box '.$classes));
    if (!is_null($heading)) {
        echo $OUTPUT->heading($heading, $level, $id);
        echo "<p>$text</p>".PHP_EOL;
    } else {
        echo "<p id=\"$id\">$text</p>".PHP_EOL;
    }
    if (!is_null($link)) {
        echo $OUTPUT->continue_button($link);
    }
    echo $OUTPUT->box_end();
}

/**
 * Get the ip address of the visitor. Will attempt to get public address first and then check private range.
 *
 * @return ip address. If an ip address cannot be identified, will return 0.0.0.0.
 */
function local_mailtest_getuserip() {
    $fieldlist = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',
            'REMOTE_ADDR', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_CLUSTER_CLIENT_IP');

    // Public range first.
    $filterlist = array(FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE, FILTER_FLAG_IPV4);
    foreach ($filterlist as $filter) {
        foreach ($fieldlist as $field) {
            if (!array_key_exists($field, $_SERVER) || empty($_SERVER[$field])) {
                continue;
            }
            $iplist = explode(',', $_SERVER[$field]);
            foreach ($iplist as $ip) {
                // Strips off port number if it exists.
                list($ip) = explode(':', $ip);
                // Sanitize.
                $ip = filter_var(trim($ip), FILTER_VALIDATE_IP, $filter);
                if ($ip !== false) {
                    return($ip);
                }
            }
        }
    }
    return("0.0.0.0");
}