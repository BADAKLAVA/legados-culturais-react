import React, { useEffect, useState } from "react";
import { Button, Form, Nav, Navbar } from "react-bootstrap";
import logo from "./assets/logo.png";
import cityPhoto from "./assets/login-city.png";

const STORAGE_KEY = "legados-library-repos";

const initialRepos = [
  {
    id: 1,
    title: "Título do repositório",
    description:
      "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean at faucibus sem...",
    tags: ["#lorem", "#loremipsum"],
    type: "Nuvem",
    date: "12/09/2026",
    comments: [
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      },
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
        indent: true,
      },
      {
        name: "Administrador",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
        admin: true,
      },
    ],
  },
  {
    id: 2,
    title: "Memória e patrimônio",
    description:
      "Acervo com documentos e registros de projetos culturais e históricos.",
    tags: ["#cultura", "#historia"],
    type: "Imagem",
    date: "10/09/2026",
    comments: [
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      },
    ],
  },
  {
    id: 3,
    title: "Relatos da comunidade",
    description:
      "Coleção de relatos e entrevistas organizados por temática.",
    tags: ["#relatos", "#comunidade"],
    type: "TXT",
    date: "07/09/2026",
    comments: [
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      },
    ],
  },
  {
    id: 4,
    title: "Fotografias do acervo",
    description:
      "Imagens selecionadas do acervo digital da instituição.",
    tags: ["#fotografia", "#acervo"],
    type: "Imagem",
    date: "03/09/2026",
    comments: [
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      },
    ],
  },
  {
    id: 5,
    title: "Editais e documentos",
    description: "Documentos institucionais e editais para consulta.",
    tags: ["#editais", "#documentos"],
    type: "Documento",
    date: "01/09/2026",
    comments: [
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      },
    ],
  },
  {
    id: 6,
    title: "Links de referência",
    description: "Links e materiais externos de apoio aos projetos.",
    tags: ["#links", "#referencia"],
    type: "Link",
    date: "28/08/2026",
    comments: [
      {
        name: "Lorem Ipsum",
        text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      },
    ],
  },
];

const icons = {
  home: "<svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"M12 3 3 10v10a1 1 0 0 0 1 1h5v-6h6v6h5a1 1 0 0 0 1-1V10l-9-7Z\"/></svg>",
  report: "<svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><rect x=\"4\" y=\"12\" width=\"3\" height=\"8\" rx=\"1\"/><rect x=\"10.5\" y=\"7\" width=\"3\" height=\"13\" rx=\"1\"/><rect x=\"17\" y=\"3\" width=\"3\" height=\"17\" rx=\"1\"/></svg>",
  settings: "<svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"m19.43 12.98.04-.98-.04-.98 2.11-1.65-2-3.46-2.5 1a8 8 0 0 0-1.69-.98L15 3h-4l-.35 2.93c-.6.24-1.16.57-1.69.98l-2.5-1-2 3.46L6.57 11c-.03.32-.05.65-.05 1s.02.68.05 1l-2.11 1.65 2 3.46 2.5-1c.53.41 1.1.74 1.69.98L11 21h4l.35-2.93c.6-.24 1.16-.57 1.69-.98l2.5 1 2-3.46-2.11-1.65ZM13 15.5A3.5 3.5 0 1 1 13 8a3.5 3.5 0 0 1 0 7.5Z\"/></svg>",
  user: "<svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><circle cx=\"12\" cy=\"8\" r=\"4\"/><path d=\"M4 21a8 8 0 0 1 16 0H4Z\"/></svg>",
  search: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\"><circle cx=\"10.8\" cy=\"10.8\" r=\"6.8\"/><path d=\"m16 16 5 5\"/></svg>",
  cloudPlus: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M7.5 18H7a5 5 0 1 1 1.5-9.77A6.5 6.5 0 0 1 21 11.5a3.5 3.5 0 0 1-3.5 6.5H7.5Z\"/><path d=\"M12 12v5M9.5 14.5H14.5\"/></svg>",
  cloudArrow: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M7.5 18H7a5 5 0 1 1 1.5-9.77A6.5 6.5 0 0 1 21 11.5a3.5 3.5 0 0 1-3.5 6.5H7.5Z\"/><path d=\"M12 16V8M9.5 10.5 12 8l2.5 2.5\"/></svg>",
  edit: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m4 16.5-.7 4.2 4.2-.7L19.3 8.2a2.8 2.8 0 0 0-4-4L3.5 15.7Z\"/><path d=\"m14.2 6.3 3.5 3.5\"/></svg>",
  trash: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\"><path d=\"M4 7h16M10 11v6M14 11v6M9 7l1-2h4l1 2M6 7l1 14h10l1-14\"/></svg>",
  image: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linejoin=\"round\"><rect x=\"4\" y=\"3\" width=\"16\" height=\"18\" rx=\"1\"/><circle cx=\"9\" cy=\"8\" r=\"1.4\"/><path d=\"m5.5 18 5.5-6 3.2 3.3 2.3-2.5 3 5.2\"/></svg>",
  doc: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linejoin=\"round\"><path d=\"M6 3h8l4 4v14H6z\"/><path d=\"M14 3v5h4M9 13h6M9 17h6\"/></svg>",
  text: "<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\"><path d=\"M5 5h14M12 5v14M8 19h8\"/></svg>",
  send: "<svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"m4 4 17 8-17 8 2-8-2-8Z\"/></svg>",
  star: "<svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"m12 3 2.8 5.8 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.7l6.2-.9L12 3Z\"/></svg>",
};

