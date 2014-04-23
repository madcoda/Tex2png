<?php

namespace Madcoda\Tests;

require_once('./vendor/autoload.php');

use Madcoda\Tex2png\Tex;

class TexTest extends \PHPUnit_Framework_TestCase
{
	var $tex;

	public function setUp()
    {
    	//Tex2png::$debug = true;
        $this->tex = null;
    }

    public function tearDown()
    {
    	error_log($this->tex);
    	$this->tex = null;

    }

    public function testNewMathDoc(){
    	$this->tex = Tex::newMathDoc("");

    	$this->assertTrue( in_array('amsmath', $this->tex->getPackages()) );
    	$this->assertTrue( in_array('amssymb', $this->tex->getPackages()) );
    	$this->assertTrue( in_array('pst-plot', $this->tex->getPackages()) );
    }


    public function testFont(){
    	$this->tex = Tex::newDoc("")->setFont('comicsans');
    	$this->assertEquals($this->tex->getFont(), 'comicsans', 'getFont() does not return correct result');

    	$finalTex = $this->tex->create();
    	$this->assertTrue( strpos($finalTex, '\usepackage{comicsans}') !== false , 'Final Tex not contain the font declaration');
    }

    public function testFontSize(){
    	$this->tex = Tex::newDoc("")->setFontSize(14);
    	$this->assertEquals($this->tex->getFontSize(), 14, 'getFontSize() does not return correct result');

    	$finalTex = $this->tex->create();
    	$this->assertTrue( strpos($finalTex, '\documentclass[14pt,fleqn]{article}') !== false , 'Final Tex not contain the font size declaration');	
    }

    public function testAddPackage(){
    	$this->tex = Tex::newDoc("");

    	$this->assertTrue(strpos('amsmath', $this->tex->getPackagesNames()) === false);
    	$this->tex->addPackage('amsmath');
    	$this->assertTrue( in_array('amsmath', $this->tex->getPackages()) );
    }

    public function testRemovePackage(){

    	$this->tex = Tex::newDoc("");

    	//add first
    	$this->tex->addPackage('amsmath');
    	$this->assertTrue( in_array('amsmath', $this->tex->getPackages()) );

    	//remove test
    	$this->tex->removePackage('amsmath');
    	$this->assertTrue(strpos('amsmath', $this->tex->getPackagesNames()) === false);

    }


    public function testCreate(){

    	$newline = '$ \sum_{i = 0}^{i = n} \frac{i}{2} $';

    	$this->tex = Tex::newMathDoc("");
    	$this->tex->append($newline);

    	$finalTex = $this->tex->create();
    	$this->assertTrue( strpos($finalTex, $newline) !== false , 'Final Tex not contain the added content');	


    }



}