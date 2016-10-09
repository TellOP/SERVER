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
namespace TellOP;

use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;
use OAuth2\Scope;
use OAuth2\Server;
use OAuth2\Storage\Memory;

/**
 * OAuth 2.0 server object.
 * @package TellOP
 */
class OAuth2Server {
    /**
     * Instance of the OAuth 2.0 server from the vendor library.
     * @var Server $server
     */
    private $server;
    /**
     * OAuth 2.0 server constructor.
     * @param \TellOP\Application $appObject The application object.
     */
    function __construct($appObject) {
        $storage = new OAuth2Storage($appObject);
        $this->server = new Server($storage, array('allow_implicit' => true));
        $memory = new Memory(array(
            'default_scope' => 'basic',
            'supported_scopes' => array(
                'basic',          // Read-only access to profile data
                'dashboard',      // Read-only access to dashboard data
                'exercises',      // Ability to perform exercises
                'profile',        // Read-write access to profile data
                'onlineresources' // Ability to perform queries to online resources
            )
        ));
        $scopeUtil = new Scope($memory);
        $this->server->setScopeUtil($scopeUtil);
        $this->server->addGrantType(new RefreshToken($storage));
        $this->server->addGrantType(new AuthorizationCode($storage));
    }
    /**
     * Returns the OAuth 2.0 server from the vendor library.
     * @return Server OAuth server.
     */
    public function getServer() {
        return $this->server;
    }
}
