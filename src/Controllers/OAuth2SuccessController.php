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

/**
 * OAuth 2.0 success controller.
 * @package TellOP
 */
class OAuth2SuccessController implements IController {
    /**
     * Displays a "OAuth 2.0 successful" message.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        \Flight::render('OAuthSuccess', array(
            'success' => (!isset($_GET['error'])),
            'errorMessage' => (isset($_GET['error']) ? $_GET['error'] : null)
        ));
    }
}
