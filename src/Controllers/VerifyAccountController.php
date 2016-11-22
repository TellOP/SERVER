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

class VerifyAccountController implements IController {
    /**
     * Displays an error message and rolls back any changes.
     * @param \PDO $apppdo Application PDO object.
     * @param bool $tokenerror <c>true</c> if the error is due to a token being
     * expired or invalid.
     */
    private function showError($apppdo, $tokenerror = false) {
        $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
        $apppdo->rollBack();
        $unlockstmt->execute();
        \Flight::render('VerifyAccountPage', array(
            'internalerror' => true,
            'tokenerror' => $tokenerror));
    }

    /**
     * Verifies a newly registered user account.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        if (!isset($_GET['token']) || $_GET['token'] == '') {
            \Flight::render('VerifyAccountPage', array(
                'missingtoken' => true));
            return;
        }
        $apppdo = $appObject->getApplicationPDO();
        $lockuser = $apppdo->prepare('LOCK TABLES users WRITE');
        if (!$lockuser->execute()) {
            $this->showError($apppdo);
            return;
        }
        $lockuser->closeCursor();
        if (!$apppdo->beginTransaction()) {
            $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
            $unlockstmt->execute();
            \Flight::render('VerifyAccountPage', array(
                'internalerror' => true));
            return;
        }
        $checkusernum = $apppdo->prepare('SELECT email FROM users WHERE '
            . 'accountstatus = 0 AND secrettoken = ? AND secrettokenexpire > '
            . 'NOW()');
        if (!$checkusernum->execute(array($_GET['token']))) {
            $this->showError($apppdo);
            return;
        }
        if ($checkusernum->rowCount() != 1) {
            $this->showError($apppdo, true);
            return;
        }
        if (($useremail = $checkusernum->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
            $this->showError($apppdo);
            return;
        }
        $checkusernum->closeCursor();
        $upduser = $apppdo->prepare('UPDATE users SET accountstatus = 1 '
            . 'WHERE email = ?');
        if (!$upduser->execute(array($useremail['email']))) {
            $this->showError($apppdo);
            return;
        }
        $upduser->closeCursor();
        $unlockuser = $apppdo->prepare('UNLOCK TABLES');
        if (!$unlockuser->execute()) {
            $this->showError($apppdo);
            return;
        }
        $unlockuser->closeCursor();
        if (!$apppdo->commit()) {
            \Flight::render('VerifyAccountPage', array(
                'internalerror' => true));
            return;
        }
        \Flight::render('VerifyAccountPage');
    }
}
