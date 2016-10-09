<?php header('Content-Type: application/javascript'); ?>
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

"use strict";

(function () {
    $(document).ready(function() {
        String.locale = "<?php
if (!isset($_SERVER['QUERY_STRING'])) {
    $locale = 'en-US';
} else {
    $locale = preg_replace('/^[A-Za-z\-]/', '', $_SERVER['QUERY_STRING']);
    $localeWithUnderscores = str_replace('_', '-', $locale);
    if (!file_exists(__DIR__ . "/../locale/$localeWithUnderscores/tellop.json")) {
        $locale = 'en-US';
    }
}
echo $locale;
        ?>";
    });
}());

var l = function (string) {
    return string.toLocaleString();
};
