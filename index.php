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
// Comment out the following lines when deploying to production
@error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require __DIR__ . '/vendor/autoload.php';

// Load the configuration file
if (($config = (include __DIR__ . '/config.php')) === FALSE) {
    exit('Unable to find the configuration file. Please rename "config.php.'
        . 'template" to "config.php" and edit it accordingly before starting '
        . 'this application.');
}

// Check for necessary prerequisites.
if (!function_exists('gettext')){
    exit('The required PHP gettext extension is not enabled. Please enable '
        . 'it in php.ini, restart the Web server, then refresh this page.');
}

(new TellOP\Application($config))->run();
