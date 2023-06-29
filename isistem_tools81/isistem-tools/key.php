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
</head>

<body>

  <div class="ui container">


    <div class="ui two column centered segment stackable grid">
      <div class="ui sixteen wide column">
        <center>
          <h3 class="ui header">ISISTEM TOOLS - CADASTRAR KEY</h3>
        </center>
      </div>
      <div class="ui ten wide column">
        <form name="cadastrarKey" method="post" action="cadastrarKey.php" class="ui fluid form">
          <div class="ui fluid field">
            <label>KEY</label>
            <input type="text" name="key" id="key" class="form-control" />
          </div>
          <div class="ui fluid field">
            <button type='submit' class='ui teal right labeled icon right floated button'><i class="privacy icon"></i>Cadastrar</button>
          </div>
        </form>

      </div>
      <div class="ui divider"></div>
      <div class="ui column">
        <center>
          <p>Caso não tenha uma key, entre em contato conosco -
            <a href="http://tools.isistem.com.br" target="__blank">tools.isistem.com.br</a>
          </p>
        </center>
      </div>
    </div>


  </div>
</body>

</html>