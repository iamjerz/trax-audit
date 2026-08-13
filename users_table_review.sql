-- Users table review — schema-intrinsic data-quality checks only
-- (no other tables referenced). Run against TraxDB (psql, pgAdmin, DBeaver,
-- etc.) — Claude's sandbox can't reach 127.0.0.1 on your machine, so these
-- need to be run locally.

-- 1. Duplicate emails, ignoring case (the unique constraint is case-sensitive,
--    so "Jerramy@x.com" and "jerramy@x.com" could both exist).
SELECT lower(email) AS email_lc, count(*)
FROM users
GROUP BY lower(email)
HAVING count(*) > 1;

-- 2. Duplicate employeeid, ignoring case.
SELECT lower(employeeid) AS employeeid_lc, count(*)
FROM users
GROUP BY lower(employeeid)
HAVING count(*) > 1;

-- 3. supervisor_id values that don't match any employeeid (there's no FK
--    constraint on this column, so nothing prevents an orphaned reference).
SELECT id, employeeid, first_name, last_name, supervisor_id
FROM users u
WHERE supervisor_id IS NOT NULL AND supervisor_id <> ''
  AND NOT EXISTS (SELECT 1 FROM users s WHERE s.employeeid = u.supervisor_id);

-- 4. Users who are their own supervisor.
SELECT id, employeeid, first_name, last_name
FROM users
WHERE supervisor_id = employeeid;

-- 5. Blank strings on required columns that have no default (an empty
--    string satisfies NOT NULL, so this slips past the schema).
SELECT id, employeeid, first_name, last_name
FROM users
WHERE trim(position) = '' OR trim(department) = '' OR trim(supervisor_id) = '';

-- 6. Position distribution — eyeball for near-duplicate spellings/casing.
SELECT position, count(*) FROM users GROUP BY position ORDER BY count(*) DESC;

-- 7. Status distribution.
SELECT status, count(*) FROM users GROUP BY status ORDER BY count(*) DESC;

-- 8. Role distribution.
SELECT role, count(*) FROM users GROUP BY role ORDER BY count(*) DESC;
