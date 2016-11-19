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
use TellOP\DAO\History;

class HistoryController extends WebServiceClientController {
    /**
     * Returns the complete history of all activities performed by the
     * logged-in user.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     * @throws DatabaseException Thrown if a database error occurs.
     */
    public function displayPage($appObject) {
        $this->checkOAuth($appObject, 'dashboard');
        $history = History::getHistoryFromDB($appObject,
            $this->getOAuthUsername());
        echo json_encode($history->getActivityList());
    }
}
