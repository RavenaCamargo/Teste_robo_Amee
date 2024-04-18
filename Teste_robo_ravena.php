<?php
/**
 * ATENÇÃO: ALTERAR DIRETÓRIO ABAIXO PARA GERAR O CSV 
 * (O CSV É ENVIADO PARA PASTA DOWNLOAD AO EXECUTAR O CÓDIGO)
 */

// Diretório onde estão localizados os arquivos CSV
$directory = 'C:\\Users\\ravena.camargo\\Desktop\\Conversao\\';

// Inicializa um array para armazenar os dados do CSV
$fatura = [];

// Verifica se o diretório existe
if (is_dir($directory)) {
    // Abre o diretório
    if ($dh = opendir($directory)) {
        // Loop através dos arquivos no diretório
        while (($file = readdir($dh)) !== false) {
            // Verifica se o arquivo é um arquivo CSV
            if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                // Caminho completo do arquivo CSV
                $csvFile = $directory . $file;

                // Abrir o arquivo CSV para leitura
                $fileHandle = fopen($csvFile, 'r');

                // Verifica se o arquivo foi aberto com sucesso
                if ($fileHandle !== false) {
                    // Lê a primeira linha do CSV para obter os nomes das variáveis
                    $header = fgetcsv($fileHandle, 1000, ',');

                    // Lê as linhas subsequentes e as associa aos nomes das variáveis
                    while (($data = fgetcsv($fileHandle, 1000, ',')) !== false) {
                        // Combina os nomes das variáveis com os valores correspondentes
                        $fatura[] = array_combine($header, $data);
                    }
                    // Fecha o arquivo após a leitura
                    fclose($fileHandle);
                } else {
                    echo "Erro ao abrir o arquivo CSV: $csvFile<br>";
                }
            }
        }
        // Fecha o diretório após a leitura
        closedir($dh);
    } else {
        echo "Erro ao abrir o diretório.";
    }
} else {
    echo "O diretório não existe.";
}

// echo '<pre>';
// var_dump($fatura);
// echo '</pre>';

/**
 * Função para formatar numeros decimais
 */

 function formatarNumero($numero) {
    // Elimina as letras
    $numero = preg_replace('/[^0-9,]/','', $numero);

    // Substitui a vírgula por ponto
    $numero = str_replace(',', '.', $numero);

    // Formata o número para duas casas decimais
    $numero = number_format($numero, 2, '.', '');

    return $numero;
}

/**
 * NORMALIZAÇÃO DOS DADOS SOLICITADOS NO DESAFIO
 */

//UNIDADE CONSUMIDORA
$unidade = preg_replace('/[^0-9]/', '', $fatura[0]["unidade"]);

//DADOS DO QR CODE (QUANDO TEM QR CODE)
if(isset($fatura[0]["qr_code"])){
    if(preg_match('/NOTA\s+FISCAL(\:|)\s+(N\W+\s+|)[0-9.-]+/i',$fatura[0]["qr_code"],$matches[0])){
        // echo '<pre>';
        // var_dump($matches[0][0]);
        // echo '</pre>';
        $notaFiscal = preg_replace('/[^0-9]/', '', $matches[0][0]);
    }else{
        $notaFiscal = 'Verificar Nota fiscal da fatura. ';
    }

    // if(preg_match('/chave\s+de\s+acesso/i',$fatura[0]["qr_code"],$matches[0])){
    //     // echo '<pre>';
    //     // var_dump($matches[0][0]);
    //     // echo '</pre>';
    //     $chaveDeAcesso = preg_replace('/[^0-9]/', '', $matches[0][0]);
    // }else{
    //     $chaveDeAcesso = '';
    // }
}elseif(isset($fatura[0]["nota_fiscal"])){
    $notaFiscal = preg_replace('/[^0-9]/', '', $fatura[0]["nota_fiscal"]);
}else{
    $notaFiscal = 'Verificar Nota fiscal da fatura. ';
}

