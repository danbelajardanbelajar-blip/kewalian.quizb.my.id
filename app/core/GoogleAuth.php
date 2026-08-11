<?php
/**
 * GoogleAuth.php — Google OAuth 2.0 Helper
 * Implementasi murni tanpa library eksternal (curl-based)
 */
class GoogleAuth
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const INFO_URL  = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function __construct()
    {
        $this->clientId     = GOOGLE_CLIENT_ID;
        $this->clientSecret = GOOGLE_CLIENT_SECRET;
        $this->redirectUri  = GOOGLE_REDIRECT_URI;
    }

    /**
     * Buat URL redirect ke halaman login Google
     */
    public function getAuthUrl(): string
    {
        // Simpan state untuk keamanan CSRF
        $state = bin2hex(random_bytes(16));
        Session::set('google_oauth_state', $state);

        $params = http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Tukar authorization code dengan access token
     */
    public function getAccessToken(string $code): ?array
    {
        $data = http_build_query([
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        $response = $this->curlPost(self::TOKEN_URL, $data);
        if (!$response) return null;

        $token = json_decode($response, true);
        return isset($token['access_token']) ? $token : null;
    }

    /**
     * Ambil data profil pengguna dari Google
     */
    public function getUserInfo(string $accessToken): ?array
    {
        $response = $this->curlGet(self::INFO_URL, $accessToken);
        if (!$response) return null;

        $info = json_decode($response, true);
        return isset($info['sub']) ? $info : null;
    }

    /**
     * Validasi state parameter (CSRF protection)
     */
    public function validateState(string $state): bool
    {
        $savedState = Session::get('google_oauth_state');
        Session::set('google_oauth_state', null);
        return !empty($savedState) && hash_equals($savedState, $state);
    }

    // ── Private HTTP Helpers ──────────────────────────────────

    private function curlPost(string $url, string $data): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        return ($error || $response === false) ? null : $response;
    }

    private function curlGet(string $url, string $accessToken): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        return ($error || $response === false) ? null : $response;
    }
}
