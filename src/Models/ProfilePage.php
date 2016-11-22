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
<div class="container">
    <h1><?php echo _('User profile'); ?></h1>
    <table class="table">
        <tbody>
            <tr><th><?php echo _('User'); ?></th><td><?php if (isset($title)) {echo htmlspecialchars($title) . ' ';}
                    /** @noinspection PhpUndefinedVariableInspection */
                    echo htmlspecialchars($displayname); ?></td></tr>
            <tr><th><?php echo _('E-mail address'); ?></th><td><?php /** @noinspection PhpUndefinedVariableInspection */
                    echo htmlspecialchars($emailaddress); ?></td></tr>
            <tr><th><?php echo _('Language level'); ?></th><td><?php /** @noinspection PhpUndefinedVariableInspection */
                    echo $languagelevel; ?></td></tr>
        </tbody>
    </table>
    <hr>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <!--suppress HtmlUnknownTarget -->
            <form method="post" action="/profile" data-toggle="validator">
                <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
                echo $csrftoken; ?>">
                <div class="form-box">
                    <div class="form-box-caption"><?php echo _('Change password'); ?></div>
                    <?php if (isset($internalerror)) {
                        echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An internal error occurred while trying to change your password. Please try again.') . '</div>';
                    } else if (isset($passwordchanged)) {
                        echo '<div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . _('Your password was changed successfully.') . '</div>';
                    } else {
                        echo '<p>' . _('Fill in this form to change your password.') . '</p>';
                    } ?>
                    <div class="form-group">
                        <label for="password"><?php echo _('Current password'); ?></label>
                        <input type="password" class="form-control<?php if ((isset($passworderror) && $passworderror) || (isset($wrongoldpw) && $wrongoldpw)) {echo ' has-error';} ?>" id="password" name="password" placeholder="<?php echo _('Current password'); ?>" data-error="<?php echo _('You must enter your current password.'); ?>" required>
                        <div class="help-block with-errors"><?php if (isset($passworderror) && $passworderror) {echo _('You must enter your current password.'); } elseif (isset($wrongoldpw) && $wrongoldpw) {echo _('Your old password is incorrect.');} ?></div>
                    </div>
                    <div class="form-group">
                        <label for="newpassword"><?php echo _('New password'); ?></label>
                        <input type="password" class="form-control<?php if (isset($newpassworderror) && $newpassworderror) {echo ' has-error';} ?>" id="newpassword" name="newpassword" placeholder="<?php echo _('New password'); ?>" data-error="<?php echo _('You must enter your new password.'); ?>" required>
                        <div class="help-block with-errors"><?php if (isset($newpassworderror) && $newpassworderror) {echo _('You must enter your new password.'); } ?></div>
                    </div>
                    <div class="form-group">
                        <label for="newpasswordconfirm"><?php echo _('Confirm new password'); ?></label>
                        <input type="password" class="form-control<?php if ((isset($newpasswordconfirmerror) && $newpasswordconfirmerror) || (isset($newpasswordsmatcherror) && $newpasswordsmatcherror)) {echo ' has-error';} ?>" id="newpasswordconfirm" name="newpasswordconfirm" placeholder="<?php echo _('Confirm new password'); ?>" data-match="#newpassword" data-error="<?php echo _('The passwords do not match.'); ?>" required>
                        <div class="help-block with-errors"><?php if (isset($newpasswordconfirmerror) && $newpasswordconfirmerror) {echo _('You must confirm your password');} if (isset($newpasswordsmatcherror) && $newpasswordsmatcherror) {echo _('The passwords do not match.');} ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-default pull-right"><?php echo _('Change password'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <h2><?php echo _('Search history'); ?></h2>
    <div id="searchhistory">
        <a href="#" id="searchhistoryreqlink"><?php echo _('Request a list of all searches'); ?></a>
    </div>
</div>
<?php include 'footer.php'; ?>
