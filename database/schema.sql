CREATE TABLE IF NOT EXISTS usuario(
	id_usuario INTEGER GENERATED ALWAYS AS IDENTITY,
	tipo CHAR(2) DEFAULT 'us',
	nome VARCHAR(50) NOT NULL,
	sobrenome VARCHAR(100) NOT NULL,
	email VARCHAR(250) NOT NULL,
	senha VARCHAR(255) NOT NULL,
	telefone VARCHAR(18),
	data_nascimento DATE NOT NULL,
	sexo CHAR(1) NOT NULL,
	genero VARCHAR(100) NOT NULL,
	estado CHAR(2) NOT NULL,
	cidade VARCHAR(100) NOT NULL,
	bairro VARCHAR(100) NOT NULL,
	logradouro VARCHAR(100),
	numero VARCHAR(10),
	complemento VARCHAR(50),
	caminho_foto_perfil VARCHAR(255),

	CONSTRAINT uk_email 
		UNIQUE(email),
	CONSTRAINT uk_telefone 
		UNIQUE (telefone),
	CONSTRAINT pk_usuario 
		PRIMARY KEY (id_usuario)
);

CREATE TABLE IF NOT EXISTS repositorio(
	id_repositorio INTEGER GENERATED ALWAYS AS IDENTITY,
	titulo VARCHAR(100) NOT NULL,
	descricao TEXT,
	tipo VARCHAR(100) NOT NULL,
	caminho VARCHAR(500),
	nome_arquivo VARCHAR(255),
	data_repositorio TIMESTAMP,

	CONSTRAINT pk_repositorio
		PRIMARY KEY (id_repositorio)
);

CREATE TABLE IF NOT EXISTS historico(
	id_historico INTEGER GENERATED ALWAYS AS IDENTITY,
	data_historico TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	id_usuario INTEGER NOT NULL,
	id_repositorio INTEGER NOT NULL,

	CONSTRAINT pk_historico
		PRIMARY KEY (id_historico),
	CONSTRAINT fk_historico_usuario
		FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_historico_repositorio
		FOREIGN KEY (id_repositorio) REFERENCES repositorio(id_repositorio)
);

CREATE TABLE IF NOT EXISTS comentario(
	id_comentario INTEGER GENERATED ALWAYS AS IDENTITY,
	texto TEXT NOT NULL,
	like_opcao INTEGER,
	dislike_opcao INTEGER,
	data_comentario TIMESTAMP NOT NULL,
	id_usuario INTEGER NOT NULL,
	id_repositorio INTEGER NOT NULL,
	id_comentario_resposta INTEGER,

	CONSTRAINT pk_comentario
		PRIMARY KEY (id_comentario),
	CONSTRAINT fk_comentario_usuario
		FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_comentario_repositorio
		FOREIGN KEY (id_repositorio) REFERENCES repositorio(id_repositorio),
	CONSTRAINT fk_comentario_resposta
		FOREIGN KEY (id_comentario_resposta) REFERENCES comentario(id_comentario)
);

CREATE TABLE IF NOT EXISTS estrela(
	id_estrela INTEGER GENERATED ALWAYS AS IDENTITY,
	nota NUMERIC(2,1) NOT NULL,
	data_estrela TIMESTAMP NOT NULL,
	id_usuario INTEGER NOT NULL,
	id_repositorio INTEGER NOT NULL,

	CONSTRAINT pk_estrela
		PRIMARY KEY (id_estrela),
	CONSTRAINT fk_estrela_usuario 
		FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_estrela_repositorio
		FOREIGN KEY (id_repositorio) REFERENCES repositorio(id_repositorio)
);

CREATE TABLE IF NOT EXISTS tag(
	id_tag INTEGER GENERATED ALWAYS AS IDENTITY,
	nome VARCHAR(20) NOT NULL,

	CONSTRAINT uk_nome
		UNIQUE (nome),
	CONSTRAINT pk_tag
		PRIMARY KEY (id_tag)
);

CREATE TABLE IF NOT EXISTS detalhe_tag(
	id_detalhe_tag INTEGER GENERATED ALWAYS AS IDENTITY,
	id_repositorio INTEGER NOT NULL,
	id_tag INTEGER NOT NULL,

	CONSTRAINT pk_detalhe_tag
		PRIMARY KEY (id_detalhe_tag),
	CONSTRAINT uk_repositorio_tag
		UNIQUE (id_repositorio, id_tag),
	CONSTRAINT fk_detalhe_tag_repositorio
		FOREIGN KEY (id_repositorio) REFERENCES repositorio(id_repositorio),
	CONSTRAINT fk_detalhe_tag_tag
		FOREIGN KEY (id_tag) REFERENCES tag(id_tag)
);

CREATE TABLE recuperacao_senha (
    id_recuperacao_senha INTEGER GENERATED ALWAYS AS IDENTITY,
    id_usuario INTEGER NOT NULL,
    token VARCHAR(64) NOT NULL,
    expiracao TIMESTAMPTZ NOT NULL,
    utilizado BOOLEAN NOT NULL DEFAULT FALSE,

	CONSTRAINT pk_recuperacao_senha
		PRIMARY KEY (id_recuperacao_senha),
	CONSTRAINT fk_recuperacao_usuario
		FOREIGN KEY (id_usuario)
			REFERENCES usuario(id_usuario),
	CONSTRAINT uk_token
		UNIQUE (token)
);