//CNPJ
if($fatura[0]["cnpj"] != ''){
    if(preg_match('/(CNPJ.*?)\s+[0-9\*X]{2}\.[0-9\*X]{3}\.[0-9\*X]{3}\/[0-9\*X]{4}\-[0-9\*X]{2}/',$fatura[0]["cnpj"],$matches[0])){
        // echo '<pre>';
        // var_dump($matches[0][0]);
        // echo '</pre>';
        $cnpj = preg_replace('/[^0-9\*X\-\/\.]/', '', $matches[0][0]);
    }elseif(preg_match('/(CNPJ.*?)\s+[0-9]{14}/',$fatura[0]["cnpj"],$matches[0])){
        // echo '<pre>';
        // var_dump($matches[0][0]);
        // echo '</pre>';
        $cnpj = preg_replace('/[^0-9]/', '', $matches[0][0]);
    }
}else{
    $cnpj = 'Fatura sem CNPJ. ';
}

//FISCO
if($fatura[0]["fisco"] != ''){
    if(preg_match('/[0-z]{4}(\.|\s+)[0-z]{4}(\.|\s+)[0-z]{4}(\.|\s+)[0-z]{4}(\.|\s+)[0-z]{4}(\.|\s+)[0-z]{4}(\.|\s+)[0-z]{4}(\.|\s+)[0-z]{4}/',$fatura[0]["fisco"],$matches[0])){
        // echo '<pre>';
        // var_dump($matches[0][0]);
        // echo '</pre>';
        $fisco = $matches[0][0];
    }
}else{
    $fisco = 'Novo modelo de nota fiscal, sem reservado ao fisco. ';
}

//CONTA CONTRATO
if(isset($fatura[0]["conta_contrato"])){
    if(preg_match('/[0-9]+/',$fatura[0]["conta_contrato"],$matches[0])){
        // echo '<pre>';
        // var_dump($matches[0][0]);
        // echo '</pre>';
        $contaContrato = $matches[0][0];
    }
}

//CODIGO DE BARRAS
if(isset($fatura[0]["codigo_de_barras"])){
    if(preg_match('/[0-9]{12}\s+[0-9]{12}\s+[0-9]{12}\s+[0-9]{12}/',$fatura[0]["codigo_de_barras"],$matches[0])){
        // echo '<pre>';
        // var_dump($matches[0][0]);
        // echo '</pre>';
        $codigoDeBarras = $matches[0][0];
    }
}

