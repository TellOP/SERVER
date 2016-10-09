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

use \TellOP\OAuth2Storage;

/**
 * OAuth 2.0 token introspection controller.
 * @package TellOP
 */
class OAuth2IntrospectionController extends WebServiceClientController {
    /**
     * Checks the status of an OAuth 2.0 token.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->dieWSMethodNotSupported();
        }

        $this->checkOAuth($appObject, 'basic');

        $postBody = json_decode(file_get_contents('php://input'), TRUE);
        if ($postBody === NULL) {
            $this->dieWSValidation('The POST body is not valid JSON.');
        }
        if (!isset($postBody['token'])) {
            $this->dieWSValidation('The token parameter must be set.');
        }

        $storage = new OAuth2Storage($appObject);
        $isRefreshToken = false;
        $token = $storage->getAccessToken($postBody['token']);
        if ($token === NULL) {
            $isRefreshToken = true;
            $token = $storage->getRefreshToken($postBody['token']);
        }

        header('Content-Type: application/json');

        if ($token === NULL) {
            echo json_encode(array('active' => false));
            die;
        }

        $response = array(
            'active' => true,
            'exp' => $token['expires'],
            'token_type' => ($isRefreshToken ? 'refresh_token' : 'access_token')
        );
        if (isset($token['scope'])) {
            $response['scope'] = $token['scope'];
        }
        if (isset($token['client_id'])) {
            $response['client_id'] = $token['client_id'];
        }
        if (isset($token['user_id'])) {
            $response['username'] = $token['user_id'];
        }

        echo json_encode($response);
    }
}
