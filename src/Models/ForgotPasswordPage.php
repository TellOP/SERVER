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
<?php if (!isset($emailsent)) { ?>
<div class="container col-md-4 col-md-offset-4">
    <!--suppress HtmlUnknownTarget -->
    <form method="post" action="/forgotpassword" data-toggle="validator">
        <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
        echo $csrftoken; ?>">
        <div class="form-box">
            <div class="form-box-caption"><?php echo _('Forgot your password?'); ?></div>
            <?php
                if (isset($iplocked)) {
                    echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> ' . _('You have performed too many login attempts. Your IP address will be unblocked three hours after the last attempt.') . '</div>';
                } elseif (isset($accountlocked)) {
                    echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> ' . _('Your account was locked by an administrator.') . '</div>';
                } elseif (isset($emailsenderror)) {
                    echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An internal error occurred while sending the verification e-mail. Please try again.') . '</div>';
                } elseif (isset($waitingforconfirmation)) {
                    echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('You still need to verify your account. Please check your inbox and click on the link contained in the registration confirmation e-mail.') . '</div>';
                } else {
                    echo '<p>'. _('Enter your e-mail address to get an e-mail which will allow you to reset your password.') . '</p>';
                }
            ?>
            <div class="form-group">
                <label for="email"><?php echo _('E-mail address'); ?></label>
                <input type="email" class="form-control<?php if (isset($emailerror)) {echo ' has-error';} ?>" id="email" name="email" placeholder="<?php echo _('E-mail address'); ?>" data-error="<?php echo _('You must enter a valid e-mail address.'); ?>" required<?php if (isset($_POST['email'])) {echo ' value="'.htmlspecialchars($_POST['email']).'"';} ?>>
                <div class="help-block with-errors"><?php if (isset($emailerror)) {echo _('You must enter a valid e-mail address.'); } ?></div>
            </div>
            <div class="row">
                <div class="col-xs-offset-8 col-xs-4">
                    <button type="submit" class="btn btn-default pull-right"><?php echo _('Send e-mail'); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php } else { ?>
    <div class="container col-md-4 col-md-offset-4">
        <h1><?php echo _('Check your e-mail'); ?></h1>
        <p><?php echo _('We have sent you an e-mail to verify your password reset request. Please click the link contained in it to choose a new password.'); ?></p>
    </div>
<?php } ?>
<?php include 'footer.php'; ?>
