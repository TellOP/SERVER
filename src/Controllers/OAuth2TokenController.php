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

use OAuth2\Request;
use TellOP\OAuth2Server;

/**
 * OAuth 2.0 token controller.
 * @package TellOP\Controllers
 */
class OAuth2TokenController implements IController {

    /**
     * Issues an OAuth 2.0 token.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        $server = new OAuth2Server($appObject);
        $server->getServer()->handleTokenRequest(Request::createFromGlobals())
            ->send();
    }
}
