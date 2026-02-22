<?php
/**
 * Simple User Edit Library
 * Clean standalone replacement for Moodle's editlib.php
 *
 * Author: Ivan (Custom Conversion)
 * License: Free to use and modify
 */

// Path to your simple data store
define('USER_DATA_FILE', __DIR__ . '/user_data.json');

/**
 * Get all users (from JSON storage)
 */
function get_all_users() {
    if (!file_exists(USER_DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(USER_DATA_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/**
 * Get a single user by username
 */
function get_user($username) {
    $users = get_all_users();
    foreach ($users as $u) {
        if (isset($u['username']) && $u['username'] === $username) {
            return $u;
        }
    }
    return null;
}

/**
 * Save (add or update) a user
 */
function save_user($data) {
    $users = get_all_users();
    $found = false;

    foreach ($users as &$u) {
        if ($u['username'] === $data['username']) {
            $u = array_merge($u, $data);
            $found = true;
            break;
        }
    }

    if (!$found) {
        $users[] = $data;
    }

    file_put_contents(USER_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

/**
 * Delete a user
 */
function delete_user($username) {
    $users = get_all_users();
    $users = array_filter($users, fn($u) => $u['username'] !== $username);
    file_put_contents(USER_DATA_FILE, json_encode(array_values($users), JSON_PRETTY_PRINT));
}

/**
 * Check if a username already exists
 */
function username_exists($username) {
    return get_user($username) !== null;
}

/**
 * Validate a password (example: for login systems)
 */
function validate_password($plain, $hash) {
    return password_verify($plain, $hash);
}
