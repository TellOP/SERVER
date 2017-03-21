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
namespace TellOP;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Main application class.
 * @package TellOP
 */
class Application {
    /**
     * Application configuration.
     */
    private $config;
    /**
     * Application locale.
     */
    private $locale;
    /**
     * Application log.
     */
    private $applicationLogger;
    /**
     * API usage log.
     */
    private $apiLogger;
    /**
     * Database connection.
     */
    private $dbh;

    /**
     * Application constructor.
     * @param mixed[] $cnf Application configuration.
     */
    function __construct($cnf) {
        $this->config = $cnf;
    }

    /**
     * Gets the application PDO object.
     * @return \PDO The application PDO object.
     */
    public function getApplicationPDO() {
        return $this->dbh;
    }

    /**
     * Return the application logger.
     * @return Logger The application logger.
     */
    public function getApplicationLogger() {
        return $this->applicationLogger;
    }

    /**
     * Return the API logger.
     * @return Logger The API logger.
     */
    public function getAPILogger() {
        return $this->apiLogger;
    }

    /**
     * Return the application configuration.
     * @return mixed[] The application configuration.
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Return the application locale.
     * @return string The application locale.
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Generates a new random token.
     * @return string A random token.
     */
    public function generateToken() {
        $token = '';
        for ($i = 0; $i < 64; ++$i) {
            if (function_exists('random_int')) {
                $chr = random_int(1, 16);
            } else {
                $chr = mt_rand(1, 16);
            }
            $token .= dechex($chr);
        }
        return $token;
    }

    /**
     * Gets a new CSRF token.
     * @return string A new CSRF token.
     */
    public function getCSRFToken() {
        if(!isset($_SESSION['csrftoken'])) {
            $_SESSION['csrftoken'] = array();
        }
        $token = $this->generateToken();
        array_push($_SESSION['csrftoken'], $token);
        return $token;
    }

    /**
     * Initialize the application.
     */
    public function run() {
        // Initialize logging
        $logStream = new StreamHandler($this->config['log']['logPath'],
            $this->config['log']['minLogLevel']);

        $this->applicationLogger = new Logger('application');
        $this->applicationLogger->pushHandler($logStream);
        $this->apiLogger = new Logger('api');
        $this->apiLogger->pushHandler($logStream);

        // Establish the database connection.
        try {
            $this->dbh = new \PDO('mysql:host='
                . $this->config['database']['host']
                . ';dbname=' . $this->config['database']['dbname'],
                $this->config['database']['dbuser'],
                $this->config['database']['dbpassword'],
                array(\PDO::ATTR_PERSISTENT => true)
            );
        } catch (\PDOException $e) {
            exit('The application is unable to establish a database connection.');
        }

        // Start a new session or resume the existing one.
        if (session_status() === PHP_SESSION_DISABLED) {
            exit('PHP sessions are disabled. Please enable them and execute'
                . 'this application again');
        }
        // Set the session cookie parameters - we do not use the options
        // parameter in session_start because it's only supported in PHP 7+
        session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME'], true, true);
        @ini_set('session.name', 'session');
        @ini_set('session.use_strict_mode', 1);
        @ini_set('session.use_cookies', 1);
        @ini_set('session.use_only_cookies', 1);
        session_start();

        if (!isset($_SESSION['username'])) {
            $_SESSION['username'] = NULL;
            // Check for a "Remember me" token and log the user in if needed
            if (isset($_COOKIE['rememberme'])) {
                $remembermestatement = $this->dbh->prepare('SELECT'
                    . ' email FROM users WHERE remembermetoken = ?');
                $rememberresult = $remembermestatement->execute(array(
                    $_COOKIE['rememberme']));
                if ($rememberresult && $remembermestatement->rowCount() == 1) {
                    $userrow = $remembermestatement->fetch(\PDO::FETCH_ASSOC);
                    $_SESSION['username'] = $userrow['email'];
                }
            }
        }

        // Bind the Gettext domain to get translated strings.
        if (isset($_SESSION['language'])) {
            $this->locale = $_SESSION['language'];
        } else {
            $this->locale = 'en_US';
            $_SESSION['language'] = 'en_US';
        }
        include 'BindGettext.php';
        bindGettext($this->locale);

        // Check the CSRF token on non-API pages.
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && substr($_SERVER['REQUEST_URI'], 0, 5) !== '/api/') {
            if (!(isset($_POST['csrftoken']) && isset($_SESSION['csrftoken']))) {
                header('HTTP/1.0 403 Forbidden');
                exit('Cross site request forgery detected');
            }
            $tokenfound = false;
            foreach ($_SESSION['csrftoken'] as $key => $value) {
                if ($value === $_POST['csrftoken']) {
                    $tokenfound = true;
                    // Clear all older tokens and the current one
                    foreach ($_SESSION['csrftoken'] as $inkey => $invalue) {
                        array_shift($_SESSION['csrftoken']);
                        if ($value === $invalue) {
                            break;
                        }
                    }
                }
            }
            if (!$tokenfound) {
                header('HTTP/1.0 403 Forbidden');
                exit('Cross site request forgery detected');
            }
        }

