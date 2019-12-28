<?php

/**
 * DvaService
 */
class DvaService
{
    private $domain;

    function __construct($domain)
    {
        $this->domain = $domain;
    }

    private function do_request($url, $params = [], $methods = 'GET', $headers = []): string
    {
        if (in_array(strtoupper($methods), ['POST', 'PUT'])) {
            if ($params) {
                $p_str = '';
                $comma = '';
                foreach ($params as $k => $v) {
                    $p_str .= $comma . $k . '=' . $v;
                    $comma = '&';
                }

                $url = $url . '?' . $p_str;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if (!empty($headers) && is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (strtoupper($methods) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        }
        if (strtoupper($methods) == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $response = curl_exec($ch);
        $err = curl_error($ch);
        if ($err) {
            throw new ErrorException("cURL Error #:" . $err);
        } else {
            return $response;
        }
    }

    function getGoDaddyPrice()
    {
        $headers = [
            'Accept: application/json, text/plain',
            'Origin: https://sg.godaddy.com',
            'Sec-Fetch-Mode: cors',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
        ];

        $url = "https://api.godaddy.com/v1/appraisal/" . $this->domain;

        $response = $this->do_request($url, [], 'GET', $headers);

        $response = json_decode($response, true);
        $price = $response['govalue'] ?? '0';

        return [
            'platform' => 'GoDaddy',
            'price' => $price,
            'currency' => 'USD'
        ];
    }

    function getWanMiPrice()
    {
        $url = 'http://www.wanmi.cc/gj/' . $this->domain;
        $output = $this->do_request($url);
        $regex4 = "/<div class=\"gujia\".*?>.*?<\/div>/ism";
        if (preg_match_all($regex4, $output, $matches)) {
            preg_match('/(¥)(.*)(元)/', $matches[0][0], $return, PREG_OFFSET_CAPTURE);
            $price = $return[2][0] ?? '0';

            if (!$price) {
                preg_match('/(¥)(.*)(万)/', $matches[0][0], $return, PREG_OFFSET_CAPTURE);
                $price = $return[2][0] ?? '0';
                $price *= 10000;
            }
        } else {
            $price = '0';
        }

        $price = str_replace(',', '', $price);

        return [
            'platform' => 'wanMi',
            'price' => trim($price),
            'currency' => 'RMB'
        ];
    }

    function getYuMiPrice() {
        $url = "http://www.yumi.com/tool/assess/domain/" . $this->domain;
        $output = $this->do_request($url, [], false, []);
        $regex4 = "/<span class=\"col-f60 f20\".*?>.*?<\/span>/ism";
        if (preg_match_all($regex4, $output, $matches)) {
            preg_match('/>(¥)(.*)<\/span>/', $matches[0][0], $return, PREG_OFFSET_CAPTURE);
            $price = $return[2][0] ?? '0';
            if (!$price) {
                preg_match('/>(小于)(.*)(元)/', $matches[0][0], $return, PREG_OFFSET_CAPTURE);
                $price = $return[2][0] ?? '0';
            }

            if (!$price) {
                preg_match('/>(.*)<\/span>/', $matches[0][0], $return, PREG_OFFSET_CAPTURE);
                if ($return[1][0] == '域名价值巨大') {
                    $price = '10000000';
                }
            }
        } else {
            $price = '0';
        }

        return [
            'platform' => 'yuMi',
            'price' => trim($price),
            'currency' => 'RMB'
        ];
    }

    function getPrice()
    {
        $price = [];
        $price[] = $this->getGoDaddyPrice();
        $price[] = $this->getWanMiPrice();
        $price[] = $this->getYuMiPrice();
        return $price;
    }
}