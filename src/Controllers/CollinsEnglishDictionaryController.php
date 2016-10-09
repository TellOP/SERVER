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

class CollinsEnglishDictionaryController extends WebServiceClientController {
    /**
     * Performs a query to the Collins English Dictionary Web service.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'onlineresources');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->dieWSMethodNotSupported();
        }
        // Perform validation
        if (!isset($_GET['q'])) {
            $this->dieWSValidation('The q parameter is missing.');
        }
        $apiKey = $appObject->getConfig()['apikeys']['collinsDictionary'];
        if (!isset($apiKey) || $apiKey == '') {
            $this->dieWS(WebServiceClientController::SERVER_SIDE_API_KEY_MISSING);
        }
        $curlHandle = $this->curlOpen(
            'https://api.collinsdictionary.com/api/v1/dictionaries/english/search/?q='
            . urlencode($_GET['q']) . '&pagesize=100&pageindex=1&format=xml');
        $this->curlSetOption($curlHandle, CURLOPT_HTTPHEADER, array(
            'Host: api.collinsdictionary.com',
            'Accept: application/json',
            'accessKey: ' . $appObject->getConfig()['apikeys']['collinsDictionary']
        ));
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);
        // Parse the response
        if ($response === FALSE) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        echo $response;
    }
}
