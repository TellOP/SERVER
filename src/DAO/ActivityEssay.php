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
 * "Write an essay" activity.
 * @package TellOP\DAO
 */
class ActivityEssay extends Activity {
    /**
     * The essay title.
     * @var string $title
     */
    private $title;

    /**
     * A description of the task.
     * @var string $description
     */
    private $description;

    /**
     * A list of tags associated to the activity.
     * @var string[] $tags
     */
    private $tags;

    /**
     * The minimum number of words required for the essay.
     * @var int $minimumwords
     */
    private $minimumwords;

    /**
     * The maximum number of words required for the essay.
     * @var int $minimumwords
     */
    private $maximumwords;

    /**
     * ActivityEssay constructor.
     * @param \TellOP\Application $appObject The application object.
     */
    function __construct($appObject) {
        parent::__construct($appObject);
        $this->tags = array();
        $this->minimumwords = 80;
        $this->maximumwords = 250;
    }

    /**
     * Gets the string identifying the object type in a JSON representation.
     * @return string The string identifying the object type.
     */
    static function getJSONType() {
        return 'ESSAY';
    }

    /**
     * Gets the essay title.
     * @return string The essay title.
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Gets the task description.
     * @return string The task description.
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Gets the list of tags associated to the activity.
     * @return \string[] The list of tags associated to the activity.
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * Gets the minimum number of words required for the essay.
     * @return int The minimum number of words required for the essay.
     */
    public function getMinimumWords() {
        return $this->minimumwords;
    }

    /**
     * Gets the maximum number of words required for the essay.
     * @return int The maximum number of words required for the essay.
     */
    public function getMaximumWords() {
        return $this->maximumwords;
    }

    /**
     * Replaces the current activity with one loaded from a set of fields.
     * @param mixed[] $fields The database fields.
     */
    public function getActivityFromFields($fields) {
        parent::getActivityFromFieldsBase($fields);
        if (isset($fields) && is_array($fields)) {
            if (isset($fields['title'])) {
                $this->title = $fields['title'];
            }
            if (isset($fields['description'])) {
                $this->description = $fields['description'];
            }
            if (isset($fields['tags']) && strlen($fields['tags']) > 0) {
                $this->tags = explode(',', $fields['tags']);
            }
            if (isset($fields['minimumwords'])) {
                $this->minimumwords = $fields['minimumwords'];
            }
            if (isset($fields['maximumwords'])) {
                $this->maximumwords = $fields['maximumwords'];
            }
        }
    }

    /**
     * Gets a representation suitable for JSON serialization.
     * @return mixed[] An array containing the data to be serialized.
     */
    public function jsonSerialize() {
        $jsonArray = parent::jsonSerializeBase();
        $jsonArray['title'] = $this->title;
        $jsonArray['description'] = $this->description;
        $jsonArray['tags'] = $this->tags;
        $jsonArray['minimumwords'] = $this->minimumwords;
        $jsonArray['maximumwords'] = $this->maximumwords;
        return $jsonArray;
    }
}
