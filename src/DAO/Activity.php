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
 * Base class for all activities.
 * @package TellOP\DAO
 */
abstract class Activity implements IJSONTypable, \JsonSerializable {
    /**
     * The unique activity ID.
     * @var int $id
     */
    private $id;

    /**
     * The CEFR level of the activity.
     * @var string $level
     */
    private $level;

    /**
     * The CEFR language level associated to the activity.
     * @var string $language
     */
    private $language;

    /**
     * Whether this exercise is a featured one.
     * @var bool $featured
     */
    private $featured;

    /**
     * The application object.
     * @var \TellOP\Application $appObject
     */
    protected $appObject;

    /**
     * Activity constructor.
     * @param \TellOP\Application The application object.
     */
    function __construct($appObject) {
        $this->appObject = $appObject;
        $this->id = NULL;
        $this->level = 'A1';
        $this->language = 'en-US';
        $this->featured = false;
    }

    /**
     * Gets the unique activity ID.
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Gets the CEFR language level associated to this activity.
     * @return string The CEFR language level associated to the activity.
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Gets the language ID of the activity.
     * @return string The ISO 833-1 code of the language of the activity.
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Gets whether this activity is a featured one.
     * @return boolean <c>true</c> if the activity is featured, <c>false</c>
     * otherwise.
     */
    public function isFeatured() {
        return $this->featured;
    }

    /**
     * Replaces the current activity with one loaded from a set of fields.
     * @param mixed[] $fields The database fields.
     */
    public abstract function getActivityFromFields($fields);

    /**
     * Replaces the base details for this activity with the ones loaded from a
     * set of fields.
     * @param mixed[] $fields The database fields.
     */
    protected function getActivityFromFieldsBase($fields) {
        if (isset($fields) && is_array($fields)) {
            if (isset($fields['id']) && is_numeric($fields['id'])) {
                $this->id = (int) $fields['id'];
            }
            if (isset($fields['level'])) {
                $this->level = $fields['level'];
            }
            if (isset($fields['language'])) {
                $this->language = $fields['language'];
            }
            if (isset($fields['featured']) && is_bool($fields['featured'])) {
                $this->featured = (bool) $fields['featured'];
            }
        }
    }

    /**
     * Gets a representation suitable for JSON serialization.
     * @return mixed[] An array containing the data to be serialized.
     */
    public function jsonSerializeBase(){
        $jsonArray = array();
        $jsonArray['id'] = $this->id;
        $jsonArray['type'] = static::getJSONType();
        $jsonArray['level'] = $this->level;
        $jsonArray['language'] = $this->language;
        $jsonArray['featured'] = $this->featured;
        return $jsonArray;
    }

