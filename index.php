<?php

header('Content-type: application/json');
function getRequestHeader($name = '')
{
    $header = [];
    if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
        $header = $result;
    } else {
        foreach ($_SERVER as $key => $val) {
            if (0 === strpos($key, 'HTTP_')) {
                $key = str_replace('_', '-', strtolower(substr($key, 5)));
                $header[$key] = $val;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $header['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $header['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }
    }
    $resp = array_change_key_case($header);
    if (is_array($name)) {
        return $resp = array_merge($resp, $name);
    }
    if ('' === $name) {
        return $resp;
    }
    $name = str_replace('_', '-', strtolower($name));
    return isset($resp[$name]) ? $resp[$name] : null;
}

function httpRequest($url, $method, $header=[], $params = array())
{
    $result = "";
    $timeout = 50;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    switch ($method) {
        case "GET" :
            curl_setopt($curl, CURLOPT_HTTPGET, true);//TRUE 时会设置 HTTP 的 method 为 GET，由于默认是 GET，所以只有 method 被修改时才需要这个选项。
            break;
        case "POST":
            curl_setopt($curl, CURLOPT_POST, true);//TRUE 时会发送 POST 请求，类型为：application/x-www-form-urlencoded，是 HTML 表单提交时最常见的一种。
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");//HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);//全部数据使用HTTP协议中的 "POST" 操作来发送。
            break;
        case "PUT" :
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
    }
    if (isset($result)) {
        $result = curl_exec($curl);
    }
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    header('HTTP/1.1 '.$code);
    return $result;
}

$header[] = is_null($auth = getRequestHeader('Authorization')) ? '' : 'Authorization:' . $auth;
$header[]='content-type:application/json';
$params = file_get_contents('php://input');
$url = $_SERVER['REQUEST_URI'];
$url=str_replace('/index.php/','',$url);
echo httpRequest('http://yourserver/' . $url, $_SERVER['REQUEST_METHOD'], $header, $params);

//fastcgi_finish_request();
