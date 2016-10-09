<?php
/* Copyright © 2016 University of Murcia
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TellOP\Controllers;

/**
 * Controller to set the application language.
 * @package TellOP
 */
class SetLanguageController implements IController {
    /**
     * Sets the application language.
     * @param \TellOP\Application $appObject Application object.
     */
    public function displayPage($appObject) {
        if (isset($_SERVER['QUERY_STRING']) && preg_replace('[^A-Za-z_]', '',
            $_SERVER['QUERY_STRING']) == $_SERVER['QUERY_STRING']) {
            if (file_exists(__DIR__ . '/../../locale/' . $_SERVER['QUERY_STRING'])) {
                $_SESSION['language'] = $_SERVER['QUERY_STRING'];
                if (isset($_SESSION['username']) && $_SESSION['username'] != NULL) {
                    $pwcancstmt = $appObject->getApplicationPDO()->prepare(
                        'UPDATE users SET locale = ? WHERE email = ?');
                    $pwcancstmt->execute(array($_SERVER['QUERY_STRING'],
                        $_SESSION['username']));
                }
            }
        }
        if (isset($_SESSION['username']) && $_SESSION['username'] != NULL) {
            \Flight::redirect('https://' . $_SERVER['SERVER_NAME'] . '/dashboard');
        } else {
            \Flight::redirect('https://' . $_SERVER['SERVER_NAME'] . '/');
        }
    }
}
