import React from "react";
import { Button, Form, Nav, Navbar } from "react-bootstrap";
import logo from "./assets/logo.png";
import cityPhoto from "./assets/login-city.png";

const reviews = [
  {
    name: "Lorem Ipsum",
    stars: 4,
    text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
  },
  {
    name: "Lorem Ipsum",
    stars: 5,
    text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
  },
];

function Header() {
  return (
    <Navbar as="header" className="site-header">
      <Navbar.Brand
        className="brand"
        href="/"
        aria-label="Legados Culturais - início"
      >
        <img src={logo} alt="Legados Culturais" />
      </Navbar.Brand>
      <Nav as="nav" className="main-nav" aria-label="Navegação principal">
        <Nav.Link href="#sobre">Quem somos</Nav.Link>
        <Nav.Link href="#contato">Contato</Nav.Link>
        <Nav.Link href="#biblioteca">Biblioteca</Nav.Link>
      </Nav>
      <div className="header-actions">
        <Button as="a" className="btn-orange btn-small" href="/login">
          Entrar
        </Button>
        <Button as="a" className="btn-green btn-small" href="/cadastro">
          Cadastrar
        </Button>
      </div>
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
              Explore a nossa biblioteca e obtenha os materiais gratuitamente
              para consulta e referência.
            </p>
          </div>
          <a href="#catalogo" className="btn btn-orange btn-large">
            Explorar biblioteca
          </a>
        </div>
      </section>

      <section className="contact-section" id="contato">
        <div className="section-inner contact-inner">
          <h2>Fale conosco</h2>
          <div
            className="contact-form"
            aria-label="Formulário visual de contato"
          >
            <Form.Control type="text" placeholder="Nome" aria-label="Nome" />
            <Form.Control
              type="email"
              placeholder="E-mail"
              aria-label="E-mail"
            />
            <Form.Control
              type="text"
              placeholder="Assunto"
              aria-label="Assunto"
            />
            <Form.Control
              as="textarea"
              placeholder="Mensagem"
              aria-label="Mensagem"
              rows={5}
            />
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
              Cadastre-se para ajudar-nos a conhecer melhor qual é o nosso
              público e o seu feedback.
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
            {reviews.map((review, index) => (
              <article className="review" key={index}>
                <div className="review-head">
                  <div className="avatar" aria-hidden="true">
                    <span />
                  </div>
                  <div>
                    <div
                      className="stars"
                      aria-label={`${review.stars} de 5 estrelas`}
                    >
                      {"★".repeat(review.stars)}
                      {"☆".repeat(5 - review.stars)}
                    </div>
                    <div className="review-name">{review.name}</div>
                  </div>
                </div>
                <div className="review-line" />
                <p>{review.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}

function Input({ label, placeholder, type = "text", className = "" }) {
  return (
    <Form.Group className={`field ${className}`} controlId={label}>
      <Form.Label>{label}</Form.Label>
      <Form.Control type={type} placeholder={placeholder} />
    </Form.Group>
  );
}

function Cadastro() {
  return (
    <main className="form-page cadastro-page">
      <section className="form-card cadastro-card">
        <img className="form-logo" src={logo} alt="Legados Culturais" />
        <h1>Cadastro</h1>
        <div className="form-fields">
          <Input label="Nome" placeholder="Nome" />
          <Input label="Sobrenome" placeholder="Sobrenome" />
          <Input label="E-mail" placeholder="E-mail" type="email" />
          <Input label="Senha" placeholder="Senha" type="password" />

          <div className="field-row compact-row">
            <Input label="Telefone" placeholder="(11) 11111-1111" />
            <Input label="Data de nascimento" placeholder="dd/mm/aaaa" />
          </div>

          <fieldset className="radio-group">
            <legend>Sexo:</legend>
            <Form.Check inline type="radio" name="sexo" label="Masculino" />
            <Form.Check inline type="radio" name="sexo" label="Feminino" />
          </fieldset>

          <fieldset className="radio-group gender-group">
            <legend>Gênero:</legend>
            <div className="gender-options">
              <Form.Check inline type="radio" name="genero" label="Masculino" />
              <Form.Check inline type="radio" name="genero" label="Feminino" />
              <Form.Check
                inline
                type="radio"
                name="genero"
                label="Não binário"
              />
              <Form.Check inline type="radio" name="genero" label="Outro:" />
              <Form.Control className="other-input" placeholder="Outro" />
            </div>
          </fieldset>

          <div className="state-city-row">
            <label className="field state-field">
              <span>Estado</span>
              <select className="select-button">
                Selecionar
                <option value="someOption">Some option</option>
                <option value="otherOption">Other option</option>
              </select>
            </label>
            <Input label="Cidade" placeholder="Cidade" className="city-field" />
          </div>

          <Input label="Bairro" placeholder="Bairro" />
          <Input label="Logradouro" placeholder="Logradouro" />

          <div className="field-row address-row">
            <Input label="Número" placeholder="Número" />
            <Input label="Complemento" placeholder="Complemento" />
          </div>
        </div>
        <Button type="button" className="btn-orange btn-large form-submit">
          Cadastrar
        </Button>
      </section>
    </main>
  );
}

function Login() {
  return (
    <main className="login-page">
      <section className="login-panel">
        <img className="login-logo" src={logo} alt="Legados Culturais" />
        <div className="login-content">
          <h1>Entrar</h1>
          <div className="account-type">
            <span>Tipo de conta:</span>
            <Button type="button" className="select-button">
              Usuário
            </Button>
          </div>
          <Input label="E-mail" placeholder="E-mail" type="email" />
          <Input label="Senha" placeholder="Senha" type="password" />
          <Button type="button" className="btn-orange btn-large login-submit">
            Cadastrar
          </Button>
        </div>
      </section>
      <div
        className="login-image"
        style={{
          backgroundImage: `linear-gradient(rgba(32, 32, 24, .30), rgba(32, 32, 24, .30)), url(${cityPhoto})`,
        }}
        aria-hidden="true"
      />
    </main>
  );
}

export default function App() {
  const path = window.location.pathname.toLowerCase();
  if (path === "/cadastro" || path === "/cadastro/") return <Cadastro />;
  if (path === "/login" || path === "/login/") return <Login />;
  return <Home />;
}
