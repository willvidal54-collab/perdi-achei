<?php
/**
 * salvar.php — Endpoint para cadastrar um novo registro de objeto perdido ou encontrado.
 *
 * Fluxo esperado:
 * 1. O JavaScript (script.js) envia uma requisição POST com os dados do formulário em JSON.
 * 2. Este script valida os dados recebidos.
 * 3. Se válidos, adiciona o novo registro no arquivo registros.json.
 * 4. Retorna uma resposta em JSON informando sucesso ou erro.
 */

// Define que a resposta deste script será sempre um JSON com codificação UTF-8.
// Isso garante que o navegador (e o JavaScript) interpretem corretamente a resposta.
header("Content-Type: application/json; charset=UTF-8");

if($_SERVER["REQUEST_METHOD"] !== "POST"){
    //se o metodo nao for o get, vou encerrar
        http_response_code(405); // metodo nao permitido
        echo json_encode([
            "sucesso" => false,
            "erro" => "Metodo não Permitido"], 
        JSON_UNESCAPED_UNICODE);
        exit;
    }
/*vou pegar a entrada do post */
// Garante que apenas requisições POST sejam aceitas.
// Qualquer outro método (GET, PUT, DELETE...) recebe o erro 405 (Method Not Allowed).
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "sucesso" => false,
        "erro" => "Método não permitido. Use POST."
    ], JSON_UNESCAPED_UNICODE);
    exit; // Encerra a execução do script imediatamente
}

// Lê o corpo bruto da requisição HTTP.
// O JavaScript envia os dados como JSON no corpo (body) do POST,
// por isso usamos php://input em vez de $_POST.
$entrada = file_get_contents("php://input");

/*decodificar*/
// Decodifica o JSON recebido em um array associativo PHP (true = array, não objeto).
$dados = json_decode($entrada, true);

