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

use OAuth2\OpenID\Storage\AuthorizationCodeInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\Storage;

/**
 * OAuth 2.0 storage class.
 * @package TellOP
 */
class OAuth2Storage implements Storage\AccessTokenInterface,
    Storage\ClientCredentialsInterface,
    Storage\UserCredentialsInterface,
    Storage\RefreshTokenInterface,
    Storage\JwtBearerInterface,
    Storage\ScopeInterface,
    Storage\PublicKeyInterface,
    UserClaimsInterface,
    AuthorizationCodeInterface {
    /**
     * Application PDO object.
     */
    protected $appPDO;

    /**
     * OAuth2Storage constructor.
     * @param Application $appObject Application object.
     */
    public function __construct($appObject) {
        $this->appPDO = $appObject->getApplicationPDO();
    }

    /**
     * Look up the supplied OAuth token from the database.
     * @param string $oauth_token The OAuth token to be checked.
     * @return bool|mixed[] <b>FALSE</b> if the supplied OAuth token is invalid
     * or an associative array as below:
     * <ul>
     * <li><b>expires</b>: a UNIX timestamp of the token expiration time;</li>
     * <li><b>client_id</b> (optional): the stored client identifier;</li>
     * <li><b>user_id</b> (optional): the stored user identifier;</li>
     * <li><b>scope</b> (optional): a space-separated list of stored scope
     * values;</li>
     * <li><b>id_token</b> (optional): a stored ID token for OpenID Connect.</li>
     * </ul>
     */
    public function getAccessToken($oauth_token) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_access_tokens WHERE '
            . 'access_token = :access_token');
        $token = $stmt->execute(array('access_token' => $oauth_token));
        if ($token && $token = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            $token['expires'] = strtotime($token['expires']);
            return $token;
        } else {
            return FALSE;
        }
    }

    /**
     * Store the supplied OAuth token into the database.
     * @param string $oauth_token The OAuth token to be stored.
     * @param string $client_id The client identifier.
     * @param string $user_id The user identifier.
     * @param int $expires The UNIX timestamp of the token expiration time.
     * @param string|null $scope A space-separated list of scopes associated
     * with the token.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function setAccessToken($oauth_token, $client_id, $user_id,
                                   $expires, $scope = null) {
        $expires = date('Y-m-d H:i:s', $expires);
        if ($this->getAccessToken($oauth_token)) {
            $stmt = $this->appPDO->prepare('UPDATE oauth_access_tokens SET '
                . 'client_id=:client_id, expires=:expires, user_id=:user_id, '
                . 'scope=:scope WHERE access_token=:access_token');
        } else {
            $stmt = $this->appPDO->prepare('INSERT INTO oauth_access_tokens '
                . '(access_token, client_id, expires, user_id, scope) VALUES '
                . '(:access_token, :client_id, :expires, :user_id, :scope)');
        }
        $result = $stmt->execute(array(
            'access_token' => $oauth_token,
            'client_id' => $client_id,
            'expires' => $expires,
            'user_id' => $user_id,
            'scope' => $scope
        ));
        $stmt->closeCursor();
        return $result;
    }

    /**
     * Ensure that client credentials are valid.
     * @param string $client_id Client identifier to be checked.
     * @param string|null $client_secret Optional client secret to be checked.
     * @return bool Returns <b>TRUE</b> if the client credentials are valid and
     * <b>FALSE</b> otherwise, or if the credentials could not be checked due
     * to database errors.
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     */
    public function checkClientCredentials($client_id, $client_secret = null) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_clients WHERE '
            . 'client_id = :client_id');
        if ($stmt->execute(compact('client_id')) === FALSE) {
            return false;
        }
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if ($result && $result['client_secret'] == $client_secret) {
            return $result;
        }
        return FALSE;
    }

    /**
     * Determine if the client is a "public" client and therefore does not
     * require passing credentials for certain grant types.
     * @param string $client_id Client identifier to be checked.
     * @return bool Returns <b>TRUE</b> if the client is public and <b>FALSE</b>
     * if it is not, or if the client identifier could not be checked due to
     * database errors.
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     */
    public function isPublicClient($client_id) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_clients WHERE '
            . 'client_id = :client_id');
        $stmt->execute(compact('client_id'));
        if (!$result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return false;
        }
        $stmt->closeCursor();
        return empty($result['client_secret']);
    }

    /**
     * Get the client details corresponding to an identifier.
     * @param string $client_id Client identifier to be checked.
     * @return bool|mixed[] Returns <b>FALSE</b> if the given client does not
     * exist, is invalid or if the identifier could not be checked, or an
     * associative array as follows:
     * <ul>
     * <li><b>redirect_uri</b>: redirect URI for the client;</li>
     * <li><b>client_id</b> (optional): the client ID;</li>
     * <li><b>grant_types</b> (optional): a space-separated list of restricted
     * grant types;</li>
     * <li><b>user_id</b> (optional): the user identifier associated with this
     * client;</li>
     * <li><b>scope</b> (optional): a space-separated list of the scopes
     * allowed for this client.</li>
     * </ul>
     */
    public function getClientDetails($client_id) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_clients WHERE '
            . 'client_id = :client_id');
        if (!$stmt->execute(compact('client_id'))) {
            return false;
        }
        if (!$clientdetails = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return false;
        }
        $stmt->closeCursor();
        return $clientdetails;
    }

    /**
     * Gets the scopes associated with this client.
     * @param string $client_id Client identifier.
     * @return bool|string A space-separated list of scopes associated with
     * this client, or <b>FALSE</b> in case of failure.
     */
    public function getClientScope($client_id) {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }
        return null;
    }

    /**
     * Check if the specified grant type is among the ones allowed for a
     * specific client.
     * @param string $client_id Client identifier.
     * @param string $grant_type Grant type.
     * @return bool <b>TRUE</b> if the grant type is supported by this client
     * identifier, <b>FALSE</b> otherwise.
     */
    public function checkRestrictedGrantType($client_id, $grant_type) {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);
            return in_array($grant_type, (array) $grant_types);
        }
        // If no grant types are defined, assume they are unrestricted
        return true;
    }

    /**
     * Get the public key associated with a client identifier and a subject.
     * @param string $client_id Client identifier.
     * @param string $subject Public key subject.
     * @return string|bool The public key associated with the specified client
     * identifier and subject, or <b>FALSE</b> if no such key exists/the check
     * could not be completed due to database errors.
     */
    public function getClientKey($client_id, $subject) {
        $stmt = $this->appPDO->prepare('SELECT public_key FROM oauth_jwt WHERE '
            . 'client_id=:client_id AND subject=:subject');
        if (!$stmt->execute(array('client_id' => $client_id,
            'subject' => $subject))) {
            return FALSE;
        }
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();
        return $result;
    }

    /**
     * Get a JSON token identifier by matching against the client identifier,
     * subject, audience and expiration.
     * @param string $client_id Client identifier.
     * @param string $subject The subject to be matched.
     * @param string $audience The audience to be matched.
     * @param int $expiration The UNIX timestamp representing the expiration of
     * the JSON token identifier.
     * @param string $jti The JSON token identifier to be matched.
     * @return null|mixed[] <b>NULL</b> if the JSON token identifier does not
     * exist (or if it could not be retrieved due to database errors), or an
     * associative array as below:
     * <ul>
     * <li><b>issuer</b>: stored client identifier;</li>
     * <li><b>subject</b>: stored subject;</li>
     * <li><b>audience</b>: stored audience;</li>
     * <li><b>expires</b>: a UNIX timestamp representing the stored expiration
     * date and time;</li>
     * <li><b>jti</b>; the stored JSON token identifier.</li>
     * </ul>
     */
    public function getJti($client_id, $subject, $audience, $expiration, $jti) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_jti WHERE '
            . 'issuer=:client_id AND subject=:subject AND audience=:audience '
            . 'AND expires=:expires AND jti=:jti');
        if ($stmt->execute(compact('client_id', 'subject', 'audience',
            'expiration',  'jti'))) {
            if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $stmt->closeCursor();
                return array(
                    'issuer' => $result['issuer'],
                    'subject' => $result['subject'],
                    'audience' => $result['audience'],
                    'expires' => strtotime($result['expires']),
                    'jti' => $result['jti'],
                );
            }
            $stmt->closeCursor();
        }
        return NULL;
    }

    /**
     * Store a used JSON token identifier so that the library can check
     * against it to prevent replay attacks.
     * @param string $client_id Client identifier to be inserted.
     * @param string $subject Subject to be inserted.
     * @param string $audience Audience to be inserted.
     * @param int $expiration The UNIX timestamp containing the token
     * expiration date/time.
     * @param string $jti JSON token identifier to be inserted.
     * @return bool Returns <b>TRUE</b> on success and <b>FALSE</b> on failure.
     */
    public function setJti($client_id, $subject, $audience, $expiration, $jti) {
        $stmt = $this->appPDO->prepare('INSERT INTO oauth_jti (issuer, subject,'
            . ' audience, expires, jti) VALUES (:client_id, :subject,'
            . ' :audience, :expires, :jti)');

        if ($result = $stmt->execute(compact('client_id', 'subject', 'audience',
            'expiration', 'jti'))) {
            $stmt->closeCursor();
        }
        return $result;
    }

    /**
     * Get the public key for JSON token identifiers associated to the specified
     * client identifier.
     * @param string|null $client_id Client identifier.
     * @return string|void The associated public key, or nothing if no public
     * key exists.
     * @see https://bshaffer.github.io/oauth2-server-php-docs/overview/jwt-access-tokens/
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function getPublicKey($client_id = null) {
        $stmt = $this->appPDO->prepare('SELECT public_key FROM '
            . 'oauth_public_keys WHERE client_id=:client_id OR client_id IS '
            . 'NULL ORDER BY client_id IS NOT NULL DESC');
        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            return $result['public_key'];
        }
        $stmt->closeCursor();
    }

    /**
     * Get the private key for JSON token identifiers associated to the
     * specified client identifier.
     * @param string|null $client_id Client identifier.
     * @return string|void The associated private key, or nothing if no
     * private key exists.
     * @see https://bshaffer.github.io/oauth2-server-php-docs/overview/jwt-access-tokens/
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function getPrivateKey($client_id = null) {
        $stmt = $this->appPDO->prepare('SELECT private_key FROM '
            . 'oauth_public_keys WHERE client_id=:client_id OR client_id IS '
            . 'NULL ORDER BY client_id IS NOT NULL DESC');
        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            return $result['private_key'];
        }
        $stmt->closeCursor();
    }

    /**
     * Get the signature/hashing algorithm used during the creation of JSON
     * token identifiers.
     * @param string|null $client_id Client identifier.
     * @return string The hashing algorithm.
     */
    public function getEncryptionAlgorithm($client_id = null) {
        $stmt = $this->appPDO->prepare('SELECT encryption_algorithm FROM '
            . ' oauth_public_keys WHERE client_id=:client_id OR client_id '
            . 'IS NULL ORDER BY client_id IS NOT NULL DESC');
        if ($stmt->execute(compact('client_id'))) {
            if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $stmt->closeCursor();
                return $result['encryption_algorithm'];
            }
            $stmt->closeCursor();
        }
        return 'RS256';
    }

    /**
     * Grant an OAuth 2.0 refresh token.
     * @param string $refresh_token Refresh token to be checked.
     * @return mixed[]|null Returns <b>NULL</b> if the refresh token is invalid
     * (or could not be checked), or an associative array as shown below:
     * <ul>
     * <li><b>refresh_token</b>: the refresh token identifier;</li>
     * <li><b>client_id</b>: the client identifier;</li>
     * <li><b>user_id</b>: the user identifier;</li>
     * <li><b>expires</b>: a UNIX timestamp containing the token expiration
     * date and time;</li>
     * <li><b>scope</b> (optional): a space-separated list of scopes associated
     * with the token.</li>
     * </ul>
     */
    public function getRefreshToken($refresh_token) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_refresh_tokens '
            . 'WHERE refresh_token = :refresh_token');
        $token = $stmt->execute(compact('refresh_token'));
        if ($token && $token = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            $token['expires'] = strtotime($token['expires']);
            return $token;
        }
        $stmt->closeCursor();
        return NULL;
    }

    /**
     * Store the provided refresh token.
     * @param string $refresh_token Refresh token to be stored.
     * @param string $client_id Client identifier to be stored.
     * @param string $user_id User identifier to be stored.
     * @param int $expires Expiration UNIX timestamp to be stored (<pre>0</pre>
     * if the token does not expire).
     * @param string $scope An optional space-separated list of scopes.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id,
                                    $expires, $scope = null) {
        $expires = date('Y-m-d H:i:s', $expires);
        $stmt = $this->appPDO->prepare('INSERT INTO oauth_refresh_tokens '
            . '(refresh_token, client_id, user_id, expires, scope) VALUES '
            . '(:refresh_token, :client_id, :user_id, :expires, :scope)');
        $result = $stmt->execute(compact('refresh_token', 'client_id',
            'user_id', 'expires', 'scope'));
        $stmt->closeCursor();
        return $result;
    }

    /**
     * Expire a used refresh token.
     * @param string $refresh_token The refresh token to expire.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function unsetRefreshToken($refresh_token) {
        $stmt = $this->appPDO->prepare('DELETE FROM oauth_refresh_tokens WHERE '
            . 'refresh_token = :refresh_token');
        $result = $stmt->execute(compact('refresh_token'));
        $stmt->closeCursor();
        return $result;
    }

    /**
     * Check if the provided scope exists.
     * @param string $scope A space-separated string of scopes.
     * @return bool <b>TRUE</b> if it exists, <b>FALSE</b> otherwise.
     */
    public function scopeExists($scope) {
        $scope = explode(' ', $scope);
        $whereIn = implode(',', array_fill(0, count($scope), '?'));
        $stmt = $this->appPDO->prepare('SELECT count(scope) as count FROM '
            . 'oauth_scopes WHERE scope IN (' . $whereIn . ')');
        $stmt->execute($scope);
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            return $result['count'] == count($scope);
        } else {
            $stmt->closeCursor();
            return false;
        }
    }

    /**
     * Get the default scope to use in the event the client does not request
     * one. By returning <b>FALSE</b>, a request_error is returned by the
     * server to force a scope request by the client. By returning <b>null</b>,
     * opt out of requiring scopes.
     * @param string $client_id An optional client identifier that can be used
     * to return customized default scopes.
     * @return string|null|bool A representation of the default scope,
     * <b>null</b> if scopes are not defined, or <b>FALSE</b> to force scope
     * request by the client.
     */
    public function getDefaultScope($client_id = null) {
        $stmt = $this->appPDO->prepare('SELECT scope FROM oauth_scopes WHERE '
            . 'is_default=:is_default');
        $stmt->execute(array('is_default' => true));
        if ($result = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            $defaultScope = array_map(function ($row) {
                return $row['scope'];
            }, $result);
            return implode(' ', $defaultScope);
        }
        $stmt->closeCursor();
        return null;
    }

    /**
     * Grant access tokens for basic user credentials.
     * @param string $username Username to be checked.
     * @param string $password Password to be checked.
     * @return bool <b>TRUE</b> if the username and password are valid,
     * <b>FALSE</b> otherwise.
     */
    public function checkUserCredentials($username, $password) {
        $stmt = $this->appPDO->prepare('SELECT * FROM users WHERE email=:email'
            . ' AND (accountstatus=1 OR accountstatus=3 OR accountstatus=4)');
        $stmt->execute(array('email' => $username));
        if ($stmt->rowCount() == 1 && $userInfo = $stmt->fetch(
                \PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            return password_verify($password, $userInfo['password']);
        } else {
            $stmt->closeCursor();
            return false;
        }
    }

    /**
     * Get the details associated to a user account.
     * @param string $username The username.
     * @return bool|string[] Returns <b>FALSE</b> if the requested username
     * does not exist or is invalid, or an associative array structured as
     * follows:
     * <ul>
     * <li><b>user_id</b>: the user identifier;</li>
     * <li><b>scope</b> (optional): a space-separated list of restricted
     * scopes.</li>
     * </ul>
     */
    public function getUserDetails($username) {
        $stmt = $this->appPDO->prepare('SELECT * FROM users WHERE email=:email'
            . ' AND (accountstatus=1 OR accountstatus=3 OR accountstatus=4)');
        $stmt->execute(array('email' => $username));
        if ($stmt->rowCount() == 1 && $userInfo = $stmt->fetch(
                \PDO::FETCH_ASSOC)) {
            $stmt->closeCursor();
            return array_merge(array(
                'user_id' => $username
            ), $userInfo);
        } else {
            $stmt->closeCursor();
            return false;
        }
    }

    /**
     * Convert the list of user claims to an array of claims and check if they
     * are set or not.
     * @param string $claim List of user claims to check.
     * @param mixed[] $userDetails User details.
     * @return mixed[] An array having all claims listed in <pre>$claim</pre>
     * as keys and their associated values as values.
     */
    protected function getUserClaim($claim, $userDetails) {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES',
            strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);
        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ?
                $userDetails[$value] : null;
        }
        return $userClaims;
    }

    /**
     * Return a list of claims about the provided username.
     *
     * Groups of claims are returned based on the requested scopes. No group
     * is required, and no claim is required.
     *
     * @param string $user_id The username for which claims should be returned.
     * @param string $scope The requested scope. Scopes with matching claims
     * are <pre>profile</pre>, <pre>email</pre>, <pre>address</pre> and
     * <pre>phone</pre>.
     * @return mixed An array in the <pre>claim =&gt;</pre> format.
     */
    public function getUserClaims($user_id, $scope) {
        if (!$userDetails = $this->getUserDetails($user_id)) {
            return false;
        }
        $claims = explode(' ', trim($scope));
        $userClaims = array();
        // For each requested claim, if the user has the claim, set it in the
        // response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    /** @noinspection PhpParamsInspection */
                    $userClaims['address'] = $this->getUserClaim($validClaim,
                        ($userDetails['address'] ? : $userDetails));
                } else {
                    /** @noinspection PhpParamsInspection */
                    $userClaims = array_merge($userClaims,
                        $this->getUserClaim($validClaim, $userDetails));
                }
            }
        }
        return $userClaims;
    }

    /**
     * Retrieve the stored data for the given authorization code.
     * @param string $code Authorization code to be checked.
     * @return null|mixed[] Returns <b>NULL</b> if the code is invalid, or an
     * associative array as below:
     * <ul>
     * <li><b>client_id</b>: the stored client identifier;</li>
     * <li><b>user_id</b>: the stored user identifier;</li>
     * <li><b>expires</b>: a UNIX timestamp representing the authorization code
     * expiration time;</li>
     * <li><b>redirect_uri</b>: the stored redirect URI;</li>
     * <li><b>scope</b> (optional): a space-separated string of scope
     * values</li>.
     * </ul>
     */
    public function getAuthorizationCode($code) {
        $stmt = $this->appPDO->prepare('SELECT * FROM oauth_authorization_codes'
            . ' WHERE authorization_code = :code');
        if (!$stmt->execute(compact('code'))) {
            $stmt->closeCursor();
            return NULL;
        }
        $code = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if ($code) {
            $code['expires'] = strtotime($code['expires']);
            return $code;
        } else {
            return NULL;
        }
    }

    /**
     * Make a stored authorization code expire once used.
     * @param string $code Authorization code.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function expireAuthorizationCode($code) {
        $stmt = $this->appPDO->prepare('DELETE FROM oauth_authorization_codes '
            . 'WHERE authorization_code = :code');
        $result = $stmt->execute(compact('code'));
        $stmt->closeCursor();
        return $result;
    }

    /**
     * Store the provided authorization code.
     * @param string $code The authorization code to be stored.
     * @param string $client_id The client identifier to be stored.
     * @param string $user_id The user identifier to be stored.
     * @param string $redirect_uri A space-separated string of redirect URIs.
     * @param int $expires The token expiration time and date, stored as a UNIX
     * timestamp.
     * @param string $scope An optional space-separated string of scopes.
     * @param string $id_token The optional OpenID Connect id_token.
     * @return bool Returns <b>TRUE</b> if the authorization code is stored
     * successfully and <b>FALSE</b> on failure.
     */
    public function setAuthorizationCode($code, $client_id, $user_id,
                                         $redirect_uri, $expires, $scope = null,
                                         $id_token = null) {
        $expires = date('Y-m-d H:i:s', $expires);
        if (func_num_args() > 6) {
            if ($this->getAuthorizationCode($code)) {
                $sql = 'UPDATE oauth_authorization_codes SET '
                    . 'client_id=:client_id, user_id=:user_id, '
                    . 'redirect_uri=:redirect_uri, expires=:expires, '
                    . 'scope=:scope, id_token =:id_token WHERE '
                    . 'authorization_code=:code';
            } else {
                $sql = 'INSERT INTO oauth_authorization_codes '
                    . '(authorization_code, client_id, user_id, redirect_uri, '
                    . 'expires, scope, id_token) VALUES (:code, :client_id, '
                    . ':user_id, :redirect_uri, :expires, :scope, :id_token)';
            }
            $stmt = $this->appPDO->prepare($sql);
            $result = $stmt->execute(compact('code', 'client_id', 'user_id',
                'redirect_uri', 'expires', 'scope', 'id_token'));
        } else {
            if ($this->getAuthorizationCode($code)) {
                $sql = 'UPDATE oauth_authorization_codes SET '
                    . 'client_id=:client_id, user_id=:user_id, '
                    . 'redirect_uri=:redirect_uri, expires=:expires, '
                    . 'scope=:scope WHERE authorization_code=:code';
            } else {
                $sql = 'INSERT INTO oauth_authorization_codes '
                    . '(authorization_code, client_id, user_id, redirect_uri, '
                    . 'expires, scope) VALUES (:code, :client_id, :user_id, '
                    . ':redirect_uri, :expires, :scope)';
            }
            $stmt = $this->appPDO->prepare($sql);
            $result = $stmt->execute(compact('code', 'client_id', 'user_id',
                'redirect_uri', 'expires', 'scope'));
        }
        $stmt->closeCursor();
        return $result;
    }
}
