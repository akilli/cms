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
    $anc = false;
    $initWhere = 'parent_id IS NULL';
    $cond = 't.id = p.parent_id';
    $recWhere = '';
    $pId = project('id');
    $params = [[':pid1', $pId, PDO::PARAM_INT], [':pid2', $pId, PDO::PARAM_INT]];

    if (($crit['id'] ?? 0) > 0) {
        $params[] = [':id', $crit['id'], PDO::PARAM_INT];
        $initWhere = 'parent_id = :id';

        if (!empty($crit['ancestor'])) {
            $anc = true;
            $initWhere = 'id = :id';
            $cond = 't.parent_id = p.id';
        }
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
                        project_id = :pid1
                        AND active = TRUE
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
                            ON %s
                    WHERE
                        p.project_id = :pid2
                        AND p.active = TRUE
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
        $cond,
        $recWhere
    );

    foreach ($params as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    return $anc ? array_reverse($stmt->fetchAll()) : $stmt->fetchAll();
}
