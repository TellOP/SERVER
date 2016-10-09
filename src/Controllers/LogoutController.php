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
 * Logout controller.
 * @package TellOP
 */
class LogoutController implements IController {
    /**
     * Logs the current user out.
     * @param \TellOP\Application $appObject Application object.
     */
    public function displayPage($appObject) {
        $logger = $appObject->getApplicationLogger();
        $apppdo = $appObject->getApplicationPDO();

        if (isset($_SESSION['username'])) {
            // Delete the "Remember me" cookie
            $remembermestatement = $apppdo->prepare('UPDATE'
                . ' users SET remembermetoken = NULL WHERE email = ?');
            $remembermestatement->execute(array($_SESSION['username']));
            $logger->addInfo('A user has logged out.',
                array('username' => $_SESSION['username']));
            $_SESSION['username'] = NULL;
            session_regenerate_id(true);
        } else {
            $logger->addWarning('A user tried to log out without being logged'
                . ' in.');
        }

        \Flight::redirect('https://' . $_SERVER['SERVER_NAME'] . '/');
    }
}
