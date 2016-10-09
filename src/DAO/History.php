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

class History {
    /**
     * A list of all activities completed by the user named in the User
     * property, sorted decrescently by date (where available).
     * @var Activity[] $activityList
     */
    private $activityList;

    /**
     * The user ID the activity history refers to.
     * @var string $user
     */
    private $user;

    /**
     * History constructor.
     * @param Activity[] $activityList The list of activities completed by the
     * user.
     * @param string $user The user ID the activity history refers to.
     */
    public function __construct($activityList, $user) {
        $this->activityList = $activityList;
        $this->user = $user;
    }

    /**
     * Gets the activities completed by this user.
     * @return Activity[] The list of activities completed by this user,
     * sorted decrescently by date (dictionary searches are put last).
     */
    public function getActivityList() {
        return $this->activityList;
    }

    /**
     * Gets the user ID associated to the history.
     * @return string The user ID associated to the history.
     */
    public function getUserID() {
        return $this->user;
    }

    /**
     * Gets the history for a specified user from the database.
     * @param \TellOP\Application $appObject The application object.
     * @param string $username The user ID.
     * @return History The history for the specified user.
     * @throws \InvalidArgumentException Thrown if a parameter is <c>null</c>
     * or empty.
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public static function getHistoryFromDB($appObject, $username) {
        if ($appObject === NULL) {
            throw new \InvalidArgumentException('appObject is null');
        }
        if (!is_string($username) || !isset($username)) {
            throw new \InvalidArgumentException('The user ID must be a '
                . 'non-empty string');
        }

        $apppdo = $appObject->getApplicationPDO();
        $getHistory = $apppdo->prepare('SELECT UA.activity FROM '
            . 'useractivities AS UA WHERE UA.user = :user');
        if (!$getHistory->execute(array('user' => $username))) {
            throw new DatabaseException('Unable to execute the query');
        }
        if ($getHistory->rowCount() == 0) {
            $getHistory->closeCursor();
            return new History(array(), $username);
        }
        if (($history = $getHistory->fetchAll(\PDO::FETCH_ASSOC)) === FALSE) {
            throw new DatabaseException('Unable to fetch the results');
        }
        $getHistory->closeCursor();
        $objHistory = array();
        foreach ($history as $activity) {
            $objHistory[] = UserActivity::getActivityFromID($appObject,
                $username, $activity['activity']);
        }
        // TODO: the essays are sorted by date, but the dictionary searches
        // are kept last. Does this look good?
        usort($objHistory, function($a, $b) {
            if ($a instanceof UserActivityEssay) {
                if ($b instanceof UserActivityEssay) {
                    return $b->getTimestamp() - $a->getTimestamp();
                } else {
                    return -1;
                }
            } else {
                if ($b instanceof UserActivityEssay) {
                    return 1;
                } else {
                    return 0;
                }
            }
        });
        return new History($objHistory, $username);
    }
}
