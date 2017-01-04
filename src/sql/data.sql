INSERT INTO project (id, name, host, active, system) VALUES
    ('base', 'BASE', NULL, TRUE, TRUE);

INSERT INTO role (id, name, privilege, active, system, project_id) VALUES
    (1, 'admin', '["_all_"]', TRUE, TRUE, 'base');

INSERT INTO account (id, name, username, password, role_id, active, system, project_id) VALUES
    (1, 'Admin', 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, TRUE, TRUE, 'base');

INSERT INTO entity (id, name, actions, system, project_id) VALUES
    ('page', 'Page', '["admin", "create", "delete", "edit", "index", "view"]', TRUE, 'base');

INSERT INTO menu (id, uid, name, system, project_id) VALUES
    (1, 'toolbar', 'Toolbar', TRUE, 'base');

INSERT INTO node (id, name, target, root_id, lft, rgt, level, project_id) VALUES
    (1, 'Homepage', '/', 1, 1, 2, 1, 'base'),
    (2, 'Dashboard', '/account/dashboard', 1, 3, 4, 1, 'base'),
    (3, 'Profile', '/account/profile', 1, 5, 6, 1, 'base'),
    (4, 'Logout', '/account/logout', 1, 7, 8, 1, 'base'),
    (5, 'Content', '', 1, 9, 12, 1, 'base'),
    (6, 'Page', '/page/admin', 1, 10, 11, 2, 'base'),
    (7, 'Structure', '', 1, 13, 22, 1, 'base'),
    (8, 'Menu', '/menu/admin', 1, 14, 15, 2, 'base'),
    (9, 'Node', '/node/admin', 1, 16, 17, 2, 'base'),
    (10, 'Entity', '/entity/admin', 1, 18, 19, 2, 'base'),
    (11, 'Attribute', '/attr/admin', 1, 20, 21, 2, 'base'),
    (12, 'System', '', 1, 23, 32, 1, 'base'),
    (13, 'Project', '/project/admin', 1, 24, 25, 2, 'base'),
    (14, 'Account', '/account/admin', 1, 26, 27, 2, 'base'),
    (15, 'Role', '/role/admin', 1, 28, 29, 2, 'base'),
    (16, 'URL', '/url/admin', 1, 30, 31, 2, 'base');
