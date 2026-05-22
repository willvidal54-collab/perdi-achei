
/**
 * script.js — Lógica do frontend do sistema Perdi & Achei
 *
 * Responsabilidades deste arquivo:
 * 1. Capturar o envio do formulário e enviar os dados ao servidor (salvar.php) via fetch.
 * 2. Carregar e exibir os registros do servidor (listar.php) via fetch.
 * 3. Filtrar os registros exibidos por tipo (todos / perdido / encontrado).
 * 4. Renderizar cada registro como um card HTML na página.
 *
 * Conceito-chave: toda comunicação com o servidor ocorre sem recarregar a página,
 * usando a API Fetch (requisições assíncronas com async/await).
 */

// ─── Referências aos elementos do HTML ───────────────────────────────────────
// getElementById e querySelectorAll buscam elementos pelo id ou classe CSS.
// Guardamos as referências em variáveis para reutilizá-las sem buscar novamente.

const form = document.getElementById("formObjeto");           // Formulário de cadastro
const mensagem = document.getElementById("mensagem");         // Área de feedback (sucesso/erro)
const listaRegistros = document.getElementById("listaRegistros"); // Contêiner dos cards de itens
const totalRegistros = document.getElementById("totalRegistros"); // Contador no hero
const botoesFiltro = document.querySelectorAll(".filtro");    // Todos os botões de filtro
const btnAtualizar = document.getElementById("btnAtualizar"); // Botão "Atualizar"

// ─── Estado da aplicação ─────────────────────────────────────────────────────
// Variáveis que guardam o estado atual enquanto a página está aberta.

let registros = [];           // Array com todos os registros carregados do servidor
let filtroAtual = "todos";    // Filtro ativo; começa mostrando todos os itens

// ─── Funções auxiliares ───────────────────────────────────────────────────────

/**
 * Exibe uma mensagem de feedback para o usuário.
 * @param {string} texto - Texto a ser exibido.
 * @param {string} tipo  - "sucesso" ou "erro" (define o estilo CSS aplicado).
 */
function mostrarMensagem(texto, tipo) {
  mensagem.textContent = texto;
  // A classe CSS determina a cor de fundo: verde para sucesso, vermelho para erro.
  mensagem.className = `mensagem ${tipo}`;
}

/**
 * Apaga a mensagem de feedback após alguns segundos.
 * setTimeout agenda a execução de uma função depois de um intervalo (em milissegundos).
 */
function limparMensagemDepoisDeAlgunsSegundos() {
  setTimeout(() => {
    mensagem.textContent = "";
    mensagem.className = "mensagem"; // Remove as classes de estilo (sucesso/erro)
  }, 4500); // 4.500 ms = 4,5 segundos
}

/**
 * Retorna um emoji representativo com base no nome do objeto.
 * Usa includes() para verificar se o nome contém uma palavra-chave.
 * @param {string} nome - Nome do objeto cadastrado.
 * @returns {string} Emoji correspondente ou 📦 como padrão.
 */
function iconeDoObjeto(nome) {
  const texto = String(nome).toLowerCase(); // Normaliza para minúsculas para comparação

  if (texto.includes("fone")) return "🎧";
  if (texto.includes("chave")) return "🔑";
  if (texto.includes("mochila")) return "🎒";
  if (texto.includes("carteira")) return "👛";
  if (texto.includes("celular")) return "📱";
  if (texto.includes("livro")) return "📚";
  if (texto.includes("óculos") || texto.includes("oculos")) return "👓";
  if (texto.includes("garrafa")) return "🧴";

  return "📦"; // Emoji padrão para objetos não identificados
}

/**
 * Converte uma data no formato ISO (AAAA-MM-DD) para o formato brasileiro (DD/MM/AAAA).
 * @param {string} dataISO - Data no formato "2026-05-07".
 * @returns {string} Data no formato "07/05/2026" ou mensagem de fallback.
 */
