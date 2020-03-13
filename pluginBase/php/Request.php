<?php
namespace php;

class Request {
    public const CONTENT_JSON = "Content-type: application/json";
    public const CONTENT_QUERY = "Content-type: application/x-www-form-urlencoded";
    /**
     * @param string $method
     * @param string $url
     * @param string $data
     * @param array|string $header
     * @return string|null
     */
    public static function send(string $method, string $url, $data = null, $header = null) {
        $options = [
            "http" => [
                "method" => strtoupper($method)
            ]
        ];
        if ($data) $options["http"]["content"] = $data;
        if ($header) $options["http"]["header"] = $header;
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result === false ? null : $result;
    }
    /** @return string|null */
    public static function postJson(string $url, array $data = null) {
        return self::send("post", $url, $data === null ? null : json_encode($data), $data === null ? null : self::CONTENT_JSON);
    }
    /** @return string|null */
    public static function getJson(string $url, array $data = null) {
        return self::send("get", $url, $data === null ? null : http_build_query($data), $data === null ? null : self::CONTENT_QUERY);
    }
}