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

use OAuth2\Request;

class APIProfileController extends WebServiceClientController {
    /**
     * Gets/sets a user profile.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $requiredscope = 'basic';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $requiredscope = 'profile';
        } else {
            $this->dieWSMethodNotSupported();
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $this->checkOAuth($appObject, $requiredscope);
        $token = $this->getOAuthServer()->getAccessTokenData(
            Request::createFromGlobals());
        $tokenUsername = $token['user_id'];
        $apppdo = $appObject->getApplicationPDO();
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (($userstmt = $apppdo->prepare('SELECT email, locale, title, '
                    . 'displayname, languagelevel FROM users WHERE email = ?'))
                === FALSE
            ) {
                $this->dieWS([1001, 'Unable to retrieve the required information.']);
            }
            if (($userstmt->execute(array($tokenUsername))) === FALSE) {
                $this->dieWS([1001, 'Unable to retrieve the required information.']);
            }
            if ($userstmt->rowCount() != 1) {
                $this->dieWS([1002, 'The server returned zero or more than one '
                    . 'result.']);
            }
            if (($userdetails = ($userstmt->fetch(\PDO::FETCH_ASSOC))) === FALSE) {
                $this->dieWS([1003, 'Unable to fetch the required information.']);
            }
            echo json_encode($userdetails);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // TODO: not supported at this time
            $this->dieWSMethodNotSupported();
        } else {
            $this->dieWSMethodNotSupported();
        }
    }
}
