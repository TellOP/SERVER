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
 * Profile page controller.
 * @package TellOP
 */
class ProfilePageController implements IController {
    /**
     * Displays an error message and rolls back any changes.
     * @param \TellOP\Application $appObject Application object.
     * @param mixed[] $userrec The user record.
     */
    private function showError($appObject, $userrec) {
        $apppdo = $appObject->getApplicationPDO();
        $apppdo->rollBack();
        $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
        $unlockstmt->execute();
        $unlockstmt->closeCursor();
        \Flight::render('ProfilePage', array(
            'csrftoken' => $appObject->getCSRFToken(),
            'title' => $userrec['title'],
            'displayname' => $userrec['displayname'],
            'emailaddress' => $_SESSION['username'],
            'languagelevel' => $userrec['languagelevel'],
            'internalerror' => true
        ));
    }

    /**
     * Allows the user to view or edit his/her profile.
     * @param \TellOP\Application $appObject Application object.
     */
    public function displayPage($appObject) {
        if ($_SESSION['username'] === NULL) {
            \Flight::redirect('/');
        }
        $apppdo = $appObject->getApplicationPDO();
        $userdatastmt = $apppdo->prepare('SELECT title, displayname, '
            . 'languagelevel FROM users WHERE email = ?');
        $userdata = $userdatastmt->execute(array($_SESSION['username']));
        if (($userdata === FALSE) || ($userdatastmt->rowCount() != 1)
            || (($userrec = $userdatastmt->fetch(\PDO::FETCH_ASSOC)) === FALSE)) {
            \Flight::render('ProfilePage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'internalerror' => true));
            return;
        }
        $userdatastmt->closeCursor();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $passworderror = (!isset($_POST['password']) ||
                $_POST['password'] == '');
            $newpassworderror = (!isset($_POST['newpassword']) ||
                $_POST['newpassword'] == '');
            $newpasswordconfirmerror = (!isset($_POST['newpasswordconfirm']) ||
                $_POST['newpasswordconfirm'] == '');
            if (!$newpassworderror && !$newpasswordconfirmerror) {
                $newpasswordsmatcherror = ($_POST['newpassword'] !=
                    $_POST['newpasswordconfirm']);
            } else {
                $newpasswordsmatcherror = false;
            }
            if ($passworderror || $newpassworderror || $newpasswordconfirmerror
                || $newpasswordsmatcherror) {
                \Flight::render('ProfilePage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'title' => $userrec['title'],
                    'displayname' => $userrec['displayname'],
                    'emailaddress' => $_SESSION['username'],
                    'languagelevel' => $userrec['languagelevel'],
                    'passworderror' => $passworderror,
                    'newpassworderror' => $newpassworderror,
                    'newpasswordconfirmerror' => $newpasswordconfirmerror,
                    'newpasswordsmatcherror' => $newpasswordsmatcherror
                ));
                return;
            }
            if (!$apppdo->beginTransaction()) {
                \Flight::render('ProfilePage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'title' => $userrec['title'],
                    'displayname' => $userrec['displayname'],
                    'emailaddress' => $_SESSION['username'],
                    'languagelevel' => $userrec['languagelevel'],
                    'internalerror' => true
                ));
                return;
            }
            $locktables = $apppdo->prepare('LOCK TABLES users WRITE');
            if (!$locktables->execute()) {
                $this->showError($appObject, $userrec);
                return;
            }
            $locktables->closeCursor();
            $checkpw = $apppdo->prepare('SELECT password FROM users WHERE '
                . 'email = ?');
            if (!$checkpw->execute(array($_SESSION['username']))) {
                $this->showError($appObject, $userrec);
                return;
            }
            if (($oldpwrec = $checkpw->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
                $this->showError($appObject, $userrec);
                return;
            }
            $checkpw->closeCursor();
            if (!password_verify($_POST['password'], $oldpwrec['password'])) {
                $apppdo->rollBack();
                $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
                $unlockstmt->execute();
                $unlockstmt->closeCursor();
                \Flight::render('ProfilePage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'title' => $userrec['title'],
                    'displayname' => $userrec['displayname'],
                    'emailaddress' => $_SESSION['username'],
                    'languagelevel' => $userrec['languagelevel'],
                    'wrongoldpw' => true
                ));
                return;
            }
            $newpw = $apppdo->prepare('UPDATE users SET password = ? WHERE '
                . 'email = ?');
            if (!$newpw->execute(array(password_hash($_POST['newpassword'],
                PASSWORD_DEFAULT)))) {
                $this->showError($appObject, $userrec);
                return;
            }
            $newpw->closeCursor();
            $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
            $unlockstmt->execute();
            $unlockstmt->closeCursor();
            if (!$apppdo->commit()) {
                $this->showError($appObject, $userrec);
                return;
            }
        } else {
            \Flight::render('ProfilePage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'title' => $userrec['title'],
                'displayname' => $userrec['displayname'],
                'emailaddress' => $_SESSION['username'],
                'languagelevel' => $userrec['languagelevel']
            ));
        }
    }
}
