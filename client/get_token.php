<?php
namespace TokenNS;

class Token {
    private $scope;
    private $uri_suffix="/v1/token";
    private $key;
    private $secret;
    private $env = [];

    public function __construct() {
        $this->loadEnv();
        $this->scope = $this->env['SCOPE'];
        $this->key = $this->env['OKTACLIENTID'];
        $this->secret = $this->env['OKTASECRET'];
        $this->uri = $this->env['OKTAISSUER'] . $this->uri_suffix;
    }

    private function loadEnv() {
        $arr_params = explode("\n", file_get_contents(__DIR__ . "/../.env", 'r'));
        foreach ($arr_params as $row) {
            if (!$row) continue;
            preg_match('/(?<key>.+)=(?<value>.+)/', $row, $matches);
            $key = $matches["key"];
            $value = $matches["value"];
            $this->env[$key] = $value;
        }
    }

    public function getToken() {
        $payload = "grant_type=client_credentials&scope=$this->scope";
        $token = base64_encode("$this->key:$this->secret");
        $contentType = "Content-Type: application/x-www-form-urlencoded";
        $authorization = "Authorization: Basic $token";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($contentType, $authorization));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $result = json_decode(curl_exec($ch));
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300)
            return false;
        else
            return $result->access_token;
            //return $result->token_type . " " . $result->access_token;
    }
}

//$token = new Token;
//echo $token->getToken();
?>
