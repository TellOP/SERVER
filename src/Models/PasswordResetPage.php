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
    <?php if (isset($completed) && $completed) { ?>
        <h1><?php echo _('Your password was reset successfully'); ?></h1>
        <p><?php echo _('You may now <!--suppress HtmlUnknownTarget -->
<a href="/login">log in</a> with your new password.'); ?></p>
    <?php } elseif (isset($missingtoken) && $missingtoken) { ?>
        <h1><?php echo _('Missing password reset token'); ?></h1>
        <p><?php echo _('Check the address you tried to visit is complete.'); ?></p>
    <?php } elseif (isset($oldtoken) && $oldtoken) { ?>
        <h1><?php echo _('Password reset link invalid/expired'); ?></h1>
        <p><?php echo _('The password reset link you clicked on is invalid or has expired. Please <a href="/forgotpassword">request a new password reset</a> and use the link provided in the last message that will be sent to you.'); ?></p>
    <?php } else { ?>
        <!--suppress HtmlUnknownTarget -->
        <form method="post" action="/passwordreset?token=<?php echo htmlspecialchars($_GET['token']); ?>" data-toggle="validator">
            <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
            echo $csrftoken; ?>">
            <div class="form-box">
                <div class="form-box-caption"><?php echo _('Choose a new password'); ?></div>
                <?php
                if (isset($internalerror)) {
                    echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An internal error occurred while trying to reset your password. Please try again.') . '</div>';
                } else {
                    echo '<p>'. _('Choose your new password below.') . '</p>';
                }
                ?>
                <div class="form-group<?php if (isset($newpwerr) && $newpwerr) {echo ' has-error';} ?>">
                    <label for="newpassword"><?php echo _('New password'); ?></label>
                    <input type="password" class="form-control" name="newpassword" id="newpassword" placeholder="<?php echo _('New password'); ?>" data-error="<?php echo _('You must enter your new password.'); ?>" required>
                    <div class="help-block with-errors"><?php if (isset($newpwerr) && $newpwerr) {echo _('You must enter your new password.'); } ?></div>
                </div>
                <div class="form-group<?php if ((isset($newpwcnferr) && $newpwcnferr) || (isset($pwmismatch) && $pwmismatch)) {echo ' has-error';} ?>">
                    <label for="newpasswordconfirm"><?php echo _('Confirm new password'); ?></label>
                    <input type="password" class="form-control" name="newpasswordconfirm" id="newpasswordconfirm" placeholder="<?php echo _('Confirm new password'); ?>" data-match="#newpassword" data-error="<?php echo _('You must enter your new password again.'); ?>" required>
                    <div class="help-block with-errors"><?php if (isset($newpwcnferr) && $newpwcnferr) {echo _('You must enter your new password again.'); } elseif (isset($pwmismatch) && $pwmismatch) {echo _('The passwords do not match.'); } ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-default pull-right"><?php echo _('Change password'); ?></button>
                    </div>
                </div>
            </div>
        </form>
    <?php } ?>
</div>
<?php include 'footer.php'; ?>