if(!is_array($dados)){
    http_response_code(400); // bad request (invalida)
// Verifica se a decodificação foi bem-sucedida (json_decode retorna null em caso de erro).
if (!is_array($dados)) {
    http_response_code(400); // 400 = Bad Request (requisição inválida)
    echo json_encode([
        "sucesso" => false,
        "erro" => "JSON Inválido"
        "erro" => "JSON inválido."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

foreach($camposObrigatorios as $campo){
    if(!isset($dados[$campo]) || trim($dados[$campo]) ==="" ){
// Lista dos campos que precisam estar presentes e preenchidos na requisição.
$camposObrigatorios = ["objeto", "tipo", "local", "data", "descricao", "contato"];

// Percorre cada campo obrigatório e verifica se foi enviado e não está vazio.
foreach ($camposObrigatorios as $campo) {
    if (!isset($dados[$campo]) || trim($dados[$campo]) === "") {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "erro" => "O campo {$campo} é obrigatório"
            // A interpolação {$campo} insere o nome do campo na mensagem de erro.
            "erro" => "O campo {$campo} é obrigatório."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function limparTexto($valor, $limite = 300){
    /*função de segurança, para evitar injeção de codigos
    maliciosos, longe dos perigos noturnos */
    $valor = trim((string) $valor); 
    //garante que é texto e remove espaço nas bordas
    $valor = strip_tags($valor);
    // garante não injeção de html
    return $valor; // da o direito de ir tomar um café e 
    //voltar as 21h
// Normaliza o campo "tipo": remove espaços e converte para minúsculas.
$tipo = strtolower(trim($dados["tipo"]));

// Valida que o tipo seja exatamente "perdido" ou "encontrado" (lista branca de valores).
// O parâmetro true em in_array ativa a comparação estrita (tipo e valor).
if (!in_array($tipo, ["perdido", "encontrado"], true)) {
    http_response_code(400);
    echo json_encode([
        "sucesso" => false,
        "erro" => "Tipo inválido. Use perdido ou encontrado."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sanitiza um campo de texto:
 * - Remove espaços extras nas bordas (trim).
 * - Remove tags HTML para evitar injeção de código (strip_tags).
 * - Limita o comprimento para evitar textos excessivamente longos (mb_substr).
 *
 * @param string $valor  Valor recebido do formulário.
 * @param int    $limite Número máximo de caracteres permitidos.
 * @return string Texto limpo e seguro.
 */
function limparTexto($valor, $limite = 300) {
    $valor = trim((string) $valor);  // Garante que seja string e remove espaços nas bordas
    $valor = strip_tags($valor);     // Remove qualquer tag HTML (<script>, <b>, etc.)
    $valor = mb_substr($valor, 0, $limite, "UTF-8"); // Corta pelo limite usando multibyte (suporta acentos)
    return $valor;
}
/* chama o arquivo */

// Caminho absoluto para o arquivo JSON de armazenamento.
// __DIR__ retorna o diretório onde este script está localizado, tornando o caminho portável.
$arquivo = __DIR__ . "/registros.json";

/*garantir se o arquivo existe */
if(!file_exists($arquivo)){
    file_put_contents($arquivo, "[]"); 
    /* pra nao dar pau, vou chamar um arquivo vazio */
// Se o arquivo ainda não existir, cria-o com um array vazio ([]).
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, "[]");
}

// Lê o conteúdo atual do arquivo JSON.
$conteudoAtual = file_get_contents($arquivo);

$registros = json_decode($conteudoAtual, true); /* 
transformando em array */
// Decodifica o JSON do arquivo em um array PHP.
$registros = json_decode($conteudoAtual, true);

if (!is_array($registros)){
    $registros = []; /*garrantindo o array*/
// Proteção: garante que $registros seja sempre um array mesmo que o arquivo esteja corrompido.
if (!is_array($registros)) {
    $registros = [];
}
/*montar o meu array para um novo registro */

// Monta o novo registro com os dados recebidos e sanitizados.
$novoRegistro = [
    // uniqid() gera um ID único baseado no tempo; o prefixo "item_" e o parâmetro true
    // adicionam mais entropia para reduzir a chance de colisões.
    "id" => uniqid("item_", true),
    "objeto" => limparTexto($dados["objeto"], 80),
    "tipo" => $tipo,
    "local" => limparTexto($dados["local"], 100),
    "data" => limparTexto($dados["data"], 10),
    "objeto"    => limparTexto($dados["objeto"], 80),
    "tipo"      => $tipo,
    "local"     => limparTexto($dados["local"], 100),
    "data"      => limparTexto($dados["data"], 10),
    "descricao" => limparTexto($dados["descricao"], 350),
    "contato" => limparTexto($dados["contato"], 100),
    //pegar a data e hora do servidor
    "contato"   => limparTexto($dados["contato"], 100),
    // Registra a data e hora do servidor no momento do cadastro.
    "criado_em" => date("d/m/Y H:i:s")
];

// Insere o novo registro no início do array (o mais recente aparece primeiro na lista).
array_unshift($registros, $novoRegistro);

$salvou = file_put_contents($arquivo, json_encode(
    $registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
// Salva o array atualizado de volta no arquivo JSON.
// JSON_PRETTY_PRINT formata o JSON com indentação (facilita leitura humana).
// JSON_UNESCAPED_UNICODE preserva acentos e caracteres especiais sem escapar.
// LOCK_EX bloqueia o arquivo durante a escrita para evitar conflitos em acessos simultâneos.
$salvou = file_put_contents(
    $arquivo,
    json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);
?>

// Verifica se a escrita foi bem-sucedida (file_put_contents retorna false em caso de falha).
if ($salvou === false) {
    http_response_code(500); // 500 = Internal Server Error
    echo json_encode([
        "sucesso" => false,
        "erro" => "Não foi possível gravar o arquivo JSON."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Retorna a resposta de sucesso junto com o registro que acabou de ser criado.
// O JavaScript usa esses dados para atualizar a interface sem recarregar a página.
echo json_encode([
    "sucesso"   => true,
    "mensagem"  => "Registro salvo com sucesso.",
    "registro"  => $novoRegistro
], JSON_UNESCAPED_UNICODE);
