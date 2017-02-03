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

use TellOP\DAO\Activity;
use TellOP\DAO\DatabaseException;

class FeaturedController extends WebServiceClientController {
    /**
     * Returns a list of all featured exercises.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public function displayPage($appObject) {
        //error_reporting(E_ALL);
        $this->checkOAuth($appObject, 'exercises');
        $logger = $appObject->getApplicationLogger();
        //$logger->addInfo('[' . __CLASS__ . ':' . __LINE__ . '] GET: ', $_GET);
        //$logger->addInfo('[' . __CLASS__ . ':' . __LINE__ . '] POST: ', $_POST);

        $lang = array();

        if(isset($_GET['en-GB'])) {
            array_push($lang, 'en-GB');
        }
        if(isset($_GET['es-ES'])) {
            array_push($lang, 'es-ES');
        }
        if(isset($_GET['en-US'])) {
            array_push($lang, 'en-US');
        }
        if(isset($_GET['fr-FR'])) {
            array_push($lang, 'fr-FR');
        }
        if(isset($_GET['de-DE'])) {
            array_push($lang, 'de-DE');
        }
        if(isset($_GET['it-IT'])) {
            array_push($lang, 'it-IT');
        }

        if (sizeof($lang) == 0) {
            array_push($lang, 'en-GB');
        }

        $logger->addInfo('[' . __CLASS__ . ':' . __LINE__ . '] Languages: ', $lang);

        $result = Activity::getFeaturedExercises($appObject, $lang);
        //$logger->addInfo('[' . __CLASS__ . ' ' . __FUNCTION__ . ':' . __LINE__ . '] Received object: ', $result);
        $out = json_encode($result);
        //$logger->addInfo('[' . __CLASS__ . ' ' . __FUNCTION__ . ':' . __LINE__ . '] Output: ' . $out);
        echo $out;
    }
}
