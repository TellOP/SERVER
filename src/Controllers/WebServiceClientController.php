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
use TellOP\Application;
use TellOP\OAuth2Server;

/**
 * Base abstract class for Internet Web service clients.
 * @package TellOP\Controllers
 */
abstract class WebServiceClientController implements IController {
    /**
     * The Web service can not give a result because it was unable to initialize
     * the cURL library.
     */
    const UNABLE_TO_INITIALIZE_CURL =
        [1, 'Unable to initialize the cURL library'];
    /**
     * The Web service was unable to set a cURL option.
     */
    const UNABLE_TO_SET_CURL_OPTION =
        [2, 'Unable to set a cURL option'];
    /**
     * The Web service was unable to execute a cURL request.
     */
    const UNABLE_TO_EXECUTE_CURL_REQUEST =
        [3, 'Unable to execute the cURL request.'];
    /**
     * The cURL request was completed successfully, but the remote server
     * returned an error.
     */
    const ERROR_IN_CURL_RESPONSE =
        [4, 'The remote resource returned an error.'];
    /**
     * The provided data does not pass validation.
     */
    const VALIDATION_ERROR =
        [5, 'The parameters you supplied did not pass validation.'];
    /**
     * The Web service is unable to parse the remote response (either due to an
     * internal error or to the response being malformed).
     */
    const UNABLE_TO_PARSE_REMOTE_RESPONSE =
        [6, 'The Web service is unable to parse the remote response'];
    /**
     * The Web service is unable to extract local data from the database.
     */
    const UNABLE_TO_FETCH_DATA_FROM_DATABASE =
        [7, 'Unable to fetch data from the database'];
    /**
     * The Web service is unable to save data locally into the database.
     */
    const UNABLE_TO_SAVE_DATA_INTO_DATABASE =
        [8, 'Unable to save data into the database'];
    /**
     * A server-side API key required to access an endpoint is missing.
     */
    const SERVER_SIDE_API_KEY_MISSING =
        [9, 'A required server-side API key is missing'];

    /**
     * OAuth 2.0 server as provided by Tell-OP.
     * @var OAuth2Server $server
     */
    private $server;

    /**
     * Gets the OAuth 2.0 server as provided by the upstream library.
     * @return \OAuth2\Server The OAuth 2.0 server associated to this controller.
     */
    protected function getOAuthServer() {
        return $this->server->getServer();
    }

    /**
     * Emits a JSON fatal error message and quits.
     * @param mixed[] $code Error code.
     */
    protected function dieWS($code) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(array('code' => $code[0],
            'error_message' => $code[1]));
        die;
    }

    /**
     * Emits a Method not supported error and quits.
     */
    protected function dieWSMethodNotSupported() {
        header('HTTP/1.1 402 Method Not Supported');
        die;
    }

    /**
     * Emits a JSON fatal error validation message and quits.
     * @param string|null $description An optional detailed description of the
     * validation error.
     */
    protected function dieWSValidation($description) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(array('code' => $this::VALIDATION_ERROR[0],
            'error_message' => (isset($description) ? $description :
                $this::VALIDATION_ERROR[1])));
        die;
    }
    /**
     * Initialize the OAuth 2.0 server and check that the user is authorized to
     * call this API.
     * @var Application $appObject Application object.
     * @var string $scope Requested scope.
     */
    protected function checkOAuth($appObject, $scope) {
        $this->server = new OAuth2Server($appObject);
        $response = new Response();
        if (!$this->getOAuthServer()->verifyResourceRequest(
            Request::createFromGlobals(),
            $response,
            $scope)) {
            $response->send();
            die;
        }
    }

    /**
     * Gets the username associated with the access token currently in use.
     * @return string|null The username associated with the access token, or
     * <c>null</c> if the token was not already checked.
     */
    protected function getOAuthUsername() {
        if ($this->server === NULL) {
            return NULL;
        }
        $token = $this->getOAuthServer()->getAccessTokenData(
            Request::createFromGlobals());
        return $token['user_id'];
    }

    /**
     * Opens a cURL handle (or dies if the function was unable to do so).
     * @param string $url URL to fetch.
     * @return resource cURL handle.
     */
    protected function curlOpen($url) {
        if (!$request = curl_init()) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_INITIALIZE_CURL);
        }
        $this->curlSetOption($request, CURLOPT_URL, $url);
        $this->curlSetOption($request, CURLOPT_RETURNTRANSFER, TRUE);
        $this->curlSetOption($request, CURLOPT_TIMEOUT, 30);
        return $request;
    }

    /**
     * Sets a cURL option (or dies if the function was unable to do so).
     * @param resource $request cURL handle.
     * @param int $option cURL option.
     * @param mixed $value cURL option value.
     * @return void
     */
    protected function curlSetOption($request, $option, $value) {
        if (!curl_setopt($request, $option, $value)) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_SET_CURL_OPTION);
        }
    }

    /**
     * Executes the cURL request.
     * @param resource $request cURL handle.
     * @param \TellOP\Application $appObject The application object.
     * @return mixed The response as returned from the Web server.
     */
    protected function curlExec($request, $appObject) {
        if ($appObject->getConfig()['security']['curlBundle'] != '') {
            $this->curlSetOption($request, CURLOPT_CAINFO,
                $appObject->getConfig()['security']['curlBundle']);
        }
        $result = curl_exec($request);
        if ($result === FALSE) {
            $this->dieWS(
                WebServiceClientController::UNABLE_TO_EXECUTE_CURL_REQUEST);
        }
        return $result;
    }

    /**
     * Closes a cURL request handle.
     * @param resource $request cURL handle.
     * @return void
     */
    protected function curlClose($request) {
        curl_close($request);
    }
}
