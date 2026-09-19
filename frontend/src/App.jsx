import React from "react";
import { Button, Form, Nav, Navbar } from "react-bootstrap";
import logo from "./assets/logo.png";
import cityPhoto from "./assets/login-city.png";
import { useState } from "react";

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

function Input({ label, placeholder, type = "text", className = "", value, onChange }) {
  return (
    <Form.Group className={`field ${className}`} controlId={label}>
      <Form.Label>{label}</Form.Label>
      <Form.Control 
        type={type} 
        placeholder={placeholder} 
        value={value}
        onChange={onChange}
      />
    </Form.Group>
  );
}
/*
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
}*/

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

    if (!nome.trim()) {
      novosErros.nome = "O nome é obrigatório.";
    }

    if (!sobrenome.trim()) {
      novosErros.sobrenome = "O sobrenome é obrigatório.";
    }

    if (!email.includes("@") || !email.includes(".")) {
      novosErros.email = "Digite um e-mail válido.";
    }

    if (senha.length < 8) {
      novosErros.senha = "A senha deve ter pelo menos 8 caracteres.";
    }

    if (!dataNascimento) {
      novosErros.dataNascimento = "A data de nascimento é obrigatória.";
    }

    if (!sexo) {
      novosErros.sexo = "Selecione o sexo.";
    }

     if (!genero) {
      novosErros.genero = "Selecione o gênero.";
    }

    let generoFinal = genero;

    if (genero === "Outro") {
      if(!outroGenero.trim()) {
        novosErros.outroGenero = "Especifique o gênero.";
      } else {
        generoFinal = outroGenero
      } 
    }

    if (!estado) {
      novosErros.estado = "Selecione o estado.";
    }

    if (!cidade.trim()) {
      novosErros.cidade = "A cidade é obrigatória.";
    }

    if (!bairro.trim()) {
      novosErros.bairro = "O bairro é obrigatório.";
    }

    setErros(novosErros);

    if (Object.keys(novosErros).length > 0) {
      return;
    }

    console.log("Formulário enviado com sucesso!", {
      nome,
      sobrenome,
      email,
      senha,
      telefone,
      dataNascimento,
      sexo,
      generoFinal,
      estado,
      cidade,
      bairro,
      logradouro,
      numero,
      complemento
    });

    //enviar para o back
    // use "/backend/routes.php?rota=cadastro" quando não for teste local
    const resposta = await fetch("http://localhost:8000/routes.php?rota=cadastro", {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify({
        nome: nome,
        sobrenome: sobrenome,
        email: email,
        senha: senha,
        telefone: telefone,
        dataNascimento: dataNascimento,
        sexo: sexo,
        genero: generoFinal,
        estado: estado,
        cidade: cidade,
        bairro: bairro,
        logradouro: logradouro,
        numero: numero,
        complemento: complemento
      })
    });

    const dados = await resposta.json();

    if(resposta.ok) {
      alert("Cadastro realizado com sucesso");
    } else {
      alert("Erro ao cadastrar!");
    }

  }

  return (
    <main className="form-page cadastro-page">
      <section className="form-card cadastro-card">
        <img className="form-logo" src={logo} alt="Legados Culturais" />
        <h1>Cadastro</h1>
        <form onSubmit={cadastrar}>
          <div className="form-fields">
            <div>
              <Input 
                label="Nome" 
                placeholder="Nome" 
                value={nome} 
                onChange={(e) => setNome(e.target.value)} 
              />
              {erros.nome && <span className="form-error">{erros.nome}</span>}
            </div>

            <div>
              <Input 
                label="Sobrenome" 
                placeholder="Sobrenome" 
                value={sobrenome} 
                onChange={(e) => setSobrenome(e.target.value)} 
              />
              {erros.sobrenome && <span className="form-error">{erros.sobrenome}</span>}
            </div>

            <div>
              <Input 
                label="E-mail" 
                placeholder="E-mail" 
                type="email" 
                value={email} 
                onChange={(e) => setEmail(e.target.value)} 
              />
              {erros.email && <span className="form-error">{erros.email}</span>}
            </div>

            <div>
              <Input 
                label="Senha" 
                placeholder="Senha" 
                type="password" 
                value={senha} 
                onChange={(e) => setSenha(e.target.value)} 
              />
              {erros.senha && <span className="form-error">{erros.senha}</span>}
            </div>

            <div className="field-row compact-row">
              <div>
                <Input 
                  label="Telefone" 
                  placeholder="(11) 11111-1111" 
                  value={telefone} 
                  onChange={(e) => setTelefone(e.target.value)} 
                />
              </div>
              <div>
                <Input 
                  label="Data de nascimento" 
                  placeholder="dd/mm/aaaa" 
                  type="date"
                  value={dataNascimento} 
                  onChange={(e) => setDataNascimento(e.target.value)} 
                />
                {erros.dataNascimento && <span className="form-error">{erros.dataNascimento}</span>}
              </div>
            </div>

            <fieldset className="radio-group">
              <legend>Sexo:</legend>
              <Form.Check 
                inline 
                type="radio" 
                name="sexo" 
                label="Masculino" 
                value="m"
                onChange={(e) => setSexo(e.target.value)}
              />
              <Form.Check 
                inline 
                type="radio" 
                name="sexo" 
                label="Feminino" 
                value="f"
                onChange={(e) => setSexo(e.target.value)}
              />
              {erros.sexo && <span className="form-error" style={{ display: "block" }}>{erros.sexo}</span>}
            </fieldset>

            <fieldset className="radio-group gender-group">
              <legend>Gênero:</legend>
              <div className="gender-options">
                <Form.Check 
                  inline 
                  type="radio" 
                  name="genero" 
                  label="Masculino" 
                  value="Masculino"
                  onChange={(e) => setGenero(e.target.value)}
                />
                <Form.Check 
                  inline 
                  type="radio" 
                  name="genero" 
                  label="Feminino" 
                  value="Feminino"
                  onChange={(e) => setGenero(e.target.value)}
                />
                <Form.Check
                  inline
                  type="radio"
                  name="genero"
                  label="Não binário"
                  value="Não binário"
                  onChange={(e) => setGenero(e.target.value)}
                />
                <Form.Check 
                  inline 
                  type="radio" 
                  name="genero" 
                  label="Outro:" 
                  value="Outro"
                  onChange={(e) => setGenero(e.target.value)}
                />
                <Form.Control 
                  className="other-input" 
                  placeholder="Outro" 
                  value={outroGenero}
                  onChange={(e) => setOutroGenero(e.target.value)}
                  disabled={genero !== "Outro"}
                />
              </div>
              {erros.genero && <span className="form-error" style={{ display: "block" }}>{erros.genero}</span>}
              {erros.outroGenero && <span className="form-error" style={{ display: "block" }}>{erros.outroGenero}</span>}
            </fieldset>

            <div className="state-city-row">
              <label className="field state-field">
                <span>Estado</span>
                <select 
                  className="select-button"
                  value={estado}
                  onChange={(e) => setEstado(e.target.value)}
                >
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
                <Input 
                  label="Cidade" 
                  placeholder="Cidade" 
                  className="city-field" 
                  value={cidade}
                  onChange={(e) => setCidade(e.target.value)}
                />
                {erros.cidade && <span className="form-error">{erros.cidade}</span>}
              </div>
            </div>

            <div>
              <Input 
                label="Bairro" 
                placeholder="Bairro" 
                value={bairro}
                onChange={(e) => setBairro(e.target.value)}
              />
              {erros.bairro && <span className="form-error">{erros.bairro}</span>}
            </div>

            <div>
              <Input 
                label="Logradouro" 
                placeholder="Logradouro" 
                value={logradouro}
                onChange={(e) => setLogradouro(e.target.value)}
              />
            </div>

            <div className="field-row address-row">
              <div>
                <Input 
                  label="Número" 
                  placeholder="Número" 
                  value={numero}
                  onChange={(e) => setNumero(e.target.value)}
                />
              </div>
              <div>
                <Input 
                  label="Complemento" 
                  placeholder="Complemento" 
                  value={complemento}
                  onChange={(e) => setComplemento(e.target.value)}
                />
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

  const [tipo, setTipo] = useState("User");
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");

  const [erroTipo, setErroTipo] = useState("");
  const [erroEmail, setErroEmail] = useState("");
  const [erroSenha, setErroSenha] = useState("");

  const padraoEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  function validarLogin(e) {
    e.preventDefault();
    //inicia as mensagens de erro vazias
    setErroEmail("");
    setErroSenha("");

    if(email === "") {
      setErroEmail("Insira o e-mail*");
    } else if(!padraoEmail.test(email)) {
      setErroEmail("E-mail inválido*");
    }
    if(senha === "") {
      setErroSenha("Digite a senha*");
    }
  }

  return (
    <main className="login-page">
      <section className="login-panel">
        <form className="login-form" aria-label="Formulário de login" onSubmit={validarLogin}>
          <img className="login-logo" src={logo} alt="Legados Culturais" />
          <div className="login-content">
            <h1>Entrar</h1>
            <div className="account-type">
              <span>Tipo de conta:</span>
              <select 
                className="select-button"
                onChange={(e) => setTipo(e.target.value)}
              >
                  <option value="User">Usuário</option>
                  <option value="Adm">Admin</option>
              </select>
              {erroTipo && (<span>{erroTipo}</span>)}
            </div>
            <Input 
              label="E-mail" 
              placeholder="E-mail"  
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            {erroEmail && (<span>{erroEmail}</span>)}
            <Input 
              label="Senha" 
              placeholder="Senha" 
              type="password" 
              value={senha}
              onChange={(e) => setSenha(e.target.value)}  
            />
            {erroSenha && (<span>{erroSenha}</span>)}
            <Button type="submit" className="btn-orange btn-large login-submit">
              Entrar
            </Button>
          </div>
        </form>
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