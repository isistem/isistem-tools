<?php
session_start();
error_reporting(0);
ini_set("display_errors", "Off");
include_once("inc/funcoes.php");
include_once("migrador/isistemtoolsbackup/preferencias.php");
include_once("migrador/isistemtoolsbackup/informacoes.php");


$verify = verificaToken();
if ($verify === false) {
  header('Location: key.php');
  exit;
}

// Cria a lista de dominios
$lista_dominios = explode("|", get_dominios($_ENV['REMOTE_USER']));
array_multisort($lista_dominios, SORT_ASC);
foreach ($lista_dominios as $dominio_usuario) {
  if ($dominio_usuario) {
    list($dominio, $usuario) = explode(":", $dominio_usuario);
    if ($dominio) {
      $option_dominios .= '<option value="' . $dominio . '">' . $dominio . '</option>';
      $option_dominios_backups .= '<option value="' . $usuario . '">' . $dominio . '</option>';
    }
  }
}

// Cria a lista de datas disponiveis
$lista_datas = explode("|", get_mysql_datas());
array_multisort($lista_datas, SORT_ASC);
foreach ($lista_datas as $data) {
  if ($data) {
    $option_mysql_data .= '<option value="' . $data . '">' . str_replace("-", "/", $data) . '</option>';
  }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Isistem Tools</title>
  <link rel="stylesheet" type="text/css" href="public/semantic/semantic.min.css">

  <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
  <script src="public/semantic/semantic.min.js"></script>
  <script type="text/javascript" src="inc/sortable.js"></script>
  <script type="text/javascript">
    $(document).ready(function(e) {
      $(".ui.stackable.pointing.secondary.teal.menu .item").tab();
      $(".ui.stackable.tabular.blue.menu .item").tab();
      $(".ui.radio.checkbox").checkbox();
      $('.ui.table').tablesort();
      $(".ui.form").form();
      $(".ui.dropdown").dropdown();
      $(".ui.checkbox").checkbox();
      $('.message .close')
        .on('click', function() {
          $(this)
            .closest('.message')
            .transition('fade');
        });
    });
  </script>

</head>

<body style="background-color: #1E88E5">

  <div class="ui container segment" style="margin-top: 24px">
    <div class="column">
      <?php
      if (isset($_SESSION['status_acao'])) {
        $status_acao = stripslashes($_SESSION['status_acao']);
        echo '<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin-top:5px; margin-bottom:5px">' . $status_acao . '</table>';
        unset($_SESSION['status_acao']);
      }
      ?>
    </div>
    <div class="container">
      <div class="ui stackable pointing secondary teal menu">

        <a data-tab="p1" class="active item">FERRAMENTAS</a>
        <a data-tab="p3" class="item">BACKUP</a>

      </div>
    </div>

    <!-- Ferramentas -->
    <div class="ui tab active" data-tab="p1" id="p1" style="padding-top: 16px">

      <div id="ui stackable grid">
        <div class="ui stackable tabular blue top attached menu">
          <a href="#1" data-tab="1" class="active item">Bloqueio/Desbloqueio de IP</a>
          <a href="#2" data-tab="2" class="item">MX Google</a>
          <a href="#3" data-tab="3" class="item">Correção de erro 403/500</a>

          <?php if ($_ENV['REMOTE_USER'] == 'root') { ?>
            <a href="#4" data-tab="4" class="item">Limitador de Emails</a>
          <?php } ?>
        </div>

        <!-- IPs -->
        <div class="ui tab bottom attached segment active stackable grid" data-tab="1" id="1">
          <div class="ui five wide column">
            <form name="firewall" method="post" action="gerenciar-firewall.php" class="ui form">
              <div class="field">
                <label>IP</label>
                <input type="text" name="ip" id="ip" class="form-control" />
              </div>
              <div class="inline field">
                <div class="ui radio checkbox">
                  <label>Bloquear</label>
                  <input type="radio" id="inlineradio1" name="acao" value="b" checked="checked">
                </div>
                <div class="ui radio checkbox">
                  <label>Desbloquear</label>
                  <input type="radio" id="inlineradio2" name="acao" value="d">
                </div>
              </div>
              <div class="field">
                <button type='submit' class='ui right labeled icon teal button'>
                  <i class="play icon"></i>
                  Executar
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- MX Google -->
        <div class="ui tab bottom attached segment stackable grid" data-tab="2" id="2">
          <div class="ui five wide column">
            <form id="google" name="google" class="ui form" method="post" action="configurar-google-mx.php">
              <div class="field">
                <select name="dominio" id="dominio" class="ui dropdown">
                  <option selected="selected" value="">Selecione um domínio</option>
                  <?php echo $option_dominios; ?>
                </select>
              </div>
              <div class="inline field">
                <div class="ui radio checkbox">
                  <label>Google</label>
                  <input type="radio" name="mx" value="google">
                </div>
                <div class="ui radio checkbox">
                  <label>Padrão do Servidor</label>
                  <input type="radio" name="mx" value="padrao" checked="checked">
                </div>
              </div>
              <div class="field">
                <button type='submit' class='ui right labeled icon teal button'>
                  <i class="play icon"></i>
                  Executar
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Erro 403/500 -->
        <div class="ui tab bottom attached segment stackable grid" data-tab="3" id="3" style="margin: 0px 0px 14px;">
          <div class="ui five wide column">
            <form id="erro500" class="ui form" name="erro500" method="post" action="corrigir-erros.php">
              <div class="field">
                <select name="dominio" id="dominio" class="ui dropdown">
                  <option selected="selected" value="">Selecione um domínio</option>
                  <?php echo $option_dominios; ?>
                </select>
              </div>
              <div class="inline field">
                <div class="ui radio checkbox">
                  <label>403</label>
                  <input type="radio" name="erro" value="403">
                </div>
                <div class="ui radio checkbox">
                  <label>500</label>
                  <input type="radio" name="erro" value="500" checked="checked">
                </div>
              </div>
              <div class="field">
                <button type='submit' class='ui right labeled icon teal button'>
                  <i class="play icon"></i>
                  Executar
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Limite de Emails -->
        <div class="ui tab bottom attached segment stackable grid" data-tab="4" id="4" style="margin: 0px 0px 14px;">
          <div class="ui five wide column">
            <form id="limiteemail" class="ui form" name="limiteemail" method="post" action="configurar-limite-email.php">
              <div class="field">
                <select name="dominio" id="dominio" class="ui dropdown">
                  <option selected="selected" value="">Selecione um domínio</option>
                  <?php echo $option_dominios; ?>
                </select>
              </div>
              <div class="inline field">
                <div class="column field">
                  <label>Limite</label>
                  <input name="limite" type="text" id="limite" value="1000" />
                </div>
              </div>
              <div class="field">
                <button type='submit' class='ui right labeled icon teal button'>
                  <i class="play icon"></i>
                  Executar
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>

    </div>

    <!-- Backups -->
    <div class="ui tab" data-tab='p3' id="p3" style="padding-top: 16px">
      <div class="ui stackable grid">
        <div class="column">
          <div class="ui stackable tabular blue top attached menu">
            <a class="active item" data-tab='b1'>Migração</a>
          </div>

          <div class="ui active tab bottom attached segment" data-tab='b1' style="margin-bottom: 14px;">
            <div class="ui icon info mini message">
              <i class="info sign icon"></i>
              <div class="content">
                <div class="header">
                  Informação
                </div>
                <p>Nesta aba, você pode importar revendas ou contas cPanel.</p>
              </div>
            </div>
            <form id='migracao' name='migracao' method='post' action='migracao-post.php' class="ui form stackable grid">
              <input type='hidden' name='migracao' value='1' />
              <div class="three column row">
                <div class="column field">
                  <label>Domínio</label>
                  <input type='text' name='dominio' id='dominio' />
                </div>
                <div class="column field">
                  <label>Usuário</label>
                  <input type='text' name='usuario' id='usuario' />
                </div>
                <div class="column field">
                  <label>Senha</label>
                  <input type='password' name='senha' id='senha' />
                </div>
                <div class="column field">
                  <label>Tipo</label>
                  <select name='tipo' class='ui dropdown selection'>
                    <option value='revenda'>Revenda WHM</option>
                    <option value='conta'>Conta cPanel</option>
                  </select>
                </div>
                <div class="column field">
                  <label>Contas</label>
                  <select name='filtro' class='ui dropdown'>
                    <option value='TODAS'>Todas</option>
                    <option value='IN'>Apenas estas</option>
                    <option value='EX'>Não estas</option>
                  </select>
                </div>
              </div>
              <div class="ui divider"></div>
              <div class="three column row">
                <div class="column grouped field">
                  <label>Opções</label>
                  <div class="field">
                    <div class="ui checkbox">
                      <input name='forcado' id='forcado' type='checkbox' value='1' />
                      <label>Criar backup usando o Forced Backup®</label>
                    </div>
                  </div>
                  <div class="field">
                    <div class="ui checkbox">
                      <input name='existente' id='existente' type='checkbox' value='1'/>
                      <label>Importar mesmo se já existir neste servidor</label>
                    </div>
                  </div>
                  <div class="field">
                    <div class="ui fluid checkbox">
                      <input name='agendar' id='agendar' type='checkbox' value='1' />
                      <label>Agendar migração para daqui a </label>
                    </div>
                    <select name=tempo class='ui fluid dropdown'><?php echo $sellected_time ?></select>
                  </div>
                  <?php if ($_ENV['REMOTE_USER'] == 'root') : ?>
                    <div class="field">
                      <div class="ui fluid checkbox">
                        <input name='mudar' id='mudar' type='checkbox' value='1' />
                        <label>Mover as contas para outra revenda </label>
                      </div>
                      <select name=revenda class='ui fluid dropdown'>
                        <?php echo $reseller_user ?>
                      </select>
                    </div>
                  <?php endif ?>
                </div>
              </div>
              <div class="sixteen wide column field">
                <button type='submit' class='ui teal right labeled icon button'>
                  <i class="download icon"></i>Iniciar Transferência
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>


    <div class="container">
      <center>
        <p>Isistem Tools -
          <a href="http://tools.isistem.com.br" target="__blank">http://tools.isistem.com.br</a>
        </p>
      </center>
    </div>


</body>

</html>