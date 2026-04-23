<?php

return [
    'hikvision' => [
        'scheme' => env('HIKVISION_SCHEME', 'http'),
        'http_auth' => env('HIKVISION_HTTP_AUTH', 'digest'),
        'username' => env('HIKVISION_USERNAME', 'admin'),
        'password' => env('HIKVISION_PASSWORD', ''),

        'paths' => [
            'record_person' => env('HIKVISION_RECORD_PERSON_PATH', '/ISAPI/AccessControl/UserInfo/Record?format=json'),
            'modify_person' => env('HIKVISION_MODIFY_PERSON_PATH', '/ISAPI/AccessControl/UserInfo/Modify?format=json'),
            'search_person' => env('HIKVISION_SEARCH_PERSON_PATH', '/ISAPI/AccessControl/UserInfo/Search?format=json'),
            'delete_person' => env('HIKVISION_DELETE_PERSON_PATH', '/ISAPI/AccessControl/UserInfoDetail/Delete?format=json'),
        ],
    ],
];