    /**
     * Gets an activity given its unique ID.
     * @param \TellOP\Application $appObject The application object.
     * @param int $id The unique ID of the activity to be retrieved.
     * @throws \InvalidArgumentException Thrown if an argument is <c>null</c>
     * or the ID is negative.
     * @throws DatabaseException Thrown if a database-related error occurred.
     * @return Activity|null The requested activity if it exists, <c>null</c>
     * otherwise.
     */
    public static function getActivityFromID($appObject, $id) {
        if ($appObject === NULL) {
            throw new \InvalidArgumentException('appObject is null');
        }
        if (!is_int($id)) {
            throw new \InvalidArgumentException('id must be an integer');
        }
        if ($id < 0) {
            throw new \InvalidArgumentException(('id must be greater or equal'
                . ' to zero'));
        }

        $apppdo = $appObject->getApplicationPDO();
        // TODO: add additional tables here if needed
        $getActivities = $apppdo->prepare('SELECT A.id, A.type, A.level, '
            . 'A.language, A.featured, E.title, E.description, E.tags, '
            . 'E.minimumwords, E.maximumwords, E.text FROM activity AS A '
            . 'LEFT JOIN activity_essay AS E ON (A.id = E.id) '
            . 'WHERE A.id = :id GROUP BY A.id');
        if (!$getActivities->execute(array('id' => $id))) {
            throw new DatabaseException('Unable to execute the query');
        }
        if ($getActivities->rowCount() != 1) {
            $getActivities->closeCursor();
            return NULL;
        }
        if (($actFields = $getActivities->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
            throw new DatabaseException('Unable to fetch the results');
        }
        $getActivities->closeCursor();
        switch ($actFields['type']) {
            case ActivityEssay::getJSONType():
                $activity = new ActivityEssay($appObject);
                break;
            case ActivityDictionarySearch::getJSONType():
                $activity = new ActivityDictionarySearch($appObject);
                break;
            default:
                throw new DatabaseException('Invalid activity value stored in '
                    . 'the database');
        }
        $activity->getActivityFromFields($actFields);
        return $activity;
    }

    /**
     * Gets a list of all featured exercises.
     * @param \TellOP\Application $appObject The application object.
     * @return Activity[] The list of featured exercises.
     * @throws \InvalidArgumentException Thrown if a parameter is <c>null</c>
     * or empty.
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public static function getFeaturedExercises($appObject) {
        if ($appObject === NULL) {
            throw new \InvalidArgumentException('appObject is null');
        }

        $apppdo = $appObject->getApplicationPDO();
        // TODO: add additional tables here if needed
        $getFeatured = $apppdo->prepare('SELECT A.id, A.type, A.level, '
            . 'A.language, A.featured, E.title, E.description, E.tags, '
            . 'E.minimumwords, E.maximumwords, E.text FROM activity AS A '
            . 'LEFT JOIN activity_essay AS E ON (A.id = E.id) '
            . 'WHERE A.featured = TRUE GROUP BY A.id');
        if (!$getFeatured->execute()) {
            throw new DatabaseException('Unable to execute the query');
        }
        if ($getFeatured->rowCount() == 0) {
            $getFeatured->closeCursor();
            return array();
        }
        if (($featured = $getFeatured->fetchAll(\PDO::FETCH_ASSOC)) === FALSE) {
            throw new DatabaseException('Unable to fetch the results');
        }
        $getFeatured->closeCursor();
        $objFeatured = array();
        foreach ($featured as $activity) {
            switch ($activity['type']) {
                case ActivityEssay::getJSONType():
                    $newActivity = new ActivityEssay($appObject);
                    break;
                case ActivityDictionarySearch::getJSONType():
                    $newActivity = new ActivityDictionarySearch($appObject);
                    break;
                default:
                    throw new DatabaseException('Invalid activity value stored '
                        . 'in the database');
            }
            $newActivity->getActivityFromFields($activity);
            $objFeatured[] = $newActivity;
        }
        return $objFeatured;
    }

    /**
     * Gets a list of all featured exercises not done by the current user.
     * @param \TellOP\Application $appObject The application object.
     * @param string $username The username of the current user.
     * @return Activity[] The list of featured exercises.
     * @throws \InvalidArgumentException Thrown if a parameter is <c>null</c>
     * or empty.
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public static function getFeaturedAndNotDoneExercises($appObject, $username) {
        if ($appObject === NULL) {
            throw new \InvalidArgumentException('appObject is null');
        }

        $apppdo = $appObject->getApplicationPDO();
        // TODO: add additional tables here if needed
        $getFeatured = $apppdo->prepare('SELECT A.id, A.type, A.level, '
            . 'A.language, A.featured, E.title, E.description, E.tags, '
            . 'E.minimumwords, E.maximumwords, E.text UA.user FROM activity AS A '
            . 'LEFT JOIN activity_essay AS E ON (A.id = E.id) '
            . 'LEFT JOIN useractivities AS UA ON (UA.activity = A.id) '
            . 'WHERE A.featured = TRUE AND UA.user != :user GROUP BY A.id');
        if (!$getFeatured->execute(array(':user' => $username))) {
            throw new DatabaseException('Unable to execute the query');
        }
        if ($getFeatured->rowCount() == 0) {
            $getFeatured->closeCursor();
            return array();
        }
        if (($featured = $getFeatured->fetchAll(\PDO::FETCH_ASSOC)) === FALSE) {
            throw new DatabaseException('Unable to fetch the results');
        }
        $getFeatured->closeCursor();
        $objFeatured = array();
        foreach ($featured as $activity) {
            switch ($activity['type']) {
                case ActivityEssay::getJSONType():
                    $newActivity = new ActivityEssay($appObject);
                    break;
                case ActivityDictionarySearch::getJSONType():
                    $newActivity = new ActivityDictionarySearch($appObject);
                    break;
                default:
                    throw new DatabaseException('Invalid activity value stored '
                        . 'in the database');
            }
            $newActivity->getActivityFromFields($activity);
            $objFeatured[] = $newActivity;
        }
        return $objFeatured;
    }
}
