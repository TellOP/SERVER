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
use TellOP\DAO\UserActivity;
use TellOP\DAO\UserActivityDictionarySearch;
use TellOP\DAO\UserActivityEssay;

class ExerciseController extends WebServiceClientController {
    /**
     * Unlocks all tables.
     * @param \TellOP\Application $appObject Application object.
     */
    private function unlockTables($appObject) {
        $apppdo = $appObject->getApplicationPDO();
        $unlockstmt = $apppdo->prepare('UNLOCK TABLES');
        $unlockstmt->execute();
        $unlockstmt->closeCursor();
    }

    /**
     * Gets an exercise or submits it to the server.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'exercises');
        $username = $this->getOAuthUsername();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Perform validation
            if (!isset($_POST['id'])) {
                $this->dieWSValidation('The id parameter is missing');
            }
            if (!is_numeric($_POST['id'])) {
                $this->dieWSValidation('The id parameter is not an integer');
            }
            $id = (int) $_POST['id'];
            if ($id < 0) {
                $this->dieWSValidation('The id parameter can not be negative');
            }
            if (!isset($_POST['type'])) {
                $this->dieWSValidation('The type parameter is missing');
            }
            // TODO: add additional table types if needed
            switch ($_POST['type']) {
                case UserActivityDictionarySearch::getJSONType():
                    // Do nothing for now
                    break;
                case UserActivityEssay::getJSONType():
                    if (!isset($_POST['text'])) {
                        $this->dieWSValidation('The text parameter is missing');
                    }
                    break;
                default:
                    $this->dieWSValidation('The exercise type is invalid');
            }

            $apppdo = $appObject->getApplicationPDO();
            // TODO: add additional table types if needed
            if (!$lockExercise = $apppdo->prepare('LOCK TABLES activity WRITE, '
                . 'useractivities WRITE, useractivity_essay WRITE')) {
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            if (!$lockExercise->execute()) {
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            $lockExercise->closeCursor();

            // Save it (overwriting the old exercise if needed)
            // TODO: add additional tables types if needed
            switch ($_POST['type']) {
                case UserActivityDictionarySearch::getJSONType():
                    $activity = new UserActivityDictionarySearch();
                    $activity->setUser($username);
                    $activity->setActivity($id);
                    break;
                case UserActivityEssay::getJSONType():
                    $activity = new UserActivityEssay();
                    $activity->setUser($username);
                    $activity->setActivity($id);
                    $activity->setPassed(false);
                    $activity->setText($_POST['text']);
                    $activity->setTimestamp(date('Y-m-d H:i:s'));
                    break;
                default:
                    $this->unlockTables($appObject);
                    $this->dieWSValidation('The exercise type is invalid');
            }
            /** @noinspection PhpUndefinedVariableInspection */
            try {
                $activity->saveUserActivity($appObject);
            } catch (DatabaseException $e) {
                $this->unlockTables($appObject);
                $this->dieWS(WebServiceClientController::UNABLE_TO_SAVE_DATA_INTO_DATABASE);
            }

            $this->unlockTables($appObject);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_GET['id'])) {
                $this->dieWSValidation('The id parameter is missing');
            }
            if (!is_numeric($_GET['id'])) {
                $this->dieWSValidation('The id parameter is not an integer');
            }
            if ($_GET['id'] < 0) {
                $this->dieWSValidation('The id parameter can not be negative');
            }
            try {
                $activityDetails = array();
                $requestedAct = Activity::getActivityFromID($appObject,
                    (int) $_GET['id']);
                if ($requestedAct !== NULL) {
                    $activityDetails = $requestedAct->jsonSerialize();
                    // Also load the user activity if it exists.
                    $useractivity = UserActivity::getActivityFromID($appObject,
                        $username, (int) $_GET['id']);
                    if ($useractivity !== NULL) {
                        $activityDetails['userActivity'] =
                            $useractivity->jsonSerialize();
                    }
                }
                echo json_encode($activityDetails);
            } catch (DatabaseException $e) {
                $this->dieWS([1001,
                    'Unable to get the activity from the database.']);
            }
        } else {
            $this->dieWSMethodNotSupported();
        }
    }
}
