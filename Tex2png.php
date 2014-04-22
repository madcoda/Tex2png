<?php

namespace Gregwar\Tex2png;

/**
 * Helper to generate PNG from LaTeX document
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class Tex2png
{
    
    /**
    * Where is the LaTex ?
    */
    const LATEX = "$(which latex)";
    
    /**
    * Where is the DVIPNG ?
    */
    const DVIPNG = "$(which dvipng)";

    /**
     * LaTeX packges
     */
    public $packages = array('amssymb,amsmath', 'color', 'amsfonts', 'amssymb', 'pst-plot');

    /**
     * Temporary directory
     * This is needed to write temporary files needed for
     * generation
     */
    public $tmpDir = '/tmp';

    /**
     * Target file
     */
    public $file = null;

    /**
     * Target actual file
     */
    public $actualFile = null;

    /**
     * Hash
     */
    public $hash;

    /**
     * LaTeX document
     */
    public $document;

    /**
     * Target density
     */
    public $density;

    /**
     * Error (if any)
     */
    public $error = null;

    /**
     * Set to true to output error msg to logs
     * @var boolean
     */
    public static $debug = false;


    public static function create($document, $density = 155)
    {
        return new self($document, $density);
    }

    public function __construct($document, $density = 155)
    {
        $datas = array(
            'document' => $document,
            'density' => $density,
        );

        $this->document = $document;
        $this->density = $density;
        $this->hash = sha1(serialize($datas));

        return $this;
    }

    /**
     * Sets the target directory
     */
    public function saveTo($file)
    {
        $this->actualFile = $this->file = $file;

        return $this;
    }

    /**
     * Generates the image
     */
    public function generate()
    {
        $tex2png = $this;

        $generate = function($target) use ($tex2png) {
            $tex2png->actualFile = $target;

            try {
                // Generates the LaTeX file
                $tex2png->createFile();
           
                // Compile the latexFile     
                $tex2png->latexFile();

                // Converts the DVI file to PNG
                $tex2png->dvi2png();
            } catch (\Exception $e) {
                $this->debug($e);
                $tex2png->error = $e;
            }

            $tex2png->clean();
        };

        if ($this->actualFile === null) {
            $target = $this->hash . '.png';
            $this->actualFile = $this->file = $this->tmpDir . '/' . $target;
        }

        $generate($this->actualFile);

        return $this;
    }



    public function createFile()
    {
        $tmpfile = $this->tmpDir . '/' . $this->hash . '.tex';

        $tex = $this->document;

        if (file_put_contents($tmpfile, $tex) === false) {
            throw new \Exception('Failed to open target file');
        }
    }

    /**
     * Create the LaTeX file
     */
    public function createMathFile()
    {
        $tmpfile = $this->tmpDir . '/' . $this->hash . '.tex';

        $tex = '\documentclass[12pt]{article}'."\n";
        $tex .= '\usepackage{comicsans}'."\n";
        $tex .= '\usepackage[utf8]{inputenc}'."\n";

        // Packages
        foreach ($this->packages as $package) {
            $tex .= '\usepackage{' . $package . "}\n";
        }
        
        $tex .= '\begin{document}'."\n";
        $tex .= '\pagestyle{empty}'."\n";
        $tex .= '\begin{displaymath}'."\n";
        
        $tex .= $this->document."\n";
        
        $tex .= '\end{displaymath}'."\n";
        $tex .= '\end{document}'."\n";

        $this->debug($tex);

        if (file_put_contents($tmpfile, $tex) === false) {
            throw new \Exception('Failed to open target file');
        }
    }

    /**
     * Compiles the LaTeX to DVI
     */
    public function latexFile()
    {

        //$command = 'cd ' . $this->tmpDir . '; ' . self::LATEX . ' ' . $this->hash . '.tex < /dev/null |grep ^!|grep -v Emergency > ' . $this->tmpDir . '/' .$this->hash . '.err 2> /dev/null 2>&1';
        $command = 'cd ' . $this->tmpDir . '; ' . self::LATEX . ' ' . $this->hash . '.tex </dev/null 2>&1';
        $this->debug("command = " . $command);

        $output = array();
        $return_var = -1;
        $last_line = exec($command, $output, $return_var);
        $this->debug('return_var = ' . $return_var);
        $this->debug('output =' . print_r($output, true));

        //if (!file_exists($this->tmpDir . '/' . $this->hash . '.dvi')) {
        //    throw new \Exception('Unable to compile LaTeX document (is latex installed? check syntax)');
        //}
        if( $return_var !== 0 || !file_exists($this->tmpDir . '/' . $this->hash . '.dvi') ){
            $full_error = implode("\n", $output);
            throw new \Exception('Error compiling the .tex file : ' . $full_error);
        }
    }

    /**
     * Converts the DVI file to PNG
     */
    public function dvi2png()
    {
        $this->debug("in dvi2png");

        // XXX background: -bg 'rgb 0.5 0.5 0.5'
        $command = self::DVIPNG . ' -q -T tight -bg Transparent -D ' . $this->density . ' -o ' . $this->actualFile . ' ' . $this->tmpDir . '/' . $this->hash . '.dvi 2>&1';
        $this->debug("command = " . $command);

        $output = array();
        $return_var = -1;
        $last_line = exec($command, $output, $return_var);
        $this->debug('return_var =' . $return_var);
        $this->debug('output =' . print_r($output, true));

        if ($return_var !== 0) {
            $full_error = implode("\n", $output);
            throw new \Exception('Unable to convert the DVI file to PNG (is dvipng installed?) :' . $full_error);
            //throw new \Exception('Unable to convert the DVI file to PNG (is dvipng installed?)');
        }
        $this->debug("finished dvi2png");
    }

    /**
     * Cleaning
     */
    public function clean()
    {
        @shell_exec('rm -f ' . $this->tmpDir . '/' . $this->hash . '.* 2>&1');
    }

    /**
     * Gets the HTML code for the image
     */
    public function html()
     {
        if ($this->error)
        {
            return '<span style="color:red">LaTeX: syntax error (' . $this->error->getMessage() . ')</span>';
        }
        else
        {
            return '<img class="document" title="document" src="' . $this->getFile() . '">';
        }
    }

    /**
     * Sets the temporary directory
     */
    public function setTempDirectory($directory)
    {
        $this->tmpDir = $directory;
    }

    /**
     * Returns the PNG file
     */
    public function getFile()
    {
        return $this->hookFile($this->file);
    }

    /**
     * Hook that helps extending this class (eg: adding a prefix or suffix)
     */
    public function hookFile($filename)
    {
        return $filename;
    }

    /**
     * The string representation is the cache file
     */
    public function __toString()
    {
        return $this->getFile();
    }

    private function debug($msg){
        if(self::$debug === true){
            error_log($msg);
        }
    }
}