function treeLogo() {
  return `<svg viewBox="0 0 100 100" width="100%" height="100%"><g fill="none" stroke="#586d2f" stroke-width="3"><path d="M50 70V39M50 52 37 41M50 56 64 42M50 62 42 54M50 63 59 51"/></g><g fill="#586d2f"><circle cx="32" cy="29" r="10"/><circle cx="47" cy="19" r="11"/><circle cx="63" cy="24" r="10"/><circle cx="73" cy="36" r="8"/><circle cx="23" cy="40" r="8"/></g><path d="M36 75c12 7 25 7 38 0-5 12-11 18-24 19-8-3-13-8-14-19Z" fill="#c9752c"/><path d="M24 83c11 3 20 10 42 7" fill="none" stroke="#586d2f" stroke-width="3" stroke-linecap="round"/></svg>`;
}

function repoTypeIcon(type) {
  if (type === "Imagem") return icons.image;
  if (type === "Documento") return icons.doc;
  if (type === "TXT") return icons.text;
  if (type === "Link") return icons.cloudArrow;
  return icons.cloudArrow;
}

function loadRepos() {
  if (typeof window === "undefined") return initialRepos;
  try {
    const saved = window.localStorage.getItem(STORAGE_KEY);
    return saved ? JSON.parse(saved) : initialRepos;
  } catch {
    return initialRepos;
  }
}

function useLibraryLocation() {
  const [path, setPath] = useState(() => {
    if (typeof window === "undefined") return "/";
    return window.location.pathname.toLowerCase();
  });

  useEffect(() => {
    if (typeof window === "undefined") return undefined;
    const onChange = () => setPath(window.location.pathname.toLowerCase());
    window.addEventListener("popstate", onChange);
    return () => window.removeEventListener("popstate", onChange);
  }, []);

  const navigate = (next) => {
    if (typeof window === "undefined") return;
    const normalized = String(next || "/");
    window.history.pushState({}, "", normalized);
    setPath(normalized.toLowerCase());
  };

  return { path, navigate };
}

function Header() {
  return (
    <Navbar as="header" expand="lg" className="site-header">
      <Navbar.Brand className="brand" href="/" aria-label="Legados Culturais - início">
        <img src={logo} alt="Legados Culturais" />
      </Navbar.Brand>
      <Navbar.Toggle aria-controls="main-navigation" aria-label="Abrir menu" />
      <Navbar.Collapse id="main-navigation">
        <Nav as="nav" className="main-nav" aria-label="Navegação principal">
          <Nav.Link href="#sobre">Quem somos</Nav.Link>
          <Nav.Link href="#contato">Contato</Nav.Link>
          <Nav.Link href="/biblioteca">Biblioteca</Nav.Link>
        </Nav>
        <div className="header-actions">
          <Button as="a" className="btn-orange btn-small" href="/login">
            Entrar
          </Button>
          <Button as="a" className="btn-green btn-small" href="/cadastro">
            Cadastrar
          </Button>
        </div>
      </Navbar.Collapse>
    </Navbar>
  );
}

