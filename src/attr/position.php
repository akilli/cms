<?php
declare(strict_types=1);

namespace attr\position;

function viewer(string $val): string
{
    return preg_replace('#(^|\.)0+#', '$1', $val);
}
