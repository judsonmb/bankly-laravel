<?php

namespace WeDevBr\Bankly\Auth;

use Illuminate\Support\Facades\Http;
use WeDevBr\Bankly\Events\BanklyAuthenticatedEvent;

/**
 * Class Auth
 *
 * @author Rafael Teixeira <rafael.teixeira@wedev.software>
 * @package WeDevBr\Bankly
 */
final class Auth
{
    /** @var self */
    private static $login;

    /** @var string */
    protected $loginUrl;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var string */
    protected $grantType = 'client_credentials';

    /** @var string */
    protected $scope;

    /** @var string */
    private $token;

    /** @var string */
    private $tokenExpiry;

    /** @var string */
    private $mtlsCert;

    /** @var string */
    private $mtlsKey;

    /** @var string */
    private $mtlsPassphrase;

    private function __construct()
    {
        //
    }

    /**
     * Returns the instance of this class
     *
     * @param string|null $loginUrl
     * @param string|null $mtlsCert
     * @param string|null $mtlsKey
     * @param string|null $mtlsPassphrase
     * @return self
     */
    public static function login(
        string $loginUrl = null,
        string $mtlsCert = null,
        string $mtlsKey = null,
        string $mtlsPassphrase = null
    )
    {
        if (is_null(self::$login)) {
            self::$login = new Auth();
        }

        self::$login->loginUrl = $loginUrl ?? config('bankly')['login_url'];
        self::$login->mtlsCert = $mtlsCert ?? null;
        self::$login->mtlsKey = $mtlsKey ?? null;
        self::$login->mtlsPassphrase = $mtlsPassphrase ?? null;

        return self::$login;
    }

    /**
     * @return self
     */
    public function setClientCredentials()
    {
        $this->clientId = $this->clientId ?? config('bankly')['client_id'];
        $this->clientSecret = $this->clientSecret ?? config('bankly')['client_secret'];
        if (empty($this->scope)) {
            $this->setScope();
        }
        return $this;
    }

    /**
     * @param null|string $clientId
     * @return self
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @param null|string $clientSecret
     * @return self
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @param string $grantType
     * @return self
     */
    public function setGrantType(string $grantType)
    {
        $this->grantType = $grantType;
        return $this;
    }

    /**
     * @param string|array $scope
     * @return self
     */
    public function setScope($scope = null)
    {
        $this->scope = config('bankly')['scope'] ?? [];
        if (!empty($scope)) {
            $this->scope = $scope;
        }
        if (is_array($this->scope)) {
            $this->scope = join(' ', $this->scope);
        }
        return $this;
    }

    /**
     * @param string $token
     * @return self
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Reset token for new request
     *
     * @return self
     */
    public function resetToken(): self
    {
        $this->token = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        if (now()->unix() > $this->tokenExpiry || !$this->token) {
            $this->auth();
        }

        return $this->token;
    }

    /**
     * @param string $tokenExpiry
     * @return self
     */
    public function setTokenExpiry(string $tokenExpiry)
    {
        $this->tokenExpiry = $tokenExpiry;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenExpiry()
    {
        return $this->tokenExpiry;
    }

    /**
     * @return void
     */
    private function auth(): void
    {
        $this->setClientCredentials();

        //TODO: Add auth for username and password
        $body = [
            'grant_type' => $this->grantType,
            'client_secret' => $this->clientSecret,
            'client_id' => $this->clientId
        ];

        if ($this->scope) {
            $body['scope'] = $this->scope;
        }

        $request = Http::asForm();
        if ($this->mtlsCert && $this->mtlsKey && $this->mtlsPassphrase) {
            $request = $request->withOptions([
                'cert' => $this->mtlsCert,
                'ssl_key' => [$this->mtlsKey, $this->mtlsPassphrase]
            ]);
        }
        $response = $request->post($this->loginUrl, $body)->throw()->json();

        $this->token = $response['access_token'];
        $this->tokenExpiry = now()->addSeconds($response['expires_in'])->unix();

        event(new BanklyAuthenticatedEvent($this->token, $this->tokenExpiry));
    }
}
