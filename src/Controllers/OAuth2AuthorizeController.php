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
use OAuth2\Response;
use \TellOP\OAuth2Server;

/**
 * OAuth 2.0 authorize controller.
 * @package TellOP
 */
class OAuth2AuthorizeController implements IController {
    /**
     * Displays the page associated to the controller and to the application
     * status.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        if ($_GET['response_type'] == 'code' || $_GET['response_type'] ==
            'token') {
            $response_type = $_GET['response_type'];
        } else {
            $response_type = 'token';
        }
        // Check if the user is logged in (redirect to the login page if
        // necessary)
        if ($_SESSION['username'] == NULL) {
            $_SESSION['oauthlogin'] = true;
            $redirecturl = 'https://' . $_SERVER['SERVER_NAME'] . '/login?'
                . 'response_type=' . $response_type
                . '&client_id=' . urlencode($_GET['client_id']);
            if (isset($_GET['redirect_uri'])) {
                $redirecturl .= '&redirect_uri=' . urlencode($_GET['redirect_uri']);
            }
            if (isset($_GET['scope'])) {
                $redirecturl .= '&scope=' . urlencode($_GET['scope']);
            }
            if (isset($_GET['state'])) {
                $redirecturl .= '&state=' . urlencode($_GET['state']);
            }
            header("HTTP/1.1 303 See Other");
            header("Location: $redirecturl");
            return;
        }
        // Validate the authorize request
        $server = new OAuth2Server($appObject);
        $request = Request::createFromGlobals();
        $response = new Response();
        if (!$server->getServer()->validateAuthorizeRequest($request, $response)) {
            $response->send();
            die;
        }
        // Display an authorization form if needed
        $clientId = '';
        if (empty($_POST)) {
            $appname = _('Unknown application');
            $apppdo = $appObject->getApplicationPDO();
            if ($clientstmt = $apppdo->prepare('SELECT app_name, client_id FROM '
                . 'oauth_clients WHERE client_id = ?')) {
                if ($clientstmt->execute(array($_GET['client_id']))) {
                    if ($clientstmt->rowCount() == 1) {
                        if (($clientapp = $clientstmt->fetch(\PDO::FETCH_ASSOC))
                            !== FALSE) {
                            $appname = $clientapp['app_name'];
                            $clientId = $clientapp['client_id'];
                        }
                    }
                }
            }
            if ($clientId != '1011a510829210912e6b9c63f4108e5b28fdc110e7dde792dfb1ee45524cc5c1f4a78') {
                \Flight::render('OAuthAuthorize', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'appname' => $appname,
                    'scope' => explode(' ',
                        (isset($_GET['scope']) ? $_GET['scope'] : 'basic'))
                ));
                return;
            }
        }
        $is_authorized = ($_POST['authorized'] === 'yes' || $clientId == '1011a510829210912e6b9c63f4108e5b28fdc110e7dde792dfb1ee45524cc5c1f4a78');
        $server->getServer()->handleAuthorizeRequest($request, $response,
            $is_authorized, $_SESSION['username']);
        // If the user logged in right now, close his session
        if (isset($_SESSION['oauthlogin'])) {
            unset($_SESSION['oauthlogin']);
            $_SESSION['username'] = NULL;
            session_regenerate_id(true);
        }
        $response->send();
    }
}
