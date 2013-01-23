<?php

class MySvn
{

    public $xml               = null;
    public $changes           = array();
    public $strip_repo_prefix = '/trunk/public_html/';

    protected $action_add = 'A';
    protected $action_del = 'D';
    protected $action_mod = 'M';
    protected $kind_file  = 'file';
    protected $kind_dir   = 'dir';


    public function packFiles($start_revision, $end_revision, $destination)
    {

        $this->getSvnLog($start_revision, $end_revision);
        $this->parseLog();
        $this->createZip($destination);

    }
    protected function getSvnLog($start_revision, $end_revision)
    {
        exec("svn log -r $start_revision:$end_revision -q -v --xml", $output);
        $xmlString = implode(" ", $output);

        $this->xml = simplexml_load_string($xmlString);

    }

    protected function parseLog()
    {
        $query = '/log/logentry/paths/path';
        $result = $this->xml->xpath($query);
        $changes = array();
        foreach ($result as $item) {
            $attrs = $item->attributes();
            $action = $attrs['action'];
            $kind = $attrs['kind'];
            $path = (string) $item;

            if ($kind == $this->kind_file) {
                switch($action) {
                    case $this->action_add:
                    case $this->action_mod:
                        $changes[] = $path;
                        break;
                    case $this->action_del:
                        echo "DEL found: nothing done\t\t$path\n";
                        break;
                }
            }
        }
        $this->changes = array_unique($changes);
        foreach ($this->changes as $id => $path) {
            $this->changes[$id] = str_replace($this->strip_repo_prefix, '', $path);
        }
    }


    protected function createZip($destination = '',$overwrite = true) 
    {

        if (file_exists($destination) && !$overwrite) {

            throw new Exception("File already exists.");
        }

        $valid_files = array();
        foreach ($this->changes as $file) {
            if (file_exists($file)) {

                $valid_files[] = $file;
            } else {

                echo "[WARNING] Can't find file $file\n";
            }
        }

        if (count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {

                throw new Exception("Can't open Zip file at $destination");
            }

            foreach($valid_files as $file) {
                $zip->addFile($file,$file);
            }
            $zip->close();

            //check to make sure the file exists
            if (!file_exists($destination)) {

                throw new Exception("Could not create zip file $destination");
            }

        } else {

            throw new Exception("No files found to include in Zip file!");
        }
    }
}


function usage()
{
    return <<<USAGE
Usage: php -f svnpackfiles.php -- [options]
    -s <number>
    --start <number>    svn revision number to start from

    -e <number>
    --end <number>      svn revision number to end with

    -f <filename>
    --file <filename>   zip file filename

USAGE;
}

function getOption($short, $long, $options) {

    $output = null;
    if (!empty($options[$short])) {

        $output = $options[$short];
    }
    if (!empty($options[$long])) {

        $output = $options[$long];
    }

    if (empty($output)) {

        echo "Missing option --$long\n";
        die(usage());
    } else {

        return $output;
    }
}

$shortopts = "";
$shortopts .= "s:";
$shortopts .= "e:";
$shortopts .= "f:";

$longopts = array(
    "start:",
    "end:",
    "file:",
);

$options = getopt($shortopts, $longopts);

$start = getOption('s', 'start', $options);
$end   = getOption('e', 'end', $options);
$file  = getOption('f', 'file', $options);

$svn = new MySvn();
$svn->packFiles($start, $end, $file);

