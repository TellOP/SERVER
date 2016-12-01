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
 * A class representing a dictionary search performed by a user.
 * @package TellOP\DAO
 */
class UserActivityDictionarySearch extends UserActivity {
    /**
     * The word or expression that was searched for.
     * @var string $word
     */
    private $word;

    /**
     * The date and time the user performed the search.
     * @var string $timestamp
     */
    private $timestamp;

    /**
     * Gets the word or expression that was searched for.
     * @return string The word or expression that was searched for.
     */
    public function getWord() {
        return $this->word;
    }

    /**
     * Sets the word or expression that was searched for.
     * @param string $word The word or expression that was searched for.
     */
    public function setWord($word) {
        $this->word = $word;
    }

    /**
     * Gets the date and time the user submitted the essay.
     * @return string The date and time the user submitted the essay in the
     * MySQL timestamp format.
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * Sets the date and time the user submitted the essay.
     * @param string $timestamp The date and time the user submitted the
     * essay in the MySQL timestamp format "Y-m-d H:i:s".
     */
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * Gets the string identifying the object type in a JSON representation.
     * @return string The string identifying the object type.
     */
    static function getJSONType() {
        return 'DICT_SEARCH';
    }

    /**
     * Gets a representation suitable for JSON serialization.
     * @return mixed[] An array containing the data to be serialized.
     */
    public function jsonSerialize(){
        $jsonArray = parent::jsonSerializeBase();
        $jsonArray['word'] = $this->word;
        $jsonArray['timestamp'] = (new \DateTime($this->timestamp))->format(\DateTime::ATOM);
        return $jsonArray;
    }

    /**
     * Fills in the details of a user activity from a series of fields.
     * @param mixed[] $fields The fields from which the details should be
     * loaded.
     * @return void
     */
    function getUserActivityFromFields($fields) {
        parent::getUserActivityFromFieldsBase($fields);
        if (isset($fields) && is_array($fields)) {
            if (isset($fields['word'])) {
                $this->word = $fields['word'];
            }
            if (isset($fields['dstimestamp'])) {
                $this->timestamp = $fields['dstimestamp'];
            }
        }
    }

    /**
     * Saves the user activity into the database. Derived classes are NOT
     * required to call <c>saveUserActivityDetailsBase</c>, but must just
     * prepare and execute the relevant statements.
     * @param \PDO $apppdo The application PDO.
     * @return void
     * @throws DatabaseException Thrown if the application is unable to save
     * the activity due to a database error.
     */
    protected function saveUserActivityDetails($apppdo) {
        $essaydetails = $apppdo->prepare('INSERT INTO useractivity_dictionarysearch '
            . '(user, activity, word, timestamp) VALUES (:user, :activity, '
            . ':word, :timestamp) ON DUPLICATE KEY UPDATE '
            . 'word=:word, timestamp=:timestamp');
        if (!($essaydetails->execute(array(
            ':user' => $this->getUser(),
            ':activity' => $this->getActivity(),
            ':word' => $this->word,
            ':timestamp' => $this->timestamp)))) {
            $essaydetails->closeCursor();
            throw new DatabaseException('Unable to save the dictionary details '
                . 'of the user activity');
        }
        $essaydetails->closeCursor();
    }
}
