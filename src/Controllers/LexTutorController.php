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

class LexTutorController extends WebServiceClientController {
    /**
     * Performs a query to the LexTutor Web service.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'onlineresources');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->dieWSMethodNotSupported();
        }
        // Perform validation
        if (!isset($_POST['text_name'])) {
            $this->dieWSValidation('The text_name parameter is missing.');
        }
        if (strlen($_POST['text_name']) > 40) {
            $this->dieWSValidation('The text_name parameter must not be longer '
                . 'than 40 characters.');
        }
        $postBody = 'text_name=' . urlencode($_POST['text_name']);
        $postBody .= '&vintage=bnc_coca';
        $postBody .= '&wants_sax=on';
        $postBody .= '&wants_edit=on';
        $postBody .= '&count_sentences=on';
        $postBody .= '&exceptions=';
        $postBody .= '&user_recats=';
        $postBody .= '&via=formulaire';
        $postBody .= '&wants_count=on';
        if (!isset($_POST['text_input'])) {
            $this->dieWSValidation('The text_input parameter is missing.');
        }
        if (strlen($_POST['text_input']) > 400000) {
            $this->dieWSValidation('The text_input parameter can not be longer'
                . ' than 400000 characters.');
        }
        $postBody .= '&text_input=' .$_POST['text_input'];
        // Submit to the remote Web server
        $curlHandle = $this->curlOpen(
            'http://www.lextutor.ca/cgi-bin/vp/comp/output.pl');
        $this->curlSetOption($curlHandle, CURLOPT_POST, TRUE);
        $this->curlSetOption($curlHandle, CURLOPT_POSTFIELDS, $postBody);
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
        // Get the K-x frequencies
        // Each frequency block (K-n words) is made of five rows: the first is
        // the header, the other ones are the frequencies (families, types,
        // tokens and cumulative token percent).
        $entries = $domXPath->query('/html/body/font/div/blockquote/table/td/div/table/tr');
        $numEntry = 0;
        $freqLevelType = '';
        foreach ($entries as $entry) {
            foreach ($entry->childNodes as $child) {
                if ($child->nodeName == 'td') {
                    switch ($numEntry) {
                        case 0:
                            if (preg_match('/^(K-[0-9]+) Words :/',
                                    $child->nodeValue, $matches) === 1) {
                                $freqLevelType = $matches[1];
                            } else if ($child->nodeValue == 'Off-List:') {
                                $freqLevelType = 'offlist';
                            } else if ($child->nodeValue == 'Total (unrounded)') {
                                $freqLevelType = 'total';
                            } else {
                                $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
                            }
                            break;
                        case 1:
                            if ($freqLevelType == '') {
                                $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
                            }
                            if (trim($child->nodeValue) === '') {
                                $jsonResponse['frequencyLevels'][$freqLevelType]['families']['number']
                                    = 0;
                                $jsonResponse['frequencyLevels'][$freqLevelType]['families']['percent']
                                    = 0;
                            } else {
                                $familiesSplit = explode(' ', trim($child->nodeValue));
                                if (sizeof($familiesSplit) == 2
                                    && preg_replace('/[^0-9\.]/', '', $familiesSplit[0]) == $familiesSplit[0]
                                    && preg_replace('/[^0-9\(\)\.]/', '', $familiesSplit[1]) == $familiesSplit[1]) {
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['families']['number']
                                        = (int) $familiesSplit[0];
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['families']['percent']
                                        = (float) substr($familiesSplit[1], 1, strlen($familiesSplit[1]) - 2);
                                } else {
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['families']['number']
                                        = 0;
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['families']['percent']
                                        = 0;
                                }
                            }
                            break;
                        case 2:
                            if ($freqLevelType == '') {
                                $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
                            }
                            if (trim($child->nodeValue) === '') {
                                $jsonResponse['frequencyLevels'][$freqLevelType]['types']['number']
                                    = 0;
                                $jsonResponse['frequencyLevels'][$freqLevelType]['types']['percent']
                                    = 0;
                            } else {
                                $typesSplit = explode(' ', trim($child->nodeValue));
                                if (sizeof($typesSplit) == 2
                                    && preg_replace('/[^0-9\.]/', '', $typesSplit[0]) == $typesSplit[0]
                                    && preg_replace('/[^0-9\(\)\.]/', '', $typesSplit[1]) == $typesSplit[1]) {
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['types']['number']
                                        = (int)$typesSplit[0];
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['types']['percent']
                                        = (float)substr($typesSplit[1], 1, strlen($typesSplit[1]) - 2);
                                } else {
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['types']['number']
                                        = 0;
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['types']['percent']
                                        = 0;
                                }
                            }
                            break;
                        case 3:
                            if ($freqLevelType == '') {
                                $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
                            }
                            if (trim($child->nodeValue) === '') {
                                $jsonResponse['frequencyLevels'][$freqLevelType]['tokens']['number']
                                    = 0;
                                $jsonResponse['frequencyLevels'][$freqLevelType]['tokens']['percent']
                                    = 0;
                            } else {
                                $tokensSplit = explode(' ', trim($child->nodeValue));
                                if (sizeof($tokensSplit) == 2
                                    && preg_replace('/[^0-9\.]/', '', $tokensSplit[0]) == $tokensSplit[0]
                                    && preg_replace('/[^0-9\(\)\.]/', '', $tokensSplit[1]) == $tokensSplit[1]) {
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['tokens']['number']
                                        = (int)$tokensSplit[0];
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['tokens']['percent']
                                        = (float)substr($tokensSplit[1], 1, strlen($tokensSplit[1]) - 2);
                                } else {
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['tokens']['number']
                                        = 0;
                                    $jsonResponse['frequencyLevels'][$freqLevelType]['tokens']['percent']
                                        = 0;
                                }
                            }
                            break;
                        case 4:
                            if ($freqLevelType == '') {
                                $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
                            }
                            if (trim($child->nodeValue) === '') {
                                $jsonResponse['frequencyLevels'][$freqLevelType]['cumulativeToken']
                                     = "";
                            } else {
                                $jsonResponse['frequencyLevels'][$freqLevelType]['cumulativeToken']
                                    = preg_replace('/[^0-9\.]/', '', $child->nodeValue);
                            }
                            break;
                    }
                    ++$numEntry;
                    if ($numEntry == 5) {
                        $numEntry = 0;
                    }
                }
            }
        }
        // Ratios and indices
        $entries = $domXPath->query('/html/body/font/div/blockquote/table/td/table/tr/td');
        $jsonResponse['ratios']['wordsInText'] = (int) trim($entries[3]->nodeValue);
        $jsonResponse['ratios']['differentWords'] = (int) trim($entries[6]->nodeValue);
        $jsonResponse['ratios']['typeTokenRatio'] = (float) trim($entries[8]->nodeValue);
        $jsonResponse['ratios']['tokensPerType'] = (float) trim($entries[10]->nodeValue);
        $jsonResponse['ratios']['tokensOnList'] = (int) trim($entries[15]->nodeValue);
        $jsonResponse['ratios']['typesOnList'] = (int) trim($entries[17]->nodeValue);
        $jsonResponse['ratios']['familiesOnList'] = (int) trim($entries[19]->nodeValue);
        $jsonResponse['ratios']['tokensPerFamily'] = (float) trim($entries[21]->nodeValue);
        $jsonResponse['ratios']['typesPerFamily'] = (float) trim($entries[23]->nodeValue);
        $jsonResponse['ratios']['cognatesTokens'] = (int) trim($entries[28]->nodeValue);
        $jsonResponse['ratios']['cognatesWithFrench'] = (int) trim($entries[30]->nodeValue);
        $jsonResponse['ratios']['notCognatesWithFrench'] = (int) trim($entries[32]->nodeValue);
        $jsonResponse['ratios']['cognateness'] = (float) trim(str_replace(
            chr(194).chr(160), '',  preg_replace('/(?:\([0-9]+\/[0-9]+=\))/',
            '',  $entries[34]->nodeValue)));
        $jsonResponse['ratios']['sumIndividualFreqs'] =
            (float) trim(str_replace(',', '', $entries[40]->nodeValue));
        $jsonResponse['ratios']['freqsByRateableTokens'] =
            (int) trim($entries[42]->nodeValue);
        $meanFrequencySplit = explode(' ', trim($entries[44]->nodeValue));
        if (sizeof($meanFrequencySplit) == 2) {
            $jsonResponse['ratios']['meanFrequency']['value']
                = (float) str_replace(',', '', $meanFrequencySplit[0]);
            $jsonResponse['ratios']['meanFrequency']['standardDeviation']
                = (float) str_replace(',', '', substr($meanFrequencySplit[1], 4,
                strlen($meanFrequencySplit[1]) - 5));
        } else {
            $jsonResponse['ratios']['meanFrequency']['value']
                = (float) str_replace(',', '', $meanFrequencySplit[0]);
        }
        $countIndexLog10Split = explode(' ', trim($entries[46]->nodeValue));
        if (sizeof($countIndexLog10Split) == 2) {
            $jsonResponse['ratios']['countIndexLog10']['value']
                = (float) str_replace(',', '', str_replace(chr(194).chr(160),
                '', $countIndexLog10Split[0]));
            $jsonResponse['ratios']['countIndexLog10']['standardDeviation']
                = (float) str_replace(',', '', substr($countIndexLog10Split[1],
                4, strlen(str_replace(chr(194).chr(160), '',
                    $countIndexLog10Split[1])) - 5));
        } else {
            $jsonResponse['ratios']['countIndexLog10']['value']
                = (float) str_replace(',', '', str_replace(chr(194).chr(160),
                '', $countIndexLog10Split[0]));
        }

        $appObject->getApplicationLogger()->addInfo("LexTutorResult: " . json_encode($jsonResponse));
        echo json_encode($jsonResponse);
    }
}
