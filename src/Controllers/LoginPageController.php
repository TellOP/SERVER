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
 * Login page controller.
 * @package TellOP
 */
class LoginPageController implements IController {
    /**
     * Updates the IP block list and displays an error message if the
     * credentials given by the user are wrong.
     * @param \TellOP\Application $appObject Application object.
     * @param bool $oauth2req The result of the OAuth 2.0 request check.
     * @param string|null $response_type The OAuth 2.0 response type.
     */
    private function showCredentialError($appObject, $oauth2req, $response_type) {
        $logger = $appObject->getApplicationLogger();
        $logger->addInfo('A user with a blocked IP tried to log in with a wrong'
            . ' set of credentials.',  array('ip' => $_SERVER['SERVER_ADDR'],
            'username' => (isset($_POST['email']) ? $_POST['email'] : 'unknown')));
        $apppdo = $appObject->getApplicationPDO();
        $checkipblockstmt = $apppdo->prepare('SELECT * FROM ipblock WHERE ip ='
            . ' ?');
        $checkipblockstmtres = $checkipblockstmt->execute(array(
            $_SERVER['REMOTE_ADDR']));
        if (!$checkipblockstmtres) {
            $logger->addWarning('Unable to check if the IP is present in the '
                . 'ipblock table. The table will not be updated.',
                array('ip' => $_SERVER['REMOTE_ADDR']));
        } else {
            if ($checkipblockstmt->rowCount() == 0) {
                $updateSQL = 'INSERT INTO ipblock (ip, tries, expire) VALUES '
                    . '(?, 1, NOW() + INTERVAL 3 HOUR)';
            } else {
                $updateSQL = 'UPDATE ipblock SET tries = tries + 1, '
                    . 'expire = NOW() + INTERVAL 3 HOUR WHERE ip = ?';
            }
            $checkipblockstmt->closeCursor();
            $updateblockstmt = $apppdo->prepare($updateSQL);
            if (!$updateblockstmt->execute(array($_SERVER['REMOTE_ADDR']))) {
                $logger->addWarning('Unable to add/update the client IP in the '
                    . 'ipblock table. The table will not be updated.',
                    array('ip' => $_SERVER['REMOTE_ADDR']));
            } else {
                $logger->addInfo('Added/updated the client IP in the '
                    . 'ipblock table.', array('ip' => $_SERVER['REMOTE_ADDR']));
            }
            $updateblockstmt->closeCursor();
        }
        \Flight::render('LoginPage', array(
            'csrftoken' => $appObject->getCSRFToken(),
            'wrongcredentials' => true,
            'emailaddress' => (isset($_POST['email']) ?
                $_POST['email'] : NULL),
            'rememberme' => isset($_POST['rememberme']),
            'oauth2req' => $oauth2req,
            'response_type' => (isset($response_type) ? $response_type : NULL),
            'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
            'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
            'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
            'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
            ));
    }

