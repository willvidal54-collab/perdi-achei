
<?php
/**
endpoint - retornar dados cadastrados 
endpoint de leitura responde um json*/

header("Content-Type: application/json; charset=UTF-8")
//garante os caracteres especiais ex: açucar
//vou testar se a pagina me enviou uma requisição "get"
if($_SERVER["REQUEST_METHOD"] !== "GET"){
//se o metodo nao for o get, vou encerrar
    http_response_code(405); // metodo nao permitido
    echo json_encode(["erro" => "Metodo não Permitido"], 
    JSON_UNESCAPED_UNICODE);
    exit;
 * listar.php — Endpoint para retornar todos os registros cadastrados.
 *
 * Fluxo esperado:
 * 1. O JavaScript (script.js) envia uma requisição GET para este script.
 * 2. O script lê o arquivo registros.json do servidor.
 * 3. Retorna o conteúdo como um array JSON para o frontend.
 *
 * Este arquivo não modifica dados; apenas os lê e os expõe via HTTP.
 */

// Define que a resposta será sempre um JSON com codificação UTF-8,
// garantindo que acentos e caracteres especiais sejam transmitidos corretamente.
header("Content-Type: application/json; charset=UTF-8");

// Garante que apenas requisições GET sejam aceitas.
// Isso é uma boa prática: endpoints de leitura não devem aceitar métodos que alterem dados.
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405); // 405 = Method Not Allowed
    echo json_encode([
        "erro" => "Método não permitido. Use GET."
    ], JSON_UNESCAPED_UNICODE);
    exit; // Encerra o script aqui, sem continuar a execução
}

/*fazer a leitura do arquivo json*/
// Caminho absoluto para o arquivo JSON onde os registros estão armazenados.
// __DIR__ garante que o caminho seja relativo ao diretório deste script,
// funcionando independentemente de onde o servidor Apache estiver instalado.
$arquivo = __DIR__ . "/registros.json";

/*tratar um erro, caso o arquivo nao */
if(!file_exists($arquivo)){
// Se o arquivo ainda não existir (ex: nenhum registro foi cadastrado ainda),
// retorna um array vazio [] para que o JavaScript não receba um erro.
if (!file_exists($arquivo)) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

/*ler o conteudo do json */
// Lê todo o conteúdo do arquivo como uma string.
$conteudo = file_get_contents($arquivo);

/*transformar ele em json */
$registro = json_decode($conteudo, true);
// Converte a string JSON em um array PHP associativo (true = array, não objeto).
$registros = json_decode($conteudo, true);

// Proteção: se o arquivo estiver vazio ou corrompido, retorna um array vazio
// em vez de propagar um erro para o JavaScript.
if (!is_array($registros)) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

/*mostrar o conteudo do json*/ 
echo json_encode($registro, JSON_UNESCAPED_UNICODE);
?>
// Retorna todos os registros como JSON.
// JSON_UNESCAPED_UNICODE preserva caracteres acentuados sem escaping desnecessário (ex: ã, ç, é).
echo json_encode($registros, JSON_UNESCAPED_UNICODE);
