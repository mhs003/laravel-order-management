<?php

/**
 * ilog -> Illuminate\Support\Facades\Log::info(...)
 */
function ilog(...$args): void
{
    Illuminate\Support\Facades\Log::info(...$args);
}

/**
 * dlog -> Illuminate\Support\Facades\Log::debug(...)
 */
function dlog(...$args): void
{
    Illuminate\Support\Facades\Log::debug(...$args);
}

/**
 * elog -> Illuminate\Support\Facades\Log::error(...)
 */
function elog(...$args): void
{
    Illuminate\Support\Facades\Log::error(...$args);
}


