<?php
namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // Generation of the token

    /**
     * Génération du JWT
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 3600): string
    {
        if($validity > 0){
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        // Encoding in base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // Cleaning the encoded values (removal of the +, / and =)
        $base64Header = $this->cleaning($base64Header);
        $base64Payload = $this->cleaning($base64Payload);

        // Generation of the signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $base64Signature = $this->cleaning($base64Signature);

        // Creation of the token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    // Checks that the token is correctly formed
    public function isValid(string $token): bool
    {
        return preg_match(
                '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
                $token
            ) === 1;
    }

    // Gets payload
    public function getPayload(string $token): array
    {
        $array = explode('.', $token);

        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    // Gets header
    public function getHeader(string $token): array
    {
        $array = explode('.', $token);

        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    // Checks if the token has expired
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    // Checks the token signature
    public function check(string $token, string $secret):bool
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // Regeneration of the token
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }

    /**
     * This function cleans the encoded values (removal of the +, / and =)
     *
     * @param string $var
     * @return string
     */
    public function cleaning(string $var): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], $var);
    }

}


