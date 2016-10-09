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
$additionalincludes = array(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TELL-OP</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <link href="/css/flags.min.css" rel="stylesheet">
    <link hreflang="en-US" href="/locale/en_US/tellop.json" type="application/vnd.oftn.l10n+json" rel="localization" />
    <link rel="apple-touch-icon" sizes="76x76" href="/images/apple-touch-icon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/images/apple-touch-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/apple-touch-icon-152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/images/apple-touch-icon-167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon-180.png">
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only"><?php echo _('Toggle navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php if ($_SESSION['username'] === NULL) { ?>/<?php } else { ?>/dashboard<?php } ?>"><img alt="<?php echo _('TellOP logo'); ?>" width="20" height="20" src="/images/navbar-logo.png"></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php
                    switch ($_SESSION['language']) {
                        case 'en_US':
                            echo 'English';
                            break;
                        case 'it_IT':
                            echo 'Italiano';
                            break;
                        default:
                            // Do not localize
                            echo 'Change language';
                            break;
                    } ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu f16">
                        <li><!--suppress HtmlUnknownTarget -->
                            <a href="/setlang?en_US"><i class="flag gb"></i> English</a></li>
                        <li><!--suppress HtmlUnknownTarget -->
                            <a href="/setlang?it_IT"><i class="flag it"></i> Italiano</a></li>
                    </ul>
                </li>
                <?php if ($_SESSION['username'] === NULL) { ?>
                    <li><!--suppress HtmlUnknownTarget -->
                        <a href="/login"><?php echo _('Log in/Sign up'); ?></a></li>
                <?php } else { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo htmlspecialchars($_SESSION['username']); ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><!--suppress HtmlUnknownTarget -->
                            <a href="/profile"><?php echo _('My profile'); ?></a></li>
                        <li><!--suppress HtmlUnknownTarget -->
                            <a href="/applications"><?php echo _('Authorized applications'); ?></a></li>
                        <li role="separator" class="divider"></li>
                        <li><!--suppress HtmlUnknownTarget -->
                            <a href="/logout"><?php echo _('Log out'); ?></a></li>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Warn the user if cookies and/or scripts are disabled -->
<div class="container col-md-8 col-md-offset-2 hidden" id="cookiewarning">
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo _('Close'); ?>"><span aria-hidden="true">&times;</span></button>
        <span class="glyphicon glyphicon-alert" aria-hidden="true"></span> <strong><?php echo _('Cookies are not enabled'); ?></strong><br />
        <?php echo _('Your browser does not accept cookies, which means this site might not function correctly. Please enable them and reload this page.'); ?>
    </div>
</div>
<noscript>
    <div class="container col-md-8 col-md-offset-2">
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo _('Close'); ?>"><span aria-hidden="true">&times;</span></button>
            <span class="glyphicon glyphicon-alert" aria-hidden="true"></span> <strong><?php echo _('JavaScript is not enabled'); ?></strong><br />
            <?php echo _('Your browser does not support JavaScript or it was turned off, which means this site might not function correctly. Please enable it and reload this page.'); ?>
        </div>
    </div>
</noscript>
