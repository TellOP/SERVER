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

    function changeLangLevelDesc() {
        /* Get selected language level */
        var levelDescription = $("#languageleveldesc");
        switch ($("#languagelevel").val()) {
            case "A1":
                levelDescription.html(l("%A1leveldescription"));
                break;
            case "A2":
                levelDescription.html(l("%A2leveldescription"));
                break;
            case "B1":
                levelDescription.html(l("%B1leveldescription"));
                break;
            case "B2":
                levelDescription.html(l("%B2leveldescription"));
                break;
            case "C1":
                levelDescription.html(l("%C1leveldescription"));
                break;
            case "C2":
                levelDescription.html(l("%C2leveldescription"));
                break;
            default:
                levelDescription.html("");
                break;
        }
    }
    $(document).ready(changeLangLevelDesc);
    $("#languagelevel").change(changeLangLevelDesc);

    function openTermsOrPrivacy(event) {
        var callingLink = $(event.currentTarget);
        $("#termsPrivacyModalLabel").text(callingLink.data("modal-title"));
        $("#termsPrivacyModalBody").load(callingLink.data("modal-body") + " #maincontainer", function(response, status, xhr) {
            if (status == "error") {
                $("#termsPrivacyModalBody").html(l("%errorloadingtermsorprivacy"));
            }
            $('#termsPrivacyModal').modal();
        });
        event.stopPropagation();
        return false;
    }
    $("#termsLink").click(openTermsOrPrivacy);
    $("#privacyLink").click(openTermsOrPrivacy);
}());
