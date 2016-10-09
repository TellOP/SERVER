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
<div class="container">
    <h1><?php echo _('Authorized applications'); ?></h1>
    <?php
    if (isset($internalerror)) {
        if ($internalerror) {
            echo '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> ' . _('An internal error occurred while trying to revoke access to the application. Please try again.') . '</div>';
        } else {
            echo '<div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . _('The application was removed from the list.') . '</div>';
        }
    }
    if (isset($unabletoretrievelist) && $unabletoretrievelist) {
        echo '<p>' . _('TellOP was unable to get the list of applications you authorized to access your account.') . '</p>';
    } else {
        /** @noinspection PhpUndefinedVariableInspection */
        if (count($userapps) == 0) {
            echo '<p>' . _('You did not authorize any applications to access your account.') . '</p>';
        } else {
            echo '<p>' . _('The following applications and Web sites are authorized'
                    . ' to access your account. Click on <strong>View permissions</strong>'
                    . ' next to the application name to view the tasks it can perform'
                    . ' on TellOP, or click on <strong>Revoke access</strong> to prevent'
                    . ' it from performing such actions until you authorize it again.')
                . '</p>';
            ?>
    <table class="table">
        <tbody>
            <?php foreach ($userapps as $app) { ?>
            <tr>
                <td width="50px">
                    <img src="<?php if (file_exists(__DIR__ . '/../../usercontent/clients/' . $app['client_id'] . '.png')) {
                        echo '/usercontent/clients/' . $app['client_id'] . '.png';
                    } else {
                        echo '/images/genericappicon.png';
                    }
                    ?>" width="48" height="48" alt="Application icon">
                </td>
                <td><strong><?php echo htmlspecialchars($app['app_name']); ?></strong><br><?php echo htmlspecialchars($app['displayname']); ?></td>
                <td>
                    <div class="modal fade" id="permissions-<?php echo $app['client_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="permissionslabel-<?php echo $app['client_id']; ?>">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _('Close'); ?>"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="permissionslabel-<?php echo $app['client_id']; ?>"><?php echo sprintf(_('%s permissions'), htmlspecialchars($app['displayname'])); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <p><?php echo sprintf(_('<strong>%s</strong> is authorized to perform the following actions:'), htmlspecialchars($app['displayname'])); ?></p>
                                    <ul class="list-icon list-glyphicon">
                                        <?php
                                        foreach (explode(' ', $app['scope']) as $scope) {
                                            switch ($scope) {
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
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo _('Close'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="revoke-<?php echo $app['client_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="revokelabel-<?php echo $app['client_id']; ?>">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _('Close'); ?>"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="revokelabel-<?php echo $app['client_id']; ?>"><?php echo sprintf(_('Revoke access for %s'), htmlspecialchars($app['displayname'])); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <p><?php echo sprintf(_('Are you sure you want to revoke access to <strong>%s</strong>?'), htmlspecialchars($app['displayname'])); ?></p>
                                    <p><?php echo sprintf(_('Note that this action will <em>not</em> delete any data already stored by <strong>%s</strong>. To do so, please contact its author.'), htmlspecialchars($app['displayname'])); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel'); ?></button>
                                    <!--suppress HtmlUnknownTarget -->
                                    <form action="/applications" method="post" class="form-inline frm-inline">
                                        <input type="hidden" name="appid" value="<?php echo $app['client_id']; ?>">
                                        <input type="hidden" name="csrftoken" value="<?php /** @noinspection PhpUndefinedVariableInspection */
                                        echo $csrftoken; ?>">
                                        <button type="submit" class="btn btn-danger"><?php echo _('Revoke access'); ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#permissions-<?php echo $app['client_id']; ?>"><?php echo _('View permissions'); ?></button>&nbsp;
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#revoke-<?php echo $app['client_id']; ?>"><?php echo _('Revoke access'); ?></button>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
<?php   }
    } ?>
</div>
<?php include 'footer.php'; ?>
