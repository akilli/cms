INSERT INTO project (id, name, host, active, system) VALUES
    ('base', 'BASE', NULL, TRUE, TRUE);

INSERT INTO role (id, name, privilege, active, system, project_id) VALUES
    (1, 'admin', '["_all_"]', TRUE, TRUE, 'base');

INSERT INTO account (id, name, username, password, role_id, active, system, project_id) VALUES
    (1, 'Admin', 'admin', '$2y$10$9wnkOfY1qLvz0sRXG5G.d.rf2NhCU8a9m.XrLYIgeQA.SioSWwtsW', 1, TRUE, TRUE, 'base');

INSERT INTO entity (id, name, actions, system, project_id) VALUES
    ('page', 'Page', '["admin", "create", "delete", "edit", "index", "view"]', TRUE, 'base');
