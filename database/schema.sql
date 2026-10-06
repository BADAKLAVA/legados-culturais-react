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
	data_repositorio TIMESTAMP,

	CONSTRAINT pk_repositorio
		PRIMARY KEY (id_repositorio)
);

CREATE TABLE IF NOT EXISTS historico(
	id_historico INTEGER GENERATED ALWAYS AS IDENTITY,
	data_historico TIMESTAMP NOT NULL,
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
	CONSTRAINT fk_detalhe_tag_repositorio
		FOREIGN KEY (id_repositorio) REFERENCES repositorio(id_repositorio),
	CONSTRAINT fk_detalhe_tag_tag
		FOREIGN KEY (id_tag) REFERENCES tag(id_tag)
);
