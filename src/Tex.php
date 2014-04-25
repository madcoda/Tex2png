<?php

namespace Madcoda\Tex2png;

/**
 * Abstraction of a Tex document
 * 
 */
class Tex{


	protected $packages = array('color', 'amsfonts');

    protected $font = null;

    protected $fontSize = 12;

    protected $lines = array();

    private function __construct($content, $packages = array()){

        $this->append($content);
    	if(is_array($packages)){
    		$this->packages = array_merge($this->packages, $packages);
    	}

    }


    public static function newDoc($content=''){
    	$tex = new Tex($content);
    	return $tex;
    }

    /**
     * Adding a few math packages (amsmath, pst-plot)
     * @return [type] [description]
     */
    public static function newMathDoc($content=''){
    	$tex = new Tex($content, array('amssymb', 'amsmath', 'pst-plot'));
    	return $tex;
    }

    public function create(){
    	$tex = self::createTex($this->getContent(), $this->fontSize, $this->font, $this->packages);
    	return $tex;
    }

    public function addLine($line){
    	$this->lines[] = $line;
        return $this;
    }

    public function getLines(){
    	return $this->lines;
    }


    public function getContent(){
    	return implode("\n", $this->lines);
    }

    public function setContent($content){
        if($content && $content !== ''){
            $this->lines = explode("\n", $content);
        }
        return $this;
    }

    public function append($content){
        if($content && $content !== ''){
            $this->lines = array_merge($this->lines, explode("\n", $content ));
        }
        
        return $this;
    }


    /**
     * serialize the packages in comma-separated string
     * @return string 
     */
    public function getPackagesNames(){
    	return implode(',', $this->packages);
    }

    public function getPackages(){
    	return $this->packages;
    }

    public function addPackage($package){
    	if(! in_array($package, $this->packages)){
    		$this->packages[] = $package;
    	}
        return $this;
    }

    public function removePackage($package){
    	$key = array_search($package, $this->packages);
    	if($key !== false){
    		array_splice($this->packages, $key, 1);
    	}
        return $this;
    }

    public function setFont($font){
        $this->font = $font;
        return $this;
    }

    public function getFont(){
        return $this->font;
    }

    public function setFontSize($fontSize){
        $this->fontSize = $fontSize;
        return $this;
    }

    public function getFontSize(){
        return $this->fontSize;
    }


    public static function createTex($content, $fontSize, $font = null, $packages=array())
    {

        $tex = '\documentclass['.$fontSize.'pt,fleqn]{article}'."\n";
        if($font){
        	$tex .= '\usepackage{'.$font.'}'."\n";
        }
        $tex .= '\usepackage[utf8]{inputenc}'."\n";

        foreach ($packages as $package) {
            $tex .= '\usepackage{' . $package . "}\n";
        }
        
        $tex .= '\pagestyle{empty}'."\n";
        $tex .= '\begin{document}'."\n";
        $tex .= $content."\n";
        $tex .= '\end{document}'."\n";

        return $tex;
    }

    public function __toString(){
        return $this->create();
    }

}