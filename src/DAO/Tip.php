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
 * A language tip.
 * @package TellOP\DAO
 */
class Tip {
    /**
     * The unique tip ID.
     * @var int $id
     */
    private $id;

    /**
     * The ISO 833-1 code of the locale this tip is appropriate for.
     * @var string $locale
     */
    private $locale;

    /**
     * The CEFR level this tip is appropriate for.
     * @var string $cefrLevel
     */
    private $cefrLevel;

    /**
     * The text of the tip.
     * @var string $text
     */
    private $text;

    /**
     * Tip constructor.
     * @param int $id The unique tip ID.
     * @param string $locale The ISO 833-1 code of the locale this tip is
     * appropriate for.
     * @param string $cefrLevel The CEFR level this tip is appropriate for.
     * @param string $text The text of the tip.
     */
    public function __construct($id, $locale, $cefrLevel, $text) {
        $this->id = $id;
        $this->locale = $locale;
        $this->cefrLevel = $cefrLevel;
        $this->text = $text;
    }

    /**
     * Gets a tip given its unique ID.
     * @param \TellOP\Application $appObject The application object.
     * @param int $id The unique ID of the tip to be retrieved.
     * @throws \InvalidArgumentException Thrown if an argument is <c>null</c>
     * or the ID is negative.
     * @throws DatabaseException Thrown if a database-related error occurred.
     * @return Tip|null The requested tip if it exists, <c>null</c>
     * otherwise.
     */
    public static function getTipFromID($appObject, $id) {
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
        $getTip = $apppdo->prepare('SELECT LCID, languagelevel, text FROM tips '
            . 'WHERE id = :id');
        if (!$getTip->execute(array('id' => $id))) {
            throw new DatabaseException('Unable to execute the query');
        }
        if ($getTip->rowCount() != 1) {
            $getTip->closeCursor();
            return NULL;
        }
        if (($tipFields = $getTip->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
            throw new DatabaseException('Unable to fetch the results');
        }
        $getTip->closeCursor();
        return new Tip($id, $tipFields['LCID'], $tipFields['languagelevel'],
            $tipFields['text']);
    }
}
