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

class Stands4DictionaryDefinitionsController extends WebServiceClientController {
    /**
     * Performs a query to the Stands4 Dictionary Definitions Web service.
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

        $apiUID = $appObject->getConfig()['apikeys']['stands4DictionaryDefinitionsUID'];
        $apiPWD =  $appObject->getConfig()['apikeys']['stands4DictionaryDefinitionsTokenID'];
        if (!isset($apiUID) || $apiUID == '' || !isset($apiPWD)
            || $apiPWD == '') {
            $this->dieWS(WebServiceClientController::SERVER_SIDE_API_KEY_MISSING);
        }

        $curlHandle = $this->curlOpen(
            'http://www.stands4.com/services/v2/defs.php?uid=' . $apiUID
            .'&tokenid=' . $apiPWD . '&word=' . urlencode($_GET['q']));
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);

        // Parse the response
        if ($response === FALSE) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $jsonResponse = array();
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        if (!$dom->loadXML($response)) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        libxml_clear_errors();
        $domXPath = new \DOMXPath($dom);
        $entries = $domXPath->query('/results/result');
        foreach ($entries as $entry) {
            $arrayEntry = array();
            foreach ($entry->childNodes as $childNode) {
                switch ($childNode->nodeName) {
                    case 'term':
                    case 'definition':
                    case 'partofspeech':
                    case 'example':
                        $arrayEntry[$childNode->nodeName] = $childNode->textContent;
                        break;
                    default:
                        /* Ignore the field */
                        break;
                }
            }
            $jsonResponse[] = $arrayEntry;
        }
        echo json_encode($jsonResponse);
    }
}
