<?php

function dd($data)
{
    echo "<pre>";
    print_r($data);
    exit;
}

function generate_response($status, $message, $group)
{
    return [
        'status'    => $status,
        'message'   => $message,
        'group'     => $group,
    ];
}
