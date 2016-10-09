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
 * Allows a DAO that is a subclass of a base DAO to identify itself in a
 * JSON representation.
 * @package TellOP\DAO
 */
interface IJSONTypable {
    /**
     * Gets the string identifying the object type in a JSON representation.
     * @return string The string identifying the object type.
     */
    static function getJSONType();
}
