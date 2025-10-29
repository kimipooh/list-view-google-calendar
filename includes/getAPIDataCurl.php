<?php 
// Reference: Changed cURL to wp_remote_get.
function k_getAPIDataCurl($url){
    $referer = get_permalink() ? get_permalink() : home_url('/');

    $args = [
        'timeout' => 3, // タイムアウト（秒）
        'referer' => $referer, // リファラーを設定
    ];

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        // error_log('API request failed: ' . $response->get_error_message());
        return [];
    }

    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code !== 200) {
        // error_log('API returned non-200 status: ' . $http_code);
        return [];
    }   

    $json = wp_remote_retrieve_body($response);

    return $json;
}