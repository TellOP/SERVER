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
 * Registration page controller.
 * @package TellOP
 */
class RegistrationPageController {
    /**
     * Sends a confirmation e-mail.
     * @param \TellOP\Application $appObject Application object.
     * @param string $secrettoken Secret token.
     */
    private function sendConfirmation($appObject, $secrettoken) {
        // Send the confirmation e-mail
        $appConfig = $appObject->getConfig();
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject(_('TellOP account confirmation'))
                ->setFrom(array($appConfig['email']['fromaddress'] => _('TellOP')))
                // FIXME: filter the display name here if needed!
                ->setTo(array($_POST['email'] => $_POST['displayname']))
                ->setBody(sprintf(_("Hi %s,\nan account was registered in your "
                    . "name at Tell-OP.\n\nTo confirm your e-mail address, "
                    . "please click on the following link:\n%s\n\nIf you did "
                    . "not register this account, simply do nothing and it "
                    . "will be automatically deleted after one day.\n\nTell-OP"),
                    $_POST['displayname'], 'https://' . $_SERVER['SERVER_NAME']
                    . '/verifyaccount?token=' . $secrettoken), 'text/plain',
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
            \Flight::render('RegistrationPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'locale' => $appObject->getLocale(),
                'emailsent' => true));
        } catch (\Swift_TransportException $e) {
            \Flight::render('RegistrationPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'locale' => $appObject->getLocale(),
                'emailsenderror' => true));
        }
    }

    /**
     * Registers a new user.
     * @param $appObject \TellOP\Application Application object.
     */
    public function displayPage($appObject) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emailerror = (!isset($_POST['email'])) || (filter_var($_POST['email'],
                FILTER_VALIDATE_EMAIL) === FALSE);
            $emailconfirmerror = (!isset($_POST['emailconfirm'])) ||
                ($_POST['email'] != $_POST['emailconfirm']);
            $missingpassword = (!isset($_POST['password'])) ||
                ($_POST['password'] == '');
            $confirmpassworderror = (!isset($_POST['passwordconfirm'])) ||
                ($_POST['password'] != $_POST['passwordconfirm']) ||
                ($_POST['password'] == '');
            $titleerror = (isset($_POST['title']) && strlen($_POST['title']) > 50);
            $displaynameerror = (!isset($_POST['displayname'])) ||
                (strlen($_POST['displayname']) > 250) ||
                ($_POST['displayname'] == '');
            $langlevelerror = (!isset($_POST['languagelevel']) ||
                ($_POST['languagelevel'] != 'A1'
                    && $_POST['languagelevel'] != 'A2'
                    && $_POST['languagelevel'] != 'B1'
                    && $_POST['languagelevel'] != 'B2'
                    && $_POST['languagelevel'] != 'C1'
                    && $_POST['languagelevel'] != 'C2'));
            if ($emailerror || $emailconfirmerror || $missingpassword ||
                $confirmpassworderror || $titleerror || $displaynameerror ||
                $langlevelerror) {
                \Flight::render('RegistrationPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'emailerror' => $emailerror,
                    'emailconfirmerror' => $emailconfirmerror,
                    'missingpassword' => $missingpassword,
                    'confirmpassworderror' => $confirmpassworderror,
                    'titleerror' => $titleerror,
                    'displaynameerror' => $displaynameerror,
                    'langlevelerror' => $langlevelerror,
                    'locale' => $appObject->getLocale()));
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
                    \Flight::render('RegistrationPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'iplocked' => true,
                        'locale' => $appObject->getLocale()));
                    return;
                }
            }
            // E-mail must not exist (as email or newemail)
            $checkemailstmt = $apppdo->prepare('SELECT accountstatus FROM '
                . 'users WHERE email = ? OR newemail = ?');
            $checkemail = $checkemailstmt->execute(array($_POST['email'],
                $_POST['email']));
            if ($checkemail && $checkemailstmt->rowCount() > 0) {
                // Resend the confirmation e-mail if the account is newly
                // registered and waiting for confirmation.
                if ($checkemailstmt->rowCount() == 1) {
                    $checkemailrcrd = $checkemailstmt->fetch(\PDO::FETCH_ASSOC);
                    if ($checkemailrcrd['accountstatus'] === 0) {
                        $secrettoken = $appObject->generateToken();
                        $gettokenstmt = $apppdo->prepare('UPDATE users SET '
                            . 'secrettoken = ?, secrettokenexpire = NOW() + '
                            . 'INTERVAL 1 DAY WHERE email = ?');
                        $gettokenstmt->execute(array($secrettoken, $_POST['email']));
                        $this::sendConfirmation($appObject, $secrettoken);
                        return;
                    }
                }
                \Flight::render('RegistrationPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'emailexisting' => true,
                    'locale' => $appObject->getLocale()));
                return;
            }
            // Register the user
            $secrettoken = $appObject->generateToken();
            $registerstmt = $apppdo->prepare('INSERT INTO users '
                . '(email, password, locale, title, displayname, secrettoken, '
                . 'secrettokenexpire, accountstatus, languagelevel) VALUES '
                . '(?, ?, ?, ?, ?, ?, NOW() + INTERVAL 1 DAY, 0, ?)');
            $registerstmt->execute(array($_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_SESSION['language'], $_POST['title'], $_POST['displayname'],
                $secrettoken, $_POST['languagelevel']));
            // Send the confirmation e-mail
            $this::sendConfirmation($appObject, $secrettoken);
            \Flight::render('RegistrationPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'emailsent' => true,
                'locale' => $appObject->getLocale()));
        } else {
            \Flight::render('RegistrationPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'locale' => $appObject->getLocale()));
        }
    }
}