    /**
     * Displays the login page and/or logs the user in.
     * @param $appObject \TellOP\Application Application object.
     */
    public function displayPage($appObject) {
        $logger = $appObject->getApplicationLogger();
        if ($_SESSION['username'] != NULL) {
            \Flight::redirect('/');
        }
        // Check if this is an "ordinary" login call or if we are being called
        // because we need to authenticate the user before letting them approve
        // an OAuth 2.0 request
        $oauth2req = (isset($_GET['response_type']) && isset($_GET['client_id']));
        if (isset($_GET['response_type'])) {
            if ($_GET['response_type'] == 'code' || $_GET['response_type'] ==
                'token') {
                $response_type = $_GET['response_type'];
            } else {
                $response_type = 'token';
            }
        } else {
            $response_type = 'token';
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Perform form validation
            if (!isset($_POST['email'])) {
                $emailerror = true;
            } else {
                $emailerror = (filter_var($_POST['email'],
                    FILTER_VALIDATE_EMAIL) === FALSE);
            }
            $missingpassword = (!isset($_POST['password'])
                || ($_POST['password'] == ''));
            if ($emailerror || $missingpassword) {
                \Flight::render('LoginPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'emailerror' => $emailerror,
                    'missingpassword' => $missingpassword,
                    'emailaddress' => (isset($_POST['email']) ?
                        $_POST['email'] : NULL),
                    'rememberme' => isset($_POST['rememberme']),
                    'oauth2req' => $oauth2req,
                    'response_type' => (isset($response_type) ? $response_type : NULL),
                    'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                    'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                    'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                    'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                ));
                return;
            }
            // Check for previous login attempts from the same IP
            $apppdo = $appObject->getApplicationPDO();
            $delblocks = $apppdo->prepare('DELETE FROM ipblock WHERE '
                . 'expire < NOW()');
            $delblocks->execute();
            $delblocks->closeCursor();
            $ipblockstmt = $apppdo->prepare('SELECT tries FROM '
                . 'ipblock WHERE ip = ? LIMIT 1');
            $ipblocks = $ipblockstmt->execute(array($_SERVER['REMOTE_ADDR']));
            if ($ipblocks && $ipblockstmt->rowCount() > 0) {
                $tryrec = $ipblockstmt->fetch(\PDO::FETCH_ASSOC);
                if ($tryrec != FALSE) {
                    $numtries = (int) $tryrec['tries'];
                    if ($numtries >= 3) {
                        $ipblockstmt->closeCursor();
                        $logger->addInfo('A user has tried to log in for '
                            . 'three times or more from an IP in the ipblock '
                            . 'table. Showing the IP blocked page.',
                            array('ip' => $_SERVER['REMOTE_ADDR']));
                        \Flight::render('LoginPage', array(
                            'csrftoken' => $appObject->getCSRFToken(),
                            'iplocked' => true,
                            'emailaddress' => $_POST['email'],
                            'rememberme' => isset($_POST['rememberme']),
                            'oauth2req' => $oauth2req,
                            'response_type' => (isset($response_type) ? $response_type : NULL),
                            'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                            'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                            'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                            'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                            ));
                        return;
                    }
                }
            }
            $ipblockstmt->closeCursor();
            // Check credentials
            $credentialsstmt = $apppdo->prepare('SELECT accountstatus, password,'
                . ' locale FROM users WHERE email = ?');
            if (!($credentialsstmt->execute(array($_POST['email'])))) {
                $logger->addError('The login page is unable to determine all '
                    . 'accounts matching the specified e-mail address.',
                    array('username' => $_POST['email']));
                \Flight::render('LoginPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'internalerror' => true,
                    'emailaddress' => $_POST['email'],
                    'rememberme' => isset($_POST['rememberme']),
                    'oauth2req' => $oauth2req,
                    'response_type' => (isset($response_type) ? $response_type : NULL),
                    'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                    'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                    'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                    'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                ));
                return;
            }
            switch ($credentialsstmt->rowCount()) {
                case 1:
                    break;
                default:
                    $this->showCredentialError($appObject, $oauth2req,
                        $response_type);
                    return;
            }
            // Check the password
            $credentials = $credentialsstmt->fetch(\PDO::FETCH_ASSOC);
            $credentialsstmt->closeCursor();
            if ($credentials === FALSE) {
                $logger->addError('The login page is unable to fetch all '
                    . 'accounts matching the specified e-mail address.',
                    array('username' => $_POST['email']));
                \Flight::render('LoginPage', array(
                    'csrftoken' => $appObject->getCSRFToken(),
                    'internalerror' => true,
                    'emailaddress' => $_POST['email'],
                    'rememberme' => isset($_POST['rememberme']),
                    'oauth2req' => $oauth2req,
                    'response_type' => (isset($response_type) ? $response_type : NULL),
                    'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                    'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                    'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                    'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                    ));
                return;
            }
            if (!password_verify($_POST['password'], $credentials['password'])) {
                $this->showCredentialError($appObject, $oauth2req, $response_type);
                return;
            }
            // Check account status
            switch ($credentials['accountstatus']) {
                case 0: /* Waiting for e-mail confirmation */
                    $logger->addInfo('A user tried to log in with an account '
                        . 'having an e-mail address that needs to be confirmed. '
                        . 'Displaying a message asking to complete registration '
                        . 'first.', array('username' => $_POST['email']));
                    \Flight::render('LoginPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'waitingforconfirmation' => true,
                        'emailaddress' => $_POST['email'],
                        'rememberme' => isset($_POST['rememberme']),
                        'oauth2req' => $oauth2req,
                        'response_type' => (isset($response_type) ? $response_type : NULL),
                        'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                        'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                        'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                        'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                    ));
                    return;
                case 1: /* Regular account */
                case 3: /* Waiting for e-mail change confirmation */
                    break;
                case 2: /* Account locked */
                    $logger->addInfo('User ' . $_POST['email'] . ' was trying '
                        . 'to log in, but the account was blocked');
                    \Flight::render('LoginPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'accountlocked' => true,
                        'emailaddress' => $_POST['email'],
                        'rememberme' => isset($_POST['rememberme']),
                        'oauth2req' => $oauth2req,
                        'response_type' => (isset($response_type) ? $response_type : NULL),
                        'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                        'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                        'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                        'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                    ));
                    return;
                case 4: /* Waiting for a password reset */
                    // We assume the password reset request must be cancelled
                    // since the user has proved to know the old one now
                    $pwcancstmt = $apppdo->prepare('UPDATE users SET '
                        . 'secrettoken = NULL, secrettokenexpire = NULL, '
                        . 'accountstatus = 1 WHERE email = ?');
                    if ($pwcancstmt->execute(array($_POST['email'])) === FALSE) {
                        $logger->addWarning('The user is about to log in, but '
                            . 'the system was unable to cancel the pending '
                            . 'password change request',
                            array('username' => $_POST['email']));
                    } else {
                        $logger->addInfo('The user is about to log in, '
                            . 'cancelled password change request',
                            array('username' => $_POST['email']));
                    }
                    break;
                default:
                    \Flight::render('LoginPage', array(
                        'csrftoken' => $appObject->getCSRFToken(),
                        'internalerror' => true,
                        'emailaddress' => (isset($_POST['email']) ?
                            $_POST['email'] : NULL),
                        'rememberme' => isset($_POST['rememberme']),
                        'oauth2req' => $oauth2req,
                        'response_type' => (isset($response_type) ? $response_type : NULL),
                        'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                        'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                        'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                        'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                    ));
                    break;
            }
            // Log in successful, store the username, rehash the password if
            // needed and redirect to the dashboard
            //$logger->addInfo('User logging in.', array('username' => $_POST['email']));
            $_SESSION['username'] = $_POST['email'];
            $_SESSION['language'] = $credentials['locale'];
            session_regenerate_id(true);
            if (password_needs_rehash($credentials['password'], PASSWORD_DEFAULT)) {
                $pwrehashstmt = $apppdo->prepare('UPDATE users SET '
                    . 'password = ? WHERE email = ?');
                if (!$pwrehashstmt->execute(array(password_hash($_POST['password'],
                    PASSWORD_DEFAULT), $_POST['email']))) {
                    $logger->addWarning('Unable to rehash password.',
                        array('username' => $_POST['email']));
                } else {
                    $logger->addInfo('Password rehashed.',
                        array('username' => $_POST['email']));
                }
            }
            // Reset the IP block
            $ipblockrststmt = $apppdo->prepare('DELETE FROM '
                . 'ipblock WHERE ip = ? LIMIT 1');
            $ipblockrststmt->execute(array($_SERVER['REMOTE_ADDR']));
            $ipblockrststmt->closeCursor();
            // Set or delete the "Remember me" cookie
            if (isset($_POST['rememberme'])) {
                $remembermetoken = $appObject->generateToken();
                $remembermestatement = $apppdo->prepare('UPDATE'
                    . ' users SET remembermetoken = ? WHERE email = ?');
                $remembermestatement->execute(array(
                    $remembermetoken, $_POST['email']));
                setcookie('rememberme', $remembermetoken, time() + 2592000, '/',
                    $_SERVER['SERVER_NAME'], true, true);
            } else {
                if (isset($_COOKIE['rememberme'])) {
                    setcookie('rememberme', '', time() - 3600, '/',
                        $_SERVER['SERVER_NAME'], true, true);
                }
            }
            // Perform the appropriate redirect
            if ($oauth2req) {
                $redirecturl = 'https://' . $_SERVER['SERVER_NAME'] . '/oauth/'
                    . '2/authorize?response_type=' . $response_type
                    . '&client_id=' . urlencode($_GET['client_id']);
                if (isset($_GET['redirect_uri'])) {
                    $redirecturl .= '&redirect_uri=' . urlencode($_GET['redirect_uri']);
                }
                if (isset($_GET['scope'])) {
                    $redirecturl .= '&scope=' . urlencode($_GET['scope']);
                }
                if (isset($_GET['state'])) {
                    $redirecturl .= '&state=' . urlencode($_GET['state']);
                }
                \Flight::redirect($redirecturl);
            } else {
                \Flight::redirect('https://' . $_SERVER['SERVER_NAME'] . '/dashboard');
            }
        } else {
            \Flight::render('LoginPage', array(
                'csrftoken' => $appObject->getCSRFToken(),
                'oauth2req' => $oauth2req,
                'response_type' => (isset($response_type) ? $response_type : NULL),
                'client_id' => (isset($_GET['client_id']) ? $_GET['client_id'] : NULL),
                'redirect_uri' => (isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : NULL),
                'scope' => (isset($_GET['scope']) ? $_GET['scope'] : NULL),
                'state' => (isset($_GET['state']) ? $_GET['state'] : NULL)
                ));
        }
    }
}
