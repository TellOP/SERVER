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
     * Gets the string identifying the object type in a JSON representation.
     * @return string The string identifying the object type.
     */
    static function getJSONType() {
        return 'DICT_SEARCH';
    }

    /**
     * Fills in the details of a user activity from a series of fields.
     * @param mixed[] $fields The fields from which the details should be
     * loaded.
     * @return void
     */
    function getUserActivityFromFields($fields) {
        parent::getUserActivityFromFieldsBase($fields);
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
    }
}
