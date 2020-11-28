<?php
declare(strict_types=1);

namespace event\role;

use app;
use entity;
use DomainException;

/**
 * @throws DomainException
 */
function predelete(array $data): array
{
    if (entity\size('account', [['role_id', $data['id']]])) {
        throw new DomainException(app\i18n('Cannot delete used role'));
    }

    return $data;
}
