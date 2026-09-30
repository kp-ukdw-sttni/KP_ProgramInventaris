<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;

trait AssertsListStateLinks
{
    /**
     * Pastikan sebuah link (href) atau form action (action) mengarah ke $base
     * sambil tetap membawa seluruh parameter query yang diberikan.
     *
     * Urutan parameter pada URL hasil render tidak dijamin, jadi tiap
     * parameter diperiksa dengan lookahead terpisah.
     */
    protected function assertUrlCarries(TestResponse $response, string $base, array $params): void
    {
        $lookaheads = '';

        foreach ($params as $key => $value) {
            $lookaheads .= '(?=[^"]*'.preg_quote($key.'='.$value, '/').'(?:["&]))';
        }

        $this->assertMatchesRegularExpression(
            '/(?:href|action)="'.preg_quote($base.'?', '/').$lookaheads.'[^"]*"/',
            $response->getContent(),
            'Link atau form action menuju '.$base.' tidak lagi membawa: '.implode(', ', array_keys($params))
        );
    }
}
