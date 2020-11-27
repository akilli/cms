<?php
declare(strict_types=1);

namespace attr\pos;

function viewer(string $val): string
{
    return preg_replace('#(^|\.)0+#', '$1', $val);
}
