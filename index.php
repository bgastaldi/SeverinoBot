<?php

/*
 * PROCESSANDO A MENSAGEM 
 */

function processMessage($update) {
    if ($update["result"]["action"] == "consulta.cep") {
        $array = ConsultaCep($update);
        sendMessage($array);
    }
}

/*
 * FUNÇÃO PARA ENVIAR A MENSAGEM
 */

function sendMessage($parameters) {
    echo json_encode($parameters);
}

/*
 * PEGANDO A REQUISIÇÃO
 */

$update_response = file_get_contents("php://input");
$update = json_decode($update_response, true);
if (isset($update["result"]["action"])) {
    processMessage($update);
}

/*
 * FUNÇÃO PARA CONSULTAR O CEP
 * API - https://correiosapi.apphb.com/
 */

function ConsultaCep($update = array()) {

    $mensagem = array();
    if (strlen($update['result']['parameters']['CEP']) == 8) {
        $dados = json_decode(getPage('https://correiosapi.apphb.com/' . $update['result']['parameters']['CEP']), true);
        if (isset($dados['cep'])) {
            $mensagem[] = array(
                'type' => 0,
                'speech' => 'O CEP ' . $dados['cep'] . ' é referente ao endereço: ' . $dados['tipoDeLogradouro'] . ' ' . $dados['logradouro'] . ', ' . $dados['bairro'] . ' - ' . $dados['cidade'] . ' - ' . $dados['estado']
            );
        } else {
            $mensagem[] = array(
                'type' => 0,
                'speech' => 'Desculpe, não consegui localizar o CEP ' . $update['result']['parameters']['CEP']
            );
        }
        $mensagem[] = array(
            'type' => 0,
            'speech' => 'Gostaria de realizar uma nova consulta?',
        );
    } else {
        $mensagem[] = array(
            'type' => 0,
            'speech' => 'Por favor, digite um CEP válido'
        );
    }

    return array(
        'source' => $update['result']['source'],
        'messages' => $mensagem,
        'contextOut' => array(
            array(
                'name' => 'ctx-cep',
                'lifespan' => 1,
                'parameters' => array()
            )
        )
    );
}

/*
 * FUNÇÃO CURL
 */
function getPage($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_ENCODING, "utf8");
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
