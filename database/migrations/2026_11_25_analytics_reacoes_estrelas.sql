-- Execute uma vez no PostgreSQL já utilizado pela aplicação.
-- Se houver mais de uma estrela para o mesmo usuário/repositório, resolva as duplicatas
-- antes de aplicar a restrição UNIQUE (consulte o README).
DO $$
DECLARE
    tem_likes_anteriores BOOLEAN;
    tem_dislikes_anteriores BOOLEAN;
BEGIN
    SELECT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = current_schema() AND table_name = 'comentario'
          AND column_name = 'likes_anteriores'
    ) INTO tem_likes_anteriores;

    SELECT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = current_schema() AND table_name = 'comentario'
          AND column_name = 'dislikes_anteriores'
    ) INTO tem_dislikes_anteriores;

    ALTER TABLE comentario
        ADD COLUMN IF NOT EXISTS likes_anteriores INTEGER NOT NULL DEFAULT 0,
        ADD COLUMN IF NOT EXISTS dislikes_anteriores INTEGER NOT NULL DEFAULT 0;

    -- Os contadores antigos não identificavam quem reagiu. Preserve-os como base
    -- histórica apenas na primeira execução desta migração.
    IF NOT tem_likes_anteriores THEN
        UPDATE comentario SET likes_anteriores = COALESCE(like_opcao, 0);
    END IF;
    IF NOT tem_dislikes_anteriores THEN
        UPDATE comentario SET dislikes_anteriores = COALESCE(dislike_opcao, 0);
    END IF;
END $$;

UPDATE comentario
SET like_opcao = COALESCE(like_opcao, 0),
    dislike_opcao = COALESCE(dislike_opcao, 0);

ALTER TABLE comentario
    ALTER COLUMN like_opcao SET DEFAULT 0,
    ALTER COLUMN like_opcao SET NOT NULL,
    ALTER COLUMN dislike_opcao SET DEFAULT 0,
    ALTER COLUMN dislike_opcao SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'uk_estrela_usuario_repositorio'
          AND conrelid = 'estrela'::regclass
    ) THEN
        ALTER TABLE estrela
            ADD CONSTRAINT uk_estrela_usuario_repositorio
            UNIQUE (id_usuario, id_repositorio);
    END IF;
END $$;

CREATE TABLE IF NOT EXISTS reacao_comentario(
    id_reacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_comentario INTEGER NOT NULL REFERENCES comentario(id_comentario) ON DELETE CASCADE,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    tipo VARCHAR(7) NOT NULL CHECK (tipo IN ('like', 'dislike')),
    CONSTRAINT uk_reacao_comentario_usuario UNIQUE (id_comentario, id_usuario)
);

CREATE TABLE IF NOT EXISTS evento_site(
    id_evento INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tipo_evento VARCHAR(20) NOT NULL CHECK (tipo_evento IN ('visualizacao', 'heartbeat', 'login')),
    pagina VARCHAR(255) NOT NULL,
    id_usuario INTEGER REFERENCES usuario(id_usuario) ON DELETE SET NULL,
    sessao_hash CHAR(64) NOT NULL,
    data_evento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_evento_site_data_tipo ON evento_site (data_evento, tipo_evento);
CREATE INDEX IF NOT EXISTS idx_evento_site_pagina ON evento_site (pagina, data_evento);
