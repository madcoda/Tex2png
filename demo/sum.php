<?php

require('../autoload.php');

use Madcoda\Tex2png\Tex2png;

session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

	//echo "Generating sum.png...\n";

	Tex2png::create($_REQUEST['tex'], 150)
    ->saveTo('sum.png')
    ->generate();

    $_SESSION['tex'] = $_REQUEST['tex'];

    header('Location: ' . $_SERVER["REQUEST_URI"]);
    die();
}

?>
<img src="sum.png?r=<?php echo rand()  ?>" />
<hr />
<form action="" method="post">
	<? if(isset($_SESSION['tex'])): ?>
	<textarea name="tex" rows="3" style="width:600px;font-size:16px"><?php echo $_SESSION['tex'] ?></textarea>
	<? else: ?>
	<textarea name="tex" rows="3" style="width:600px;font-size:16px">
\documentclass[12pt,fleqn]{article} 
\usepackage{amsfonts}
\usepackage{comicsans}
\usepackage{amssymb,amsmath}

\begin{document} 
\pagestyle{empty}
\noindent By completing square,
\begin{align}
  y = { a ( x-h ) }^2 +k \nonumber \\
  vertex = \left( h,k \right) \nonumber
\end{align}
axis of symmetry:~~x = h
\end{document} 
	</textarea>
	<? endif; ?>
	<br>
	<input type="submit" value="Submit" />
</form>
