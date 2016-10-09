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

class PasswordResetController implements IController {
    /**
     * Displays an error message and rolls back any changes.
     * @param \TellOP\Application $appObject Application object.
     */
    private function showError($appObject) {
        $apppdo = $appObject->getApplicationPDO();
        $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
        $apppdo->rollBack();
        $unlockstmt->execute();
        $unlockstmt->closeCursor();
        \Flight::render('PasswordResetPage', array(
            'csrftoken' => $appObject->getCSRFToken(),
            'internalerror' => true));
    }

    /**
     * Completes the password reset procedure.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        if (!isset($_GET['token']) || $_GET['token'] == '') {
            \Flight::render('PasswordResetPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'missingtoken' => true));
            return;
        }
        $apppdo = $appObject->getApplicationPDO();
        $lockuser = $apppdo->prepare('LOCK TABLES users WRITE');
        if (!$lockuser->execute()) {
            $this->showError($appObject);
            return;
        }
        $lockuser->closeCursor();
        if (!$apppdo->beginTransaction()) {
            $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
            $unlockstmt->execute();
            \Flight::render('PasswordResetPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'internalerror' => true));
            return;
        }
        $checkusernum = $apppdo->prepare('SELECT email FROM users WHERE '
            . 'accountstatus = 4 AND secrettoken = ? AND secrettokenexpire > '
            . 'NOW()');
        if (!$checkusernum->execute(array($_GET['token']))) {
            $this->showError($appObject);
            return;
        }
        if ($checkusernum->rowCount() != 1) {
            $this->showError($appObject);
            return;
        }
        if (($useremail = $checkusernum->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
            $this->showError($appObject);
            return;
        }
        $checkusernum->closeCursor();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newpwerr = (!isset($_POST['newpassword']) ||
                $_POST['newpassword'] == '');
            $newpwcnferr = (!isset($_POST['newpasswordconfirm']) ||
                $_POST['newpasswordconfirm'] == '');
            $pwmismatch = (!$newpwerr && !$newpwcnferr &&
                $_POST['newpassword'] != $_POST['newpasswordconfirm']);
            if ($newpwerr || $newpwcnferr || $pwmismatch) {
                $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
                $apppdo->rollBack();
                $unlockstmt->execute();
                \Flight::render('PasswordResetPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'newpwerr' => $newpwerr,
                    'newpwcnferr' => $newpwcnferr,
                    'pwmismatch' => $pwmismatch
                ));
                return;
            }
            $upduser = $apppdo->prepare('UPDATE users SET password = ? '
                . 'WHERE email = ?');
            if (!$upduser->execute(array(password_hash($_POST['newpassword'],
                PASSWORD_DEFAULT), $_SESSION['username']))) {
                $this->showError($appObject);
                return;
            }
            $upduser->closeCursor();
        }
        $unlockuser = $apppdo->prepare('UNLOCK TABLES');
        if (!$unlockuser->execute()) {
            $this->showError($appObject);
            return;
        }
        $unlockuser->closeCursor();
        if (!$apppdo->commit()) {
            \Flight::render('PasswordResetPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'internalerror' => true
            ));
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            \Flight::render('PasswordResetPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'completed' => true
            ));
        } else {
            \Flight::render('PasswordResetPage', array(
                'csrftoken' => $appObject->getCSRFToken()
            ));
        }
    }
}
