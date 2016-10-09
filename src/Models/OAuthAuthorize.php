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
include 'header.php'; include 'OAuthScopes.php'; ?>
<div class="container col-md-4 col-md-offset-4">
    <form method="post">
        <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
        echo $csrftoken; ?>">
        <h1><?php echo _('Authorize application'); ?></h1>
        <p><?php /** @noinspection PhpUndefinedVariableInspection */
            echo sprintf(_('<strong>%s</strong> would like to perform the following actions on TellOP:'), htmlspecialchars($appname)); ?></p>
        <ul class="list-icon list-glyphicon">
            <?php
            /** @noinspection PhpUndefinedVariableInspection */
            foreach ($scope as $singlescope) {
                switch ($singlescope) {
                    case 'basic': ?>
                        <li class="list-glyphicon-basic"><strong><?php echo OAUTH_SCOPE_BASIC_TITLE; ?></strong><br><?php echo OAUTH_SCOPE_BASIC_DESCRIPTION; ?></li>
                        <?php break;
                    case 'dashboard': ?>
                        <li class="list-glyphicon-dashboard"><strong><?php echo OAUTH_SCOPE_DASHBOARD_TITLE; ?></strong><br><?php echo OAUTH_SCOPE_DASHBOARD_DESCRIPTION; ?></li>
                        <?php break;
                    case 'exercises': ?>
                        <li class="list-glyphicon-exercises"><strong><?php echo OAUTH_SCOPE_EXERCISES_TITLE; ?></strong><br><?php echo OAUTH_SCOPE_EXERCISES_DESCRIPTION; ?></li>
                        <?php break;
                    case 'profile': ?>
                        <li class="list-glyphicon-profile"><strong><?php echo OAUTH_SCOPE_PROFILE_TITLE; ?></strong><br><?php echo OAUTH_SCOPE_PROFILE_DESCRIPTION; ?></li>
                        <?php break;
                }
            }
            ?>
        </ul>
        <button class="btn btn-primary" name="authorized" value="yes" type="submit"><?php echo _('Allow access'); ?></button>
        <button class="btn btn-default" name="authorized" value="no" type="submit"><?php echo _('Do not allow'); ?></button>
        </form>
    </form>
</div>
<?php include 'footer.php'; ?>
