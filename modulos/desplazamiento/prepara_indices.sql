
SET client_encoding = 'UTF8';

SELECT setval('clasifdesp_seq', MAX(id)) FROM (SELECT 100 as id UNION SELECT MAX(id) FROM clasifdesp) AS s;
SELECT setval('tipodesp_seq', MAX(id)) FROM (SELECT 100 as id UNION SELECT MAX(id) FROM tipodesp) AS s;
SELECT setval('declaroante_seq', MAX(id)) FROM (SELECT 100 as id UNION SELECT MAX(id) FROM declaroante) AS s;
SELECT setval('inclusion_seq', MAX(id)) FROM (SELECT 100 as id UNION SELECT MAX(id) FROM inclusion) AS s;
SELECT setval('acreditacion_seq', MAX(id)) FROM (SELECT 100 as id UNION SELECT MAX(id) FROM acreditacion) AS s;
SELECT setval('modalidadtierra_seq', MAX(id)) FROM (SELECT 100 as id UNION SELECT MAX(id) FROM modalidadtierra) AS s;


