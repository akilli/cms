START TRANSACTION;

-- ---------------------------------------------------------------------------------------------------------------------
-- Role
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO public.role (id, name, privilege)
VALUES (1, 'admin', '{"_all_"}');

SELECT setval('public.role_id_seq', (SELECT max(id) FROM public.role));

-- ---------------------------------------------------------------------------------------------------------------------
-- Account
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO public.account (id, name, uid, role_id, username, password, active)
VALUES (1, 'admin', 'admin', 1, 'admin', '$2y$12$agdFD9Lrh2CL2ACeessD4OKHktYDJbi4xcMdWDmiRMO5Dzaj2HSz6', true);

SELECT setval('public.account_id_seq', (SELECT max(id) FROM public.account));

-- ---------------------------------------------------------------------------------------------------------------------
-- Page
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO public.page (id, name, url)
VALUES (1, 'Homepage', '/');

SELECT setval('public.page_id_seq', (SELECT max(id) FROM public.page));

-- ---------------------------------------------------------------------------------------------------------------------

COMMIT;
