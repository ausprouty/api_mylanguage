-- 012_hl_languages_normalize.sql
-- Normalize hl_languages for HL→ISO mapping.
-- - utf8mb4 defaults
-- - widen code columns (BCP-47 capable)
-- - add UNIQUE on (non-empty) HL via generated column
-- - index ISO
-- - ensure PK on id; drop redundant unique "ID"

START TRANSACTION;

-- 0) Ensure defaults are utf8mb4 (no data rewrite)
ALTER TABLE hl_languages
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 1) Null-safety before type changes
UPDATE hl_languages SET languageCodeHL  = '' WHERE languageCodeHL  IS NULL;
UPDATE hl_languages SET languageCodeIso = '' WHERE languageCodeIso IS NULL;

-- 2) Widen code columns (BCP-47 can be >6 chars)
--    HL examples: 'cmn-Hans-CN' (11), 'yue-Hant-HK' (11)
--    ISO examples: 'zh-Hans' (7), 'sr-Latn' (7), 'pt-BR' (5)
ALTER TABLE hl_languages
  MODIFY COLUMN languageCodeHL  VARCHAR(32) NOT NULL DEFAULT '',
  MODIFY COLUMN languageCodeIso VARCHAR(32) NOT NULL DEFAULT '';

-- 3) Add a normalized (nullable) generated column so we can enforce
--    uniqueness only when HL is non-empty (UNIQUE allows multiple NULLs).
SET @c := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name   = 'hl_languages'
    AND column_name  = 'hlNormalized'
);
SET @sql := IF(@c=0,
  'ALTER TABLE hl_languages
     ADD COLUMN hlNormalized VARCHAR(32)
       GENERATED ALWAYS AS (NULLIF(languageCodeHL, ''''))
       VIRTUAL;',
  'SELECT 1;'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 4) Add UNIQUE on the normalized HL (non-empty only)
SET @idx := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=DATABASE()
    AND table_name='hl_languages'
    AND index_name='uq_hlNormalized'
);
SET @sql := IF(@idx=0,
  'ALTER TABLE hl_languages
       ADD UNIQUE KEY uq_hlNormalized (hlNormalized);',
  'SELECT 1;'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 5) Add an index on ISO for reverse lookups
SET @idx := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=DATABASE()
    AND table_name='hl_languages'
    AND index_name='idx_languageCodeIso'
);
SET @sql := IF(@idx=0,
  'ALTER TABLE hl_languages
       ADD KEY idx_languageCodeIso (languageCodeIso);',
  'SELECT 1;'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 6) Ensure a PRIMARY KEY on id; drop redundant UNIQUE 'ID'
-- 6a) Add PK if missing
SET @has_pk := (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE table_schema=DATABASE()
    AND table_name='hl_languages'
    AND constraint_type='PRIMARY KEY'
);
SET @sql := IF(@has_pk=0,
  'ALTER TABLE hl_languages
       ADD PRIMARY KEY (id);',
  'SELECT 1;'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 6b) Drop old UNIQUE index named `ID` if it exists (redundant with PK)
SET @idx := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=DATABASE()
    AND table_name='hl_languages'
    AND index_name='ID'
);
SET @sql := IF(@idx=1,
  'ALTER TABLE hl_languages DROP INDEX ID;',
  'SELECT 1;'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

COMMIT;
