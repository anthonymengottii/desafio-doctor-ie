-- Extensoes usadas pela busca e similaridade de titulos.
-- pg_trgm: similaridade fuzzy (trigram) com indice GIN.
-- unaccent: normaliza acentuacao para busca case/acento-insensitive.
CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION IF NOT EXISTS unaccent;
