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
<div class="container col-md-8 col-md-offset-2">
    <!--suppress HtmlUnknownTarget -->
    <form method="post" action="/register" data-toggle="validator">
        <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
        echo $csrftoken; ?>">
        <div class="form-box">
            <div class="form-box-caption"><?php echo _('Register'); ?></div>
            <?php
            if (isset($iplocked)) {
                echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> ' . _('You have performed too many login attempts. Your IP address will be unblocked three hours after the last attempt.') . '</div>';
            } elseif (isset($emailexisting)) {
                echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An account with the e-mail address you specified already exists.') . '</div>';
            } elseif (isset($emailsenderror)) {
                echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An internal error occurred while sending the confirmation e-mail. Please try again.') . '</div>';
            } else {
                echo '<p>'. _('Register to access TellOP.') . '</p>';
            }
            ?>
            <div class="form-group<?php if (isset($emailerror) && $emailerror) {echo ' has-error';} ?>">
                <label for="email" class="required"><?php echo _('E-mail address'); ?></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="<?php echo _('E-mail address'); ?>" data-error="<?php echo _('You must enter a valid e-mail address.'); ?>" required<?php if (isset($_POST['email'])) {echo ' value="'.htmlspecialchars($_POST['email']).'"';} ?> maxlength="254">
                <div class="help-block with-errors"><?php if (isset($emailerror) && $emailerror) {echo _('You must enter a valid e-mail address.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($emailconfirmerror) && $emailconfirmerror) {echo ' has-error';} ?>">
                <label for="emailconfirm" class="required"><?php echo _('Confirm e-mail address'); ?></label>
                <input type="email" class="form-control" id="emailconfirm" name="emailconfirm" placeholder="<?php echo _('Confirm e-mail address'); ?>" data-error="<?php echo _('The e-mail addresses do not match.'); ?>" required<?php if (isset($_POST['emailconfirm'])) {echo ' value="'.htmlspecialchars($_POST['emailconfirm']).'"';} ?> maxlength="254">
                <div class="help-block with-errors"><?php if (isset($emailconfirmerror) && $emailconfirmerror) {echo _('The e-mail addresses do not match.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($missingpassword) && $missingpassword) {echo ' has-error';} ?>">
                <label for="password" class="required"><?php echo _('Password'); ?></label>
                <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo _('Password'); ?>" data-error="<?php echo _('You must enter a password.'); ?>" required>
                <div class="help-block with-errors"><?php if (isset($missingpassword) && $missingpassword) {echo _('You must enter your password.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($confirmpassworderror) && $confirmpassworderror) {echo ' has-error';} ?>">
                <label for="passwordconfirm" class="required"><?php echo _('Confirm password'); ?></label>
                <input type="password" class="form-control" id="passwordconfirm" name="passwordconfirm" placeholder="<?php echo _('Confirm password'); ?>" data-error="<?php echo _('The passwords do not match.'); ?>" required>
                <div class="help-block with-errors"><?php if (isset($confirmpassworderror) && $confirmpassworderror) {echo _('The passwords do not match.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($titleerror) && $titleerror) {echo ' has-error';} ?>">
                <label for="title"><?php echo _('Title'); ?></label>
                <input type="text" class="form-control" id="title" name="title" placeholder="<?php echo _('Title'); ?>" data-error="<?php echo _('The title can be at most 50 characters long.'); ?>"<?php if (isset($_POST['title'])) {echo ' value="'.htmlspecialchars($_POST['title']).'"';} ?> maxlength="50">
                <div class="help-block with-errors"><?php if (isset($titleerror) && $titleerror) {echo _('The title can be at most 50 characters long.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($displaynameerror) && $displaynameerror) {echo ' has-error';} ?>">
                <label for="displayname" class="required"><?php echo _('Name and surname'); ?></label>
                <input type="text" class="form-control" id="displayname" name="displayname" placeholder="<?php echo _('Name and surname'); ?>" <?php if (isset($_POST['displayname'])) {echo ' value="'.htmlspecialchars($_POST['displayname']).'"';} ?> data-error="<?php echo _('You must enter your name and surname and it must be at most 250 characters long'); ?>" required maxlength="250">
                <div class="help-block with-errors"><?php if (isset($displaynameerror) && $displaynameerror) {echo _('You must enter your name and surname and it must be at most 250 characters long.'); } ?></div>
            </div>
            <div class="form-group<?php if (isset($langlevelerror) && $langlevelerror) {echo ' has-error';} ?>">
                <label for="languagelevel" class="required"><?php echo _('Language level'); ?></label>
                <select size="1" class="form-control" id="languagelevel" name="languagelevel" data-error="<?php echo _('You must specify a language level.'); ?>" required>
                    <option value="A1"<?php if (isset($_POST['languagelevel']) && $_POST['languagelevel'] == 'A1') {echo ' selected';}?>><?php echo _('A1: breakthrough'); ?></option>
                    <option value="A2"<?php if (isset($_POST['languagelevel']) && $_POST['languagelevel'] == 'A2') {echo ' selected';}?>><?php echo _('A2: waystage'); ?></option>
                    <option value="B1"<?php if (isset($_POST['languagelevel']) && $_POST['languagelevel'] == 'B1') {echo ' selected';}?>><?php echo _('B1: threshold'); ?></option>
                    <option value="B2"<?php if (isset($_POST['languagelevel']) && $_POST['languagelevel'] == 'B2') {echo ' selected';}?>><?php echo _('B2: vantage'); ?></option>
                    <option value="C1"<?php if (isset($_POST['languagelevel']) && $_POST['languagelevel'] == 'C1') {echo ' selected';}?>><?php echo _('C1: effective operational proficiency'); ?></option>
                    <option value="C2"<?php if (isset($_POST['languagelevel']) && $_POST['languagelevel'] == 'C2') {echo ' selected';}?>><?php echo _('C2: mastery'); ?></option>
                </select>
                <div id="languageleveldesc"></div>
            </div>
            <p><?php echo _('By clicking on <strong>Sign up</strong>, you agree to our <!--suppress HtmlUnknownTarget -->
<a href="/terms">Terms of service</a> and to our <!--suppress HtmlUnknownTarget -->
<a href="/privacy">Privacy policy</a>.'); ?></p>
            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-default pull-right"><?php echo _('Sign up'); ?></button>
                </div>
            </div>
        </div>
    </form>
    <?php $additionalincludes[] = '/js/registration.js'; ?>
</div>
<?php } else { ?>
    <div class="container col-md-4 col-md-offset-4">
        <h1><?php echo _('Confirm your e-mail address'); ?></h1>
        <p><?php echo _('We have sent you an e-mail to verify your address. Please click the link contained in it to complete the account creation process.'); ?></p>
    </div>
<?php } ?>
<?php include 'footer.php'; ?>

