<?php

if (!function_exists('json_request')) {
    function json_request()
    {
        return (!request()->header('x-inertia') && request()->ajax());
    }
}