function formatarData(dataISO) {
  if (!dataISO) return "Data não informada";

  // split("-") divide a string pelo traço: ["2026", "05", "07"]
  const partes = dataISO.split("-");
  if (partes.length !== 3) return dataISO; // Retorna o original se o formato for inesperado

  // Reordena para o padrão brasileiro: dia/mês/ano
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

/**
 * Escapa caracteres especiais do HTML para evitar ataques de XSS (Cross-Site Scripting).
 * XSS ocorre quando um texto malicioso é inserido diretamente no HTML sem tratamento,
 * permitindo a execução de scripts não autorizados.
 * @param {string} valor - Texto a ser escapado.
 * @returns {string} Texto seguro para ser inserido no HTML.
 */
function escaparHTML(valor) {
  return String(valor)
    .replaceAll("&", "&amp;")   // & deve ser &amp; no HTML
    .replaceAll("<", "&lt;")    // < seria interpretado como início de tag
    .replaceAll(">", "&gt;")    // > seria interpretado como fim de tag
    .replaceAll('"', "&quot;")  // " poderia fechar atributos HTML
    .replaceAll("'", "&#039;"); // ' idem
}

/**
 * Renderiza (gera e exibe) os cards de registros na tela.
 * Filtra o array global `registros` de acordo com `filtroAtual` e reconstrói o HTML da lista.
 */
function renderizarRegistros() {
  // Filtra os registros: se filtroAtual for "todos", usa o array completo;
  // caso contrário, mantém apenas itens com o tipo correspondente.
  const registrosFiltrados = filtroAtual === "todos"
    ? registros
    : registros.filter((item) => item.tipo === filtroAtual);

  // Atualiza o contador de registros exibido no hero da página
  totalRegistros.textContent = registros.length;

  // Limpa a lista antes de renderizar novamente (evita duplicações)
  listaRegistros.innerHTML = "";

  // Se não houver registros para o filtro atual, exibe uma mensagem informativa
  if (registrosFiltrados.length === 0) {
    listaRegistros.innerHTML = "<p>Nenhum registro encontrado para este filtro.</p>";
    return; // Encerra a função aqui
  }

  // Para cada registro, cria um elemento <div> e injeta o HTML com os dados do item
  registrosFiltrados.forEach((item) => {
    const div = document.createElement("div");
    div.className = "item";

    // Template literal (crase + ${}) monta uma string HTML com os dados do item.
    // escaparHTML() protege cada campo contra XSS antes de inserir no HTML.
    div.innerHTML = `
      <div class="item-img">${iconeDoObjeto(item.objeto)}</div>
      <div>
        <span class="tag ${escaparHTML(item.tipo)}">${escaparHTML(item.tipo).toUpperCase()}</span>
        <h3>${escaparHTML(item.objeto)}</h3>
        <p><strong>Local:</strong> ${escaparHTML(item.local)}</p>
        <p><strong>Data:</strong> ${formatarData(escaparHTML(item.data))}</p>
        <p><strong>Descrição:</strong> ${escaparHTML(item.descricao)}</p>
        <p><strong>Contato:</strong> ${escaparHTML(item.contato)}</p>
        <p><strong>Registrado em:</strong> ${escaparHTML(item.criado_em || "Não informado")}</p>
      </div>
    `;

    // Adiciona o card criado ao contêiner da lista no HTML
    listaRegistros.appendChild(div);
  });
}

// ─── Funções de comunicação com o servidor (fetch / AJAX) ────────────────────

/**
 * Busca os registros do servidor fazendo uma requisição GET para listar.php.
 * async/await simplifica o trabalho com Promises (operações assíncronas).
 */
async function carregarRegistros() {
  try {
    // fetch() realiza uma requisição HTTP.
    // cache: "no-store" garante que o navegador não use dados em cache,
    // forçando a busca dos dados mais recentes do servidor.
    const resposta = await fetch("listar.php", {
      method: "GET",
      cache: "no-store"
    });

    // Se o servidor retornar um status de erro (ex: 404, 500), lança uma exceção
    if (!resposta.ok) {
      throw new Error("Erro ao carregar registros.");
    }

    // .json() interpreta o corpo da resposta como JSON e retorna um objeto/array JavaScript
    const dados = await resposta.json();

    // Garante que `registros` seja sempre um array, mesmo se a resposta for inesperada
    registros = Array.isArray(dados) ? dados : [];
    renderizarRegistros();
  } catch (erro) {
    // O bloco catch captura qualquer erro (rede, JSON inválido, etc.)
    listaRegistros.innerHTML = `
      <p>
        Não foi possível carregar os registros. Verifique se o projeto está rodando pelo Apache/XAMPP.
      </p>
    `;
  }
}

/**
 * Envia os dados do formulário ao servidor via requisição POST para salvar.php.
 * Os dados são enviados como JSON no corpo da requisição (body).
 * @param {Object} dados - Objeto com os campos do formulário.
 * @returns {Promise<Object>} Resposta do servidor em formato JSON.
 */
async function salvarRegistro(dados) {
  const resposta = await fetch("salvar.php", {
    method: "POST",
    headers: {
      // Informa ao servidor que o corpo da requisição está em formato JSON
      "Content-Type": "application/json"
    },
    // JSON.stringify() converte o objeto JavaScript em uma string JSON para enviar
    body: JSON.stringify(dados)
  });

  return resposta.json(); // Interpreta e retorna a resposta do servidor como objeto
}

// ─── Eventos ─────────────────────────────────────────────────────────────────

/**
 * Evento de envio do formulário.
 * addEventListener("submit", ...) escuta quando o usuário clica em "Salvar registro".
 */
form.addEventListener("submit", async function (evento) {
  // preventDefault() impede o comportamento padrão do formulário
  // (que seria recarregar a página ao enviar), pois faremos o envio via fetch.
  evento.preventDefault();

  // FormData lê automaticamente todos os campos do formulário pelo atributo name=""
  const formData = new FormData(form);

  // Monta o objeto com os dados, aplicando trim() para remover espaços desnecessários
  const dados = {
    objeto: formData.get("objeto").trim(),
    tipo: formData.get("tipo"),
    local: formData.get("local").trim(),
    data: formData.get("data"),
    descricao: formData.get("descricao").trim(),
    contato: formData.get("contato").trim()
  };

  try {
    const resultado = await salvarRegistro(dados);

    if (resultado.sucesso) {
      mostrarMensagem("Registro salvo com sucesso!", "sucesso");
      form.reset(); // Limpa todos os campos do formulário
      await carregarRegistros(); // Atualiza a lista com o novo registro
    } else {
      // Se o servidor retornar sucesso: false, exibe a mensagem de erro recebida
      mostrarMensagem(`Erro: ${resultado.erro}`, "erro");
    }
  } catch (erro) {
    // Erro de conexão: servidor offline ou URL incorreta
    mostrarMensagem("Falha ao conectar com o servidor. Abra pelo http://localhost/perdi-achei/ no XAMPP.", "erro");
  }

  limparMensagemDepoisDeAlgunsSegundos();
});

/**
 * Evento de clique nos botões de filtro (Todos / Perdidos / Encontrados).
 * forEach percorre todos os botões e adiciona um listener a cada um.
 */
botoesFiltro.forEach((botao) => {
  botao.addEventListener("click", () => {
    // Remove a classe "ativo" de todos os botões de filtro
    botoesFiltro.forEach((b) => b.classList.remove("ativo"));
    // Marca o botão clicado como ativo (destaque visual via CSS)
    botao.classList.add("ativo");
    // Atualiza o filtro atual com o valor do atributo data-filtro do botão
    filtroAtual = botao.dataset.filtro;
    // Re-renderiza os registros respeitando o novo filtro
    renderizarRegistros();
  });
});

// Atualiza a lista ao clicar no botão "Atualizar" (nova requisição ao servidor)
btnAtualizar.addEventListener("click", carregarRegistros);

// ─── Inicialização ────────────────────────────────────────────────────────────
// Carrega os registros assim que a página termina de carregar o script.
carregarRegistros();
