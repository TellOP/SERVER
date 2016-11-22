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

// Note for developers: do not include the Android brand name as this would
// require approval by Google.
// Make sure to comply with all badge display guidelines:
// - Android: https://play.google.com/intl/en_us/badges/
include 'header.php'; ?>
<div class="container">
    <h1><?php echo _('Download the Tell-OP app to get started'); ?></h1>
    <p><?php echo _('Thanks for registering an account with us! Download our mobile app and log in to start improving your language skills.'); ?></p>
    <div class="row">
        <div class="col-md-4 vcenter text-center">
            <?php
            // Translators: please use the Google Play badge page at https://play.google.com/intl/en_us/badges/ to generate the markup for the "Get it on Google Play" badge, then add the "width" and "height" attributes
            echo _('<a href="https://play.google.com/store/apps/details?id=es.um.tellop&amp;utm_source=global_co&amp;utm_medium=prtnr&amp;utm_content=Mar2515&amp;utm_campaign=PartBadge&amp;pcampaignid=MKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1"><img alt="Get it on Google Play" src="https://play.google.com/intl/en_us/badges/images/generic/en_badge_web_generic.png" width="118" height="46" /></a>');
            ?>
        </div><!--
        --><div class="col-md-8 vcenter text-center">
            <?php
            echo _('Apps for other mobile phones will be coming soon!');
            ?>
        </div>
    </div>
    <h2><?php echo _('Important notes'); ?></h2>
    <p><?php echo _('The app is in the <em>beta</em> phase, which means it is not fully finished. Additionally, you might encounter crashes or unexpected errors.'); ?></p>
    <p><strong><?php echo _('Make sure to backup your phone regularly while using this app.'); ?></strong></p>
    <p><?php echo _('We will automatically collect usage data and error reports to fix any bugs and improve your experience.'); ?></p>
    <p><?php echo _('Please report any issues you find by following <a href="https://github.com/TellOP/APP/wiki/Beta-testing">the beta testing instructions on our GitHub project</a>.'); ?></p>
    <p class="text-muted small">
    <?php
    // Translators: this attribution must be the one given by the badge generator at https://play.google.com/intl/en_us/badges/ for your language
    echo _('Google Play and the Google Play logo are trademarks of Google Inc.');
    ?>
    </p>
</div>
<?php include 'footer.php'; ?>
