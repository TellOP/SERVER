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
include 'header.php'; ?>
<div class="container col-md-4 col-md-offset-4">
    <h1><?php
        /** @noinspection PhpUndefinedVariableInspection */
        if ($success) {
            echo _('Authorization successful');
        } else {
            echo _('Authorization unsuccessful');
        } ?></h1>
    <p><?php if (!isset($errorMessage)) {
            echo _('Your application is now authorized to access the Tell-OP API. Please wait...');
        } elseif ($errorMessage === 'invalid_request') {
            echo _('The OAuth 2.0 request is missing a required parameter.');
        } elseif ($errorMessage === 'unauthorized_client') {
            echo _('Your application is not authorized to request an authorization code using this method.');
        } elseif ($errorMessage === 'access_denied') {
            echo _('The application is not authorized to access the Tell-OP server.');
        } elseif ($errorMessage === 'unsupported_response_type') {
            echo _('The authorization server can not provide an authorization code using the method requested by the application.');
        } elseif ($errorMessage === 'invalid_scope') {
            echo _('The requested permissions are unknown or malformed.');
        } elseif ($errorMessage === 'server_error') {
            echo _('The Tell-OP server encountered an unexpected error.');
        } elseif ($errorMessage === 'temporarily_unavailable') {
            echo _('The Tell-OP server is currently unable to handle the request.');
        } else {
            echo _('The Tell-OP server encountered an unexpected error.');
        } ?></p>
</div>
<?php include 'footer.php'; ?>
