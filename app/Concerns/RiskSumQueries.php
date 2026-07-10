<?php

namespace App\Concerns;

trait RiskSumQueries
{
    private function riskSumSql(string $alias, int $level): string
    {
        return "SUM(CASE WHEN attrition_risk_level = ? THEN 1 ELSE 0 END) as {$alias}";
    }

    private function riskSumBindings(int $level): array
    {
        return [$level];
    }
}
