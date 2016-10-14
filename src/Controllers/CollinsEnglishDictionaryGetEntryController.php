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

class CollinsEnglishDictionaryGetEntryController extends WebServiceClientController {
    /**
     * Gets the part of speech corresponding to the given XML string
     * representation.
     * @param string $text The part of speech in text form.
     * @return string The part of speech in standardized form.
     */
    private function getPartOfSpeech($text) {
        switch ($text) {
            // TODO: this list is reverse engineered
            case 'adjective':
            case 'adverb':
            case 'conjunction':
            case 'determiner':
            case 'exclamation':
            case 'preposition':
            case 'pronoun':
            // TODO: the dictionary does not discriminate between modal,
            // auxiliary... and ordinary verbs
            case 'verb':
                return $text;
            // TODO: the dictionary does not discriminate between
            // common and proper nouns
            case 'noun':
                return 'commonNoun';
            default:
                return 'unclassified';
        }
    }

    /**
     * Performs a query to the Collins English Dictionary Web service to get a
     * single word definition.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'onlineresources');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->dieWSMethodNotSupported();
        }
        // Perform validation
        if (!isset($_GET['entryId'])) {
            $this->dieWSValidation('The entryId parameter is missing.');
        }
        $apiKey = $appObject->getConfig()['apikeys']['collinsDictionary'];
        if (!isset($apiKey) || $apiKey == '') {
            $this->dieWS(WebServiceClientController::SERVER_SIDE_API_KEY_MISSING);
        }
        $curlHandle = $this->curlOpen(
            'https://api.collinsdictionary.com/api/v1/dictionaries/english/entries/'
            . urlencode($_GET['entryId']) . '?format=xml');
        $this->curlSetOption($curlHandle, CURLOPT_HTTPHEADER, array(
            'Host: api.collinsdictionary.com',
            'Accept: application/json',
            'accessKey: ' . $appObject->getConfig()['apikeys']['collinsDictionary']
        ));
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);
        if ($response === FALSE) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $decodedResponse = json_decode($response, TRUE);
        if ($decodedResponse === NULL) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $jsonResponse = array();
        $jsonResponse['dictionaryCode'] = $decodedResponse['dictionaryCode'];
        $jsonResponse['entryId'] = $decodedResponse['entryId'];
        $jsonResponse['entryLabel'] = $decodedResponse['entryLabel'];
        $jsonResponse['entryUrl'] = $decodedResponse['entryUrl'];
        if (isset($decodedResponse['topics'])) {
            $jsonResponse['topics'] = array();
            foreach ($decodedResponse['topics'] as $topic) {
                // Copy topicId, topicLabel, topicUrl, topicParentId
                $jsonResponse['topics'][] = $topic;
            }
        }

        // The Collins API endpoint returns XML embedded inside the entryContent
        // item (!) - decode it to a format suitable for the app
        $resp = $decodedResponse['entryContent'];
        $jsonResponse['entryContent'] = array();
        $dom = new \DOMDocument;
        if (!$dom->loadXML($decodedResponse['entryContent'])) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $domXPath = new \DOMXPath($dom);
        $homographicEntries = $domXPath->query('/entry/hom');
        foreach ($homographicEntries as $homographicEntry) {
            $homEntryJSON = array();
            $partOfSpeechNode = $domXPath->query('gramGrp/pos', $homographicEntry);
            if ($partOfSpeechNode === FALSE || $partOfSpeechNode->length == 0) {
                $homEntryJSON['partOfSpeech'] = 'unclassified';
            } else {
                $homEntryJSON['partOfSpeech']
                    = $this->getPartOfSpeech($partOfSpeechNode[0]->nodeValue);
            }

            $homSenses = $domXPath->query('sense', $homographicEntry);
            $homSensesJSON = array();
            foreach ($homSenses as $homSense) {
                // Some definitions might have nested senses. In that case,
                // try to merge them
                $nestedSensesQuery = $domXPath->query('sense', $homSense);
                if ($nestedSensesQuery !== FALSE && $nestedSensesQuery->length > 0) {
                    $nestedPrefix = 'sense/';
                } else {
                    $nestedPrefix = '';
                }

                $homSenseJSON = array();

                $homSenseDef = $domXPath->query("${nestedPrefix}def", $homSense);
                $homSenseDefinitionJSON = array();
                if ($homSenseDef !== FALSE) {
                    foreach ($homSenseDef as $homSenseSingleDef) {
                        $homSenseDefinitionJSON[] = $homSenseSingleDef->textContent;
                    }
                }
                $homSenseJSON['definitions'] = $homSenseDefinitionJSON;

                $homSenseCitation
                    = $domXPath->query("${nestedPrefix}cit[@type='example']",
                    $homSense);
                $homSenseCitationJSON = array();
                if ($homSenseCitation !== FALSE) {
                    foreach ($homSenseCitation as $homSenseSingleCitation) {
                        $homSenseCitationJSON[] = $homSenseSingleCitation->textContent;
                    }
                }
                $homSenseJSON['examples'] = $homSenseCitationJSON;

                $homSenseSeeAlso
                    = $domXPath->query("${nestedPrefix}xr/ref[@resource='english']",
                    $homSense);
                $homSenseSeeAlsoJSON = array();
                if ($homSenseSeeAlso !== FALSE) {
                    foreach ($homSenseSeeAlso as $homSenseSingleSeeAlso) {
                        $homSenseSingleSeeAlsoJSON = array();
                        $homSenseSingleSeeAlsoJSON['target']
                            = $homSenseSingleSeeAlso->attributes->
                        getNamedItem('target')->nodeValue;
                        $homSenseSingleSeeAlsoJSON['content']
                            = $homSenseSingleSeeAlso->textContent;
                        $homSenseSeeAlsoJSON[] = $homSenseSingleSeeAlsoJSON;
                    }
                }
                $homSenseJSON['seeAlso'] = $homSenseSeeAlsoJSON;

                $homSenseRelated
                    = $domXPath->query("${nestedPrefix}re/xr/ref[@resource='english']",
                    $homSense);
                $homSenseRelatedJSON = array();
                if ($homSenseRelated !== FALSE) {
                    foreach ($homSenseRelated as $homSenseSingleRelated) {
                        $homSenseSingleRelatedJSON = array();
                        $homSenseSingleRelatedJSON['target']
                            = $homSenseSingleRelated->attributes->
                                getNamedItem('target')->nodeValue;
                        $homSenseSingleRelatedJSON['content']
                            = $homSenseSingleRelated->textContent;
                        $homSenseRelatedJSON[] = $homSenseSingleRelatedJSON;
                    }
                }
                $homSenseJSON['related'] = $homSenseRelatedJSON;

                $homSensesJSON[] = $homSenseJSON;
            }
            $homEntryJSON['senses'] = $homSensesJSON;
            $jsonResponse['entryContent']['entries'][] = $homEntryJSON;
        }

        $relatedEntries = $domXPath->query('/entry/re');
        if ($relatedEntries !== FALSE) {
            $relatedEntriesJSON = array();
            foreach ($relatedEntries as $relatedEntry) {
                $relatedEntryJSON = array();
                $relatedEntriesName = $domXPath->query('form/orth',
                    $relatedEntry);
                if ($relatedEntriesName !== FALSE && $relatedEntriesName->length > 0) {
                    $relatedEntryJSON['name'] = $relatedEntriesName[0]->nodeValue;
                }
                $relatedEntriesPOS = $domXPath->query('hom/gramGrp/pos',
                    $relatedEntry);
                if ($relatedEntriesPOS !== FALSE && $relatedEntriesPOS->length > 0) {
                    $relatedEntryJSON['partOfSpeech']
                        = $this->getPartOfSpeech($relatedEntriesPOS[0]->nodeValue);
                }
                $relatedEntriesJSON[] = $relatedEntryJSON;
            }
            $jsonResponse['entryContent']['related'] = $relatedEntriesJSON;
        }

        echo json_encode($jsonResponse);
    }
}
