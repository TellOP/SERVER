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

(function () {
    "use strict";
    $(document).ready(function() {
        /* Initialize form validation. */
        if (H5F.setup) {
            H5F.setup(document.forms);
        }

        /* Check if cookies are enabled. */
        if (!navigator.cookieEnabled) {
            document.cookie = "cookietest=1";
            var ret = document.cookie.indexOf("cookietest=") != -1;
            document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
            if (!ret) {
                document.getElementById("cookiewarning").className = "container col-md-8 col-md-offset-2 show";
            }
        }
    });
}());
