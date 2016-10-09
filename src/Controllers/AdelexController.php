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

class AdelexController extends WebServiceClientController {
    /**
     * Performs a query to the Adelex Web service.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'onlineresources');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->dieWSMethodNotSupported();
        }
        if (!isset($_POST['text'])) {
            $this->dieWSValidation('The text parameter is missing.');
        }
        if (!isset($_POST['order'])) {
            $this->dieWSValidation('The order parameter is missing.');
        }
        if ($_POST['order'] != 'frequency' && $_POST['order'] !=
            'alphabetical') {
            $this->dieWSValidation('The order parameter must be either '
                . '"frequency" or "alphabetical".');
        }
        foreach ($_POST as $postKey => $postValue) {
            if ($postKey != 'text' && $postKey != 'order') {
                $this->dieWSValidation('Unexpected parameter encountered: '
                    . $postKey);
            }
        }
        $srvPostBody = array(
            'texto' => $_POST['text'],
            'orden' => ($_POST['order'] == 'alphabetical' ? 'Alfabetico' :
                'Frecuencia'));
        // Submit to the remote Web server
        // TODO: check if a session identifier is needed (I think not)
        $curlHandle = $this->curlOpen(
            'http://www.ugr.es/~inped/ada/analisysfrecuency.php?lng=english');
        $this->curlSetOption($curlHandle, CURLOPT_POST, TRUE);
        $this->curlSetOption($curlHandle, CURLOPT_POSTFIELDS, $srvPostBody);
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);
        // Parse the response
        if ($response === FALSE) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $jsonResponse = array();
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->strictErrorChecking = FALSE;
        if (!$dom->loadHTML($response)) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        libxml_clear_errors();
        $dom->normalizeDocument();
        $domXPath = new \DOMXPath($dom);
        $entries = $domXPath->query('/html/body/table/tr');
        $entryCount = 1;
        /** @var int $orderNumber */
        $orderNumber = 0;
        foreach ($entries as $entry) {
            if ($entryCount == 3) { // Occurrencies
                $matches = array();
                if (preg_match('/^TOKENS: ([0-9]+)/', $entry->nodeValue,
                        $matches) == 1) {
                    $jsonResponse['tokenCount'] = $matches[1];
                }
            } else if ($entryCount == 4) { // Types
                $matches = array();
                if (preg_match('/^TYPES: ([0-9]+)/', $entry->nodeValue,
                        $matches) == 1) {
                    $jsonResponse['types'] = $matches[1];
                }
            } else if ($entryCount == 5) { // Type/token ratio
                $matches = array();
                if (preg_match('/^TYPE\/TOKEN RATIO: ([0-9\.]+)/',
                        $entry->nodeValue, $matches) == 1) {
                    $jsonResponse['typeTokenRatio'] = $matches[1];
                }
            } else if ($entryCount == 6) { // Lexical diversity
                $matches = array();
                if (preg_match('/^LEXICAL DIVERSITY: ([0-9\.]+)/',
                        $entry->nodeValue, $matches) == 1) {
                    $jsonResponse['lexicalDiversity'] = $matches[1];
                }
            } else if ($entryCount >= 10) { // Order/type/frequency table
                $rowInBlockOfNine = 0;
                foreach ($entry->childNodes as $token) {
                    switch ($rowInBlockOfNine) {
                        case 0:
                        case 5:
                            // Order number - must start from 0, otherwise the
                            // array will be JSON-encoded as an object
                            $orderNumber =
                                ((int) $entry->childNodes[$rowInBlockOfNine]->nodeValue) - 1;
                            break;
                        case 1:
                        case 6:
                            // Type
                            $jsonResponse['tokens'][$orderNumber]['type'] =
                                $entry->childNodes[$rowInBlockOfNine]->nodeValue;
                            break;
                        case 2:
                        case 7:
                            // Frequency
                            $jsonResponse['tokens'][$orderNumber]['frequency'] =
                                $entry->childNodes[$rowInBlockOfNine]->nodeValue;
                            break;
                        case 3:
                        case 8:
                            // Percent
                            $jsonResponse['tokens'][$orderNumber]['percent'] =
                                $entry->childNodes[$rowInBlockOfNine]->nodeValue;
                            break;
                        case 4:
                        default:
                            // Empty row - ignore
                            break;
                    }
                    ++$rowInBlockOfNine;
                }
            }
            ++$entryCount;
        }
        echo json_encode($jsonResponse);
    }
}
