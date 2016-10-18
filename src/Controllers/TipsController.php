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

use TellOP\DAO\DatabaseException;

class TipsController extends WebServiceClientController {
    /**
     * Returns a random selection of tips matching a given language and CEFR
     * level.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'basic');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->dieWSMethodNotSupported();
        }
        // Perform validation
        /** @var int $maxNum */
        $maxNum = 10;
        if (isset($_GET['maxNum'])) {
            if (!is_numeric($_GET['maxNum'])) {
                $this->dieWSValidation('The maxNum parameter is not an '
                    . 'integer.');
            } else {
                $maxNum = (int) $_GET['maxNum'];
                if ($maxNum < 1) {
                    $this->dieWSValidation('The maximum number of tips must '
                        . 'be greater than zero');
                }
            }
        } else {
            $maxNum = -1;
        }
        if (!isset($_GET['language'])) {
            $this->dieWSValidation('The language parameter is missing.');
        }
        if (!isset($_GET['cefrLevel'])) {
            $this->dieWSValidation('The cefrLevel parameter is missing.');
        }
        switch ($_GET['cefrLevel']) {
            case 'A1':
            case 'A2':
            case 'B1':
            case 'B2':
            case 'C1':
            case 'C2':
                break;
            default:
                $this->dieWSValidation('The cefrLevel parameter must be a '
                    . 'proper CEFR level (A1, A2, B1, B2, C1, C2).');
        }

        // Get the tips
        $apppdo = $appObject->getApplicationPDO();
        if ($maxNum == -1) {
            $tips = $apppdo->prepare('SELECT id, text FROM tips WHERE '
                . 'languagelevel = :languagelevel AND LCID = :LCID');
            if (!$tips->execute(array(':languagelevel' => $_GET['cefrLevel'],
                ':LCID' => $_GET['language']))) {
                throw new DatabaseException('Unable to retrieve the tips');
            }
        } else {
            $tips = $apppdo->prepare('SELECT id, text FROM tips WHERE '
                . 'languagelevel = :languagelevel AND LCID = :LCID LIMIT :lim');
            if (!$tips->execute(array(':languagelevel' => $_GET['cefrLevel'],
                ':LCID' => $_GET['language'], ':lim' => $maxNum))) {
                throw new DatabaseException('Unable to retrieve the tips');
            }
        }
        if (($tipsfields = $tips->fetchAll(\PDO::FETCH_ASSOC)) === FALSE) {
            $tips->closeCursor();
            throw new DatabaseException('Unable to fetch tips details');
        }
        $tips->closeCursor();

        // Force cast string IDs to numbers
        for ($i = 0; $i < count($tipsfields); ++$i) {
            $tipsfields[$i]['id'] = (int) $tipsfields[$i]['id'];
        }

        echo json_encode($tipsfields);
    }
}
