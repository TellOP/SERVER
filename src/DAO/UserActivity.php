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
namespace TellOP\DAO;

/**
 * An activity performed by a user.
 * @package TellOP\DAO
 */
abstract class UserActivity implements IJSONTypable, \JsonSerializable {
    /**
     * The user who did the activity.
     * @var string $user
     */
    private $user;

    /**
     * The activity done by the user.
     * @var int $activity
     */
    private $activity;

    /**
     * Gets the user who did the activity.
     * @return string The user who did the activity.
     */
    function getUser() {
        return $this->user;
    }

    /**
     * Sets the user who did the activity.
     * @param string $user The new name of the user who did the activity.
     */
    function setUser($user) {
        $this->user = $user;
    }

    /**
     * Gets the unique ID of the activity.
     * @return int The unique ID of the activity.
     */
    function getActivity() {
        return $this->activity;
    }

    /**
     * Sets the unique ID of the activity.
     * @param int $activity The unique ID of the activity.
     */
    function setActivity($activity) {
        $this->activity = $activity;
    }

    /**
     * Fills in the details of a user activity from a series of fields.
     * @param mixed[] $fields The fields from which the details should be
     * loaded.
     * @return void
     */
    abstract function getUserActivityFromFields($fields);

    /**
     * Fills in the base details of a user activity from a series of fields.
     * @param mixed[] $fields The fields from which the base details should be
     * loaded.
     * @return void
     */
    protected function getUserActivityFromFieldsBase($fields) {
        if (isset($fields) && is_array($fields)) {
            if (isset($fields['user'])) {
                $this->user = $fields['user'];
            }
            if (isset($fields['activity'])) {
                $this->activity = $fields['activity'];
            }
        }
    }

    /**
     * Gets a representation suitable for JSON serialization.
     * @return mixed[] An array containing the data to be serialized.
     */
    public function jsonSerializeBase(){
        $jsonArray = array();
        $jsonArray['user'] = $this->user;
        $jsonArray['type'] = static::getJSONType();
        $jsonArray['activity'] = (int) $this->activity;
        return $jsonArray;
    }

    /**
     * Saves the user activity into the database.
     * @param \TellOP\Application $appObject The application object.
     * @return void
     * @throws DatabaseException Thrown if the application is unable to save
     * the activity due to a database error.
     */
    function saveUserActivity($appObject) {
        $apppdo = $appObject->getApplicationPDO();
        if (!$apppdo->beginTransaction()) {
            return;
        }
        $this->saveUserActivityDetailsBase($apppdo);
        $this->saveUserActivityDetails($apppdo);
        $apppdo->commit();
    }

    /**
     * Saves the user activity into the database. Derived classes are NOT
     * required to call <c>saveUserActivityDetailsBase</c>, but must just
     * prepare and execute the relevant statements. The function must NOT
     * lock tables, that will be managed by the site.
     * @param \PDO $apppdo The application PDO.
     * @return void
     * @throws DatabaseException Thrown if the application is unable to save
     * the activity due to a database error.
     */
    protected abstract function saveUserActivityDetails($apppdo);

    /**
     * Saves the base activity details into the database.
     * @param \PDO $apppdo The application PDO.
     * @return void
     * @throws DatabaseException Thrown if the application is unable to save
     * the details due to a database error.
     */
    protected function saveUserActivityDetailsBase($apppdo) {
        $basedetails = $apppdo->prepare('INSERT INTO useractivities (user, '
            . 'activity) VALUES (:user, :activity) ON DUPLICATE KEY UPDATE '
            . 'activity=:activity');
        if (!($basedetails->execute(array(':user' => $this->user,
            ':activity' => $this->activity)))) {
            $basedetails->closeCursor();
            throw new DatabaseException('Unable to save the base details of '
                . 'the user activity');
        }
        $basedetails->closeCursor();
    }

    /**
     * Gets the details of a user activity given its ID and username.
     * @param \TellOP\Application $appObject The application object.
     * @param string $username The user name.
     * @param int $id The activity ID.
     * @throws \InvalidArgumentException Thrown when <c>appObject</c>,
     * <c>id</c> or <c>username</c> are not set.
     * @throws DatabaseException Thrown if the application is unable to get
     * the details due to a database error.
     * @return Activity|null The requested activity if it exists, <c>null</c>
     * otherwise.
     */
    public static function getActivityFromID($appObject, $username, $id) {
        if (!isset($appObject)) {
            throw new \InvalidArgumentException('The application object must '
                . 'be set');
        }
        if (!isset($username)) {
            throw new \InvalidArgumentException('The username must be set');
        }
        if (!isset($id)) {
            throw new \InvalidArgumentException('The activity ID must be set');
        }

        $apppdo = $appObject->getApplicationPDO();
        // TODO: add additional tables here
        $useractivity = $apppdo->prepare('SELECT U.user, U.activity, A.type, '
            . 'E.text, E.timestamp, E.passed FROM useractivities AS U '
            . 'JOIN activity AS A ON U.activity = A.id '
            . 'LEFT JOIN useractivity_essay AS E ON U.user = E.user AND '
            . 'U.activity = E.activity WHERE U.user = :user AND '
            . 'U.activity = :activity GROUP BY U.user');
        if (!$useractivity->execute(array(':user' => $username,
            ':activity' => $id))) {
            throw new DatabaseException('Unable to retrieve user activity '
                . 'details');
        }
        if ($useractivity->rowCount() != 1) {
            $useractivity->closeCursor();
            return NULL;
        }
        if (($useractivityfields = $useractivity->fetch(\PDO::FETCH_ASSOC))
            === FALSE) {
            $useractivity->closeCursor();
            throw new DatabaseException('Unable to fetch user activity '
                . 'details');
        }
        $useractivity->closeCursor();
        switch ($useractivityfields['type']) {
            case UserActivityEssay::getJSONType():
                /** @var Activity $activity */
                $activity = new UserActivityEssay();
                break;
            case UserActivityDictionarySearch::getJSONType():
                $activity = new UserActivityDictionarySearch();
                break;
            default:
                throw new DatabaseException('Unexpected value in the user '
                    . 'activity type field');
        }
        $activity->getUserActivityFromFields($useractivityfields);
        return $activity;
    }
}
