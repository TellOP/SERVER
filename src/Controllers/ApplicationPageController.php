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
 * Application page controller.
 * @package TellOP
 */
class ApplicationPageController implements IController {
    /**
     * Releases all table locks.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    private function unlockTables($appObject) {
        $apppdo = $appObject->getApplicationPDO();
        $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
        $unlockstmt->execute();
        $unlockstmt->closeCursor();
    }

    /**
     * Revokes access to the client having the specified ID.
     * @param \TellOP\Application $appObject Application object,
     * @param string $clientID Client ID of the application to which access
     * should be revoked.
     * @return bool <c>true</c> if the revocation was completed, <c>false</c>
     * otherwise.
     */
    private function revokeAccess($appObject, $clientID) {
        $apppdo = $appObject->getApplicationPDO();

        // FIXME: add JWT tables in the future
        $locks = $apppdo->prepare('LOCK TABLES oauth_access_tokens WRITE, '
            . 'oauth_authorization_codes WRITE, '
            . 'oauth_refresh_tokens WRITE');
        if (!$locks->execute()) {
            return false;
        }
        $locks->closeCursor();

        if (!$apppdo->beginTransaction()) {
            $this->unlockTables($appObject);
            return false;
        }
        $removeaccess = $apppdo->prepare('DELETE FROM oauth_access_tokens '
            . 'WHERE client_id = :client_id');
        if (!$removeaccess->execute(array(':client_id' => $clientID))) {
            $apppdo->rollBack();
            $this->unlockTables($appObject);
            return false;
        }
        $removeaccess->closeCursor();
        $removeauth = $apppdo->prepare('DELETE FROM oauth_authorization_codes '
            . 'WHERE client_id = :client_id');
        if (!$removeauth->execute(array(':client_id' => $clientID))) {
            $apppdo->rollBack();
            $this->unlockTables($appObject);
            return false;
        }
        $removeauth->closeCursor();
        $removerefresh = $apppdo->prepare('DELETE FROM oauth_refresh_tokens '
            . 'WHERE client_id = :client_id');
        if (!$removerefresh->execute(array(':client_id' => $clientID))) {
            $apppdo->rollBack();
            $this->unlockTables($appObject);
            return false;
        }
        $removerefresh->closeCursor();
        // FIXME: add JWT tables in the future

        if (!$apppdo->commit()) {
            $this->unlockTables($appObject);
            return false;
        }

        $this->unlockTables($appObject);
        return true;
    }

    /**
     * Show the applications which can access this account via OAuth2 and/or
     * add/remove them.
     * @param \TellOP\Application $appObject Application object.
     */
    public function displayPage($appObject) {
        if ($_SESSION['username'] === NULL) {
            \Flight::redirect('/');
        }

        $apppdo = $appObject->getApplicationPDO();

        $internalerror = false;
        $unabletoretrievelist = false;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['appid'])) {
                if (!$this->revokeAccess($appObject, $_POST['appid'])) {
                    $internalerror = true;
                }
            } else {
                $internalerror = true;
            }
        }

        // FIXME Add JTI in the future
        $applist = $apppdo->prepare(
            'SELECT DISTINCT alltokens.client_id, alltokens.app_name,'
            . ' alltokens.scope, users.displayname'
            . ' FROM users INNER JOIN ('
                . 'SELECT oauth_clients.client_id, oauth_clients.app_name,'
                . ' oauth_clients.user_id, oauth_clients.scope'
                . ' FROM oauth_clients'
				. ' INNER JOIN oauth_access_tokens'
                . ' ON oauth_clients.client_id = oauth_access_tokens.client_id'
				. ' WHERE oauth_access_tokens.user_id = :current_user'
				. ' UNION'
                . ' SELECT oauth_clients.client_id, oauth_clients.app_name,'
                . ' oauth_clients.user_id, oauth_clients.scope'
				. ' FROM oauth_clients'
				. ' INNER JOIN oauth_authorization_codes'
                . ' ON oauth_clients.client_id = oauth_authorization_codes.client_id'
				. ' WHERE oauth_authorization_codes.user_id = :current_user'
				. ' UNION'
                . ' SELECT oauth_clients.client_id, oauth_clients.app_name,'
                . ' oauth_clients.user_id, oauth_clients.scope'
				. ' FROM oauth_clients'
				. ' INNER JOIN oauth_refresh_tokens'
                . ' ON oauth_clients.client_id = oauth_refresh_tokens.client_id'
				. ' WHERE oauth_refresh_tokens.user_id = :current_user'
            . ') alltokens'
            . ' ON users.email = alltokens.user_id');
        $userapps = array();
        if ($applist->execute(array(':current_user' => $_SESSION['username']))) {
            if ($applist->rowCount() > 0) {
                if (($userapps = $applist->fetchAll(\PDO::FETCH_ASSOC)) === FALSE) {
                    $unabletoretrievelist = true;
                }
            }
        } else {
            $unabletoretrievelist = true;
        }
        $applist->closeCursor();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            \Flight::render('ApplicationPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'userapps' => $userapps,
                'unabletoretrievelist' => $unabletoretrievelist,
                'internalerror' => $internalerror
            ));
        } else {
            \Flight::render('ApplicationPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'userapps' => $userapps,
                'unabletoretrievelist' => $unabletoretrievelist
            ));
        }
    }
}