function Home() {
  return (
    <main className="home-page">
      <Header />

      <section className="hero" id="biblioteca">
        <div className="hero-inner">
          <h1>BEM-VINDO À NOSSA BIBLIOTECA!</h1>
          <div className="hero-copy">
            <p>
              A biblioteca da <strong>Legados Culturais</strong> reúne diversos
              tipos de materiais relacionados ao patrimônio cultural brasileiro.
            </p>
            <p>
              Explore a nossa biblioteca e obtenha os materiais gratuitamente para
              consulta e referência.
            </p>
          </div>
          <a href="/biblioteca" className="btn btn-orange btn-large">
            Explorar biblioteca
          </a>
        </div>
      </section>

      <section className="contact-section" id="contato">
        <div className="section-inner contact-inner">
          <h2>Fale conosco</h2>
          <div className="contact-form" aria-label="Formulário visual de contato">
            <Form.Control type="text" placeholder="Nome" aria-label="Nome" />
            <Form.Control type="email" placeholder="E-mail" aria-label="E-mail" />
            <Form.Control type="text" placeholder="Assunto" aria-label="Assunto" />
            <Form.Control as="textarea" placeholder="Mensagem" aria-label="Mensagem" rows={5} />
            <Button type="button" className="btn-orange contact-submit">
              Enviar mensagem
            </Button>
          </div>
        </div>
      </section>

      <section className="signup-callout" id="sobre">
        <div className="section-inner signup-inner">
          <div className="signup-copy">
            <h2>Nos ajude</h2>
            <p>
              Cadastre-se para ajudar-nos a conhecer melhor qual é o nosso público
              e o seu feedback.
            </p>
            <a href="/cadastro" className="btn btn-green btn-large">
              Cadastre-se
            </a>
          </div>
          <div className="reading-photo" aria-hidden="true">
            <div className="book-scene" />
          </div>
        </div>
      </section>

      <section className="reviews-section">
        <div className="section-inner">
          <h2>Eles aprovaram, e você?</h2>
          <div className="reviews-grid">
            {[4, 5].map((stars, index) => (
              <article className="review" key={index}>
                <div className="review-head">
                  <div className="avatar" aria-hidden="true">
                    <span />
                  </div>
                  <div>
                    <div className="stars" aria-label={`${stars} de 5 estrelas`}>
                      {"★".repeat(stars)}
                      {"☆".repeat(5 - stars)}
                    </div>
                    <div className="review-name">Lorem Ipsum</div>
                  </div>
                </div>
                <div className="review-line" />
                <p>
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do
                  eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </p>
              </article>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}

function LibraryNav({ active }) {
  return (
    <nav className="side-nav">
      <button
        className={`nav-btn ${active === "library" ? "active" : ""}`}
        type="button"
        title="Biblioteca"
        onClick={() => window.location.assign("/biblioteca")}
        dangerouslySetInnerHTML={{ __html: icons.home }}
      />
      <button
        className={`nav-btn ${active === "users" ? "active" : ""}`}
        type="button"
        title="Relatório de usuários"
        onClick={() => window.location.assign("/biblioteca/usuarios")}
        dangerouslySetInnerHTML={{ __html: icons.report }}
      />
      <button
        className="nav-btn"
        type="button"
        title="Configurações"
        onClick={() => window.alert("Configurações: somente front-end por enquanto")}
        dangerouslySetInnerHTML={{ __html: icons.settings }}
      />
      <button
        className="nav-btn"
        type="button"
        title="Perfil"
        onClick={() => window.alert("Perfil: somente front-end por enquanto")}
        dangerouslySetInnerHTML={{ __html: icons.user }}
      />
    </nav>
  );
}

function LibraryPage({ repos, setRepos, navigate }) {
  const [search, setSearch] = useState("");
  const [selectedTag, setSelectedTag] = useState(null);
  const [filterType, setFilterType] = useState("Todos os tipos");
  const [sort, setSort] = useState("A-Z");

  const allTags = Array.from(new Set(repos.flatMap((repo) => repo.tags)));

  const filteredRepos = [...repos]
    .filter((repo) => {
      const query = search.trim().toLowerCase();
      const matchesQuery =
        !query ||
        `${repo.title} ${repo.description} ${repo.tags.join(" ")}`
          .toLowerCase()
          .includes(query);
      const matchesTag = !selectedTag || repo.tags.includes(selectedTag);
      const matchesType = filterType === "Todos os tipos" || repo.type === filterType;
      return matchesQuery && matchesTag && matchesType;
    })
    .sort((a, b) => {
      if (sort === "Z-A") return b.title.localeCompare(a.title, "pt-BR");
      if (sort === "Mais recentes") return b.date.localeCompare(a.date);
      if (sort === "Mais antigos") return a.date.localeCompare(b.date);
      if (sort === "Populares") return b.id - a.id;
      return a.title.localeCompare(b.title, "pt-BR");
    });

  const addTag = () => {
    const value = window.prompt("Adicionar tag:", "#");
    const normalized = value ? value.trim() : "";
    if (!normalized) return;
    const tag = normalized.startsWith("#") ? normalized : `#${normalized}`;
    if (!allTags.includes(tag)) {
      setRepos((previous) =>
        previous.map((repo) => ({
          ...repo,
          tags: repo.tags,
        })),
      );
    }
    setSelectedTag(tag);
  };

  return (
    <div className="library-app">
      <LibraryNav active="library" />
      <main className="main">
        <a href="/" className="brand brand-link" aria-label="Voltar para a página inicial">
          <img className="brand-logo" src={logo} alt="Legados Culturais" />
        </a>

        <h1 className="page-title">Biblioteca</h1>

        <div className="search-wrap">
          <div className="search">
            <input
              type="text"
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Buscar por título descrição"
            />
            <button type="button" title="Buscar" dangerouslySetInnerHTML={{ __html: icons.search }} />
          </div>
        </div>

        <div className="add-row">
          <button type="button" className="add-repo" title="Adicionar repositório" onClick={() => navigate("/biblioteca/novo")} dangerouslySetInnerHTML={{ __html: icons.cloudPlus }} />
        </div>

        <div className="tags-block">
          <div className="tags-label">Tags:</div>
          <div className="tags-row">
            {allTags.map((tag, index) => (
              <span
                key={`${tag}-${index}`}
                className={`tag-chip ${selectedTag === tag ? "active" : ""}`}
                onClick={() => setSelectedTag(selectedTag === tag ? null : tag)}
                role="button"
                tabIndex={0}
                onKeyDown={(event) => {
                  if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    setSelectedTag(selectedTag === tag ? null : tag);
                  }
                }}
              >
                {tag}
              </span>
            ))}
            <button type="button" className="add-tag-circle" title="Adicionar tag" onClick={addTag}>
              +
            </button>
          </div>
        </div>

        <div className="control-row">
          <div className="control-group">
            <div className="control-label">Filtrar:</div>
            <select className="select" value={filterType} onChange={(event) => setFilterType(event.target.value)}>
              <option>Todos os tipos</option>
              <option>Link</option>
              <option>TXT</option>
              <option>Documento</option>
              <option>Imagem</option>
            </select>
          </div>
          <div className="control-group">
            <div className="control-label">Ordenar por:</div>
            <select className="select" value={sort} onChange={(event) => setSort(event.target.value)}>
              <option>A-Z</option>
              <option>Z-A</option>
              <option>Mais recentes</option>
              <option>Mais antigos</option>
              <option>Populares</option>
            </select>
          </div>
        </div>

        {filteredRepos.length ? (
          <div className="repo-grid">
            {filteredRepos.map((repo) => (
              <article key={repo.id} className="repo-card" onClick={() => navigate(`/biblioteca/repositorio/${repo.id}`)}>
                <div className="repo-card-icon" dangerouslySetInnerHTML={{ __html: repoTypeIcon(repo.type) }} />
                <div className="repo-card-body">
                  <div className="repo-card-title">{repo.title}</div>
                  <div className="repo-card-desc">{repo.description}</div>
                </div>
                <div className="card-actions">
                  <button
                    type="button"
                    className="icon-action edit"
                    title="Editar"
                    onClick={(event) => {
                      event.stopPropagation();
                      navigate(`/biblioteca/editar/${repo.id}`);
                    }}
                    dangerouslySetInnerHTML={{ __html: icons.edit }}
                  />
                  <button
                    type="button"
                    className="icon-action delete"
                    title="Excluir"
                    onClick={(event) => {
                      event.stopPropagation();
                      if (window.confirm("Deseja excluir este repositório?")) {
                        setRepos((previous) => previous.filter((item) => item.id !== repo.id));
                      }
                    }}
                    dangerouslySetInnerHTML={{ __html: icons.trash }}
                  />
                </div>
              </article>
            ))}
          </div>
        ) : (
          <div className="empty">Nenhum repositório encontrado.</div>
        )}
      </main>
    </div>
  );
}

function RepositoryPage({ repos, navigate, repoId }) {
  const repo = repos.find((item) => Number(item.id) === Number(repoId));

  if (!repo) {
    return <LibraryPage repos={repos} setRepos={() => {}} navigate={navigate} />;
  }

  return (
    <div className="library-app">
      <LibraryNav active="library" />
      <main className="main">
        <div className="repo-page">
          <div className="header-line">
            <button type="button" className="back-btn" onClick={() => navigate("/biblioteca")}>
              Voltar
            </button>
            <div />
          </div>

          <div className="repo-title-row">
            <h1 className="repo-title">{repo.title}</h1>
          </div>

          <div className="repo-meta">
            {repo.tags.map((tag) => (
              <span key={tag} className="tag-chip">
                {tag}
              </span>
            ))}
          </div>

          <div className="repo-info">
            <div>
              <div>Tipo de arquivo</div>
              <div>
                <strong>Publicado em {repo.date}</strong>
              </div>
            </div>
            <div className="repo-actions">
              <span className="repo-action-label">Editar:</span>
              <button
                type="button"
                className="repo-icon-btn edit"
                title="Editar"
                onClick={() => navigate(`/biblioteca/editar/${repo.id}`)}
                dangerouslySetInnerHTML={{ __html: icons.edit }}
              />
              <span className="repo-action-label">Baixar:</span>
              <button type="button" className="repo-icon-btn download" title="Baixar" dangerouslySetInnerHTML={{ __html: icons.cloudArrow }} />
            </div>
          </div>

          <div className="repo-description">{repo.description || "Sem descrição."}</div>

          <section className="comments">
            <h2>Comentários</h2>
            {(repo.comments || []).map((comment, index) => (
              <div key={`${comment.name}-${index}`} className={`comment ${comment.indent ? "indent" : ""}`}>
                <div className="comment-main">
                  <div className="comment-head">
                    <div className="avatar" dangerouslySetInnerHTML={{ __html: icons.user }} />
                    <div>
                      <span className="comment-role">{comment.admin ? "Administrador" : ""}</span>
                      <span className="comment-name">{comment.name}</span>
                    </div>
                  </div>
                  <div className="comment-text">{comment.text}</div>
                  <div className="comment-footer">
                    {!comment.admin && (
                      <div className="reactions">
                        <button type="button" className="reaction">
                          ♡ 10
                        </button>
                        <button type="button" className="reaction">
                          ♧ 10
                        </button>
                      </div>
                    )}
                    <button type="button" className="reply">
                      Responder
                    </button>
                  </div>
                </div>
              </div>
            ))}
            <div className="comment-box">
              <input type="text" placeholder="Comentar" />
              <button type="button" title="Enviar" dangerouslySetInnerHTML={{ __html: icons.send }} />
            </div>
          </section>
        </div>
      </main>
    </div>
  );
}

function FormPage({ repos, setRepos, navigate, repoId, mode }) {
  const existing = mode === "edit" ? repos.find((repo) => Number(repo.id) === Number(repoId)) : null;
  const [form, setForm] = useState({
    title: existing?.title || "",
    description: existing?.description || "",
    tags: existing?.tags || [],
    fileName: "",
    fileOk: false,
  });
  const [tagInput, setTagInput] = useState("");

  useEffect(() => {
    if (mode === "edit" && existing) {
      setForm({
        title: existing.title,
        description: existing.description,
        tags: existing.tags,
        fileName: "",
        fileOk: false,
      });
    }
    if (mode !== "edit") {
      setForm({
        title: "",
        description: "",
        tags: [],
        fileName: "",
        fileOk: false,
      });
    }
  }, [existing, mode]);

  const addDraftTag = (value) => {
    const normalized = value.trim();
    if (!normalized) return;
    const tag = normalized.startsWith("#") ? normalized : `#${normalized}`;
    setForm((previous) => ({
      ...previous,
      tags: previous.tags.includes(tag) ? previous.tags : [...previous.tags, tag],
    }));
    setTagInput("");
  };

  const removeDraftTag = (index) => {
    setForm((previous) => ({
      ...previous,
      tags: previous.tags.filter((_, tagIndex) => tagIndex !== index),
    }));
  };

  const handleSubmit = (event) => {
    event.preventDefault();

    const nextRepo = {
      id: existing?.id || Math.max(0, ...repos.map((repo) => repo.id)) + 1,
      title: form.title.trim() || "Título do repositório",
      description: form.description || "",
      tags: form.tags,
      type: "Nuvem",
      date: existing?.date || new Date().toLocaleDateString("pt-BR"),
      comments: existing?.comments || [
        {
          name: "Lorem Ipsum",
          text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
        },
      ],
    };

    if (mode === "edit") {
      setRepos((previous) => previous.map((repo) => (Number(repo.id) === Number(existing.id) ? nextRepo : repo)));
    } else {
      setRepos((previous) => [nextRepo, ...previous]);
    }

    navigate("/biblioteca");
  };

  return (
    <div className="library-app">
      <LibraryNav active="library" />
      <main className="main">
        <div className="form-page">
          <div className="form-top">
            <button type="button" className="back-btn" onClick={() => navigate("/biblioteca")}>
              Cancelar
            </button>
            <h1 className="form-title">{mode === "edit" ? "Editar" : "Criar"} Repositório</h1>
            {mode === "edit" && existing ? (
              <button
                type="button"
                className="danger-btn"
                onClick={() => {
                  if (window.confirm("Deseja excluir este repositório?")) {
                    setRepos((previous) => previous.filter((repo) => Number(repo.id) !== Number(existing.id)));
                    navigate("/biblioteca");
                  }
                }}
              >
                Excluir repositório
              </button>
            ) : (
              <div style={{ width: "1px" }} />
            )}
          </div>

          <form onSubmit={handleSubmit}>
            <div className="field">
              <label htmlFor="repo-title">Título</label>
              <input
                id="repo-title"
                required
                value={form.title}
                placeholder="Título do repositório"
                onChange={(event) => setForm((previous) => ({ ...previous, title: event.target.value }))}
              />
            </div>

            <div className="field">
              <label htmlFor="repo-description">
                Descrição <span className="optional">opcional</span>
              </label>
              <textarea
                id="repo-description"
                value={form.description}
                placeholder="Descrição"
                onChange={(event) => setForm((previous) => ({ ...previous, description: event.target.value }))}
              />
            </div>

            <div className="field">
              <label htmlFor="repo-tags">
                Tags <span className="optional">opcional</span>
              </label>
              <input
                id="repo-tags"
                value={tagInput}
                placeholder="Digite uma tag e pressione Enter"
                onKeyDown={(event) => {
                  if (event.key === "Enter") {
                    event.preventDefault();
                    addDraftTag(tagInput);
                  }
                }}
                onChange={(event) => setTagInput(event.target.value)}
              />
              <div className="form-tags">
                {form.tags.map((tag, index) => (
                  <span key={`${tag}-${index}`} className="form-tag">
                    {tag}
                    <button type="button" onClick={() => removeDraftTag(index)}>
                      ×
                    </button>
                  </span>
                ))}
              </div>
            </div>

            {mode !== "edit" && (
              <div className="upload-box">
                <div className="upload-label">Fazer upload</div>
                <div className="file-line">
                  <span className="file-name">{form.fileName || "Nenhum arquivo selecionado"}</span>
                  <button type="button" className="upload-btn" title="Selecionar arquivo" onClick={() => document.getElementById("file-input")?.click()} dangerouslySetInnerHTML={{ __html: icons.cloudPlus }} />
                </div>
                <input
                  id="file-input"
                  className="file-input"
                  type="file"
                  accept=".txt,.pdf,.doc,.docx,.odt,.png,.jpg,.jpeg,.gif,.webp"
                  onChange={(event) => {
                    const file = event.target.files?.[0];
                    setForm((previous) => ({
                      ...previous,
                      fileName: file ? file.name : "",
                      fileOk: Boolean(file),
                    }));
                  }}
                />
                {form.fileOk && <div className="success">Upload realizado com sucesso</div>}
              </div>
            )}

            <div className="form-footer">
              <button type="submit" className="primary-btn">
                {mode === "edit" ? "Salvar alterações" : "Criar repositório"}
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  );
}

function UsersPage() {
  return (
    <div className="library-app">
      <LibraryNav active="users" />
      <main className="main">
        <div className="form-page">
          <div className="header-line">
            <button type="button" className="back-btn" onClick={() => window.location.assign("/biblioteca")}>
              Voltar
            </button>
            <h1 className="form-title" style={{ margin: "0 auto", fontWeight: 400 }}>
              Relatório de usuários
            </h1>
            <div style={{ width: "64px" }} />
          </div>
          <div className="empty">
            <p>Área demonstrativa do front-end.</p>
            <p>A tela fica acessível somente quando o perfil de administrador está ativo.</p>
          </div>
        </div>
      </main>
    </div>
  );
}

function Input({ label, placeholder, type = "text", className = "", value, onChange }) {
  return (
    <Form.Group className={`field ${className}`} controlId={label}>
      <Form.Label>{label}</Form.Label>
      <Form.Control type={type} placeholder={placeholder} value={value} onChange={onChange} />
    </Form.Group>
  );
}

function Cadastro() {
  const [nome, setNome] = useState("");
  const [sobrenome, setSobrenome] = useState("");
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const [telefone, setTelefone] = useState("");
  const [dataNascimento, setDataNascimento] = useState("");
  const [sexo, setSexo] = useState("");
  const [genero, setGenero] = useState("");
  const [outroGenero, setOutroGenero] = useState("");
  const [estado, setEstado] = useState("");
  const [cidade, setCidade] = useState("");
  const [bairro, setBairro] = useState("");
  const [logradouro, setLogradouro] = useState("");
  const [numero, setNumero] = useState("");
  const [complemento, setComplemento] = useState("");
  const [erros, setErros] = useState({});

  async function cadastrar(event) {
    event.preventDefault();
    const novosErros = {};

    if (!nome.trim()) novosErros.nome = "O nome é obrigatório.";
    if (!sobrenome.trim()) novosErros.sobrenome = "*O sobrenome é obrigatório.";
    if (!email.includes("@") || !email.includes(".")) novosErros.email = "*Digite um e-mail válido.";
    if (senha.length < 8) novosErros.senha = "*A senha deve ter pelo menos 8 caracteres.";
    if (!dataNascimento) novosErros.dataNascimento = "*A data de nascimento é obrigatória.";
    if (!sexo) novosErros.sexo = "*Selecione o sexo.";
    if (!genero) novosErros.genero = "*Selecione o gênero.";

    let generoFinal = genero;
    if (genero === "Outro") {
      if (!outroGenero.trim()) {
        novosErros.outroGenero = "*Especifique o gênero.";
      } else {
        generoFinal = outroGenero;
      }
    }

    if (!estado) novosErros.estado = "*Selecione o estado.";
    if (!cidade.trim()) novosErros.cidade = "*A cidade é obrigatória.";
    if (!bairro.trim()) novosErros.bairro = "*O bairro é obrigatório.";

    setErros(novosErros);
    if (Object.keys(novosErros).length > 0) return;

    const resposta = await fetch("http://localhost:8000/routes.php?rota=cadastro", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        nome,
        sobrenome,
        email,
        senha,
        telefone,
        dataNascimento,
        sexo,
        genero: generoFinal,
        estado,
        cidade,
        bairro,
        logradouro,
        numero,
        complemento,
      }),
    });

    if (resposta.ok) {
      window.alert("Cadastro realizado com sucesso");
    } else {
      window.alert("Erro ao cadastrar!");
    }
  }

  return (
    <main className="form-page cadastro-page">
      <section className="form-card cadastro-card">
        <a href="/" aria-label="Voltar para a página inicial">
          <img className="form-logo" src={logo} alt="Legados Culturais" />
        </a>
        <h1>Cadastro</h1>
        <form onSubmit={cadastrar}>
          <div className="form-fields">
            <div>
              <Input label="Nome" placeholder="Nome" value={nome} onChange={(e) => setNome(e.target.value)} />
              {erros.nome && <span className="form-error">{erros.nome}</span>}
            </div>

            <div>
              <Input label="Sobrenome" placeholder="Sobrenome" value={sobrenome} onChange={(e) => setSobrenome(e.target.value)} />
              {erros.sobrenome && <span className="form-error">{erros.sobrenome}</span>}
            </div>

            <div>
              <Input label="E-mail" placeholder="E-mail" type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
              {erros.email && <span className="form-error">{erros.email}</span>}
            </div>

            <div>
              <Input label="Senha" placeholder="Senha" type="password" value={senha} onChange={(e) => setSenha(e.target.value)} />
              {erros.senha && <span className="form-error">{erros.senha}</span>}
            </div>

            <div className="field-row compact-row">
              <div>
                <Input label="Telefone" placeholder="(11) 11111-1111" value={telefone} onChange={(e) => setTelefone(e.target.value)} />
              </div>
              <div>
                <Input label="Data de nascimento" placeholder="dd/mm/aaaa" type="date" value={dataNascimento} onChange={(e) => setDataNascimento(e.target.value)} />
                {erros.dataNascimento && <span className="form-error">{erros.dataNascimento}</span>}
              </div>
            </div>

            <fieldset className="radio-group">
              <legend>Sexo:</legend>
              <Form.Check inline type="radio" name="sexo" label="Masculino" value="Masculino" onChange={(e) => setSexo(e.target.value)} />
              <Form.Check inline type="radio" name="sexo" label="Feminino" value="Feminino" onChange={(e) => setSexo(e.target.value)} />
              {erros.sexo && <span className="form-error" style={{ display: "block" }}>{erros.sexo}</span>}
            </fieldset>

            <fieldset className="radio-group gender-group">
              <legend>Gênero:</legend>
              <div className="gender-options">
                <Form.Check inline type="radio" name="genero" label="Masculino" value="Masculino" onChange={(e) => setGenero(e.target.value)} />
                <Form.Check inline type="radio" name="genero" label="Feminino" value="Feminino" onChange={(e) => setGenero(e.target.value)} />
                <Form.Check inline type="radio" name="genero" label="Não binário" value="Não binário" onChange={(e) => setGenero(e.target.value)} />
                <Form.Check inline type="radio" name="genero" label="Outro:" value="Outro" onChange={(e) => setGenero(e.target.value)} />
                <Form.Control className="other-input" placeholder="Outro" value={outroGenero} onChange={(e) => setOutroGenero(e.target.value)} disabled={genero !== "Outro"} />
              </div>
              {erros.genero && <span className="form-error" style={{ display: "block" }}>{erros.genero}</span>}
              {erros.outroGenero && <span className="form-error" style={{ display: "block" }}>{erros.outroGenero}</span>}
            </fieldset>

            <div className="state-city-row">
              <label className="field state-field">
                <span>Estado</span>
                <select className="select-button" value={estado} onChange={(e) => setEstado(e.target.value)}>
                  <option value="">Selecionar</option>
                  <option value="AC">AC</option>
                  <option value="AL">AL</option>
                  <option value="AP">AP</option>
                  <option value="AM">AM</option>
                  <option value="BA">BA</option>
                  <option value="CE">CE</option>
                  <option value="DF">DF</option>
                  <option value="ES">ES</option>
                  <option value="GO">GO</option>
                  <option value="MA">MA</option>
                  <option value="MT">MT</option>
                  <option value="MS">MS</option>
                  <option value="MG">MG</option>
                  <option value="PA">PA</option>
                  <option value="PB">PB</option>
                  <option value="PR">PR</option>
                  <option value="PE">PE</option>
                  <option value="PI">PI</option>
                  <option value="RJ">RJ</option>
                  <option value="RN">RN</option>
                  <option value="RO">RO</option>
                  <option value="RR">RR</option>
                  <option value="RS">RS</option>
                  <option value="SC">SC</option>
                  <option value="SP">SP</option>
                  <option value="SE">SE</option>
                  <option value="TO">TO</option>
                </select>
                {erros.estado && <span className="form-error">{erros.estado}</span>}
              </label>

              <div>
                <Input label="Cidade" placeholder="Cidade" className="city-field" value={cidade} onChange={(e) => setCidade(e.target.value)} />
                {erros.cidade && <span className="form-error">{erros.cidade}</span>}
              </div>
            </div>

            <div>
              <Input label="Bairro" placeholder="Bairro" value={bairro} onChange={(e) => setBairro(e.target.value)} />
              {erros.bairro && <span className="form-error">{erros.bairro}</span>}
            </div>

            <div>
              <Input label="Logradouro" placeholder="Logradouro" value={logradouro} onChange={(e) => setLogradouro(e.target.value)} />
            </div>

            <div className="field-row address-row">
              <div>
                <Input label="Número" placeholder="Número" value={numero} onChange={(e) => setNumero(e.target.value)} />
              </div>
              <div>
                <Input label="Complemento" placeholder="Complemento" value={complemento} onChange={(e) => setComplemento(e.target.value)} />
              </div>
            </div>
          </div>

          <Button type="submit" className="btn-orange btn-large form-submit">
            Cadastrar
          </Button>
        </form>
      </section>
    </main>
  );
}

