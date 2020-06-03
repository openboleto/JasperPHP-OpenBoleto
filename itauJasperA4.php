<?php
// para rodar este exemplo é necessario adicionar no seu composer
// "quilhasoft/jasperphp":"dev-master"
// "openboleto/openboleto":"dev-master"


//require '../autoloader.php';
//require '../../../rctnet/JasperPHP/autoloader.php';
require '../../autoload.php'; // necessario rodar o autoad principal do seu composer para pegar o openboleto, e JasperPHP

use OpenBoleto\Banco\Itau;
use OpenBoleto\Agente;
use JasperPHP\Report;
//use JasperPHP\ado\TTransaction;
//use JasperPHP\ado\TLoggerHTML;

class Boleto
{
    /* Variavel que armazenara os dados do boleto 
    / @var array();
    */
    private $data = array();
    /*
    * método __set()
    * executado sempre que uma propriedade for atribuída.
    */
    public function __set($prop, $value)
    {
        // verifica se existe método set_<propriedade>
        if (method_exists($this, 'set_'.$prop))
        {
            // executa o método set_<propriedade>
            call_user_func(array($this, 'set_'.$prop), $value);
        }
        else
        {
            if ($value === NULL)
            {
                unset($this->data[$prop]);
            }
            else
            {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }
    /*
    * método __get()
    * executado sempre que uma propriedade for requerida
    */
    public function __get($prop)
    {
        // verifica se existe método get_<propriedade>
        if (method_exists($this, 'get_'.$prop))
        {
            // executa o método get_<propriedade>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // retorna o valor da propriedade
            if (isset($this->data[$prop]))
            {
                return ($this->data[$prop]);
            }
        }
    }

    public function __construct($sequencial = null)
    {
        //
        // aqui voce pode acessar sua base de dados e coletar os dados do boleto e preencher os campos abaixo
        //
        
        $sacado = new Agente('Fernando Maia', '023.434.234-34', 'ABC 302 Bloco N', '72000-000', 'Brasília', 'DF');
        $cedente = new Agente('Empresa de cosméticos LTDA', '02.123.123/0001-11', 'CLS 403 Lj 23', '71000-000', 'Brasília', 'DF');

        $boleto = new Itau(array(
            // Parâmetros obrigatórios
            'dataVencimento' => new DateTime('2013-01-24'),
            'valor' => 23.00,
            'sequencial' => $sequencial, // 8 dígitos
            'sacado' => $sacado,
            'cedente' => $cedente,
            'agencia' => 1724, // 4 dígitos
            'carteira' => 112, // 3 dígitos
            'conta' => 12345, // 5 dígitos

            // Parâmetro obrigatório somente se a carteira for
            // 107, 122, 142, 143, 196 ou 198
            'codigoCliente' => 12345, // 5 dígitos
            'numeroDocumento' => $sequencial, // 7 dígitos

            // Parâmetros recomendáveis
            //'logoPath' => 'http://empresa.com.br/logo.jpg', // Logo da sua empresa
            'contaDv' => 2,
            'agenciaDv' => 1,
            'descricaoDemonstrativo' => array( // Até 5
                'Compra de materiais cosméticos',
                'Compra de alicate',
            ),
            'instrucoes' => array( // Até 8
                'Após o dia 30/11 cobrar 2% de mora e 1% de juros ao dia.',
                'Não receber após o vencimento.',
            ),

            // Parâmetros opcionais
            //'resourcePath' => '../resources',
            //'moeda' => Itau::MOEDA_REAL,
            //'dataDocumento' => new DateTime(),
            //'dataProcessamento' => new DateTime(),
            //'contraApresentacao' => true,
            //'pagamentoMinimo' => 23.00,
            //'aceite' => 'N',
            //'especieDoc' => 'ABC',
            //'usoBanco' => 'Uso banco',
            //'layout' => 'layout.phtml',
            //'logoPath' => 'http://boletophp.com.br/img/opensource-55x48-t.png',
            //'sacadorAvalista' => new Agente('Antônio da Silva', '02.123.123/0001-11'),
            //'descontosAbatimentos' => 123.12,
            //'moraMulta' => 123.12,
            //'outrasDeducoes' => 123.12,
            //'outrosAcrescimos' => 123.12,
            //'valorCobrado' => 123.12,
            //'valorUnitario' => 123.12,
            //'quantidade' => 1,
        ));
        $boleto->getOutput();
        $this->data = array_merge($this->data,$boleto->getData());
    }
    
    /* método para interceptar  a requisição e adicionar o codigo html necessario para correta exibição do demostrativo    */
    public function get_demonstrativo()
    {
        return '<table>
        <tr>

        <td>'.implode('<br>',$this->data['demonstrativo']).
        '</td>
        </tr>
        <table>';
    }
    
    /* método para interceptar  a requisição e adicionar o codigo html necessario para correta exibição das instrucoes    */
    public function get_instrucoes()
    {
        return '<table>
        <tr>

        <td>'.implode('<br>',$this->data['instrucoes']).'
        </td>
        </tr>
        <table>';
    }

    /* este metodo esta aqui para manter compatibilidade do jxml criado para o meu sistema*/
    public function get_carteiras_nome()
    {
        return $this->data['carteira'];
    }

}
// altere aqui para o nome do arquivo de configuração no diretorio config desativado mas pode ser usado por usuarios avançados
//JasperPHP\ado\TTransaction::open('dev'); 
    
// instancição do objeto :1 parametro: caminho do layout do boleto , 2 parametro :  array com os parametros para consulta no banco para localizar o boleto
// pode ser passado como paramtro um array com os numeros dos boletos que serão impressos desde que criado sql dentro do arquivo jrxml(desativado nesse exemplo)

//$report =new JasperPHP\Report("bol01Files/boletoCarne.jrxml",array());
$report =new JasperPHP\Report("bol01Files/boletoA4.jrxml",array());
    
JasperPHP\Instructions::prepare($report);    // prepara o relatorio lendo o arquivo
$report->dbData = array(new Boleto(1),new boleto(2)); // aqui voce pode construir seu array de boletos em qualquer estrutura incluindo 
$report->generate(array());                // gera o relatorio

$report->out();                     // gera o pdf
$pdf  = JasperPHP\PdfProcessor::get();       // extrai o objeto pdf de dentro do report
$pdf->Output('boleto.pdf',"I");  // metodo do TCPF para gerar saida para o browser
