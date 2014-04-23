<?php

namespace Madcoda\Tests;

require_once('./vendor/autoload.php');

use Madcoda\Tex2png\Tex2png;

class Tex2PngTest extends \PHPUnit_Framework_TestCase
{

	var $tex2png;

	public function setUp()
    {
    	//Tex2png::$debug = true;

        $this->tex2png = null;
       	@unlink('test.png');
    }

    public function tearDown()
    {
    	$this->tex2png = null;
    	@unlink('test.png');
    }

    public function GoodExample(){
    	return array(
    		array("\\documentclass[12pt,fleqn]{article}
					\\usepackage{amsfonts}
					\\usepackage{amssymb,amsmath}
					\\pagestyle{empty}
					\\begin{document}
					\\begin{math}
					Profit~Loss~~ =~~ \\cfrac { Profit~Loss-cost }{ Cost } \\times 100%
					\\end{math}
					\\end{document}")
    	);
    }

     public function BadExample(){
    	return array(
    		array("\\usepackage{amsfonts}
					\\usepackage{amssymb,amsmath}
					\\pagestyle{empty}
					\\begin{document}
					Profit~Loss~~ =~~ \\cfrac { Profit~Loss-cost }{ Cost } \\times 100%
					\\end{document}"),

    		array("\\documentclass[12pt,fleqn]{article}
					\\usepackage{amsfonts}
					\\usepackage{amssymb,amsmath}
					\\pagestyle{empty}
					\\begin{document}
					Profit~Loss~~ =~~ \\cfrac { Profit~Loss-cost }{ Cost } \\times 100%
					\\end{document}")
    	);
    }


    /**
     * @dataProvider GoodExample
     */
    public function testSuccess($input){

    	//error_log($input);

    	$this->tex2png = Tex2png::create($input, 150)
    	->saveTo('test.png')
    	->generate();

    	$this->assertTrue(is_file('test.png'), "test.png not generated");
    	$this->assertTrue($this->tex2png->error === null, "error found");
    	//error_log($this->tex2png->error);
    }



    /**
     * @dataProvider BadExample
     * @expectedException Exception
     */
    public function testSyntaxError(){
    	$this->tex2png = Tex2png::create($input, 150)
    	->saveTo('test.png')
    	->generate();
    	//error_log($this->tex2png->error);
    }


}