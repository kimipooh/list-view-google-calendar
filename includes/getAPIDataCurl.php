<?php 
// Reference: https://qiita.com/shinkuFencer/items/d7546c8cbf3bbe86dab8
function k_getAPIDataCurl($url){
    $option = [
        CURLOPT_RETURNTRANSFER => true, //Return as string
        CURLOPT_TIMEOUT        => 3, // timeout period(second)
        CURLOPT_REFERER        => get_permalink(), // Set to counteract the fact that curl returns no value on some servers when the referrer is not set.
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $option);

    $json    = curl_exec($ch);
    $info    = curl_getinfo($ch);
    $errorNo = curl_errno($ch);

    // Returns a blank array because it is an error except OK.
    if ($errorNo !== CURLE_OK) {
        // If you want to handle errors in detail, check with $errorNo.
        // E.g. for timeouts, this can be checked with CURLE_OPERATION_TIMEDOUT.
        return [];
    }

    // Status codes other than 200 are regarded as failures and an empty array is returned.
    if ($info['http_code'] !== 200) {
        return [];
    }

    return $json;
}