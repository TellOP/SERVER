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
namespace TellOP\Controllers;

/**
 * "Forgot your password?" page controller.
 * @package TellOP
 */
class ForgotPasswordPageController implements IController {
    /**
     * Sends a confirmation e-mail.
     * @param \TellOP\Application $appObject Application object.
     * @param string $secrettoken Secret token.
     * @param string $displayname Receiver name.
     */
    private function sendConfirmation($appObject, $secrettoken, $displayname) {
        // Send the confirmation e-mail
        $appConfig = $appObject->getConfig();
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject(_('TellOP password recovery'))
                ->setFrom(array($appConfig['email']['fromaddress'] => _('TellOP')))
                // FIXME: filter the display name here if needed!
                ->setTo(array($_POST['email'] => $displayname))
                ->setBody(sprintf(_("Hi %s,\nsomeone has requested a password "
                    . "reset at Tell-OP.\n\nTo choose a new password, please "
                    . "click on the following link:\n%s\n\nIf you did not "
                    . "request a password reset, simply do nothing.\n\nTell-OP"),
                    $displayname, 'https://' . $_SERVER['SERVER_NAME']
                    . '/passwordreset?token=' . $secrettoken), 'text/plain',
                    'UTF-8');
            $mailtransport = \Swift_SmtpTransport::newInstance(
                $appConfig['email']['host'], $appConfig['email']['port'])
                ->setUsername($appConfig['email']['username'])
                ->setPassword($appConfig['email']['password']);
            if ($appConfig['email']['encryption'] != '') {
                $mailtransport->setEncryption($appConfig['email']['encryption']);
            }
            $mailer = \Swift_Mailer::newInstance($mailtransport);
            // TODO: check
            $mailer->send($message);
            \Flight::render('ForgotPasswordPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'locale' => $appObject->getLocale(),
                'emailsent' => true));
        } catch (\Swift_TransportException $e) {
            \Flight::render('ForgotPasswordPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'locale' => $appObject->getLocale(),
                'emailsenderror' => true));
        }
    }

    /**
     * Displays the "Forgot password" page and/or resets the user's password.
     * @param \TellOP\Application $appObject Application object.
     */
    public function displayPage($appObject) {
        if ($_SESSION['username'] != NULL) {
            \Flight::redirect('/dashboard');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Perform form validation
            if (!isset($_POST['email'])) {
                $emailerror = true;
            } else {
                $emailerror = (filter_var($_POST['email'],
                        FILTER_VALIDATE_EMAIL) === FALSE);
            }
            if ($emailerror) {
                \Flight::render('ForgotPasswordPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'emailerror' => $emailerror,
                    'emailaddress' => (isset($_POST['email']) ? $_POST['email']
                        : '')));
                return;
            }
            // Check for previous login attempts from the same IP
            $apppdo = $appObject->getApplicationPDO();
            $delblocks = $apppdo->prepare('DELETE FROM ipblock WHERE '
                . 'expire < NOW()');
            $delblocks->execute();
            $ipblockstmt = $apppdo->prepare('SELECT tries FROM '
                . 'ipblock WHERE ip = ? LIMIT 1');
            $ipblocks = $ipblockstmt->execute(array($_SERVER['REMOTE_ADDR']));
            if ($ipblocks && $ipblockstmt->rowCount() > 0) {
                $tryrec = $ipblockstmt->fetch(\PDO::FETCH_ASSOC);
                $numtries = (int) $tryrec['tries'];
                if ($numtries >= 3) {
                    \Flight::render('ForgotPasswordPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'iplocked' => true,
                        'emailaddress' => $_POST['email']));
                }
            }
            $credentialsstmt = $apppdo->prepare('SELECT accountstatus, '
                . 'displayname FROM users WHERE email = ?');
            if (!($credentialsstmt->execute(array($_POST['email'])))) {
                \Flight::render('ForgotPasswordPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'internalerror' => true,
                    'emailaddress' => $_POST['email']));
                return;
            }
            switch ($credentialsstmt->rowCount()) {
                case 1:
                    $userrecord = $credentialsstmt->fetch(\PDO::FETCH_ASSOC);
                    $userdisplayname = $userrecord['displayname'];
                    break;
                default:
                    $updateblockstmt = $apppdo->prepare('UPDATE ipblock SET '
                        . 'tries = tries + 1 WHERE ip = ?');
                    $updateblockstmt->execute(array($_SERVER['REMOTE_ADDR']));
                    \Flight::render('ForgotPasswordPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'wrongcredentials' => true,
                        'emailaddress' => $_POST['email']));
                    return;
            }
            $credentials = $credentialsstmt->fetch(\PDO::FETCH_ASSOC);
            // Check account status
            switch ($credentials['accountstatus']) {
                case 0: /* Waiting for e-mail confirmation */
                    \Flight::render('ForgotPasswordPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'waitingforconfirmation' => true,
                        'emailaddress' => $_POST['email']));
                    return;
                case 3: /* Waiting for e-mail change confirmation */
                    // We assume the e-mail change request must be cancelled
                case 1: /* Regular account */
                case 4: /* Waiting for a password reset */
                    // Generate the secret token
                    $secrettoken = $appObject->generateToken();
                    $secrettokenstmt = $apppdo->prepare('UPDATE users SET '
                        . 'secrettoken = ?, secrettokenexpire = NOW() + '
                        . 'INTERVAL 10 MINUTE, accountstatus = 4 WHERE '
                        . 'email = ?');
                    $secrettokenstmt->execute(array($secrettoken,
                        $_POST['email']));
                    $this->sendConfirmation($appObject, $secrettoken,
                        $userdisplayname);
                    return;
                case 2: /* Account locked */
                    \Flight::render('ForgotPasswordPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'accountlocked' => true,
                        'emailaddress' => $_POST['email']));
                    return;
                default:
                    \Flight::render('ForgotPasswordPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'internalerror' => true,
                        'emailaddress' => $_POST['email']));
                    break;
            }
        } else {
            \Flight::render('ForgotPasswordPage', array(
                'csrftoken' => $appObject->getCSRFToken()));
        }
    }
}