function Login() {
  const [tipo, setTipo] = useState("us");
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const [erroEmail, setErroEmail] = useState("");
  const [erroSenha, setErroSenha] = useState("");
  const padraoEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  async function validarLogin(e) {
    e.preventDefault();
    setErroEmail("");
    setErroSenha("");

    let temErro = false;
    if (email === "") {
      setErroEmail("Insira o e-mail*");
      temErro = true;
    } else if (!padraoEmail.test(email)) {
      setErroEmail("E-mail inválido*");
      temErro = true;
    }

    if (senha === "") {
      setErroSenha("Digite a senha*");
      temErro = true;
    }
    if (temErro) return;

    const resposta = await fetch("http://localhost:8000/routes.php?rota=login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ tipo, email, senha }),
    });

    const dados = await resposta.json();
    if (resposta.ok) {
      window.location.href = "http://localhost:8000/teste.php";
    } else {
      setErroSenha(dados.erro);
    }
  }

  return (
    <main className="login-page">
      <section className="login-panel">
        <form className="login-form" aria-label="Formulário de login" onSubmit={validarLogin}>
          <a href="/" aria-label="Voltar para a página inicial">
            <img className="login-logo" src={logo} alt="Legados Culturais" />
          </a>
          <div className="login-content">
            <h1>Entrar</h1>
            <div className="account-type">
              <span>Tipo de conta:</span>
              <select className="select-button" onChange={(e) => setTipo(e.target.value)}>
                <option value="us">Usuário</option>
                <option value="ad">Admin</option>
              </select>
            </div>
            <Input label="E-mail" placeholder="E-mail" value={email} onChange={(e) => setEmail(e.target.value)} />
            {erroEmail && <span className="form-error">{erroEmail}</span>}
            <Input label="Senha" placeholder="Senha" type="password" value={senha} onChange={(e) => setSenha(e.target.value)} />
            {erroSenha && <span className="form-error">{erroSenha}</span>}
            <Button type="submit" className="btn-orange btn-large login-submit">
              Entrar
            </Button>
          </div>
        </form>
      </section>
      <div className="login-image" style={{ backgroundImage: `linear-gradient(rgba(32,32,24,.30), rgba(32,32,24,.30)), url(${cityPhoto})` }} aria-hidden="true" />
    </main>
  );
}