-- FUNCTIONS
-- DELETAR HISTORICO DE USUARIO INEXISTENTE
CREATE OR REPLACE FUNCTION function_delete_historico_usuario()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM historico
		WHERE id_usuario = OLD.id_usuario;
		RETURN OLD;
	END;
$$;

-- DELETAR HISTORICO DE REPOSITORIO INEXISTENTE
CREATE OR REPLACE FUNCTION function_delete_historico_repositorio()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM historico
		WHERE id_repositorio = OLD.id_repositorio;
		RETURN OLD;
	END;
$$;

-- DELETAR COMENTARIO DE USUARIO INEXISTENTE
CREATE OR REPLACE FUNCTION function_delete_comentario_usuario()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM comentario
		WHERE id_usuario = OLD.id_usuario;
		RETURN OLD;
	END;
$$;

-- DELETAR COMENTARIO DE REPOSITORIO INEXISTENTE
CREATE OR REPLACE FUNCTION function_delete_comentario_repositorio()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM comentario
		WHERE id_repositorio = OLD.id_repositorio;
		RETURN OLD;
	END;
$$;	

-- DELETAR COMENTARIO RESPOSTA SEM UM COMENTARIO RESPONDIDO CORRESPONDENTE
CREATE OR REPLACE FUNCTION function_delete_comentario_resposta()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM comentario
		WHERE id_comentario_resposta = OLD.id_comentario;
		RETURN OLD;
	END;
$$;

-- DELETAR ESTRELA SEM UM USUARIO CORRESPONDENTE
CREATE OR REPLACE FUNCTION function_delete_estrela_usuario()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM estrela
		WHERE id_usuario = OLD.id_usuario;
		return OLD;
	END;
$$;

-- DELETAR ESTRELA SEM UM REPOSITORIO CORRESPONDENTE
CREATE OR REPLACE FUNCTION function_delete_estrela_repositorio()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN
		DELETE FROM estrela
		WHERE id_repositorio = OLD.id_repositorio;
		return OLD;
	END;
$$;

-- DELETAR DETALHE_TAG SEM UM REPOSITORIO CORRESPONDENTE
CREATE OR REPLACE FUNCTION function_delete_detalhe_tag_repositorio()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
	BEGIN 
		DELETE FROM detalhe_tag
		WHERE id_repositorio = OLD.id_repositorio;
		RETURN OLD;
	END;
$$;

-- TRIGGERS
-- USUARIO SEM CORRESPONDENCIA EM HISTORICO APOS DELETE 
CREATE TRIGGER trigger_fk_historico_usuario
BEFORE DELETE ON usuario
FOR EACH ROW
EXECUTE FUNCTION function_delete_historico_usuario();

-- REPOSITORIO SEM CORRESPONDENCIA EM HISTORICO APOS DELETE 	
CREATE TRIGGER trigger_fk_historico_repositorio
BEFORE DELETE ON repositorio
FOR EACH ROW
EXECUTE FUNCTION function_delete_historico_repositorio();

-- USUARIO SEM CORRESPONDENCIA EM COMENTARIO APOS DELETE 
CREATE TRIGGER trigger_fk_comentario_usuario
BEFORE DELETE ON usuario
FOR EACH ROW
EXECUTE FUNCTION function_delete_comentario_usuario();

-- REPOSITORIO SEM CORRESPONDENCIA EM COMENTARIO APOS DELETE 
CREATE TRIGGER trigger_fk_comentario_repositorio
BEFORE DELETE ON repositorio
FOR EACH ROW
EXECUTE FUNCTION function_delete_comentario_repositorio();

-- COMENTARIO DE RESPOSTA SEM CORRESPONDENCIA NO COMENTARIO RESPONDIDO APOS DELETE 
CREATE TRIGGER trigger_fk_comentario_resposta
BEFORE DELETE ON comentario
FOR EACH ROW
EXECUTE FUNCTION function_delete_comentario_resposta();

-- USUARIO SEM CORRESPONDENCIA EM ESTRELA
CREATE TRIGGER trigger_fk_estrela_usuario
BEFORE DELETE ON usuario
FOR EACH ROW
EXECUTE FUNCTION function_delete_estrela_usuario();

-- REPOSITORIO SEM CORRESPONDENCIA EM ESTRELA
CREATE TRIGGER trigger_fk_estrela_repositorio
BEFORE DELETE ON repositorio
FOR EACH ROW
EXECUTE FUNCTION function_delete_estrela_repositorio();

-- REPOSITORIO SEM CORRESPONDENCIA EM DETALHE_TAG
CREATE TRIGGER trigger_fk_detalhe_tag_repositorio
BEFORE DELETE ON repositorio
FOR EACH ROW
EXECUTE FUNCTION function_delete_detalhe_tag_repositorio();