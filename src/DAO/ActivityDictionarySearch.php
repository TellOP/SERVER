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
 * "Dictionary search" activity.
 * @property int minimumwords
 * @property int maximumwords
 * @package TellOP\DAO
 */
class ActivityDictionarySearch extends Activity {
    /**
     * ActivityDictionarySearch constructor.
     * @param \TellOP\Application $appObject The application object.
     */
    function __construct($appObject) {
        parent::__construct($appObject);
    }

    /**
     * Gets the string identifying the object type in a JSON representation.
     * @return string The string identifying the object type.
     */
    static function getJSONType() {
        return 'DICT_SEARCH';
    }

    /**
     * Replaces the current activity with one loaded from a set of fields.
     * @param mixed[] $fields The database fields.
     */
    public function getActivityFromFields($fields) {
        parent::getActivityFromFieldsBase($fields);
    }

    /**
     * Gets a representation suitable for JSON serialization.
     * @return mixed[] An array containing the data to be serialized.
     */
    public function jsonSerialize(){
        return parent::jsonSerializeBase();
    }
}