export default function App() {
  const [repos, setRepos] = useState(loadRepos);
  const { path, navigate } = useLibraryLocation();

  useEffect(() => {
    if (typeof window !== "undefined") {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(repos));
    }
  }, [repos]);

  if (path === "/cadastro" || path === "/cadastro/") return <Cadastro />;
  if (path === "/login" || path === "/login/") return <Login />;
  if (path === "/biblioteca" || path === "/biblioteca/") return <LibraryPage repos={repos} setRepos={setRepos} navigate={navigate} />;
  if (path === "/biblioteca/usuarios" || path === "/biblioteca/usuarios/") return <UsersPage />;
  if (path === "/biblioteca/novo" || path === "/biblioteca/novo/") return <FormPage repos={repos} setRepos={setRepos} navigate={navigate} mode="create" />;

  const repoMatch = path.match(/^\/biblioteca\/repositorio\/(\d+)$/);
  if (repoMatch) {
    return <RepositoryPage repos={repos} setRepos={setRepos} navigate={navigate} repoId={repoMatch[1]} />;
  }

  const editMatch = path.match(/^\/biblioteca\/editar\/(\d+)$/);
  if (editMatch) {
    return <FormPage repos={repos} setRepos={setRepos} navigate={navigate} repoId={editMatch[1]} mode="edit" />;
  }

  return <Home />;
}