//LEITURAS E CONSTANTE
if($fatura[0]["leituras"] != ''){
    if(preg_match('/ENRG\s+ATV\s+(\W+|U)NICO\s+[0-9,.]+\s+[0-9,.]+/',$fatura[0]["leituras"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2[0][0]);
        // echo '</pre>';
        if($matches2[0][0] <= $matches2[0][1] && preg_match('/[0-9]+\.[0-9]{3,}/', $matches2[0][0])){
            $leituraAnterior = preg_replace('/\./', '', $matches2[0][0]);
            $leituraAtual = preg_replace('/\./', '', $matches2[0][1]);
        }else{
            echo 'Leitura Atual maior do que a anterior. ';
        }

        $constante = preg_replace('/\,/', '.', $fatura[0]["constante"]);

        //Só calcula o consumo quando tem todas as leituras e a constante na fatura
        if($leituraAnterior > 0 && $leituraAtual > 0 && $constante > 0){
            $consumoFaturado = formatarNumero(($leituraAtual - $leituraAnterior) * $constante);
        }
    }
}else{
    $leituraAnterior = 'Fatura sem leitura anterior. ';
    $leituraAtual = 'Fatura sem leitura atual.  ';
}

//TOTAL DA FATURA
if(isset($fatura[0]["total_fatura"])){
    $totalFatura = formatarNumero($fatura[0]["total_fatura"]);
}

//CONSUMOS E CIP
if(isset($fatura[0]["itens_fatura"])){
    // echo '<pre>';
    // var_dump($fatura[0]["itens_fatura"]);
    // echo '</pre>';
    if(preg_match('/USO\s+SIST\.\s+DISTR\.\s+\(TUSD\)\s+KWH\s+[0-9,.]+/i',$fatura[0]["itens_fatura"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        $consumo = formatarNumero(end($matches2[0]));
    }elseif(preg_match('/CONSUMO.*?[0-9,.]+/i',$fatura[0]["itens_fatura"],$matches[0]) &&
    !preg_match('/CONSUMO\s+(INJETADO|INJ).*?[0-9,.]+/i',$fatura[0]["itens_fatura"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        $consumo = formatarNumero(end($matches2[0]));
    }

    if(preg_match('/.*?(INJETADO|INJ).*?\s+[0-9,.]+/i',$fatura[0]["itens_fatura"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        $consumoInjetado = formatarNumero(end($matches2[0]));
    }else{
        $consumoInjetado = '';
    }

    if(preg_match('/.*?(COSIP|CIP|Ilum).*?[0-9,.]+/i',$fatura[0]["itens_fatura"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        $cip = formatarNumero(end($matches2[0]));
    }else{
        $cip = '';
    }
}

//ICMS, PIS E COFINS
if(isset($fatura[0]["tributos"])){
    if(preg_match('/(PIS.*?)\s+[0-9,.]+\s+[0-9,.]+\s+[0-9,.]+/i',$fatura[0]["tributos"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        //Verifica se a base (o maior numero) está na primeira posição
        if(formatarNumero(reset($matches2[0])) > formatarNumero($matches2[0][1])){
            $base2 = formatarNumero(reset($matches2[0]));
            $aliquotaPis = formatarNumero($matches2[0][1]);
            $pis = formatarNumero(end($matches2[0]));
        }else{
            $base2 = formatarNumero(end($matches2[0]));
            $aliquotaPis = formatarNumero($matches2[0][1]);
            $pis = formatarNumero(reset($matches2[0]));
        }
    }
    if(preg_match('/(COFINS.*?)\s+[0-9,.]+\s+[0-9,.]+\s+[0-9,.]+/i',$fatura[0]["tributos"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        //Verifica se a base (o maior numero) está na primeira posição
        if(formatarNumero(reset($matches2[0])) > formatarNumero($matches2[0][1])){
            $aliquotaCofins = formatarNumero($matches2[0][1]);
            $cofins = formatarNumero(end($matches2[0]));
        }else{
            $aliquotaCofins = formatarNumero($matches2[0][1]);
            $cofins = formatarNumero(reset($matches2[0]));
        }
    }
    if(preg_match('/((I|)CMS.*?)\s+[0-9,.]+\s+[0-9,.]+\s+[0-9,.]+/i',$fatura[0]["tributos"],$matches[0])){
        preg_match_all('/[0-9,.]+/', $matches[0][0], $matches2);
        // echo '<pre>';
        // var_dump($matches2);
        // echo '</pre>';
        //Verifica se a base (o maior numero) está na primeira posição
        if(formatarNumero(reset($matches2[0])) > formatarNumero($matches2[0][1])){
            $base = formatarNumero(reset($matches2[0]));
            $aliquotaICMS = formatarNumero($matches2[0][1]);
            $icms = formatarNumero(end($matches2[0]));
        }else{
            $base = formatarNumero(end($matches2[0]));
            $aliquotaICMS = formatarNumero($matches2[0][1]);
            $icms = formatarNumero(reset($matches2[0]));
        }
    }
}

/**
 * VISUALIZAÇÃO DOS DADOS SOLICITADOS NO DESAFIO 
 * (só deu tempo de testar com a fatura da Enel)
 */

// Dados para exibir na tabela
$dados = [
    'Unidade' => $unidade,
    'Nota Fiscal' => $notaFiscal,
    'CNPJ' => $cnpj,
    'Conta Contrato' => $contaContrato,
    'Codigo de Barras' => $codigoDeBarras,
    'Fisco' => $fisco,
    'Leitura Anterior' => $leituraAnterior,
    'Leitura Atual' => $leituraAtual,
    'Constante' => $constante,
    'Consumo Faturado' => $consumoFaturado,
    'Total Fatura' => $totalFatura,
    'Consumo' => $consumo,
    'Consumo Injetado' => $consumoInjetado,
    'CIP' => $cip,
    'Base' => $base,
    'Aliquota ICMS' => $aliquotaICMS,
    'ICMS' => $icms,
    'Base 2' => $base2,
    'Aliquota Pis' => $aliquotaPis,
    'PIS' => $pis,
    'Aliquota Cofins' => $aliquotaCofins,
    'Cofins' => $cofins
];


// Nome do arquivo CSV a ser gerado
$nomeArquivoCSV = 'dados_fatura.csv';

// Cabeçalho do arquivo CSV
$csv = "Tipo,Valor\n";

// Adiciona os dados ao arquivo CSV
foreach ($dados as $tipo => $valor) {
    $csv .= "$tipo,$valor\n";
}

// Define o cabeçalho HTTP para indicar que o conteúdo é um arquivo CSV
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$nomeArquivoCSV\"");

// Envia o conteúdo do arquivo CSV
echo $csv;

?>