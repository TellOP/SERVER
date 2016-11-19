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
 * A class representing an essay submitted by a user.
 * @package TellOP\DAO
 */
class UserActivityEssay extends UserActivity {
    /**
     * The essay text.
     * @var string $text
     */
    private $text;

    /**
     * The date and time the user submitted the essay.
     * @var string $timestamp
     */
    private $timestamp;

    /**
     * Whether the text was deemed satisfactory or not.
     * @var bool $passed
     */
    private $passed;

    /**
     * Gets the essay text.
     * @return string The essay text.
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Sets the essay text.
     * @param string $text The new essay text.
     */
    public function setText($text) {
        $this->text = $text;
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
     * Gets whether the text was deemed satisfactory.
     * @return boolean <c>true</c> if the essay was deemed satisfactory by an
     * evaluator, <c>false</c> if it was not.
     */
    public function isPassed() {
        return $this->passed;
    }

    /**
     * Sets whether the text was deemed satisfactory.
     * @param boolean $passed <c>true</c> if the essay was deemed satisfactory
     * by an evaluator, <c>false</c> if it was not.
     */
    public function setPassed($passed) {
        $this->passed = $passed;
    }

    /**
     * Gets the string identifying the object type in a JSON representation.
     * @return string The string identifying the object type.
     */
    static function getJSONType() {
        return 'ESSAY';
    }

    /**
     * Gets a representation suitable for JSON serialization.
     * @return mixed[] An array containing the data to be serialized.
     */
    public function jsonSerialize() {
        $jsonArray = parent::jsonSerializeBase();
        $jsonArray['text'] = $this->text;
        $jsonArray['timestamp'] = (new \DateTime($this->timestamp))->format(\DateTime::ATOM);
        if ($this->passed === NULL) {
            $jsonArray['passed'] = NULL;
        } else {
            $jsonArray['passed'] = (bool) $this->passed;
        }
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
            if (isset($fields['text'])) {
                $this->text = $fields['text'];
            }
            if (isset($fields['timestamp'])) {
                $this->timestamp = $fields['timestamp'];
            }
            if (isset($fields['passed'])) {
                $this->passed = $fields['passed'];
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
        $essaydetails = $apppdo->prepare('INSERT INTO useractivity_essay '
            . '(user, activity, text, timestamp, passed) VALUES (:user, '
            . ':activity, :text, :timestamp, :passed) ON DUPLICATE KEY UPDATE '
            . 'text=:text, timestamp=:timestamp, passed=:passed');
        if (!($essaydetails->execute(array(
            ':user' => $this->getUser(),
            ':activity' => $this->getActivity(),
            ':text' => $this->text,
            ':timestamp' => $this->timestamp,
            ':passed' => $this->passed)))) {
            $essaydetails->closeCursor();
            throw new DatabaseException('Unable to save the essay details of '
                . 'the user activity');
        }
        $essaydetails->closeCursor();
    }
}
