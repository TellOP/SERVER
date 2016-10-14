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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->getOAuthUsername();
            $postBody = json_decode(file_get_contents('php://input'), TRUE);
            // Perform validation
            if ($postBody === NULL) {
                $this->dieWSValidation('The POST body is not valid JSON.');
            }
            if (!isset($postBody['id'])) {
                $this->dieWSValidation('The id parameter is missing');
            }
            if (!is_int($postBody['id'])) {
                $this->dieWSValidation('The id parameter is not an integer');
            }
            if ($postBody['id'] < 0) {
                $this->dieWSValidation('The id parameter can not be negative');
            }
            if (!isset($postBody['type'])) {
                $this->dieWSValidation('The type parameter is missing');
            }
            // TODO: add additional table types if needed
            switch ($postBody['type']) {
                case UserActivityDictionarySearch::getJSONType():
                    // Do nothing for now
                    break;
                case UserActivityEssay::getJSONType():
                    if (!isset($postBody['text'])) {
                        $this->dieWSValidation('The text parameter is missing');
                    }
                    break;
                default:
                    $this->dieWSValidation('The exercise type is invalid');
            }

            $apppdo = $appObject->getApplicationPDO();
            // TODO: add additional table types if needed
            if (!$lockExercise = $apppdo->prepare('LOCK TABLES activity, '
                . 'useractivities, useractivity_essay WRITE')) {
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            if (!$lockExercise->execute()) {
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            $lockExercise->closeCursor();

            // The exercise must exist and must not have already been sent
            if (!$checkExercise = $apppdo->prepare('SELECT COUNT(*) AS num '
                . 'FROM activity AS A '
                . 'LEFT JOIN useractivities AS UA ON A.id = UA.activity '
                . 'WHERE (UA.USER IS NULL OR UA.user <> :user)')) {
                $this->unlockTables($appObject);
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            if (!$checkExercise->execute(array(':user' => $username))) {
                $this->unlockTables($appObject);
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            if (!$exNum = $checkExercise->fetch(\PDO::FETCH_ASSOC)) {
                $this->unlockTables($appObject);
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_FETCH_DATA_FROM_DATABASE);
            }
            $checkExercise->closeCursor();
            if ($exNum['num'] != 0) {
                $this->unlockTables($appObject);
                $this->dieWS([1001, 'The exercise does not exist or has '
                    . 'already been sent']);
            }

            // Save it
            if (!$apppdo->beginTransaction()) {
                $this->unlockTables($appObject);
                $this->dieWS(
                    WebServiceClientController::UNABLE_TO_SAVE_DATA_INTO_DATABASE);
            }
            // TODO: add additional tables types if needed
            switch ($postBody['type']) {
                case UserActivityDictionarySearch::getJSONType():
                    $activity = new UserActivityDictionarySearch();
                    $activity->setUser($username);
                    break;
                case UserActivityEssay::getJSONType():
                    $activity = new UserActivityEssay();
                    $activity->setUser($username);
                    $activity->setPassed(false);
                    $activity->setText($postBody['text']);
                    $activity->setTimestamp(date('Y-m-d H:i:s'));
                    break;
                default:
                    $this->dieWSValidation('The exercise type is invalid');
            }
            /** @noinspection PhpUndefinedVariableInspection */
            $activity->saveUserActivity($appObject);
            $apppdo->commit();

            // Unlock tables
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
                $requestedAct = Activity::getActivityFromID($appObject,
                    (int) $_GET['id']);
                echo json_encode($requestedAct);
            } catch (DatabaseException $e) {
                $this->dieWS([1001,
                    'Unable to get the activity from the database.']);
            }
        } else {
            $this->dieWSMethodNotSupported();
        }
    }
}
