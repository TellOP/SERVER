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
<?php if (isset($internalerror)) {
    if (isset($tokenerror) && $tokenerror) { ?>
    <div class="container col-md-4 col-md-offset-4">
        <h1><?php echo _('Invalid account confirmation link'); ?></h1>
        <p><?php echo _('The account confirmation link you followed was already used, has expired or is incomplete. Please <a href="/register">register again</a> to get a new link.'); ?></p>
    </div>
<?php } else { ?>
    <div class="container col-md-4 col-md-offset-4">
        <h1><?php echo _('Error'); ?></h1>
        <p><?php echo _('An internal error occurred while trying to verify your e-mail address. Please try again.'); ?></p>
    </div>
<?php }
} elseif (isset($missingtoken)) { ?>
    <div class="container col-md-4 col-md-offset-4">
        <h1><?php echo _('Error'); ?></h1>
        <p><?php echo _('Looks like the address you visited to try to verify your e-mail account is wrong. Please check it and try again.'); ?></p>
    </div>
<?php } else { ?>
    <div class="container col-md-4 col-md-offset-4">
        <h1><?php echo _('Your e-mail address was verified'); ?></h1>
        <p><?php echo _('Thanks for verifying your e-mail address, you may now <!--suppress HtmlUnknownTarget -->
<a href="/login">log in</a>.'); ?></p>
    </div>
<?php } ?>
<?php include 'footer.php'; ?>
