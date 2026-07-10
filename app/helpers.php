<?php

if (! function_exists('growth_percentage')) {
    function growth_percentage(float|int $current, float|int $previous): float
    {
        if ($previous <= 0) {
            return 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
