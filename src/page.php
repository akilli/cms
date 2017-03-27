<?php
declare(strict_types = 1);

namespace qnd;

use PDO;

/**
 * Page tree
 *
 * @param array $crit
 *
 * @return array
 */
function page_tree(array $crit = []): array
{
    $initWhere = 'parent_id IS NULL';
    $recWhere = '';
    $params = [];

    if (($crit['id'] ?? 0) > 0) {
        $initWhere = !empty($crit['ancestor']) ? 'id = :id' : 'parent_id = :id';
        $params[] = [':id', $crit['id'], PDO::PARAM_INT];
    }

    if (($crit['depth'] ?? 0) > 0) {
        $recWhere = 'AND t.depth < :depth';
        $params[] = [':depth', $crit['depth'], PDO::PARAM_INT];
    }

    $stmt = db_prep(
        "
            WITH RECURSIVE tree AS (
                    SELECT
                        id,
                        name,
                        url,
                        parent_id,
                        sort,
                        content,
                        1 AS depth,
                        LPAD(CAST(sort AS text), 3, '0') AS pos
                    FROM
                        page
                    WHERE
                        active = TRUE
                        AND %s
                UNION
                    SELECT
                        p.id,
                        p.name,
                        p.url,
                        p.parent_id,
                        p.sort,
                        p.content,
                        t.depth + 1 AS depth,
                        t.pos || '.' || LPAD(CAST(p.sort AS text), 3, '0') AS pos
                    FROM
                        page p
                    INNER JOIN
                        tree t
                            ON t.id = p.parent_id
                    WHERE
                        p.active = TRUE
                        %s
            )
            SELECT
                id,
                name,
                url,
                parent_id,
                sort,
                content,
                depth
            FROM
                tree
            ORDER BY
                pos ASC
        ",
        $initWhere,
        $recWhere
    );

    foreach ($params as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    return $stmt->fetchAll();
}
