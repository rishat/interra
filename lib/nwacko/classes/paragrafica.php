<?php
/*

  Typografica library: paragrafica class.
  v.2.3
  20 October 2004.

  ---------

  http://www.pixel-apes.com/typografica

  Copyright (c) 2004, Kuso Mendokusee <mailto:mendokusee@yandex.ru>
  All rights reserved.

  For LICENSE see typografica-license.txt

  ---------

  * Correct( $what ) -- обрамить параграфами текст
      - $data -- корректируемый текст

  * constructor( &$wacko ) -- конструктор
      - &$wacko -- ссылка на некоторый объект, пока неважно на какой.
                   в WackoWiki это объект-синглетон класса Wacko

=============================================================== (kuso@npj)
*/

 class paragrafica
 {
   var $ignore = "/(<!--notypo-->.*?<!--\/notypo-->)/si"; // regex, который игнорируется.
   // абзац/параграф это такая хрень:   <t->text, text, fcuking text<-t>
   var $wacko;
   var $t0 = array( // терминаторы вида <-t>$1<t->
          "/(<br[^>]*>)(\s*<br[^>]*>)+/si", 
          "/(<hr[^>]*>)/si", 

                 );
   var $t1 = array( // терминаторы вида <-t>$1
      array( // rightinators
          "!(<table)!si", 
          "!(<a[^>]*></a><h[1-9]>)!si", 
          "!(<(u|o)l)!si", 
          "!(<div)!si", 
          "!(<p)!si", 
          "!(<form)!si", 
          "!(<textarea)!si", 
          "!(<blockquote)!si", 
		  "!(\[cut)!si",
          //"/<!--notypo-->/si", // ??? obvious
          ),
      array( // wronginators
          "!(</td>)!si", 
          "!(</li>)!si", 
          )
                 );
   var $t2 = array( // терминаторы вида $1<t->
      array( // rightinators
          "!(</table>)!si", 
          "!(</h[1-9]>)!si", 
          "!(</(u|o)l>)!si", 
          "!(</div>)!si", 
          "!(</p>)!si", 
          "!(</form>)!si", 
          "!(</textarea>)!si", 
          "!(</blockquote>)!si", 
		  "!(\[/cut\])!si", 
          //"/<!--\/notypo-->/si", // ??? obvious
          ),
      array( // wronginators
          "!(<td[^>]*>)!si", 
          "!(<li[^>]*>)!is", 
          )
                 );
   var $mark1 = "\200"; // <-t>
   var $mark2 = "\201"; // <t->
   var $mark3 = "\199"; // (*) wronginator mark: в конструкциях вида <t->(*).....<-t> & vice versa -- параграфы не ставятся
   /*
   var $mark1 = "<-t>"; // <-t>
   var $mark2 = "<=t>"; // <t->
   */

   var $prefix1 = '<p class="auto" id="p';  
   var $prefix2 = '">';
   var $postfix = '</p>';
//   var $prefix  = '<+t>';
//   var $postfix = '<-t>';

   function paragrafica( &$wacko )
   { $this->wacko = &$wacko; }

   function correct( $what )
   {
      // -2. игнорируем ещё регексп
      $ignored = array();
      {
        $total = preg_match_all($this->ignore, $what, $matches);
        $what = preg_replace($this->ignore, "\202", $what);
        for ($i=0;$i<$total;$i++)
        {
          $ignored[] = $matches[0][$i];
        }
      }

     $page_id = $this->wacko->page["id"];
     if (!$page_id) $page_id = substr(crc32(time()),0,5);
  // 1. insert terminators appropriately
     foreach ($this->t0 as $t)
       $what = preg_replace( $t, $this->mark1."$1".$this->mark2, $what );
     foreach ($this->t1[0] as $t)
       $what = preg_replace( $t, $this->mark1."$1", $what );
     foreach ($this->t2[0] as $t)
       $what = preg_replace( $t, "$1".$this->mark2, $what );
     foreach ($this->t1[1] as $t)
       $what = preg_replace( $t, $this->mark3.$this->mark1."$1", $what );
     foreach ($this->t2[1] as $t)
       $what = preg_replace( $t, "$1".$this->mark2.$this->mark3, $what );
     $what = $this->mark2.$what.$this->mark1;
  // 2bis. swap <t-><br /> -> <br /><t->
     $what = preg_replace( "!(".$this->mark2.")((\s*<br[^>]*>)+)!si", "$2$1", $what );
    //noneedin: 
     $what = preg_replace( "!((<br[^>]*>\s*)+)(".$this->mark1.")!s", "$3", $what );
  // 2. cleanup <t->\s<-t>
     do 
     {
      $_w = $what;
      $what = preg_replace( "!(".$this->mark2.")((\s|(<br[^>]*>))*)(".$this->mark1.")!si", "$2", $what );
     }
     while ($_w != $what);

  // 3. replace each <t->....<-t> to <p class="auto">....</p>
     $pcount = 0;
     $pieces = explode( $this->mark2, $what );
     foreach( $pieces as $k=>$v )
     if ($k > 0)
     { $pos = strpos($v, $this->mark1);
       $pos2 = strpos($v, $this->mark3);
       if ($pos !== false)
       if ($pos2 === false)
       {
         $pcount++;
         $pieces[$k] = '<a name="p'.$page_id.'-'.$pcount.'"></a>'.
                       $this->prefix1.
                       $page_id.'-'.$pcount.
                       $this->prefix2.
//                       '<poloskuns1-'.$page_id.'-'.$pcount.'/>'.
                       substr($v, 0, $pos).
//                       '<poloskuns2-'.$page_id.'-'.$pcount.'/>'.
                       $this->postfix.substr($v,$pos+1);
       }
     }
     $what = implode("", $pieces);
  // 4. remove unused <t-> & <-t>
     $what = str_replace( $this->mark1, "", $what );
     $what = str_replace( $this->mark2, "", $what );
     $what = str_replace( $this->mark3, "", $what );
  // -. done with P
  // ==================================================================
  // Forming body_toc 
  //  * in wacko formatter we have done "#h1249_1"
  //  * right here we have done         "#p1249_1"
  // 1. get all ^^ of this
     $this->toc = array();
     $what = preg_replace_callback( "!".
         "(<a name=\"(h[0-9]+-[0-9]+)\"></a><h([0-9])>(.*?)</h\\3>)". // 2=id, 3=depth, 4=name
                                    "|".
         "(<a name=\"(p[0-9]+-[0-9]+)\"></a>)".                       // 6=id
                                    "|".
         "\xA1\xA1include\s+[^=]+=([^\xA1 ]+)(\s+notoc=\"?[^0]\")?.*?\xA1\xA1". 
                                                                      // {{include xxxx="TAG" notoc="1"}}
                                    "!si", array( &$this, "add_toc_entry" ), $what );


       // БЕСКОНЕЧНОСТЬ-2. вставляем ещё сигнорированный регексп
       {
         $what .= " ";
         $a = explode( "\202", $what );
         if ($a)
         {
           $what = $a[0];
           $size = count($a);
           for ($i=1; $i<$size; $i++)
           {
            $what= $what.$ignored[$i-1].$a[$i];
           }
         }
       }
	
	 $what = str_replace("<br />","\n",$what);
     $what = str_replace("---","<br />",$what);
     return $what;
   }

  function add_toc_entry($matches)
  {
    if ($matches[7] != "")
    {
     if ($matches[8] == "")
      $this->toc[] = array($this->wacko->UnwrapLink(trim($matches[7],'"')),"(include)",99999);
    }
    else
                    // id, text, depth 
    if ($matches[6] != "")
     $this->toc[] = array($matches[6],"(p)",77777);
    else
     $this->toc[] = array($matches[2],$matches[4],$matches[3]);
    return $matches[0];
  }
 }



?>