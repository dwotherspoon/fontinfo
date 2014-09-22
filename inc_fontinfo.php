<?php
    /* PHP FontInfo class, based on C# version.
    *
    */
  class FontInfo {
    const TTF = 1;
    const OTF = 2;
    const _3B2 = 3;   
    private $type = 0;
    private $file = null;
    private $error = "none";
    private $fh = null;
    
    private $fullName = null;
    private $familyName = null;
    private $version = null;
    private $weight = null;
    private $shortName = null; //3B2 only.

    
    function __construct($file) {
         if (!file_exists($file)) {
             $this->error = "Error: File does not exist.";
             return;
         }
         $this->file = $file;
         $this->fh = fopen($file, "r");
         $mnum = fread($this->fh, 4);        
         $mnum = unpack("C*", $mnum);
         //print_r($mnum);
         if (strtoupper(substr($file, -4)) == ".FNT") {//3B2 fonts don't have a magic number, we assume it's okay.
            $this->type = self::_3B2;
         }
         elseif (array_diff($mnum, array(0x00, 0x01, 0x00, 0x00)) === array()){
            $this->type = self::TTF;
         }
         else if (array_diff($mnum, array(0x4F, 0x54, 0x54, 0x45)) === array()) {
            $this->type = self::OTF;
         }
         else {
            $this->error("Error: Magic number not recognised.");
         }
    }
    
    public function readInfo()
    {
        switch ($this->type)
        {
            case self::OTF:
                $this->parseTTF();
                break;
            case self::TTF:
                $this->parseTTF();
                break;
            case self::_3B2:
                $this->parse3B2();
                break;
            default:
                $this->error("Error: Unknown font type.");
                break;
        }
        fclose($this->fh);   
    }
    
    //reads a binary string of n bytes and reveses it.
    private function rrev($n) {
        return strrev(fread($this->fh, $n)); 
    }
    
    //n.b. big endian
    private function parseTTF() {
        $numTables = array_pop(unpack("n", fread($this->fh, 2))); //unsigned 16bit shorts.
        $searchRange = array_pop(unpack("n", fread($this->fh, 2)));
        $entrySelector = array_pop(unpack("n", fread($this->fh, 2)));
        $rangeShift = array_pop(unpack("n", fread($this->fh, 2)));
        $tables = array();
        $ntable = null; 
        //print_r("numTables: {$numTables}\n");                                
        //read in tables.
        for ($i = 0; $i < $numTables; $i++) {
            $entry = array(
                            "tag" => fread($this->fh, 4), //4 chars
                            "checksum" => array_pop(unpack("N", fread($this->fh, 4))), //unsigned 32bit longs 
                            "offset" => array_pop(unpack("N", fread($this->fh, 4))),
                            "length" => array_pop(unpack("N", fread($this->fh, 4))));
            //print_r($entry);
            array_push($tables, $entry);
            //is this a name table?
            if ($entry['tag'] == "name") $ntable = new FontInfo_NameTable($this->fh, $entry);    
        }
        if (!$ntable) {
            $this->error = "Error: Could not find a name tag.";
        } 
        else {
            $has_plat1 = false;
            $has_plat3 = false; //windows, utf-16 encoded...
            foreach ($ntable->nameRecords as $k=>$v) {
                 if ($v['nameID'] == 4 && $v['languageID'] == 0 
                    && $v['platformID'] == 1 && $v['encodingID'] == 0) $has_plat1 = true;
                 else if ($v['nameID'] == 4 && $v['languageID'] == 0x409 
                    && $v['platformID'] == 3 && $v['encodingID'] == 1) $has_plat3 = true;
            }
            //print_r("Has platform 1: {$has_plat1}\n");
            //print_r("Has platform 3: {$has_plat3}\n");
            if ($has_plat1) { //prefer plat1
                //pull out data
                foreach ($ntable->nameRecords as $k => $v) {
                    if ($v['nameID'] == 4 && $v['languageID'] == 0 
                        && $v['platformID'] == 1 && $v['encodingID'] == 0) $this->fullName = $v['data'];
                    else if ($v['nameID'] == 1 && $v['languageID'] == 0 
                        && $v['platformID'] == 1 && $v['encodingID'] == 0) $this->familyName = $v['data'];
                    else if ($v['nameID'] == 5 && $v['languageID'] == 0 
                        && $v['platformID'] == 1 && $v['encodingID'] == 0) $this->version = $v['data'];
                    else if ($v['nameID'] == 2 && $v['languageID'] == 0 
                        && $v['platformID'] == 1 && $v['encodingID'] == 0) $this->weight = $v['data'];
                }      
            }
            else if ($has_plat3) {
                //Fall back to Windows, N.B. this is UTF-16 BE
                foreach ($ntable->nameRecords as $k => $v) {
                    if ($v['nameID'] == 4 && $v['languageID'] == 0x409 
                        && $v['platformID'] == 3 && $v['encodingID'] == 1) $this->fullName = $v['data'];
                    else if ($v['nameID'] == 1 && $v['languageID'] == 0x409 
                        && $v['platformID'] == 3 && $v['encodingID'] == 1) $this->familyName = $v['data'];
                    else if ($v['nameID'] == 5 && $v['languageID'] == 0x409 
                        && $v['platformID'] == 3 && $v['encodingID'] == 1) $this->version = $v['data'];
                    else if ($v['nameID'] == 2 && $v['languageID'] == 0x409 
                        && $v['platformID'] == 3 && $v['encodingID'] == 1) $this->weight = $v['data'];
                }     
            }
            else {
                $this->error = "Error: Unable to find desired name record.";
            }
        }
    }
    
    private function parse3B2() {
        $this->shortName = array();
        $this->fullName = array();
        /* Based on fmg3b2.exe binary assembly */
        fseek($this->fh, 0x00, SEEK_SET);
        do {
            //seek back to index #0 to get the last marker 
            $last = array_pop(unpack("C", fread($this->fh, 1))) == 0x00;
            fseek($this->fh, 0x0F, SEEK_CUR);
            //now we're pointing at 0x10
            $sns = fread($this->fh, 0x10);
            $sns = array_shift(explode("\x00", $sns));
            
            array_push($this->shortName, $sns); //max 16 chars
            //now we're pointing at 0x20
            $snl = fread($this->fh, 0x30);
            $snl = array_shift(explode("\x00", $snl));
            array_push($this->fullName, $snl); //max 48 chars 
            //next block
            fseek($this->fh, 0x400 - 0x50, SEEK_CUR);
            //echo "LAST:".($last?"true":"false")."\n";
        }   while (!$last);    
    }
    /* Getters */
    
    public function getError() {
        return $this->error;
    }
    
    public function getFullName() {
      return $this->fullName;
    }
   
    public function getFamilyName() {
      return $this->familyName;
    }
    
    public function getVersion() {
      return $this->version;
    }
    
    public function getWeight() {
      return $this->weight;
    }    
  }
  
  //For TTF name tables.
  class FontInfo_NameTable {
      private $entry; //the table entry from preamble.
      private $format;
      private $count;
      private $strOffset;
      private $langTagCount;
      public $nameRecords;
      public $langTagRecords;
      
      function __construct($fh, $item) {
        $this->entry = $item;
        $this->nameRecords = array();
        $spos = ftell($fh); //store the position for later.
        fseek($fh, $item['offset'], SEEK_SET); //move to offset
        $this->format = array_pop(unpack("n", fread($fh, 2)));
        $this->count = array_pop(unpack("n", fread($fh, 2)));
        $this->strOffset = array_pop(unpack("n", fread($fh, 2)));
        //for each name record.
        for ($i = 0; $i < $this->count; $i++) {
            array_push($this->nameRecords, $this->readNameRecord($fh, $item['offset']+$this->strOffset));   
        }
        //format v1 
        if ($this->format == 1) {
            $this->langTagRecords = array();
            for ($i = 0; $i < $this->langTagCount; $i++) {
                array_push($this->langTagRecords, $this->readLangTagRecord($fh, $item['offset'] + $this->strOffset));   
            } 
        }
        fseek($fh, $spos, SEEK_SET); //back to start 
      }
      
      //read a name record
      private function readNameRecord($fh, $straddr) {
        $ret = array("platformID" => array_pop(unpack("n", fread($fh, 2))),
                        "encodingID" => array_pop(unpack("n", fread($fh, 2))),
                        "languageID" => array_pop(unpack("n", fread($fh, 2))),
                        "nameID" => array_pop(unpack("n", fread($fh, 2))),
                        "length" => array_pop(unpack("n", fread($fh, 2))),
                        "offset" => array_pop(unpack("n", fread($fh, 2)))
                        );
        $cpos = ftell($fh);
        fseek($fh, $straddr + $ret['offset'], SEEK_SET);
        $ret['data'] = fread($fh, $ret['length']);
        fseek($fh, $cpos, SEEK_SET);
        return $ret;
      }
      
      //read a language tag record.
      private function readLangTagRecord($fh, $straddr) {
          $ret = array("length" => array_pop(unpack("n", fread($fh, 2))),
                        "offset" => array_pop(unpack("n", fread($fh, 2)))
                        );
          $cpos = ftell($fh);
          fseek($fh, $straddr + $ret['offset'], SEEK_SET);
          $ret['data'] = fread($fh, $ret['length']);
          fseek($fh, $cpos, SEEK_SET);        
          return $ret;
      }
  }
  /* Tests */
  echo "<pre>";
  $fonts = array(
    'C:\Windows\Fonts\times.ttf',
    'C:\Windows\Fonts\amiri-regular.ttf',
    'C:\Windows\Fonts\amiri-bold.ttf',
    'C:\Windows\Fonts\Proxima Nova Cond Sbold.ttf',
    'C:\3B2WIN\advee___.ttf',
    'C:\Windows\Fonts\Arimo-Bold.ttf',
    'C:\Users\Christine\Desktop\Gotham-Book.otf',
    'C:\3B2WIN\SETTINGHOST_FONTS\AVENIR.FNT',
    'C:\3B2WIN\SETTINGHOST_FONTS\xmaths.FNT');
  foreach($fonts as $f) {
      print_r($f."\n");
      $fi = new FontInfo($f);
      print_r($fi);
      $fi->readInfo();
      print_r($fi);
      print_r("--------------------------------------\n");
  }
  echo "<pre>";
?>
