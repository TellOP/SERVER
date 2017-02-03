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

use Symfony\Component\Config\Definition\Exception\Exception;

class OxfordDictionary extends WebServiceClientController {

    /**
     * @var string Language for interrogating the dictionary.
     */
    private $language = "es";

    public function __construct($appObject, $language) {
        $this->language = $language;
    }

    /**
     * Performs a query to the Oxford Dictionary Web service.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject)
    {
        //error_reporting(E_ALL);
        //echo "<xmp>";
        $logger = $appObject->getApplicationLogger();

        /*
        $this->checkOAuth($appObject, 'onlineresources');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->dieWSMethodNotSupported();
        }
        */
        // Perform validation
        if (!isset($_GET['q'])) {
            $this->dieWSValidation('The q parameter is missing.');
        }
        $logger->addInfo("Oxford: " . $_GET['q']);

        //$logger->addInfo($_GET['q']);
        //$logger->addInfo(urldecode($_GET['q']));
        //$logger->addInfo(base64_decode(urldecode($_GET['q'])));

        $search = base64_decode(urldecode($_GET['q']));
        if (!$search) {
            $this->dieWSValidation('The q parameter must be base64 encoded.');
        }

        $unwanted_array = array(
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
            'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I',
            'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
            'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a',
            'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e',
            'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y' );

        $search = strtr($search, $unwanted_array);

        $apiUID = $appObject->getConfig()['apikeys']['oxfordDictionaryAppID'];
        $apiPWD = $appObject->getConfig()['apikeys']['oxfordDictionaryAppKey'];
        if (!isset($apiUID) || $apiUID == '' || !isset($apiPWD) || $apiPWD == '') {
            $this->dieWS(WebServiceClientController::SERVER_SIDE_API_KEY_MISSING);
        }

        $curlHandle = $this->curlOpenCustomHeader(
            'https://od-api.oxforddictionaries.com/api/v1/entries/' . $this->language . '/' . urlencode($search),
            array('Accept: application/json', 'app_id: ' . $apiUID, 'app_key: ' . $apiPWD)
        );

        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);
        // Parse the response
        if ($response === FALSE) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $response_info = curl_getinfo($curlHandle);

        if($response_info['http_code'] != '200') {
            $logger->addError("Response status: " . $response_info['http_code']);
            $logger->addError("Response: " . $response);
            $logger->addError("Response Info: ", $response_info);
            echo json_encode(array("no_value" => "error"));
            return;
        }
        //var_dump($response);

        // We need to preparse it because of the lexicalCategory. We need this data correctly formatted and included
        // in the lowest entry.
        $out = array();

        try {
            $json = json_decode($response)->results;
        }
        catch (Exception $ex) {
            var_dump($response);
        }

        foreach ($json as $result) {
            $term = $result->word;
            foreach ($result->lexicalEntries as $lexentry) {
                switch ($lexentry->lexicalCategory) {
                    case 'Adjective':
                        $pos = 'adjective';
                        break;
                    case 'Adverb':
                        $pos = 'adverb';
                        break;
                    case 'Conjunction':
                        $pos = 'conjunction';
                        break;
                    case 'Determiner':
                        $pos = 'determiner';
                        break;
                    case 'Interjection':
                        $pos = 'interjectionOrDiscourseMarker';
                        break;
                    case 'Noun':
                        $pos = 'commonNoun';
                        break;
                    case 'Numeral':
                        $pos = 'cardinalNumber';
                        break;
                    case 'Preposition':
                        $pos = 'preposition';
                        break;
                    case 'Pronoun':
                        $pos = 'pronoun';
                        break;
                    case 'Verb':
                        $pos = 'verb';
                        break;
                    default:
                        $pos = 'unclassified';
                        break;
                }
                foreach ($lexentry->entries as $entry) {
                    $tmpentry = array();
                    foreach ($entry->senses as $sense) {
                        if (!isset($sense->definitions)) {
                            continue;
                        }
                        $definitions = $sense->definitions;
                        $id = $sense->id;

                        $examples = array();
                        if (isset($sense->examples) && $sense->examples != null) {
                            foreach ($sense->examples as $example) {
                                array_push($examples, $example->text);
                            }
                        }
                        array_push($tmpentry, array(
                                'id' => $id,
                                'term' => $term,
                                'pos' => $pos,
                                'level' => 'UNKNOWN',
                                'definitions' => $definitions,
                                'examples' => $examples)
                        );
                    }
                    array_push($out, $tmpentry);
                }
            }
        }
        //var_dump($out);
        echo json_encode($out);

        //echo "</xmp>";
    }
}
