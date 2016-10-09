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

class NetspeakFollowingController extends WebServiceClientController {
    /**
     * Performs a query to the Netspeak "Following words" Web service.
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
        if (!isset($_GET['t'])) {
            $this->dieWSValidation('The t parameter is missing.');
        }
        $tvalue = (int) $_GET['t'];
        if ($tvalue < 1 || $tvalue > 1000) {
            $this->dieWSValidation('The t parameter must be a number between 1'
                . ' and 1000.');
        }

        $curlHandle = $this->curlOpen(
            'http://api.netspeak.org/netspeak3/search?query='
            . urlencode($_GET['q']) . '%3F&topk=' . urlencode($_GET['t'])
            . '&corpus=web-en&format=text');
        $response = $this->curlExec($curlHandle, $appObject);
        $this->curlClose($curlHandle);

        // Parse the response
        if ($response === FALSE) {
            $this->dieWS(WebServiceClientController::UNABLE_TO_PARSE_REMOTE_RESPONSE);
        }
        $nextWords = array();
        $splitbyrow = explode("\n", $response);
        foreach ($splitbyrow as $singleitem) {
            if ($singleitem == '') {
                continue;
            }
            $splitentry = explode("\t", $singleitem);
            $nextWords[] = array(
                'ID' => $splitentry[0],
                'frequency' => (int) $splitentry[1],
                'word' => $splitentry[2]
            );
        }
        echo json_encode($nextWords);
    }
}
