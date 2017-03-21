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

class StringNetController extends WebServiceClientController {
    /**
     * Performs a query to the StringNet Web site.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        //$this->checkOAuth($appObject, 'onlineresources');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->dieWSMethodNotSupported();
        }
        // Perform validation
        if (!isset($_GET['q'])) {
            $this->dieWSValidation('The q parameter is missing.');
        }
        $appObject->getApplicationLogger()->addInfo("StringNet: " . $_GET['q']);
        // Submit to the remote Web server
        // Check for "Collocation Before"
        $URL = 'http://nav4.stringnet.org/collo?query=' . htmlentities($_GET['q'])
            . '&radio_query_type=collocation&c_collocate_pos=None'
            . '&c_collocate_position=before&c_target_pos=None&c_order_by=freq'
            . '&c_min_freq=20';
        $curlHandle = $this->curlOpen($URL);

        $appObject->getApplicationLogger()->addInfo("StringNet cURL Before: " . $URL);
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);
        // Parse the response
        if ($response === FALSE) {
            $appObject->getApplicationLogger()->addInfo("StringNet UNABLE_TO_PARSE_REMOTE_RESPONSE (" . __LINE__ . "): ", $response);
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $jsonResponse_before = array();
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->strictErrorChecking = FALSE;
        if (!$dom->loadHTML($response)) {
            $appObject->getApplicationLogger()->addInfo("StringNet UNABLE_TO_PARSE_REMOTE_RESPONSE (" . __LINE__ . ")");
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        libxml_clear_errors();
        $dom->normalizeDocument();
        $domXPath = new \DOMXPath($dom);
        $entries = $domXPath->query('/html/body/table[@id = \'collo_table\']/tr');
        $firstEntry = true;
        $isOdd = true;
        $result = array();
        foreach ($entries as $entry) {
            if ($firstEntry) {
                $firstEntry = FALSE;
                continue;
            }
            if ($isOdd) {
                $result = array(
                    'collocation' => trim(preg_replace("/\s{2,}/", ' ',
                        $entry->firstChild->firstChild->textContent)),
                    'frequency' => (int) $entry->childNodes[2]->childNodes[1]->textContent
                );
            } else {
                $result['sample'] = trim(
                    preg_replace("/\s{2,}/", ' ',
                        preg_replace('/<a href="\/collo\/collo\/sents\?.*">more examples<\/a>/', '',
                            str_replace('</span>', '',
                                str_replace('<span class="target_color">', '',
                                    str_replace('</td>', '',
                                        str_replace('<td colspan="3" style="padding-left:40px; height: 80px; position: relative">', '',
                                            $dom->saveXML($entry->firstChild))))))));
                $jsonResponse_before[] = $result;
            }
            $isOdd = !$isOdd;
        }

        // Check for "Collocation After"
        $URL = 'http://nav4.stringnet.org/collo?query=' . htmlentities($_GET['q'])
            . '&radio_query_type=collocation&c_collocate_pos=None'
            . '&c_collocate_position=after&c_target_pos=None&c_order_by=freq'
            . '&c_min_freq=20';
        $appObject->getApplicationLogger()->addInfo("StringNet cURL After: " . $URL);
        $curlHandle = $this->curlOpen($URL);
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);
        // Parse the response
        if ($response === FALSE) {
            $appObject->getApplicationLogger()->addInfo("StringNet UNABLE_TO_PARSE_REMOTE_RESPONSE (" . __LINE__ . "): ", $response);
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $jsonResponse_after = array();
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->strictErrorChecking = FALSE;
        if (!$dom->loadHTML($response)) {
            $appObject->getApplicationLogger()->addInfo("StringNet UNABLE_TO_PARSE_REMOTE_RESPONSE (" . __LINE__ . ")");
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        libxml_clear_errors();
        $dom->normalizeDocument();
        $domXPath = new \DOMXPath($dom);
        $entries = $domXPath->query('/html/body/table[@id = \'collo_table\']/tr');
        $firstEntry = true;
        $isOdd = true;
        $result = array();
        foreach ($entries as $entry) {
            if ($firstEntry) {
                $firstEntry = FALSE;
                continue;
            }
            if ($isOdd) {
                $result = array(
                    'collocation' => trim(preg_replace("/\s{2,}/", ' ',
                        $entry->firstChild->firstChild->textContent)),
                    'frequency' => (int) $entry->childNodes[2]->childNodes[1]->textContent
                );
            } else {
                $result['sample'] = trim(
                    preg_replace("/\s{2,}/", ' ',
                        preg_replace('/<a href="\/collo\/collo\/sents\?.*">more examples<\/a>/', '',
                            str_replace('</span>', '',
                                str_replace('<span class="target_color">', '',
                                    str_replace('</td>', '',
                                        str_replace('<td colspan="3" style="padding-left:40px; height: 80px; position: relative">', '',
                                            $dom->saveXML($entry->firstChild))))))));
                $jsonResponse_after[] = $result;
            }
            $isOdd = !$isOdd;
        }

        $jsonResponse = array();
        $jsonResponse["before"] = $jsonResponse_before;
        $jsonResponse["after"] = $jsonResponse_after;

        echo json_encode($jsonResponse);
    }
}
