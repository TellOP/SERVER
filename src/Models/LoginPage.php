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
    <form method="post" action="/login<?php /** @noinspection PhpUndefinedVariableInspection */
    if ($oauth2req) {
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        echo '?response_type=' . $response_type . '&client_id=' . urlencode($client_id);
        if (isset($redirect_uri)) {
            echo '&redirect_uri=' . urlencode($redirect_uri);
        }
        if (isset($scope)) {
            echo '&scope=' . urlencode($scope);
        }
        if (isset($state)) {
            echo '&state=' . urlencode($state);
        }
    } ?>" data-toggle="validator">
        <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
        echo $csrftoken; ?>">
        <div class="form-box">
            <div class="form-box-caption"><?php echo _('Log in'); ?></div>
            <?php
                if (isset($iplocked)) {
                    echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> ' . _('You have performed too many login attempts. Your IP address will be unblocked three hours after the last attempt.') . '</div>';
                } elseif (isset($accountlocked)) {
                    echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> ' . _('Your account was locked by an administrator.') . '</div>';
                } elseif (isset($waitingforconfirmation)) {
                    echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('You need to verify your account before logging in. Please click on the link contained in your registration e-mail.') . '</div>';
                } elseif (isset($wrongcredentials)) {
                    echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('Incorrect username or password.') . '</div>';
                } elseif (isset($internalerror)) {
                    echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An internal error occurred while trying to log you in. Please try again.') . '</div>';
                } elseif ($oauth2req) {
                    echo '<p>'. _('Please log in to your TellOP account to continue.') . '</p>';
                } else {
                    echo '<p>'. _('If you already have an account, log in.') . '</p>';
                }
            ?>
            <div class="form-group<?php if (isset($emailerror) && $emailerror) {echo ' has-error';} ?>">
                <label for="email"><?php echo _('E-mail address'); ?></label>
                <input type="email" class="form-control" name="email" id="email" placeholder="<?php echo _('E-mail address'); ?>" data-error="<?php echo _('You must enter a valid e-mail address.'); ?>" required maxlength="254"<?php if (isset($emailaddress)) {echo ' value="' . htmlspecialchars($emailaddress) . '"';} ?>>
                <div class="help-block with-errors"><?php if (isset($emailerror) && $emailerror) {echo _('You must enter a valid e-mail address.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($missingpassword) && $missingpassword) {echo ' has-error';} ?>">
                <label for="password"><?php echo _('Password'); ?></label>
                <input type="password" class="form-control" name="password" id="password" placeholder="<?php echo _('Password'); ?>" data-error="<?php echo _('You must enter your password.'); ?>" required>
                <div class="help-block with-errors"><?php if (isset($missingpassword) && $missingpassword) {echo _('You must enter your password.'); } ?></div>
            </div>
            <div class="checkbox">
                <label><input type="checkbox" name="rememberme"<?php if (isset($rememberme) && $rememberme) {echo ' checked';} ?>> <?php echo _('Keep me logged in'); ?></label>
            </div>
            <div class="row">
                <div class="col-xs-8">
                    <div class="form-control-static"><!--suppress HtmlUnknownTarget -->
                        <a href="/forgotpassword"><?php echo _('Forgot your password?'); ?></a></div>
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-default pull-right"><?php echo _('Log in'); ?></button>
                </div>
            </div>
        </div>
    </form>
    <?php if (!$oauth2req) { ?>
    <p></p>
    <div class="form-box">
        <div class="form-box-caption"><?php echo _('Sign up'); ?></div>
        <p><?php echo _('If you do not have an account, please sign up to use this Web site.'); ?></p>
        <div class="row">
            <div class="col-xs-12">
                <!--suppress HtmlUnknownTarget -->
                <a class="btn btn-default pull-right" href="/register" role="button"><?php echo _('Sign up'); ?></a>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<?php include 'footer.php'; ?>