        // Output security headers.
        // Assume all resources are loaded only from the same domain.
        header('Content-Security-Policy: default-src \'self\'; img-src \'self\' play.google.com');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=15552000');
        header('Public-Key-Pins: ' . $this->config['security']['publicKeyPIN']);

        // Initialize the Flight framework, but only for user-facing pages.
        \Flight::set('flight.views.path', __DIR__ . '/Models');
        $neatRequestUri = strtok($_SERVER['REQUEST_URI'], '?');
        if ($neatRequestUri == '/oauth/2/token') {
            (new Controllers\OAuth2TokenController)->displayPage($this);
        } elseif ($neatRequestUri == '/oauth/2/authorize') {
            (new Controllers\OAuth2AuthorizeController)->displayPage($this);
        } elseif ($neatRequestUri == '/oauth/2/success') {
            (new Controllers\OAuth2SuccessController)->displayPage($this);
        } elseif ($neatRequestUri == '/oauth/2/introspection') {
            (new Controllers\OAuth2IntrospectionController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/app/exercise') {
            (new Controllers\ExerciseController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/app/featured') {
            (new Controllers\FeaturedController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/app/featurednotdone') {
            (new Controllers\FeaturedNotDoneController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/app/history') {
            (new Controllers\HistoryController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/app/profile') {
            (new Controllers\APIProfileController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/app/tips') {
            (new Controllers\TipsController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/adelex') {
            (new Controllers\AdelexController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/collins') {
            (new Controllers\CollinsEnglishDictionaryController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/collinsgetentry') {
            (new Controllers\CollinsEnglishDictionaryGetEntryController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/lextutor') {
            (new Controllers\LexTutorController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/netspeakfollowing') {
            (new Controllers\NetspeakFollowingController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/netspeakpreceding') {
            (new Controllers\NetspeakPrecedingController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/stringnet') {
            (new Controllers\StringNetController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/stands4dictionary') {
            (new Controllers\Stands4DictionaryDefinitionsController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/estagger') {
            (new Controllers\StanfordESTagger($this))->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/detagger') {
            (new Controllers\StanfordDETagger($this))->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/entagger') {
            (new Controllers\StanfordENTagger($this))->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/oxford/es') {
            (new Controllers\OxfordDictionary($this, 'es'))->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/collinsde') {
            (new Controllers\CollinsGermanDictionaryController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/collinsdegetentry') {
            (new Controllers\CollinsGermanDictionaryGetEntryController)->displayPage($this);
        } elseif ($neatRequestUri == '/api/v1/resource/cefr') {
            (new Controllers\CEFRTagger($this))->displayPage($this);
        } else {
            \Flight::route('/', function() {
                (new Controllers\MainPageController)->displayPage($this);
            });
            \Flight::route('/login', function() {
                (new Controllers\LoginPageController)->displayPage($this);
            });
            \Flight::route('/logout', function() {
                (new Controllers\LogoutController)->displayPage($this);
            });
            \Flight::route('/forgotpassword', function() {
                (new Controllers\ForgotPasswordPageController)->displayPage($this);
            });
            \Flight::route('/register', function() {
                (new Controllers\RegistrationPageController)->displayPage($this);
            });
            \Flight::route('/dashboard', function() {
                (new Controllers\DashboardPageController)->displayPage($this);
            });
            \Flight::route('/profile', function() {
                (new Controllers\ProfilePageController)->displayPage($this);
            });
            \Flight::route('/applications', function() {
                (new Controllers\ApplicationPageController)->displayPage($this);
            });
            \Flight::route('/setlang', function() {
                (new Controllers\SetLanguageController)->displayPage($this);
            });
            \Flight::route('/verifyaccount', function() {
                (new Controllers\VerifyAccountController)->displayPage($this);
            });
            \Flight::route('/passwordreset', function() {
                (new Controllers\PasswordResetController)->displayPage($this);
            });
            \Flight::route('/terms', function() {
                (new Controllers\TermsPageController)->displayPage($this);
            });
            \Flight::route('/privacy', function() {
                (new Controllers\PrivacyPageController)->displayPage($this);
            });

            \Flight::start();
        }
    }
}
